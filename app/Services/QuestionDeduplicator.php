<?php

namespace App\Services;

use App\Models\FillBlank;
use App\Models\Flashcard;
use App\Models\MatchingQuestion;
use App\Models\Mcq;
use App\Models\ShortAnswer;
use App\Models\TrueFalseQuestion;

class QuestionDeduplicator
{
    protected const STOP_WORDS = [
        'what', 'which', 'when', 'where', 'while', 'who', 'whom', 'whose',
        'with', 'from', 'have', 'has', 'this', 'that', 'these', 'those',
        'your', 'their', 'there', 'about', 'should', 'would', 'could',
        'than', 'then', 'them', 'each', 'other', 'more', 'most', 'some',
        'into', 'over', 'under', 'being', 'been', 'does', 'did', 'will',
        'were', 'was', 'are', 'can', 'its', 'after', 'before', 'between',
        'because', 'through', 'during', 'without', 'within', 'along',
        'such', 'only', 'also', 'just', 'make', 'made', 'call', 'called',
        'know', 'known', 'many', 'much', 'very', 'really', 'usually',
    ];

    public function collectUserQuestionTexts(int $userId, ?int $subjectId = null, ?int $documentId = null, int $limit = 120): array
    {
        $rows = [];

        $scoped = function ($query) use ($subjectId, $documentId) {
            if ($documentId) {
                return $query->where('document_id', $documentId);
            }
            if ($subjectId) {
                return $query->where('subject_id', $subjectId);
            }

            return $query;
        };

        $types = [
            [Mcq::class, 'question'],
            [TrueFalseQuestion::class, 'statement'],
            [ShortAnswer::class, 'question'],
            [FillBlank::class, 'sentence_with_blanks'],
            [Flashcard::class, 'front'],
        ];

        foreach ($types as [$model, $field]) {
            foreach ($scoped($model::where('user_id', $userId))
                ->latest()
                ->limit(40)
                ->get([$field, 'created_at']) as $row) {
                $text = trim((string) $row->{$field});
                if ($text === '') {
                    continue;
                }
                $rows[] = ['text' => $text, 'ts' => $row->created_at?->timestamp ?? 0];
            }
        }

        foreach ($scoped(MatchingQuestion::where('user_id', $userId))
            ->latest()
            ->limit(40)
            ->get(['left_items', 'right_items', 'created_at']) as $row) {
            $text = implode('|', $row->left_items ?? []).'|'.implode('|', $row->right_items ?? []);
            if (trim($text) === '') {
                continue;
            }
            $rows[] = ['text' => $text, 'ts' => $row->created_at?->timestamp ?? 0];
        }

        usort($rows, fn ($a, $b) => $b['ts'] <=> $a['ts']);

        $seen = [];
        $texts = [];
        foreach ($rows as $row) {
            $key = $this->normalize($row['text']);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $texts[] = $row['text'];
            if (count($texts) >= $limit) {
                break;
            }
        }

        return $texts;
    }

    public function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    public function significantTokens(string $text): array
    {
        $normalized = $this->normalize($text);
        $tokens = preg_split('/\s+/u', $normalized) ?: [];

        return array_values(array_unique(array_filter(
            $tokens,
            fn ($token) => mb_strlen($token) >= 4 && ! in_array($token, self::STOP_WORDS, true)
        )));
    }

    public function similarity(string $a, string $b): float
    {
        $tokensA = $this->significantTokens($a);
        $tokensB = $this->significantTokens($b);

        if (empty($tokensA) || empty($tokensB)) {
            return 0.0;
        }

        $intersection = count(array_intersect($tokensA, $tokensB));
        $union = count(array_unique(array_merge($tokensA, $tokensB)));

        if ($union === 0) {
            return 0.0;
        }

        return $intersection / $union;
    }

    public function isSimilar(string $a, string $b, float $threshold = 0.45): bool
    {
        if ($this->normalize($a) === $this->normalize($b)) {
            return true;
        }

        return $this->similarity($a, $b) >= $threshold;
    }

