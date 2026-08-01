<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mcq extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'subject_id', 'document_id', 'question',
        'options', 'correct_answer', 'explanation', 'difficulty', 'is_public'
    ];

    protected $casts = [
        'options' => 'array',
        'is_public' => 'boolean',
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
