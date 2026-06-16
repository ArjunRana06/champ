<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'type',
        'total_questions',
        'correct_answers',
        'score_percentage',
        'time_taken_seconds',
        'is_exam_mode',
        'answers_data',
    ];

    protected $casts = [
        'is_exam_mode' => 'boolean',
        'answers_data' => 'array',
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'score_percentage' => 'integer',
        'time_taken_seconds' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
