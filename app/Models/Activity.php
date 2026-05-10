<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'content',
        'file_path',
        'session_id',
    ];

    protected $casts = [
        'content' => 'array',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function emotions()
    {
        return $this->hasMany(Emotion::class);
    }

    // Helpers
    public function getParsedContentAttribute()
    {
        if ($this->type === 'text' || $this->type === 'emoji' || $this->type === 'emotion') {
            return is_array($this->content) ? ($this->content['text'] ?? '') : $this->content;
        }
        return null;
    }

    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }
}
