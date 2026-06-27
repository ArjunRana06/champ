<?php

namespace App\Services;

use App\Models\DocumentChunk;
use App\Models\Embedding;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RAGService
{
    private function getQueryEmbedding(string $query): array
    {
        $cacheKey = 'query_embedding_' . md5($query);
        return Cache::remember($cacheKey, 3600, function () use ($query) {
            $apiKey = config('services.openrouter.api_key');
            if (!$apiKey) {
                return [];
            }

            $model = config('services.openrouter.embedding_model');

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post('https://openrouter.ai/api/v1/embeddings', [
                    'model' => $model,
                    'input' => $query,
                ]);

                if ($response->failed()) {
                    Log::error('Embedding API error', ['body' => $response->body()]);
                    return [];
                }

                return $response->json()['data'][0]['embedding'] ?? [];
            } catch (\Exception $e) {
                Log::warning('Embedding API exception', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0;
        $normA = 0;
        $normB = 0;
        $count = count($a);
        for ($i = 0; $i < $count; $i++) {
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

        // Strategy: keyword-first, vector-enhanced
        // 1. Quick keyword search (DB-level, fast)
        $keywordChunks = $this->keywordSearch($query, $topK * 2);

        // 2. If keyword results are sufficient, return them
        if (count($keywordChunks) >= $topK) {
            return array_slice($keywordChunks, 0, $topK);
        }

        // 3. Otherwise, try vector search to supplement
        $queryEmbedding = $this->getQueryEmbedding($query);
        if (!empty($queryEmbedding)) {
            $vectorChunks = $this->vectorSearch($queryEmbedding, $topK, $minScore);

            // Merge: take vector results first, fill remaining with keyword
            $seenIds = [];
            $merged = [];
            foreach ($vectorChunks as $c) {
                $seenIds[$c->id] = true;
                $merged[] = $c;
            }
            foreach ($keywordChunks as $c) {
                if (!isset($seenIds[$c->id]) && count($merged) < $topK) {
                    $merged[] = $c;
                }
            }
            return $merged;
        }

        return array_slice($keywordChunks, 0, $topK);
    }

    private function keywordSearch(string $query, int $topK): array
    {
        $userId = Auth::id();
        if (!$userId) return [];

        // Try full query as a phrase first
        $chunks = DocumentChunk::whereHas('document', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('status', 'completed');
        })
        ->where('content', 'LIKE', '%' . addcslashes($query, '%_') . '%')
        ->limit($topK)
        ->get()
        ->all();

        if (!empty($chunks)) {
            return $chunks;
        }

        // Fall back to individual keywords
        $keywords = preg_split('/[\s,;:.!?()]+/u', $query);
        $keywords = array_filter($keywords, fn($w) => mb_strlen(trim($w)) > 2);
        $keywords = array_values($keywords);

        if (empty($keywords)) {
            return [];
        }

        $queryBuilder = DocumentChunk::whereHas('document', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('status', 'completed');
        });

        foreach ($keywords as $keyword) {
            $queryBuilder->where('content', 'LIKE', '%' . addcslashes(trim($keyword), '%_') . '%');
        }

        return $queryBuilder->limit($topK)->get()->all();
    }

    private function vectorSearch(array $queryEmbedding, int $topK, float $minScore): array
    {
        $userId = Auth::id();
        if (!$userId) return [];

        // Pre-filter to a reasonable candidate pool using keyword search
        // Then score only those candidates
        $candidates = Embedding::with('chunk.document')
            ->whereHas('chunk.document', function ($q) use ($userId) {
                $q->where('user_id', $userId)->where('status', 'completed');
            })
            ->limit(200)
            ->get();

        $scored = [];
        foreach ($candidates as $item) {
            $emb = json_decode($item->embedding, true);
            if (!is_array($emb)) continue;
            $score = $this->cosineSimilarity($queryEmbedding, $emb);
            if ($score >= $minScore) {
                $scored[] = ['chunk' => $item->chunk, 'score' => $score];
            }
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_column(array_slice($scored, 0, $topK), 'chunk');
    }

    public function getContextString(string $query): string
    {
        $chunks = $this->retrieveRelevantChunks($query, 8, 0.5);
        if (empty($chunks)) {
            return "__NO_RELEVANT_CONTENT__No relevant content found in your uploaded materials.";
        }

        $context = "Here are the most relevant excerpts from your uploaded study materials (ranked by relevance):\n\n";
        foreach ($chunks as $index => $chunk) {
            $docName = $chunk->document->original_name ?? 'Unknown document';
            $context .= "--- Excerpt " . ($index + 1) . " (from: $docName) ---\n";
            $context .= $chunk->content . "\n\n";
        }
        return $context;
    }
}