    public function isDuplicate(string $text, array $existingTexts): bool
    {
        $normalized = $this->normalize($text);
        if ($normalized === '') {
            return true;
        }

        foreach ($existingTexts as $existing) {
            $existingNorm = $this->normalize($existing);
            if ($existingNorm === '') {
                continue;
            }
            if ($normalized === $existingNorm) {
                return true;
            }
            similar_text($normalized, $existingNorm, $pct);
            if ($pct >= 90 || $this->similarity($text, $existing) >= 0.45) {
                return true;
            }
        }

        return false;
    }

    public function filterDuplicates(array $items, array $existingTexts, string|callable $field): array
    {
        $seen = $existingTexts;
        $kept = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $text = is_callable($field) ? $field($item) : ($item[$field] ?? null);
            $text = trim((string) $text);
            if ($text === '') {
                continue;
            }
            if ($this->isDuplicate($text, $seen)) {
                continue;
            }
            $seen[] = $text;
            $kept[] = $item;
        }

        return $kept;
    }

    public function filterDuplicatesSemantic(array $items, array $existingTexts, string|callable $field, float $threshold = 0.82): array
    {
        $existingTexts = array_values(array_unique(array_filter(array_map('trim', $existingTexts))));

        $new = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $text = is_callable($field) ? $field($item) : ($item[$field] ?? null);
            $text = trim((string) $text);
            if ($text === '') {
                continue;
            }
            $new[] = ['item' => $item, 'text' => $text];
        }

        if (empty($new)) {
            return [];
        }

        $allTexts = array_values(array_unique(array_merge($existingTexts, array_column($new, 'text'))));
        $embeddings = app(RAGService::class)->embedTexts($allTexts);

        $existingVectors = [];
        foreach ($existingTexts as $text) {
            if (isset($embeddings[$text])) {
                $existingVectors[$text] = $embeddings[$text];
            }
        }

        $kept = [];
        foreach ($new as $entry) {
            $text = $entry['text'];
            $norm = $this->normalize($text);
            $vector = $embeddings[$text] ?? null;

            $isDup = $this->matchesExisting($norm, $text, $vector, $existingTexts, $existingVectors, $threshold);

            if (! $isDup) {
                foreach ($kept as $k) {
                    $kNorm = $this->normalize($k['text']);
                    $kVector = $embeddings[$k['text']] ?? null;
                    if ($this->isDuplicatePair($norm, $text, $vector, $kNorm, $k['text'], $kVector, $threshold)) {
                        $isDup = true;
                        break;
                    }
                }
            }

            if ($isDup) {
                continue;
            }

            $kept[] = $entry;
        }

        return array_map(fn ($e) => $e['item'], $kept);
    }

    private function matchesExisting(string $norm, string $text, ?array $vector, array $existingTexts, array $existingVectors, float $threshold): bool
    {
        foreach ($existingTexts as $existing) {
            $existingNorm = $this->normalize($existing);
            $existingVector = $existingVectors[$existing] ?? null;

            if ($this->isDuplicatePair($norm, $text, $vector, $existingNorm, $existing, $existingVector, $threshold)) {
                return true;
            }
        }

        return false;
    }

    private function isDuplicatePair(
        string $norm,
        string $text,
        ?array $vector,
        string $otherNorm,
        string $otherText,
        ?array $otherVector,
        float $threshold
    ): bool {
        if ($norm !== '' && $otherNorm !== '' && $norm === $otherNorm) {
            return true;
        }

        if ($norm !== '' && $otherNorm !== '' && $this->similarity($text, $otherText) >= 0.45) {
            return true;
        }

        if ($vector !== null && $otherVector !== null && $this->cosine($vector, $otherVector) >= $threshold) {
            return true;
        }

        return false;
    }

    private function cosine(array $a, array $b): float
    {
        $countA = count($a);
        if ($countA === 0 || count($b) !== $countA) {
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
}
