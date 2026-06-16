<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyPlan extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'plan_json',
        'subjects',
        'exam_dates',
        'hours_per_day',
        'model_used',
    ];

    protected $casts = [
        'subjects' => 'array',
        'exam_dates' => 'array',
        'hours_per_day' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPlanAttribute()
    {
        return json_decode($this->plan_json, true) ?? [];
    }
}
