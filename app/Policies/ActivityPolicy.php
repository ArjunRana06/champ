<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Activity;

class ActivityPolicy
{
    public function view(User $user, Activity $activity)
    {
        return $user->id === $activity->user_id;
    }

     public function update(User $user, Activity $activity)
    {
        return $user->id === $activity->user_id;
    }

    public function delete(User $user, Activity $activity)
    {
        return $user->id === $activity->user_id;
    }
}
