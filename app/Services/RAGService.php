<?php

namespace App\Services;

use App\Models\DocumentChunk;
use App\Models\Embedding;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RAGService
{
    private function getQueryEmbedding(string $query): array
    {
        $cacheKey = 'query_embedding_'.md5($query);

        return Cache::remember($cacheKey, 3600, function () use ($query) {
            $apiKey = config('services.openrouter.api_key');
            if (! $apiKey) {
                return [];
            }

            $primary = config('services.openrouter.embedding_model');
            $fallbacks = config('services.openrouter.embedding_fallback_models', []);
            $models = array_values(array_unique(array_filter(array_merge([$primary], $fallbacks))));

            foreach ($models as $model) {
                try {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer '.$apiKey,
                        'Content-Type' => 'application/json',
                    ])->timeout(8)->post('https://openrouter.ai/api/v1/embeddings', [
                        'model' => $model,
                        'input' => $query,
                    ]);

                    if ($response->failed()) {
                        continue;
                    }

                    $embedding = $response->json()['data'][0]['embedding'] ?? [];
                    if (! empty($embedding)) {
                        return $embedding;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            return [];
        });
    }

    public function embedTexts(array $texts): array
    {
        $texts = array_values(array_unique(array_filter(array_map('trim', $texts))));
        if (empty($texts)) {
            return [];
        }

        $result = [];
        $toFetch = [];

        foreach ($texts as $text) {
            $key = 'dedup_emb_'.md5(mb_strtolower($text));
            $cached = Cache::get($key);
            if (is_array($cached) && ! empty($cached)) {
                $result[$text] = $cached;
            } else {
                $toFetch[$text] = $key;
            }
        }

        if (empty($toFetch)) {
            return $result;
        }

        $apiKey = config('services.openrouter.api_key');
        if (! $apiKey) {
            return $result;
        }

        $primary = config('services.openrouter.embedding_model');
        $fallbacks = config('services.openrouter.embedding_fallback_models', []);
        $models = array_values(array_unique(array_filter(array_merge([$primary], $fallbacks))));

        $orderedTexts = array_keys($toFetch);

        foreach (array_chunk($orderedTexts, 25) as $chunk) {
            foreach ($models as $model) {
                try {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer '.$apiKey,
                        'Content-Type' => 'application/json',
                    ])->timeout(20)->post('https://openrouter.ai/api/v1/embeddings', [
                        'model' => $model,
                        'input' => $chunk,
                    ]);

                    if ($response->failed()) {
                        continue;
                    }

                    $data = $response->json()['data'] ?? [];
                    $embeddedCount = 0;
                    foreach ($data as $item) {
                        $index = $item['index'] ?? null;
                        $vector = $item['embedding'] ?? [];
                        if ($index === null || ! isset($chunk[$index]) || empty($vector)) {
                            continue;
                        }
                        $text = $chunk[$index];
                        Cache::put($toFetch[$text], $vector, 2592000);
                        $result[$text] = $vector;
                        $embeddedCount++;
                    }

                    if ($embeddedCount > 0) {
                        break;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return $result;
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $countA = count($a);
        $countB = count($b);
        if ($countA === 0 || $countB === 0 || $countA !== $countB) {
            return 0;
        }

        $dot = 0;
        $normA = 0;
        $normB = 0;
        for ($i = 0; $i < $countA; $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }
        if ($normA == 0 || $normB == 0) {
            return 0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    public function retrieveRelevantChunks(string $query, int $topK = 8, float $minScore = 0.5): array
    {
        $userId = Auth::id();
        if (! $userId) {
            return [];
        }

        $keywordChunks = $this->keywordSearch($query, $topK * 2);

        if (count($keywordChunks) >= $topK) {
            return array_slice($keywordChunks, 0, $topK);
        }

        $queryEmbedding = $this->getQueryEmbedding($query);
        if (! empty($queryEmbedding)) {
            $vectorChunks = $this->vectorSearch($queryEmbedding, $topK, $minScore);

            $seenIds = [];
            $merged = [];
            foreach ($vectorChunks as $c) {
                $seenIds[$c->id] = true;
                $merged[] = $c;
            }
            foreach ($keywordChunks as $c) {
                if (! isset($seenIds[$c->id]) && count($merged) < $topK) {
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
        if (! $userId) {
            return [];
        }

        $chunks = DocumentChunk::whereHas('document', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('status', 'completed');
        })
            ->where('content', 'LIKE', '%'.addcslashes($query, '%_\\').'%')
            ->limit($topK)
            ->get()
            ->all();

        if (! empty($chunks)) {
            return $chunks;
        }

        $keywords = preg_split('/[\s,;:.!?()]+/u', $query);
        $keywords = array_filter($keywords, fn ($w) => mb_strlen(trim($w)) > 2);
        $keywords = array_values($keywords);

        if (empty($keywords)) {
            return [];
        }

        $queryBuilder = DocumentChunk::whereHas('document', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('status', 'completed');
        });

        $queryBuilder->where(function ($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->orWhere('content', 'LIKE', '%'.addcslashes(trim($keyword), '%_\\').'%');
            }
        });

        return $queryBuilder->limit($topK)->get()->all();
    }

    private function vectorSearch(array $queryEmbedding, int $topK, float $minScore): array
    {
        $userId = Auth::id();
        if (! $userId) {
            return [];
        }

        $candidates = Embedding::select('id', 'document_chunk_id', 'embedding')
            ->whereHas('chunk.document', function ($q) use ($userId) {
                $q->where('user_id', $userId)->where('status', 'completed');
            })
            ->get();

        $scored = [];
        foreach ($candidates as $item) {
            $emb = is_array($item->embedding) ? $item->embedding : json_decode($item->embedding, true);
            if (! is_array($emb)) {
                continue;
            }
            $score = $this->cosineSimilarity($queryEmbedding, $emb);
            if ($score >= $minScore) {
                $scored[] = ['chunk_id' => $item->document_chunk_id, 'score' => $score];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);
        $topIds = array_column(array_slice($scored, 0, $topK), 'chunk_id');

        if (empty($topIds)) {
            return [];
        }

        return DocumentChunk::whereIn('id', $topIds)->get()->all();
    }

    public function getContextString(string $query): string
    {
        $chunks = $this->retrieveRelevantChunks($query, 8, 0.5);
        if (empty($chunks)) {
            return '__NO_RELEVANT_CONTENT__No relevant content found in your uploaded materials.';
        }

        $context = "Here are the most relevant excerpts from your uploaded study materials:\n\n";
        foreach ($chunks as $index => $chunk) {
            $docName = $chunk->document->original_name ?? 'Unknown document';
            $context .= '--- Excerpt '.($index + 1)." (from: $docName) ---\n";
            $context .= mb_substr($chunk->content, 0, 1500)."\n\n";
        }

        return $context;
    }
}
