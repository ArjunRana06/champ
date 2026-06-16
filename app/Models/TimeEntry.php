<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    protected $fillable = [
        'user_id', 'subject_id', 'description',
        'started_at', 'ended_at', 'duration_minutes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
}
