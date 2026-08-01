<?php

namespace App\Services;

use App\Models\User;

class GamificationService
{
    public function awardXp(User $user, int $xp, ?string $reason = null): void
    {
        if ($xp <= 0) return;

        $user->increment('xp', $xp);
        $user->refresh();
        $newLevel = floor($user->xp / 100) + 1;
        if ($newLevel > $user->level) {
            $user->level = $newLevel;
            $user->save();
        }
        $this->updateStreak($user);
        $user->save();
    }

    public function updateStreak(User $user): void
    {
        $today = now()->startOfDay();
        $lastActive = $user->last_active_date;

        if ($lastActive && $lastActive->copy()->addDay()->startOfDay()->eq($today)) {
            $user->streak++;
        } elseif (!$lastActive || $lastActive->copy()->startOfDay()->lt($today)) {
            $user->streak = 1;
        }
        $user->last_active_date = $today;
    }

    public function getLevelProgress(User $user): int
    {
        $xpForCurrentLevel = ($user->level - 1) * 100;
        $xpForNextLevel = $user->level * 100;
        $progress = (($user->xp - $xpForCurrentLevel) / ($xpForNextLevel - $xpForCurrentLevel)) * 100;
        return min(100, max(0, (int) $progress));
    }

    public function getLevel(int $xp): int
    {
        return floor($xp / 100) + 1;
    }

    public function getXpForLevel(int $level): int
    {
        return ($level - 1) * 100;
    }

    public function getXpToNextLevel(int $xp): int
    {
        $curLevel = $this->getLevel($xp);
        return $curLevel * 100 - $xp;
    }
}
