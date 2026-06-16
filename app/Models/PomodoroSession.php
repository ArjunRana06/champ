<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PomodoroSession extends Model
{
    protected $fillable = [
        'user_id', 'subject_id', 'duration_minutes',
        'break_minutes', 'status',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
}
