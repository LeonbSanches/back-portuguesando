<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AchievementResource;
use App\Http\Resources\DailyGoalResource;
use App\Http\Resources\LeaderboardEntryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GamificationController extends Controller
{
    public function leaderboard(Request $request)
    {
        $leaderboard = DB::table('leaderboards')
            ->where('period_start', '<=', now()->toDateString())
            ->where('period_end', '>=', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        if (! $leaderboard) {
            return response()->json(['data' => []]);
        }

        $entries = DB::table('leaderboard_entries')
            ->join('users', 'users.id', '=', 'leaderboard_entries.user_id')
            ->where('leaderboard_entries.leaderboard_id', $leaderboard->id)
            ->orderBy('leaderboard_entries.rank_position')
            ->select(
                'leaderboard_entries.user_id',
                'leaderboard_entries.rank_position',
                'leaderboard_entries.score',
                'users.name'
            )
            ->get();

        return response()->json(['data' => LeaderboardEntryResource::collection($entries)]);
    }

    public function achievements(Request $request)
    {
        $rows = DB::table('achievements')
            ->leftJoin('user_achievements', function ($join) use ($request) {
                $join
                    ->on('user_achievements.achievement_id', '=', 'achievements.id')
                    ->where('user_achievements.user_id', '=', $request->user()->id);
            })
            ->where('achievements.active', true)
            ->orderBy('achievements.id')
            ->select(
                'achievements.id',
                'achievements.code',
                'achievements.title',
                'achievements.description',
                'achievements.xp_reward',
                'user_achievements.unlocked_at'
            )
            ->get();

        return response()->json(['data' => AchievementResource::collection($rows)]);
    }

    public function dailyGoal(Request $request)
    {
        $goal = DB::table('daily_goals')
            ->where('user_id', $request->user()->id)
            ->where('goal_date', now()->toDateString())
            ->first();

        if (! $goal) {
            return response()->json(['data' => new DailyGoalResource((object) [
                'goal_date' => now()->toDateString(),
                'target_questions' => 20,
                'completed_questions' => 0,
                'target_minutes' => 45,
                'completed_minutes' => 0,
                'status' => 'in_progress',
                'progress_percent' => 0,
            ])]);
        }

        $progressPercent = $goal->target_questions > 0
            ? (int) floor(($goal->completed_questions / $goal->target_questions) * 100)
            : 0;

        $goal->progress_percent = $progressPercent;

        return response()->json(['data' => new DailyGoalResource($goal)]);
    }
}
