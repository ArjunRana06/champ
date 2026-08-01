<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'semester', 'code'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function mcqs()
    {
        return $this->hasMany(Mcq::class);
    }

    public function trueFalseQuestions()
    {
        return $this->hasMany(TrueFalseQuestion::class);
    }

    public function shortAnswers()
    {
        return $this->hasMany(ShortAnswer::class);
    }

    public function fillBlanks()
    {
        return $this->hasMany(FillBlank::class);
    }

    public function matchingQuestions()
    {
        return $this->hasMany(MatchingQuestion::class);
    }

    public function flashcards()
    {
        return $this->hasMany(Flashcard::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
