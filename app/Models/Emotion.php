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

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
