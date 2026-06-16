<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flashcard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'subject_id', 'document_id',
        'front', 'back', 'difficulty', 'is_public'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function peerReviews()
    {
        return $this->morphMany(PeerReview::class, 'reviewable');
    }
}
