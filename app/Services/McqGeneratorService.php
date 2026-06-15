<?php

namespace App\Services;

use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class McqGeneratorService
{
    protected $ragService;

    public function __construct(RAGService $ragService)
    {
        $this->ragService = $ragService;
    }

    public function getRelevantChunks($subjectId, $documentId, $topic = null, $limit = 20)
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

    public function generateMcqs(array $chunks, int $count, string $difficulty = 'medium'): array
    {
        $context = implode("\n\n---\n\n", $chunks);
        // Build prompt using concatenation to avoid HEREDOC issues
        $prompt = "You are an expert educator. Based **only** on the following study material, generate $count multiple-choice questions with difficulty level '$difficulty'.\n\n";
        $prompt .= "Each question must have:\n";
        $prompt .= "- question: text\n";
        $prompt .= "- options: array of 4 strings (A, B, C, D)\n";
        $prompt .= "- correct_answer: the exact option letter (e.g., 'A')\n";
        $prompt .= "- explanation: short reason why that answer is correct\n\n";
        $prompt .= "Return ONLY valid JSON in this format (no extra text, no markdown):\n\n";
        $prompt .= "[\n";
        $prompt .= "  {\n";
        $prompt .= "    \"question\": \"...\",\n";
        $prompt .= "    \"options\": [\"A. ...\", \"B. ...\", \"C. ...\", \"D. ...\"],\n";
        $prompt .= "    \"correct_answer\": \"A\",\n";
        $prompt .= "    \"explanation\": \"...\"\n";
        $prompt .= "  }\n";
        $prompt .= "]\n\n";
        $prompt .= "Study material:\n$context";

        $apiKey = env('OPENROUTER_API_KEY');
        $model = env('OPENROUTER_MODEL', 'openai/gpt-3.5-turbo');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant that generates educational MCQs in JSON format.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.3,
            'max_tokens' => 2000,
        ]);

        if (!$response->successful()) {
            Log::error('MCQ generation API error', ['body' => $response->body()]);
            throw new \Exception('Failed to generate MCQs: ' . $response->body());
        }

        $content = $response->json()['choices'][0]['message']['content'];
        // Remove possible markdown code blocks
        $content = preg_replace('/```json\s*|\s*```/', '', $content);
        $mcqs = json_decode($content, true);

        if (!is_array($mcqs)) {
            throw new \Exception('Invalid JSON response from AI: ' . $content);
        }

        return $mcqs;
    }
}
