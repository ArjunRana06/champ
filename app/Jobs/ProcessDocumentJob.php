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

    public $timeout = 36000;
    public $tries = 3;
    public $maxExceptions = 3;
    public $backoff = 60;

    protected $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function handle()
    {
        Log::info('Processing document: ' . $this->document->id);

        try {
            $this->document->update(['status' => 'processing']);
            $this->updateProgress(5, 'Starting processing...');

            // Remove any previously stored chunks (embeddings cascade via FK),
            // so reprocessing/retries never duplicate or misalign data.
            DocumentChunk::where('document_id', $this->document->id)->delete();

            $this->updateProgress(10, 'Extracting text...');
            $fullText = $this->extractText();

            if (empty(trim($fullText))) {
                throw new \Exception('No text could be extracted from the file. It may be empty, contain only images without readable text, or use an unsupported format.');
            }

            $this->document->update(['extracted_text' => $fullText]);
            $this->updateProgress(30, 'Text extracted, splitting into chunks...');

            $textLength = strlen($fullText);
            $chunkSize = $textLength > 500000 ? 1500 : 1000;
            $overlap = $textLength > 500000 ? 150 : 200;

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

            // Retrieve the inserted chunks to get their IDs using cursor for memory efficiency
            // Order by chunk_index then id so embedding alignment is always deterministic.
            $embeddingIndex = 0;
            DocumentChunk::where('document_id', $this->document->id)
                ->orderBy('chunk_index')
                ->orderBy('id')
                ->chunk(100, function ($insertedChunks) use (&$embeddingRecords, &$embeddingIndex, $embeddings, $totalChunks, $now) {
                    foreach ($insertedChunks as $chunkModel) {
                        $embeddingRecords[] = [
                            'document_chunk_id' => $chunkModel->id,
                            'embedding' => json_encode($embeddings[$embeddingIndex] ?? []),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        if (count($embeddingRecords) >= 50) {
                            Embedding::insert($embeddingRecords);
                            $embeddingRecords = [];
                        }

                        $embeddingIndex++;
                    }
                });

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
                'processing_message' => $this->userFacingMessage($e),
            ]);
            app(NotificationService::class)->notifyDocumentFailed(
                $this->document->user_id,
                $this->document->original_name
            );
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
            throw new \Exception('File not found for document: ' . $this->document->id);
        }

        $mime = $this->document->mime_type;

        // Some servers report Office docs as application/zip or application/octet-stream.
        // Detect the real type from the file contents before deciding how to extract.
        if (in_array($mime, ['application/octet-stream', 'application/zip', '', null], true)) {
            $office = $this->detectZipOfficeType($path);
            if ($office === 'docx') {
                return $this->extractWordText($path, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            }
            if ($office === 'pptx') {
                return $this->extractPptText($path);
            }
            if ($office === 'xlsx') {
                return $this->extractSpreadsheetText($path);
            }

            $detected = @(new \finfo(FILEINFO_MIME_TYPE))->file($path);
            if ($detected && !in_array($detected, ['application/octet-stream', 'application/zip'], true)) {
                $mime = $detected;
            }
        }

        try {
            switch (true) {
                case $mime === 'application/pdf':
                    return $this->extractPdfText($path);

                case in_array($mime, [
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/msword',
                ], true):
                    return $this->extractWordText($path, $mime);

                case in_array($mime, [
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'application/vnd.ms-powerpoint',
                ], true):
                    return $this->extractPptText($path);

                case in_array($mime, [
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel',
                ], true):
                    return $this->extractSpreadsheetText($path);

                case in_array($mime, [
                    'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp',
                ], true):
                    return $this->extractImageText($path);

                case $mime === 'text/plain':
                case $mime === 'text/csv':
                    $text = file_get_contents($path);
                    if ($text === false) {
                        throw new \Exception('Could not read the file.');
                    }
                    return $text;

                case $mime === 'text/rtf':
                    return $this->extractRtfText($path);

                case $mime === 'text/html':
                    return $this->extractHtmlText($path);

                default:
                    $text = @file_get_contents($path);
                    if ($text !== false && !empty(trim($text)) && !$this->isBinary($text)) {
                        return $text;
                    }
                    throw new \Exception('No text could be extracted from this file type (' . $mime . '). Please convert it to a supported format (PDF, DOCX, PPTX, XLSX, TXT) and try again.');
            }

        } catch (\Exception $e) {
            Log::error('Extraction error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function detectZipOfficeType(string $path): ?string
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return null;
        }

        if ($zip->locateName('[Content_Types].xml') === false) {
            $zip->close();
            return null;
        }

        $type = null;
        if ($zip->locateName('word/document.xml') !== false) {
            $type = 'docx';
        } elseif ($zip->locateName('ppt/presentation.xml') !== false) {
            $type = 'pptx';
        } elseif ($zip->locateName('xl/workbook.xml') !== false) {
            $type = 'xlsx';
        }
        $zip->close();

        return $type;
    }

    private function isBinary(string $text): bool
    {
        return str_contains(substr($text, 0, 8192), "\0");
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
        // Legacy binary .doc cannot be read by PhpWord; try CLI extractors if available.
        if ($mime === 'application/msword') {
            $text = $this->runCliExtractor('antiword', $path);
            if ($text !== null) {
                return $text;
            }

            $text = $this->runCliExtractor('catdoc', $path);
            if ($text !== null) {
                return $text;
            }

            throw new \Exception('Legacy .doc files are not supported on this server. Please convert to .docx or PDF and upload again.');
        }

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

    private function runCliExtractor(string $binary, string $path): ?string
    {
        $output = null;
        $returnCode = null;
        exec('command -v ' . escapeshellarg($binary) . ' > /dev/null 2>&1', $output, $returnCode);
        if ($returnCode !== 0) {
            return null;
        }

        $output = null;
        $returnCode = null;
        exec(escapeshellarg($binary) . ' ' . escapeshellarg($path) . ' 2>/dev/null', $output, $returnCode);
        if ($returnCode !== 0) {
            return null;
        }

        $text = trim(implode("\n", $output));
        return $text === '' ? null : $text;
    }

    private function extractPptText(string $path): string
    {
        // .pptx is a zip archive; extract the slide XML directly.
        $zip = new \ZipArchive();
        if ($zip->open($path) === true) {
            $text = '';
            $slideIndex = 1;
            while (($slide = $zip->getFromName("ppt/slides/slide{$slideIndex}.xml")) !== false) {
                $text .= strip_tags(str_replace(['<a:t>', '</a:t>'], ['', ' '], $slide)) . "\n";
                $slideIndex++;
                if ($slideIndex > 200) {
                    break;
                }
            }
            $zip->close();

            if (!empty(trim($text))) {
                return $text;
            }
        }

        // Legacy binary .ppt is not a zip; try catppt if available.
        $text = $this->runCliExtractor('catppt', $path);
        if ($text !== null) {
            return $text;
        }

        throw new \Exception('Legacy .ppt files are not supported on this server. Please convert to .pptx or PDF and upload again.');
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

    private function extractHtmlText(string $path): string
    {
        $html = file_get_contents($path);
        if ($html === false) {
            throw new \Exception('Could not read the HTML file.');
        }

        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', ' ', $html);
        $html = preg_replace('#<style\b[^>]*>.*?</style>#is', ' ', $html);

        return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function extractRtfText(string $path): string
    {
        $rtf = file_get_contents($path);
        if ($rtf === false) {
            throw new \Exception('Could not read the RTF file.');
        }

        // Drop "ignore" destination groups such as {\*\fonttbl ...}
        $rtf = preg_replace('/\{\\\*[^}]*\}/s', '', $rtf);
        // Drop the main non-text destination groups
        $rtf = preg_replace('/\\{\\\\(?:info|stylesheet|fonttbl|colortbl|listtable|listoverridetable|generator|pict)[^}]*\\}/is', '', $rtf);

        // Convert paragraph / line / row breaks and tabs
        $rtf = str_ireplace(['\\par', '\\line', '\\row'], "\n", $rtf);
        $rtf = str_ireplace(['\\tab', '\\cell'], "\t", $rtf);
        $rtf = str_ireplace(['\\emdash', '\\endash', '\\bullet', '\\~', '\\-', '\\_'], ['—', '–', '•', ' ', '', ' '], $rtf);

        // Decode \'hh hex escapes (single-byte ANSI / Windows-1252 codepage)
        $rtf = preg_replace_callback("/\\\\'([0-9a-fA-F]{2})/", function ($m) {
            return mb_convert_encoding(chr(hexdec($m[1])), 'UTF-8', 'Windows-1252');
        }, $rtf);

        // Decode \uN unicode escapes (dropping the fallback marker)
        $rtf = preg_replace_callback('/\\\\u(-?\d+)(\??)/', function ($m) {
            $code = (int) $m[1];
            if ($code < 0) {
                return '';
            }
            return html_entity_decode('&#' . $code . ';', ENT_QUOTES, 'UTF-8');
        }, $rtf);

        // Strip remaining control words and symbols
        $rtf = preg_replace('/\\\\[a-zA-Z]+-?\d* ?/', '', $rtf);
        $rtf = preg_replace('/\\\\[`\'{}\\]|[*~\\\\]/', '', $rtf);

        // Collapse braces and normalise whitespace
        $rtf = str_replace(['{', '}'], '', $rtf);
        $rtf = preg_replace('/[ \t]+/', ' ', $rtf);
        $rtf = preg_replace('/\n{3,}/', "\n\n", $rtf);

        return trim($rtf);
    }

    private function userFacingMessage(\Exception $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'No text could be extracted')) {
            return 'No text could be extracted from this file. It may be empty, contain only images, or use an unsupported format. Please upload a supported file (PDF, DOCX, PPTX, XLSX, TXT, CSV, RTF, or an image).';
        }
        if (str_contains($message, 'File not found')) {
            return 'The file is missing from storage. Please upload the document again.';
        }
        if (str_contains($message, 'Legacy .doc') || str_contains($message, 'Legacy .ppt')) {
            return $message;
        }
        if (str_contains($message, 'Embedding API is not configured')) {
            return $message;
        }

        return 'Processing failed. Please try uploading the document again.';
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
        $models = $this->embeddingModels();

        $lastError = null;
        foreach ($models as $model) {
            try {
                return $this->generateEmbeddingsWithModel($texts, $model);
            } catch (\Exception $e) {
                $lastError = $e;
                Log::warning("Embedding model {$model} failed, trying next model: " . $e->getMessage());
            }
        }

        throw new \Exception('All embedding models failed. ' . ($lastError ? $lastError->getMessage() : 'The embedding API may be unreachable or out of credits.'));
    }

    private function embeddingModels(): array
    {
        $primary = config('services.openrouter.embedding_model');
        $fallbacks = config('services.openrouter.embedding_fallback_models', []);

        $models = array_values(array_unique(array_filter(array_merge([$primary], $fallbacks))));
        if (empty($models)) {
            throw new \Exception('Embedding API is not configured. Please set the OpenRouter embedding model in the admin settings.');
        }

        return $models;
    }

    private function generateEmbeddingsWithModel(array $texts, string $model): array
    {
        $apiKey = config('services.openrouter.api_key');
        if (empty($apiKey)) {
            throw new \Exception('Embedding API is not configured. Please set the OpenRouter API key in the admin settings.');
        }

        $maxRetries = 3;

        $batchSize = 50;
        $allEmbeddings = [];
        $retryDelay = 2;
        $totalChunks = count($texts);
        $processedChunks = 0;

        $chunksToProcess = $texts;

        while (!empty($chunksToProcess)) {
            $batch = array_splice($chunksToProcess, 0, $batchSize);

            $attempt = 0;
            $success = false;

            while (!$success) {
                try {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type' => 'application/json',
                    ])->timeout(120)->post('https://openrouter.ai/api/v1/embeddings', [
                        'model' => $model,
                        'input' => $batch,
                    ]);

                    if ($response->failed()) {
                        $status = $response->status();

                        if ($status === 402) {
                            // Token limit exceeded: halve the batch and retry the same
                            // chunks, but never loop forever on an irreducible batch.
                            $newBatchSize = max(1, intval($batchSize / 2));
                            if ($newBatchSize === $batchSize) {
                                throw new \Exception('Embedding request was rejected for exceeding the token limit even with a single chunk (HTTP 402). The chunk size for this document may be too large for the embedding model.');
                            }
                            $batchSize = $newBatchSize;
                            $chunksToProcess = array_merge($batch, $chunksToProcess);
                            Log::warning("Embedding token limit exceeded, reducing batch size to {$batchSize}");
                            break;
                        }

                        if (in_array($status, [429, 502, 503, 504]) && $attempt < $maxRetries) {
                            $wait = $retryDelay * pow(2, $attempt);
                            Log::warning("Embedding batch got {$status}, retrying in {$wait}s...", ['attempt' => $attempt]);
                            sleep($wait);
                            $attempt++;
                            continue;
                        }

                        throw new \Exception("Embedding API error (HTTP {$status}): " . mb_substr($response->body(), 0, 300));
                    }

                    $data = $response->json();
                    if (!isset($data['data']) || count($data['data']) !== count($batch)) {
                        throw new \Exception('Invalid embedding batch response');
                    }

                    $sorted = [];
                    foreach ($data['data'] as $item) {
                        if (isset($item['index'], $item['embedding']) && is_array($item['embedding'])) {
                            $sorted[$item['index']] = $item['embedding'];
                        }
                    }
                    for ($i = 0; $i < count($batch); $i++) {
                        if (!isset($sorted[$i])) {
                            throw new \Exception('Embedding batch response is missing index ' . $i);
                        }
                        $allEmbeddings[] = $sorted[$i];
                    }
                    $processedChunks += count($batch);
                    $pct = 40 + round(($processedChunks / $totalChunks) * 30);
                    $this->updateProgress($pct, "Embeddings: {$processedChunks} of {$totalChunks} chunks");
                    $success = true;

                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    if ($attempt < $maxRetries) {
                        $wait = $retryDelay * pow(2, $attempt);
                        Log::warning("Embedding batch timeout, retrying in {$wait}s...", ['attempt' => $attempt]);
                        sleep($wait);
                        $attempt++;
                        continue;
                    }
                    throw new \Exception('Embedding API connection timeout: ' . $e->getMessage());
                }
            }
        }

        return $allEmbeddings;
    }
}
