<?php

namespace App\Services;

use App\Traits\HasRelevantChunks;

class McqGeneratorService
{
    use HasRelevantChunks;

    protected AiService $ai;

    public function __construct(AiService $ai)
    {
        $this->ai = $ai;
    }

    public function generateMcqs(array $chunks, int $count, string $difficulty = 'medium'): array
    {
        $context = implode("\n\n---\n\n", $chunks);

        $messages = [
            ['role' => 'system', 'content' => 'You are an expert educator that generates high-quality multiple-choice questions. Think step by step: analyze the material, identify key concepts, then create MCQs that test real understanding with plausible distractors.'],
            ['role' => 'user', 'content' => $this->buildMcqPrompt($context, $count, $difficulty)],
        ];

        return $this->ai->generateJson($messages, null, 0.3, 4096);
    }

    private function buildMcqPrompt(string $context, int $count, string $difficulty): string
    {
        $prompt = "You are an expert educator. Based **only** on the following study material, generate $count multiple-choice questions with difficulty level '$difficulty'.\n\n";
        $prompt .= "First, identify the key concepts. Then for each concept, create a question with:\n";
        $prompt .= "- question: text (clear and specific)\n";
        $prompt .= "- options: array of 4 strings (A, B, C, D) — distractors should be plausible but clearly wrong\n";
        $prompt .= "- correct_answer: the exact option letter (e.g., 'A')\n";
        $prompt .= "- explanation: educational reason why that answer is correct (2-3 sentences)\n";
        $prompt .= "- source_concept: the key concept being tested\n\n";
        $prompt .= "Example:\n";
        $prompt .= '{"question": "What is the capital of France?", "options": ["A. London", "B. Paris", "C. Berlin", "D. Madrid"], "correct_answer": "B", "explanation": "Paris is the capital and most populous city of France, located on the Seine River.", "source_concept": "European Geography - Capital Cities"}' . "\n\n";
        $prompt .= "Return ONLY valid JSON array (no extra text, no markdown).\n\n";
        $prompt .= "Study material:\n$context";

        return $prompt;
    }
}
