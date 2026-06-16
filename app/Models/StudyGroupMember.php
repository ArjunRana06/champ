<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyGroupMember extends Model
{
    protected $fillable = ['study_group_id', 'user_id', 'role'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(StudyGroup::class, 'study_group_id');
    }
}
