<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrueFalseQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'subject_id', 'document_id',
        'statement', 'correct_answer', 'explanation', 'difficulty', 'is_public'
    ];

    protected $casts = [
        'correct_answer' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function peerReviews()
    {
        return $this->morphMany(PeerReview::class, 'reviewable');
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
