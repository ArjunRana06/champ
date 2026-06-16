<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpacedRepetition extends Model
{
    protected $fillable = [
        'user_id', 'reviewable_type', 'reviewable_id',
        'easiness_factor', 'interval_days', 'repetitions', 'next_review_at',
    ];

    protected $casts = [
        'easiness_factor' => 'float',
        'interval_days' => 'integer',
        'repetitions' => 'integer',
        'next_review_at' => 'date',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function reviewable() { return $this->morphTo(); }

    public function scopeDueForReview($query, $userId)
    {
        return $query->where('user_id', $userId)
            ->where('next_review_at', '<=', now());
    }
}
