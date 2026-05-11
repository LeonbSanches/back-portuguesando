<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class SpacedRepetitionService
{
    /**
     * @return array{
     *   interval_days:int,
     *   next_state:string,
     *   next_lapse_count:int,
     *   next_consecutive:int,
     *   next_ease_factor:float,
     *   next_review_at:Carbon
     * }
     */
    public function calculate(?object $currentQueue, bool $isCorrect, ?int $confidenceLevel): array
    {
        $lapseCount = (int) ($currentQueue?->lapse_count ?? 0);
        $consecutiveCorrect = (int) ($currentQueue?->consecutive_correct ?? 0);
        $easeFactor = (float) ($currentQueue?->ease_factor ?? 2.50);

        if ($isCorrect) {
            $intervalDays = (($confidenceLevel ?? 3) >= 4) ? 7 : 3;
            $easeBoost = match (true) {
                ($confidenceLevel ?? 3) >= 5 => 0.15,
                ($confidenceLevel ?? 3) >= 4 => 0.10,
                default => 0.05,
            };
            $nextEaseFactor = min(3.00, $easeFactor + $easeBoost);
            $nextReviewAt = now()->copy()->addDays($intervalDays);

            return [
                'interval_days' => $intervalDays,
                'next_state' => 'pending',
                'next_lapse_count' => $lapseCount,
                'next_consecutive' => $consecutiveCorrect + 1,
                'next_ease_factor' => $nextEaseFactor,
                'next_review_at' => $nextReviewAt,
            ];
        }

        $nextLapseCount = $lapseCount + 1;
        $intervalDays = $nextLapseCount > 1 ? 0 : 1;
        $nextEaseFactor = max(1.30, $easeFactor - 0.20);
        $nextReviewAt = $intervalDays === 0 ? now()->copy() : now()->copy()->addDay();

        return [
            'interval_days' => $intervalDays,
            'next_state' => $intervalDays === 0 ? 'due' : 'pending',
            'next_lapse_count' => $nextLapseCount,
            'next_consecutive' => 0,
            'next_ease_factor' => $nextEaseFactor,
            'next_review_at' => $nextReviewAt,
        ];
    }
}
