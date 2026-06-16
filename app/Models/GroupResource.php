<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupResource extends Model
{
    protected $fillable = ['study_group_id', 'user_id', 'resourceable_type', 'resourceable_id'];

    public function group()
    {
        return $this->belongsTo(StudyGroup::class, 'study_group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resourceable()
    {
        return $this->morphTo();
    }
}
