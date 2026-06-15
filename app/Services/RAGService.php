<?php

namespace App\Services;

use App\Models\DocumentChunk;
use App\Models\Embedding;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RAGService
{
    private function getQueryEmbedding(string $query): array
    {
        $apiKey = env('OPENROUTER_API_KEY');
        $model = env('OPENROUTER_EMBEDDING_MODEL', 'openai/text-embedding-3-small');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://openrouter.ai/api/v1/embeddings', [
            'model' => $model,
            'input' => $query,
        ]);

        if ($response->failed()) {
            Log::error('Embedding API error', ['body' => $response->body()]);
            return [];
        }

        return $response->json()['data'][0]['embedding'];
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0;
        $normA = 0;
        $normB = 0;
        for ($i = 0; $i < count($a); $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }
        if ($normA == 0 || $normB == 0) return 0;
        return $dot / (sqrt($normA) * sqrt($normB));
    }

    public function retrieveRelevantChunks(string $query, int $topK = 8, float $minScore = 0.5): array
    {
        $userId = Auth::id();
        if (!$userId) return [];

        // Try vector search first
        $queryEmbedding = $this->getQueryEmbedding($query);
        if (!empty($queryEmbedding)) {
            $chunksWithEmbeddings = Embedding::with('chunk.document')
                ->whereHas('chunk.document', function ($q) use ($userId) {
                    $q->where('user_id', $userId)->where('status', 'completed');
                })
                ->get();

            $scored = [];
            foreach ($chunksWithEmbeddings as $item) {
                $emb = json_decode($item->embedding, true);
                $score = $this->cosineSimilarity($queryEmbedding, $emb);
                if ($score >= $minScore) {
                    $scored[] = ['chunk' => $item->chunk, 'score' => $score];
                }
            }
            usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
            $vectorChunks = array_column(array_slice($scored, 0, $topK), 'chunk');
            if (!empty($vectorChunks)) {
                return $vectorChunks;
            }
        }

        // Fallback: keyword search (less ideal but better than nothing)
        return $this->fallbackKeywordSearch($query, $topK);
    }

    private function fallbackKeywordSearch(string $query, int $topK): array
    {
        $keywords = explode(' ', $query);
        $queryBuilder = DocumentChunk::whereHas('document', function ($q) {
            $q->where('user_id', Auth::id())->where('status', 'completed');
        });

        foreach ($keywords as $keyword) {
            if (strlen($keyword) > 2) {
                $queryBuilder->where('content', 'LIKE', '%' . addcslashes($keyword, '%_') . '%');
            }
        }

        $chunks = $queryBuilder->limit($topK)->get()->all();
        if (!empty($chunks)) {
            return $chunks;
        }

        // Ultimate fallback: get any completed document chunks (avoid "nothing found")
        return DocumentChunk::whereHas('document', function ($q) {
            $q->where('user_id', Auth::id())->where('status', 'completed');
        })->limit($topK)->get()->all();
    }

    public function getContextString(string $query): string
    {
        $chunks = $this->retrieveRelevantChunks($query, 8, 0.5);
        if (empty($chunks)) {
            // Check if the user has any completed document at all
            $hasDocs = \App\Models\Document::where('user_id', Auth::id())
                        ->where('status', 'completed')->exists();
            if (!$hasDocs) {
                return "You have not uploaded any processed documents yet. Please upload and wait for processing to complete.";
            }
            return "I searched your uploaded materials but could not find text that matches your question. Here are some general excerpts from your notes (might be unrelated):\n\n" .
                   $this->getGenericContext();
        }

        $context = "Here are the most relevant excerpts from your uploaded study materials (ranked by relevance):\n\n";
        foreach ($chunks as $index => $chunk) {
            $docName = $chunk->document->original_name ?? 'Unknown document';
            $context .= "--- Excerpt " . ($index + 1) . " (from: $docName) ---\n";
            $context .= $chunk->content . "\n\n";
        }
        return $context;
    }

    private function getGenericContext(): string
    {
        $chunks = DocumentChunk::whereHas('document', function ($q) {
            $q->where('user_id', Auth::id())->where('status', 'completed');
        })->limit(5)->get();

        if ($chunks->isEmpty()) return "No content available.";
        $context = "";
        foreach ($chunks as $chunk) {
            $context .= "- " . substr($chunk->content, 0, 500) . "\n\n";
        }
        return $context;
    }
}
