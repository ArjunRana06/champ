<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeerReview extends Model
{
    protected $fillable = ['reviewer_id', 'reviewable_type', 'reviewable_id', 'rating', 'comment'];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewable()
    {
        return $this->morphTo();
    }
}
