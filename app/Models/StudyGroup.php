<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyGroup extends Model
{
    protected $fillable = ['name', 'description', 'created_by'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->hasMany(StudyGroupMember::class);
    }

    public function resources()
    {
        return $this->hasMany(GroupResource::class);
    }
}
