<?php

namespace App\Services;

use App\Traits\HasRelevantChunks;

class QuestionGeneratorService
{
    use HasRelevantChunks;

    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    protected function buildPrompt(string $typeSpec, string $context, int $count, string $difficulty, array $existingQuestions = []): array
    {
        $systemPrompt = 'You are an expert educator that generates high-quality educational questions. Think step by step: first analyze the material, then create questions that test real understanding, not just recall.';

        $userPrompt = "You are an expert educator. Based **only** on the following study material, generate $count $typeSpec with difficulty level '$difficulty'.\n\n";
        $userPrompt .= "First, identify the key concepts in the material. Then create questions that test understanding of those concepts.\n";

        if (! empty($existingQuestions)) {
            $userPrompt .= "\nIMPORTANT — These questions ALREADY EXIST:\n";
            foreach ($existingQuestions as $question) {
                $userPrompt .= "- $question\n";
            }
            $userPrompt .= "\nDo NOT repeat or rephrase ANY of the questions above — not even in different wording. Every question you generate must test a completely different concept or fact.\n";
        }
        $userPrompt .= "Make sure all $count questions are distinct from one another and each tests a DIFFERENT key concept, fact, or example from the material — do not ask about the same idea more than once.\n";
        $userPrompt .= "Return ONLY valid JSON (no extra text, no markdown).\n\n";
        $userPrompt .= "Study material:\n$context";

        return [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];
    }

    public function generateTrueFalse(array $chunks, int $count, string $difficulty = 'medium', array $existingQuestions = []): array
    {
        $context = implode("\n\n---\n\n", $chunks);
        $typeSpec = "true/false questions.\n\nEach question must have:\n- statement: the statement to evaluate\n- correct_answer: true or false\n- explanation: short reason why the answer is correct\n- source_concept: the key concept from the material being tested";

        $format = "[\n  {\n    \"statement\": \"The mitochondria is the powerhouse of the cell.\",\n    \"correct_answer\": true,\n    \"explanation\": \"Mitochondria generate most of the cell's ATP through oxidative phosphorylation.\",\n    \"source_concept\": \"Cell biology - Mitochondria function\"\n  }\n]";

        $prompt = $this->buildPrompt($typeSpec, $context, $count, $difficulty, $existingQuestions);
        $prompt[1]['content'] .= "\n\nFormat:\n$format";

        return $this->aiService->generateJson($prompt, null, 0.7, 2048, false);
    }

    public function generateShortAnswers(array $chunks, int $count, string $difficulty = 'medium', array $existingQuestions = []): array
    {
        $context = implode("\n\n---\n\n", $chunks);
        $typeSpec = "short-answer questions.\n\nEach question must have:\n- question: the question text\n- expected_answer: the correct answer\n- key_points: array of key points the answer should include\n- source_concept: the key concept being tested";

        $format = "[\n  {\n    \"question\": \"What is the chemical symbol for water?\",\n    \"expected_answer\": \"H2O\",\n    \"key_points\": [\"Two hydrogen atoms\", \"One oxygen atom\"],\n    \"source_concept\": \"Chemistry - Molecular formulas\"\n  }\n]";

        $prompt = $this->buildPrompt($typeSpec, $context, $count, $difficulty, $existingQuestions);
        $prompt[1]['content'] .= "\n\nFormat:\n$format";

        return $this->aiService->generateJson($prompt, null, 0.7, 2048, false);
    }

    public function generateFillBlanks(array $chunks, int $count, string $difficulty = 'medium', array $existingQuestions = []): array
    {
        $context = implode("\n\n---\n\n", $chunks);
        $typeSpec = "fill-in-the-blank questions.\n\nEach question must have:\n- sentence_with_blanks: the sentence with _____ for each blank\n- answers: array of correct words for each blank (in order)\n- context_hint: optional hint about the topic";

        $format = "[\n  {\n    \"sentence_with_blanks\": \"The process by which plants make their food using sunlight is called _____\",\n    \"answers\": [\"photosynthesis\"],\n    \"context_hint\": \"Plant biology - Energy conversion\"\n  }\n]";

        $prompt = $this->buildPrompt($typeSpec, $context, $count, $difficulty, $existingQuestions);
        $prompt[1]['content'] .= "\n\nFormat:\n$format";

        return $this->aiService->generateJson($prompt, null, 0.7, 2048, false);
    }

    public function generateMatchingQuestions(array $chunks, int $count, string $difficulty = 'medium', array $existingQuestions = []): array
    {
        $context = implode("\n\n---\n\n", $chunks);
        $typeSpec = "matching questions.\n\nEach question must have:\n- title: the topic or theme being matched\n- left_items: array of terms/concepts (shuffled order for display)\n- right_items: array of definitions/descriptions (shuffled order for display)\n- correct_pairs: object mapping each left item to its correct right item\n- source_concept: the key concept being tested";

        $format = "[\n  {\n    \"title\": \"Cell Organelles and Functions\",\n    \"left_items\": [\"Mitochondria\", \"Ribosomes\", \"Nucleus\"],\n    \"right_items\": [\"Protein synthesis\", \"Energy production\", \"Genetic material storage\"],\n    \"correct_pairs\": {\"Mitochondria\": \"Energy production\", \"Ribosomes\": \"Protein synthesis\", \"Nucleus\": \"Genetic material storage\"},\n    \"source_concept\": \"Cell biology - Organelles\"\n  }\n]";

        $prompt = $this->buildPrompt($typeSpec, $context, $count, $difficulty, $existingQuestions);
        $prompt[1]['content'] .= "\n\nFormat:\n$format";

        return $this->aiService->generateJson($prompt, null, 0.7, 2048, false);
    }

    public function generateFlashcards(array $chunks, int $count, string $difficulty = 'medium', array $existingQuestions = []): array
    {
        $context = implode("\n\n---\n\n", $chunks);
        $typeSpec = "flashcards (question and answer pairs).\n\nEach flashcard must have:\n- front: the question or term\n- back: the answer or definition\n- topic: the subject area\n- difficulty_rank: 1 (basic) to 5 (advanced)";

        $format = "[\n  {\n    \"front\": \"What is the powerhouse of the cell?\",\n    \"back\": \"Mitochondria\",\n    \"topic\": \"Cell Biology\",\n    \"difficulty_rank\": 1\n  }\n]";

        $prompt = $this->buildPrompt($typeSpec, $context, $count, $difficulty, $existingQuestions);
        $prompt[1]['content'] .= "\n\nFormat:\n$format";

        return $this->aiService->generateJson($prompt, null, 0.7, 2048, false);
    }

    public function validateGenerated(string $type, array $items): array
    {
        $required = $this->getRequiredFields($type);

        return array_values(array_filter($items, function ($item) use ($required) {
            if (! is_array($item)) {
                return false;
            }
            foreach ($required as $field) {
                if (! array_key_exists($field, $item)) {
                    return false;
                }
                $value = $item[$field];
                if (is_string($value) && trim($value) === '') {
                    return false;
                }
                if (is_array($value) && empty($value)) {
                    return false;
                }
            }

            return true;
        }));
    }

    private function getRequiredFields(string $type): array
    {
        return match ($type) {
            'true_false' => ['statement', 'correct_answer'],
            'short_answers' => ['question', 'expected_answer'],
            'fill_blanks' => ['sentence_with_blanks', 'answers'],
            'matching' => ['left_items', 'right_items', 'correct_pairs'],
            'flashcards' => ['front', 'back'],
            default => [],
        };
    }
}
