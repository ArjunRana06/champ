<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Embedding;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory as WordFactory;

class ProcessDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    // Increase timeout for large documents (10 minutes)
    public $timeout = 600;

    protected $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function handle()
    {
        Log::info('Processing document: ' . $this->document->id);
        $this->document->update(['status' => 'processing']);

        try {
            // 1. Extract full text
            $fullText = $this->extractText();
            Log::info('Extracted text length: ' . strlen($fullText));

            if (empty(trim($fullText))) {
                throw new \Exception('No text could be extracted from the file.');
            }

            // 2. Store full extracted text
            $this->document->update(['extracted_text' => $fullText]);

            // 3. Split into chunks (larger = fewer chunks)
            $chunks = $this->splitIntoChunks($fullText, 2000, 300);
            $totalChunks = count($chunks);
            Log::info("Number of chunks: $totalChunks");

            // 4. Generate embeddings for all chunks in ONE request (batching)
            $embeddings = $this->generateEmbeddingsBatch($chunks);
            if (count($embeddings) !== $totalChunks) {
                throw new \Exception('Embedding response size mismatch');
            }

            // 5. Store chunks and embeddings
            foreach ($chunks as $index => $chunkText) {
                $chunk = DocumentChunk::create([
                    'document_id' => $this->document->id,
                    'chunk_index' => $index,
                    'content' => $chunkText,
                    'vector_id' => null,
                ]);

                Embedding::create([
                    'document_chunk_id' => $chunk->id,
                    'embedding' => json_encode($embeddings[$index]),
                ]);
            }

            $this->document->update([
                'status' => 'completed',
                'total_chunks' => $totalChunks,
            ]);

            Log::info('Document processed successfully: ' . $this->document->id);
        } catch (\Exception $e) {
            Log::error('Document processing failed: ' . $e->getMessage());
            $this->document->update(['status' => 'failed']);
            throw $e;
        }
    }

    private function extractText(): string
    {
        $path = Storage::disk('public')->path($this->document->file_path);
        if (!file_exists($path)) {
            throw new \Exception('File not found: ' . $path);
        }

        $mime = $this->document->mime_type;

        if ($mime === 'application/pdf') {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            return $pdf->getText();
        }

        if ($mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            $phpWord = WordFactory::load($path);
            $text = '';
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    }
                }
            }
            return $text;
        }

        if ($mime === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') {
            $zip = new \ZipArchive();
            $text = '';
            if ($zip->open($path) === true) {
                $slideIndex = 1;
                while (($slide = $zip->getFromName("ppt/slides/slide{$slideIndex}.xml")) !== false) {
                    $text .= strip_tags(str_replace(['<a:t>', '</a:t>'], ['', ' '], $slide)) . "\n";
                    $slideIndex++;
                }
                $zip->close();
            }
            return $text;
        }

        if (in_array($mime, ['image/jpeg', 'image/png'])) {
            return (new TesseractOCR($path))->run();
        }

        if ($mime === 'text/plain') {
            return file_get_contents($path);
        }

        return '';
    }

    private function splitIntoChunks(string $text, int $chunkSize = 2000, int $overlap = 300): array
    {
        $paragraphs = preg_split('/\n\s*\n/', $text);
        $chunks = [];
        $currentChunk = '';

        foreach ($paragraphs as $para) {
            $para = trim($para);
            if (empty($para)) continue;

            if (strlen($currentChunk) + strlen($para) <= $chunkSize) {
                $currentChunk .= $para . "\n\n";
            } else {
                if (!empty($currentChunk)) {
                    $chunks[] = trim($currentChunk);
                }
                $overlapText = substr($currentChunk, -$overlap);
                $currentChunk = $overlapText . "\n\n" . $para . "\n\n";
            }
        }
        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }
        return $chunks;
    }

    /**
     * Generate embeddings for multiple texts in one API call.
     * Falls back to individual calls if batch fails or the array is too large.
     */
    private function generateEmbeddingsBatch(array $texts): array
    {
        $apiKey = env('OPENROUTER_API_KEY');
        $model = env('OPENROUTER_EMBEDDING_MODEL', 'openai/text-embedding-3-small');

        // OpenRouter supports up to 2048 inputs per request, but some providers have lower limits.
        // Split into batches of 100 to be safe.
        $batches = array_chunk($texts, 100);
        $allEmbeddings = [];

        foreach ($batches as $batch) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://openrouter.ai/api/v1/embeddings', [
                'model' => $model,
                'input' => $batch,
            ]);

            if ($response->failed()) {
                throw new \Exception('Embedding batch API error: ' . $response->body());
            }

            $data = $response->json();
            if (!isset($data['data']) || count($data['data']) !== count($batch)) {
                throw new \Exception('Invalid embedding batch response');
            }

            // Sort by index (OpenRouter returns in same order)
            $sorted = [];
            foreach ($data['data'] as $item) {
                $sorted[$item['index']] = $item['embedding'];
            }
            for ($i = 0; $i < count($batch); $i++) {
                $allEmbeddings[] = $sorted[$i];
            }
        }

        return $allEmbeddings;
    }
}
