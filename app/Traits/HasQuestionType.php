<?php

namespace App\Traits;

use App\Models\Mcq;
use App\Models\TrueFalseQuestion;
use App\Models\ShortAnswer;
use App\Models\FillBlank;
use App\Models\MatchingQuestion;
use App\Models\Flashcard;

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
}
