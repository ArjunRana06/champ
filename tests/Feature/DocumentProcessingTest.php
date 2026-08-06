<?php

namespace Tests\Feature;

use App\Jobs\ProcessDocumentJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Embedding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentProcessingTest extends TestCase
{
    use RefreshDatabase;

    private function makeDocument(): Document
    {
        $user = User::factory()->create();

        return Document::create([
            'user_id' => $user->id,
            'original_name' => 'notes.txt',
            'file_path' => 'documents/notes.txt',
            'mime_type' => 'text/plain',
            'status' => 'completed',
        ]);
    }

    private function fakeEmbeddingApi(): void
    {
        Http::fake([
            'openrouter.ai/api/v1/embeddings' => function (\Illuminate\Http\Client\Request $request) {
                $inputs = $request['input'];
                $data = [];
                foreach ($inputs as $index => $text) {
                    $data[] = ['index' => $index, 'embedding' => [$index + 0.1, 0.2, 0.3]];
                }
                return Http::response(['data' => $data]);
            },
        ]);
    }

    public function test_reprocessing_deletes_previous_chunks_and_embeddings(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('documents/notes.txt', "First paragraph.\n\nSecond paragraph.\n\nThird paragraph.");

        $this->fakeEmbeddingApi();

        $document = $this->makeDocument();

        $job = new ProcessDocumentJob($document);
        $job->handle();

        $this->assertDatabaseHas('documents', ['id' => $document->id, 'status' => 'completed']);

        $chunkCountBefore = DocumentChunk::where('document_id', $document->id)->count();
        $this->assertGreaterThanOrEqual(1, $chunkCountBefore);
        $this->assertSame($chunkCountBefore, Embedding::whereHas('chunk', fn ($q) => $q->where('document_id', $document->id))->count());

        // Simulate a retry with different content.
        Storage::disk('public')->put('documents/notes.txt', 'New first paragraph.' . "\n\n" . 'New second paragraph.');

        $retryJob = new ProcessDocumentJob($document);
        $retryJob->handle();

        // Old chunks must be removed before reprocessing: count must not grow,
        // and the stored content must reflect the new file.
        $this->assertSame($chunkCountBefore, DocumentChunk::where('document_id', $document->id)->count(), 'Old chunks must be removed before reprocessing.');
        $this->assertSame($chunkCountBefore, Embedding::whereHas('chunk', fn ($q) => $q->where('document_id', $document->id))->count());

        $contents = DocumentChunk::where('document_id', $document->id)
            ->orderBy('chunk_index')
            ->pluck('content')
            ->map(fn ($c) => str_replace("\n\n", ' ', $c))
            ->toArray();
        $this->assertStringContainsString('New first paragraph', implode(' ', $contents));
        $this->assertStringNotContainsString('First paragraph', implode(' ', $contents));
    }

    public function test_unsupported_file_type_is_rejected_with_clear_message(): void
    {
        $user = User::factory()->create();

        Storage::fake('public');
        $this->actingAs($user);

        $response = $this->postJson(route('documents.upload'), [
            'document' => \Illuminate\Http\UploadedFile::fake()->create('notes.exe', 10, 'application/x-msdownload'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error', 'This file type is not supported. Supported formats: PDF, Word (.doc/.docx), PowerPoint (.ppt/.pptx), Excel (.xls/.xlsx), CSV, TXT, RTF, and images (JPG, PNG, GIF, BMP, WEBP).');
    }

    public function test_failed_embedding_api_marks_document_failed(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('documents/notes.txt', 'Some text content.');

        Http::fake([
            'openrouter.ai/api/v1/embeddings' => Http::response('', 500),
        ]);

        $document = $this->makeDocument();

        try {
            $job = new ProcessDocumentJob($document);
            $job->handle();
            $this->fail('Job should have thrown.');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        $this->assertDatabaseHas('documents', ['id' => $document->id, 'status' => 'failed']);
    }
}
