<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'emotion',
        'confidence_score',
    ];

    protected $casts = [
        'confidence_score' => 'float',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
