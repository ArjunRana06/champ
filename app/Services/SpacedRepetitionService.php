<?php

namespace App\Services;

use App\Models\SpacedRepetition;

class SpacedRepetitionService
{
    public function review(int $userId, string $type, int $id, int $quality): void
    {
        $rep = SpacedRepetition::firstOrCreate(
            [
                'user_id' => $userId,
                'reviewable_type' => $type,
                'reviewable_id' => $id,
            ],
            [
                'easiness_factor' => 2.5,
                'interval_days' => 0,
                'repetitions' => 0,
                'next_review_at' => now(),
            ]
        );

        $ef = $rep->easiness_factor;
        $interval = $rep->interval_days;
        $repetitions = $rep->repetitions;

        // SM-2 algorithm
        $ef = max(1.3, $ef + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02)));

        if ($quality < 3) {
            $repetitions = 0;
            $interval = 1;
        } else {
            $repetitions++;
            if ($repetitions === 1) {
                $interval = 1;
            } elseif ($repetitions === 2) {
                $interval = 6;
            } else {
                $interval = round($interval * $ef);
            }
        }

        $rep->update([
            'easiness_factor' => $ef,
            'interval_days' => $interval,
            'repetitions' => $repetitions,
            'next_review_at' => now()->addDays($interval),
        ]);
    }
}
