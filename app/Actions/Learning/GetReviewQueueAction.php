<?php

namespace App\Actions\Learning;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetReviewQueueAction
{
    public function execute(int $userId): Collection
    {
        return DB::table('review_queue')
            ->where('user_id', $userId)
            ->where(function ($query) {
                $query
                    ->whereDate('due_date', '<=', now()->toDateString())
                    ->orWhere('state', 'due');
            })
            ->orderBy('next_review_at')
            ->limit(100)
            ->get();
    }
}
