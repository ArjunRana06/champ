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
use App\Services\NotificationService;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory as WordFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetFactory;

class ProcessDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $timeout = 1800;
    public $maxExceptions = 1;

    protected $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function handle()
    {
        Log::info('Processing document: ' . $this->document->id);
        $this->updateProgress(5, 'Starting processing...');

        try {
            $this->updateProgress(10, 'Extracting text...');
            $fullText = $this->extractText();

            if (empty(trim($fullText))) {
                throw new \Exception('No text could be extracted from the file.');
            }

            $this->document->update(['extracted_text' => $fullText]);
            $this->updateProgress(30, 'Text extracted, splitting into chunks...');

            $textLength = strlen($fullText);
            $chunkSize = $textLength > 500000 ? 3000 : 2000;
            $overlap = $textLength > 500000 ? 200 : 300;

            $chunks = $this->splitIntoChunks($fullText, $chunkSize, $overlap);
            $totalChunks = count($chunks);
            Log::info("Number of chunks: $totalChunks");

            $this->updateProgress(40, "Generating embeddings ($totalChunks chunks)...");

            $embeddings = $this->generateEmbeddingsBatch($chunks);
            if (count($embeddings) !== $totalChunks) {
                throw new \Exception('Embedding response size mismatch');
            }

            $this->updateProgress(70, 'Storing chunks and embeddings...');

            $chunkRecords = [];
            $embeddingRecords = [];
            $now = now();

            foreach ($chunks as $index => $chunkText) {
                $chunkRecords[] = [
                    'document_id' => $this->document->id,
                    'chunk_index' => $index,
                    'content' => $chunkText,
                    'vector_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Batch insert chunks
            foreach (array_chunk($chunkRecords, 50) as $batch) {
                DocumentChunk::insert($batch);
            }

            // Retrieve the inserted chunks to get their IDs
            $insertedChunks = DocumentChunk::where('document_id', $this->document->id)
                ->orderBy('chunk_index')
                ->get();

            foreach ($insertedChunks as $index => $chunkModel) {
                $embeddingRecords[] = [
                    'document_chunk_id' => $chunkModel->id,
                    'embedding' => json_encode($embeddings[$index] ?? []),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($embeddingRecords) >= 50) {
                    Embedding::insert($embeddingRecords);
                    $embeddingRecords = [];
                }

                $pct = 70 + round((($index + 1) / $totalChunks) * 25);
                $this->updateProgress($pct, "Stored chunk $index of $totalChunks");
            }

            if (!empty($embeddingRecords)) {
                Embedding::insert($embeddingRecords);
            }

            $this->document->update([
                'status' => 'completed',
                'total_chunks' => $totalChunks,
                'processing_progress' => 100,
                'processing_message' => 'Completed',
            ]);

            app(NotificationService::class)->notifyDocumentProcessed(
                $this->document->user_id,
                $this->document->original_name,
                $this->document->id
            );

            Log::info('Document processed successfully: ' . $this->document->id);
        } catch (\Exception $e) {
            Log::error('Document processing failed: ' . $e->getMessage());
            $this->document->update([
                'status' => 'failed',
                'processing_message' => 'Error: ' . $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function updateProgress(int $progress, string $message): void
    {
        try {
            $this->document->update([
                'processing_progress' => $progress,
                'processing_message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to update progress: ' . $e->getMessage());
        }
    }

    private function extractText(): string
    {
        $path = Storage::disk('public')->path($this->document->file_path);
        if (!file_exists($path)) {
            throw new \Exception('File not found: ' . $path);
        }

        $mime = $this->document->mime_type;

        try {
            if ($mime === 'application/pdf') {
                return $this->extractPdfText($path);
            }

            if (in_array($mime, [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword',
            ])) {
                return $this->extractWordText($path, $mime);
            }

            if (in_array($mime, [
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.ms-powerpoint',
            ])) {
                return $this->extractPptText($path);
            }

            if (in_array($mime, [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel',
            ])) {
                return $this->extractSpreadsheetText($path);
            }

            if (in_array($mime, [
                'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp',
            ])) {
                return $this->extractImageText($path);
            }

            if ($mime === 'text/plain' || $mime === 'text/csv' || $mime === 'text/rtf') {
                return file_get_contents($path);
            }

            $ext = strtolower(pathinfo($this->document->original_name, PATHINFO_EXTENSION));
            if (in_array($ext, ['txt', 'csv'])) {
                return file_get_contents($path);
            }
            if ($ext === 'rtf') {
                return file_get_contents($path);
            }

            throw new \Exception('Unsupported file type: ' . $mime);

        } catch (\Exception $e) {
            Log::error('Extraction error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function extractPdfText(string $path): string
    {
        $tmpPath = $path . '.tmp.txt';
        $escapedPath = escapeshellarg($path);
        $escapedTmp = escapeshellarg($tmpPath);
        $cmd = "pdftotext -layout $escapedPath $escapedTmp 2>&1";
        $output = null;
        $returnCode = null;
        exec($cmd, $output, $returnCode);

        if ($returnCode === 0 && file_exists($tmpPath)) {
            $text = file_get_contents($tmpPath);
            @unlink($tmpPath);
            if (!empty(trim($text))) {
                return $text;
            }
        }

        $parser = new Parser();
        $pdf = $parser->parseFile($path);
        return $pdf->getText();
    }

    private function extractWordText(string $path, string $mime): string
    {
        $phpWord = WordFactory::load($path);
        $text = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                }
                if (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $child) {
                        if (method_exists($child, 'getText')) {
                            $text .= $child->getText() . "\n";
                        }
                    }
                }
            }
        }
        return $text;
    }

    private function extractPptText(string $path): string
    {
        if ($this->document->mime_type === 'application/vnd.ms-powerpoint') {
            $zip = new \ZipArchive();
            $text = '';
            if ($zip->open($path) === true) {
                for ($i = 1; $i <= 200; $i++) {
                    $slideXml = $zip->getFromName("ppt/slides/slide{$i}.xml");
                    if ($slideXml === false) break;
                    $xml = @simplexml_load_string($slideXml);
                    if ($xml) {
                        $xml->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
                        foreach ($xml->xpath('//a:t') as $t) {
                            $text .= (string)$t . ' ';
                        }
                        $text .= "\n";
                    }
                }
                $zip->close();
            }
            return $text;
        }

        try {
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
        } catch (\Exception $e) {
            return '';
        }
    }

    private function extractSpreadsheetText(string $path): string
    {
        try {
            $spreadsheet = SpreadsheetFactory::load($path);
            $text = '';
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = [];
                    foreach ($row->getCellIterator() as $cell) {
                        $val = $cell->getValue();
                        if ($val !== null) {
                            $cells[] = $val;
                        }
                    }
                    if (!empty($cells)) {
                        $text .= implode("\t", $cells) . "\n";
                    }
                }
                $text .= "\n--- Next Sheet ---\n\n";
            }
            return $text;
        } catch (\Exception $e) {
            Log::warning('Spreadsheet extraction failed: ' . $e->getMessage());
            return '';
        }
    }

    private function extractImageText(string $path): string
    {
        try {
            return (new \thiagoalessio\TesseractOCR\TesseractOCR($path))->run();
        } catch (\Exception $e) {
            Log::warning('OCR failed: ' . $e->getMessage());
            return '';
        }
    }

    private function splitIntoChunks(string $text, int $chunkSize = 2000, int $overlap = 300): array
    {
        // Split into paragraphs first
        $paragraphs = preg_split('/\n\s*\n/', $text);
        $paragraphs = array_map('trim', $paragraphs);
        $paragraphs = array_filter($paragraphs, fn($p) => !empty($p));
        $paragraphs = array_values($paragraphs);

        $chunks = [];
        $currentChunk = '';

        foreach ($paragraphs as $para) {
            // If a single paragraph exceeds chunkSize, split it by sentences
            if (mb_strlen($para) > $chunkSize) {
                if (!empty($currentChunk)) {
                    $chunks[] = trim($currentChunk);
                }
                $sentences = preg_split('/(?<=[.!?])\s+/', $para);
                $currentChunk = '';
                foreach ($sentences as $sentence) {
                    $sentence = trim($sentence);
                    if (empty($sentence)) continue;
                    if (mb_strlen($currentChunk) + mb_strlen($sentence) > $chunkSize) {
                        if (!empty($currentChunk)) {
                            $chunks[] = trim($currentChunk);
                        }
                        $currentChunk = $sentence . ' ';
                    } else {
                        $currentChunk .= $sentence . ' ';
                    }
                }
                continue;
            }

            // Normal case: add paragraph to current chunk
            if (empty($currentChunk)) {
                $currentChunk = $para . "\n\n";
            } elseif (mb_strlen($currentChunk) + mb_strlen($para) <= $chunkSize) {
                $currentChunk .= $para . "\n\n";
            } else {
                $chunks[] = trim($currentChunk);
                // Overlap: take last N characters up to the last sentence boundary
                $overlapText = $this->getSentenceOverlap($currentChunk, $overlap);
                $currentChunk = $overlapText . "\n\n" . $para . "\n\n";
            }
        }

        if (!empty(trim($currentChunk ?? ''))) {
            $chunks[] = trim($currentChunk);
        }

        // Fallback: if no chunks were created, split by characters
        if (empty($chunks)) {
            $chunks = [trim($text)];
        }

        return $chunks;
    }

    private function getSentenceOverlap(string $text, int $maxChars): string
    {
        if (mb_strlen($text) <= $maxChars) {
            return $text;
        }

        $tail = mb_substr($text, -$maxChars);
        // Try to find a sentence boundary in the tail
        $boundary = mb_strpos($tail, '. ');
        if ($boundary !== false) {
            return mb_substr($tail, $boundary + 2);
        }
        $boundary = mb_strpos($tail, "?\n");
        if ($boundary !== false) {
            return mb_substr($tail, $boundary + 2);
        }
        $boundary = mb_strpos($tail, "!\n");
        if ($boundary !== false) {
            return mb_substr($tail, $boundary + 2);
        }
        // Fallback: use newline
        $boundary = mb_strpos($tail, "\n");
        if ($boundary !== false) {
            return mb_substr($tail, $boundary + 1);
        }

        return $tail;
    }

    private function generateEmbeddingsBatch(array $texts): array
    {
        $apiKey = config('services.openrouter.api_key');
        $model = config('services.openrouter.embedding_model');

        $batches = array_chunk($texts, 50);
        $allEmbeddings = [];

        foreach ($batches as $batchIndex => $batch) {
            $maxRetries = 2;
            $retryDelay = 1;

            for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
                try {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type' => 'application/json',
                    ])->timeout(120)->post('https://openrouter.ai/api/v1/embeddings', [
                        'model' => $model,
                        'input' => $batch,
                    ]);

                    if ($response->failed()) {
                        if (in_array($response->status(), [429, 502, 503, 504]) && $attempt < $maxRetries) {
                            sleep($retryDelay * pow(2, $attempt));
                            continue;
                        }
                        throw new \Exception('Embedding batch API error: ' . $response->body());
                    }

                    $data = $response->json();
                    if (!isset($data['data']) || count($data['data']) !== count($batch)) {
                        throw new \Exception('Invalid embedding batch response');
                    }

                    $sorted = [];
                    foreach ($data['data'] as $item) {
                        $sorted[$item['index']] = $item['embedding'];
                    }
                    for ($i = 0; $i < count($batch); $i++) {
                        $allEmbeddings[] = $sorted[$i];
                    }
                    break;

                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    if ($attempt < $maxRetries) {
                        sleep($retryDelay * pow(2, $attempt));
                        continue;
                    }
                    throw new \Exception('Embedding API connection timeout: ' . $e->getMessage());
                }
            }

            $pct = 40 + round((($batchIndex + 1) / count($batches)) * 30);
            $this->updateProgress($pct, "Embeddings batch $batchIndex of " . count($batches));
        }

        return $allEmbeddings;
    }
}
