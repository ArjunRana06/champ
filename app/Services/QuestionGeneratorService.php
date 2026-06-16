<?php

namespace App\Services;

use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Auth;

class QuestionGeneratorService
{
    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function getRelevantChunks($subjectId, $documentId, $topic = null, $limit = 20): array
    {
        $userId = Auth::id();

        $query = DocumentChunk::whereHas('document', function ($q) use ($userId, $subjectId, $documentId) {
            $q->where('user_id', $userId)->where('status', 'completed');
            if ($subjectId) $q->where('subject_id', $subjectId);
            if ($documentId) $q->where('id', $documentId);
        });

        if ($topic) {
            $keywords = explode(' ', $topic);
            foreach ($keywords as $keyword) {
                if (strlen($keyword) > 2) {
                    $query->where('content', 'LIKE', '%' . addcslashes($keyword, '%_') . '%');
                }
            }
        }

        $chunks = $query->limit($limit)->get();

        if ($chunks->isEmpty()) {
            $query = DocumentChunk::whereHas('document', function ($q) use ($userId, $subjectId) {
                $q->where('user_id', $userId)->where('status', 'completed');
                if ($subjectId) $q->where('subject_id', $subjectId);
            })->limit($limit);
            $chunks = $query->get();
        }

        return $chunks->pluck('content')->toArray();
    }

    protected function buildPrompt(string $typeSpec, string $context, int $count, string $difficulty): array
    {
        $systemPrompt = 'You are a helpful assistant that generates educational questions in JSON format.';

        $userPrompt = "You are an expert educator. Based **only** on the following study material, generate $count $typeSpec with difficulty level '$difficulty'.\n\n";
        $userPrompt .= "Return ONLY valid JSON (no extra text, no markdown).\n\n";
        $userPrompt .= "Study material:\n$context";

        return [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];
    }

    public function generateTrueFalse(array $chunks, int $count, string $difficulty = 'medium'): array
    {
        $context = implode("\n\n---\n\n", $chunks);
        $typeSpec = "true/false questions.\n\nEach question must have:\n- statement: the statement to evaluate\n- correct_answer: true or false\n- explanation: short reason why the answer is correct";

        $format = "[\n  {\n    \"statement\": \"...\",\n    \"correct_answer\": true,\n    \"explanation\": \"...\"\n  }\n]";

        $prompt = $this->buildPrompt($typeSpec, $context, $count, $difficulty);
        $prompt[1]['content'] .= "\n\nFormat:\n$format";

        return $this->aiService->generateJson($prompt);
    }

    public function generateShortAnswers(array $chunks, int $count, string $difficulty = 'medium'): array
    {
        $context = implode("\n\n---\n\n", $chunks);
        $typeSpec = "short-answer questions.\n\nEach question must have:\n- question: the question text\n- expected_answer: the correct answer";

        $format = "[\n  {\n    \"question\": \"...\",\n    \"expected_answer\": \"...\"\n  }\n]";

        $prompt = $this->buildPrompt($typeSpec, $context, $count, $difficulty);
        $prompt[1]['content'] .= "\n\nFormat:\n$format";

        return $this->aiService->generateJson($prompt);
    }

    public function generateFillBlanks(array $chunks, int $count, string $difficulty = 'medium'): array
    {
        $context = implode("\n\n---\n\n", $chunks);
        $typeSpec = "fill-in-the-blank questions.\n\nEach question must have:\n- sentence_with_blanks: the sentence with _____ for each blank\n- answers: array of correct words for each blank (in order)";

        $format = "[\n  {\n    \"sentence_with_blanks\": \"The capital of France is _____\",\n    \"answers\": [\"Paris\"]\n  }\n]";

        $prompt = $this->buildPrompt($typeSpec, $context, $count, $difficulty);
        $prompt[1]['content'] .= "\n\nFormat:\n$format";

        return $this->aiService->generateJson($prompt);
    }

    public function generateMatchingQuestions(array $chunks, int $count, string $difficulty = 'medium'): array
    {
        $context = implode("\n\n---\n\n", $chunks);
        $typeSpec = "matching questions.\n\nEach question must have:\n- left_items: array of terms/concepts\n- right_items: array of definitions/descriptions (shuffled order)\n- correct_pairs: object mapping each left item to its correct right item";

        $format = "[\n  {\n    \"left_items\": [\"Term A\", \"Term B\", \"Term C\"],\n    \"right_items\": [\"Definition 1\", \"Definition 2\", \"Definition 3\"],\n    \"correct_pairs\": {\"Term A\": \"Definition 1\", \"Term B\": \"Definition 2\", \"Term C\": \"Definition 3\"}\n  }\n]";

        $prompt = $this->buildPrompt($typeSpec, $context, $count, $difficulty);
        $prompt[1]['content'] .= "\n\nFormat:\n$format";

        return $this->aiService->generateJson($prompt);
    }

    public function generateFlashcards(array $chunks, int $count, string $difficulty = 'medium'): array
    {
        $context = implode("\n\n---\n\n", $chunks);
        $typeSpec = "flashcards (question and answer pairs).\n\nEach flashcard must have:\n- front: the question or term\n- back: the answer or definition";

        $format = "[\n  {\n    \"front\": \"What is ...?\",\n    \"back\": \"The answer is ...\"\n  }\n]";

        $prompt = $this->buildPrompt($typeSpec, $context, $count, $difficulty);
        $prompt[1]['content'] .= "\n\nFormat:\n$format";

        return $this->aiService->generateJson($prompt);
    }
}
