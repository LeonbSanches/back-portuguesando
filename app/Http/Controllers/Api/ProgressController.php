<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    public function show(Request $request)
    {
        $userId = $request->user()->id;
        $today = now()->toDateString();

        $streak = DB::table('streaks')
            ->where('user_id', $userId)
            ->first();

        $dailyGoal = DB::table('daily_goals')
            ->where('user_id', $userId)
            ->where('goal_date', $today)
            ->first();

        $totalXp = (int) DB::table('xp_logs')
            ->where('user_id', $userId)
            ->sum('xp_delta');
        $xpPerLevel = 100;
        $level = (int) floor($totalXp / $xpPerLevel) + 1;
        $currentLevelXp = $totalXp % $xpPerLevel;
        $nextLevelXp = $level * $xpPerLevel;
        $progressPercentToNextLevel = (int) floor(($currentLevelXp / $xpPerLevel) * 100);
        $remainingXpToNextLevel = max(0, $xpPerLevel - $currentLevelXp);

        $xpLastSevenDays = DB::table('xp_logs')
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as day')
            ->selectRaw('SUM(xp_delta) as daily_xp')
            ->groupBy('day')
            ->get();

        $averageDailyXp = $xpLastSevenDays->isEmpty()
            ? 0.0
            : round($xpLastSevenDays->avg('daily_xp'), 2);

        $estimatedDaysToNextLevel = $averageDailyXp > 0
            ? (int) ceil($remainingXpToNextLevel / $averageDailyXp)
            : null;

        $attempts = DB::table('question_attempts')
            ->where('user_id', $userId)
            ->selectRaw('COUNT(*) as total_attempts')
            ->selectRaw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct_attempts')
            ->first();

        $dueReviewsCount = DB::table('review_queue')
            ->where('user_id', $userId)
            ->where(function ($query) {
                $query
                    ->whereDate('due_date', '<=', now()->toDateString())
                    ->orWhere('state', 'due');
            })
            ->count();

        $weakTopic = DB::table('weak_topics')
            ->where('user_id', $userId)
            ->orderByDesc('weakness_score')
            ->first();

        $studyDaysLast7Days = DB::table('question_attempts')
            ->where('user_id', $userId)
            ->where('answered_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('COUNT(DISTINCT DATE(answered_at)) as total_days')
            ->value('total_days');

        $consistencyScore = (int) floor((((int) $studyDaysLast7Days) / 7) * 100);
        $consistencyClassification = match (true) {
            $consistencyScore >= 70 => 'high',
            $consistencyScore >= 40 => 'medium',
            default => 'low',
        };

        $nextBestAction = match (true) {
            $dueReviewsCount > 0 => [
                'type' => 'review_due_questions',
                'message' => 'Você tem revisões vencidas. Comece pela fila de revisão.',
                'priority' => 'high',
                'expires_at' => now()->addMinutes(30)->toISOString(),
                'cta' => [
                    'route' => '/review-queue',
                    'label' => 'Revisar agora',
                    'payload' => (object) [],
                ],
            ],
            $consistencyScore < 40 => [
                'type' => 'build_study_streak',
                'message' => 'Estude hoje para melhorar sua consistência semanal.',
                'priority' => 'medium',
                'expires_at' => now()->endOfDay()->toISOString(),
                'cta' => [
                    'route' => '/study-session/start',
                    'label' => 'Estudar agora',
                    'payload' => [
                        'mode' => 'practice',
                    ],
                ],
            ],
            ! is_null($weakTopic) => [
                'type' => 'focus_weak_topic',
                'message' => 'Priorize seus tópicos fracos para acelerar sua evolução.',
                'priority' => 'medium',
                'expires_at' => now()->addHours(6)->toISOString(),
                'cta' => [
                    'route' => '/topics/'.$weakTopic->topic_id,
                    'label' => 'Reforçar tópico',
                    'payload' => [
                        'topic_id' => $weakTopic->topic_id,
                    ],
                ],
            ],
            default => [
                'type' => 'start_new_session',
                'message' => 'Inicie uma nova sessão de estudos para manter o ritmo.',
                'priority' => 'low',
                'expires_at' => now()->addHours(3)->toISOString(),
                'cta' => [
                    'route' => '/study-session/start',
                    'label' => 'Iniciar sessão',
                    'payload' => [
                        'mode' => 'practice',
                    ],
                ],
            ],
        };

        $totalAttempts = (int) ($attempts->total_attempts ?? 0);
        $correctAttempts = (int) ($attempts->correct_attempts ?? 0);
        $accuracyRate = $totalAttempts > 0
            ? (int) floor(($correctAttempts / $totalAttempts) * 100)
            : 0;

        return response()->json([
            'data' => [
                'streak' => [
                    'current_days' => $streak->current_days ?? 0,
                    'best_days' => $streak->best_days ?? 0,
                    'last_study_date' => $streak->last_study_date ?? null,
                ],
                'daily_goal' => [
                    'goal_date' => $dailyGoal->goal_date ?? $today,
                    'target_questions' => $dailyGoal->target_questions ?? 20,
                    'completed_questions' => $dailyGoal->completed_questions ?? 0,
                    'target_minutes' => $dailyGoal->target_minutes ?? 45,
                    'completed_minutes' => $dailyGoal->completed_minutes ?? 0,
                    'status' => $dailyGoal->status ?? 'in_progress',
                ],
                'xp' => [
                    'total' => $totalXp,
                    'level' => $level,
                    'current_level_xp' => $currentLevelXp,
                    'next_level_xp' => $nextLevelXp,
                    'progress_percent_to_next_level' => $progressPercentToNextLevel,
                    'estimated_days_to_next_level' => $estimatedDaysToNextLevel,
                ],
                'consistency' => [
                    'score' => $consistencyScore,
                    'study_days_last_7_days' => (int) $studyDaysLast7Days,
                    'classification' => $consistencyClassification,
                ],
                'next_best_action' => $nextBestAction,
                'performance' => [
                    'total_attempts' => $totalAttempts,
                    'correct_attempts' => $correctAttempts,
                    'accuracy_rate' => $accuracyRate,
                ],
            ],
        ]);
    }
}
