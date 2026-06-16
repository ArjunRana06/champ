<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'user_id', 'subject_id', 'title', 'exam_date',
        'time', 'location', 'notes', 'priority', 'is_completed',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'is_completed' => 'boolean',
        'priority' => 'integer',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
}
