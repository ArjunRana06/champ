<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\DocumentSummary;

class SummarizationService
{
    protected AiService $ai;

    public function __construct(AiService $ai)
    {
        $this->ai = $ai;
    }

    public function summarize(Document $document): DocumentSummary
    {
        $chunks = DocumentChunk::where('document_id', $document->id)
            ->orderBy('id')
            ->get();

        if ($chunks->isEmpty()) {
            throw new \Exception('No processed content found for this document. Make sure it has finished processing.');
        }

        $fullText = '';
        foreach ($chunks as $chunk) {
            $fullText .= $chunk->content . "\n\n";
        }

        $maxChars = 24000;
        if (strlen($fullText) > $maxChars) {
            $fullText = substr($fullText, 0, $maxChars) . "\n\n[... content truncated ...]";
        }

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an expert study assistant. Summarize the following study material in a clear, structured way. Provide:
1. A brief overview (2-3 sentences)
2. Key concepts and main ideas (bullet points)
3. Important definitions or formulas (if any)
4. Key takeaways

Use clear formatting with markdown bullet points and short paragraphs. Be concise but thorough.'
            ],
            [
                'role' => 'user',
                'content' => "Please summarize the following study material:\n\n" . $fullText
            ]
        ];

        $summary = $this->ai->chat($messages, null, 0.3, 4096);

        return DocumentSummary::updateOrCreate(
            [
                'user_id' => $document->user_id,
                'document_id' => $document->id,
            ],
            [
                'summary' => $summary,
                'model_used' => config('services.openrouter.model'),
            ]
        );
    }
}
