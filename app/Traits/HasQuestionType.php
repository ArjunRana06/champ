<?php

namespace App\Traits;

use App\Models\FillBlank;
use App\Models\Flashcard;
use App\Models\MatchingQuestion;
use App\Models\Mcq;
use App\Models\ShortAnswer;
use App\Models\TrueFalseQuestion;

trait HasQuestionType
{
    private function getModel(string $type): string
    {
        return match ($type) {
            'mcqs' => Mcq::class,
            'true_false' => TrueFalseQuestion::class,
            'short_answers' => ShortAnswer::class,
            'fill_blanks' => FillBlank::class,
            'matching' => MatchingQuestion::class,
            'flashcards' => Flashcard::class,
            default => throw new \InvalidArgumentException("Invalid question type: {$type}"),
        };
    }

    private function getSearchColumns(string $type): array
    {
        return match ($type) {
            'mcqs' => ['question', 'options'],
            'true_false' => ['statement'],
            'short_answers' => ['question', 'expected_answer'],
            'fill_blanks' => ['sentence_with_blanks', 'answers'],
            'matching' => ['left_items', 'right_items'],
            'flashcards' => ['front', 'back'],
            default => [],
        };
    }

    private function applyQuestionSearch($query, string $type, string $search): void
    {
        $jsonColumns = ['options', 'answers', 'left_items', 'right_items'];

        $query->where(function ($q) use ($type, $search, $jsonColumns) {
            $columns = $this->getSearchColumns($type);
            foreach ($columns as $index => $column) {
                if (in_array($column, $jsonColumns, true)) {
                    $q->when($index === 0, fn ($inner) => $inner->whereRaw('CAST('.$column.' AS CHAR) LIKE ?', ["%{$search}%"]), fn ($inner) => $inner->orWhereRaw('CAST('.$column.' AS CHAR) LIKE ?', ["%{$search}%"]));
                } else {
                    $q->when($index === 0, fn ($inner) => $inner->where($column, 'like', "%{$search}%"), fn ($inner) => $inner->orWhere($column, 'like', "%{$search}%"));
                }
            }
        });
    }
}
