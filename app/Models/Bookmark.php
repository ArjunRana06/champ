<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    protected $fillable = [
        'user_id',
        'bookmarkable_type',
        'bookmarkable_id',
        'label',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookmarkable()
    {
        return $this->morphTo();
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
