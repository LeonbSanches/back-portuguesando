<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PerformanceReportResource;
use App\Http\Resources\StudyTimeSummaryResource;
use App\Http\Resources\WeakTopicResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function performanceReport(Request $request)
    {
        $report = DB::table('performance_reports')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('period_end')
            ->first();

        if (! $report) {
            return response()->json(['data' => new PerformanceReportResource((object) [
                'accuracy_rate' => 0,
                'solved_questions' => 0,
                'reviewed_questions' => 0,
                'study_time_seconds' => 0,
                'period_start' => null,
                'period_end' => null,
            ])]);
        }

        return response()->json(['data' => new PerformanceReportResource($report)]);
    }

    public function weakTopics(Request $request)
    {
        $weakTopics = DB::table('weak_topics')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('weakness_score')
            ->limit(10)
            ->get();

        return response()->json(['data' => WeakTopicResource::collection($weakTopics)]);
    }

    public function studyTime(Request $request)
    {
        $totalSeconds = (int) DB::table('study_time_logs')
            ->where('user_id', $request->user()->id)
            ->sum('duration_seconds');

        return response()->json(['data' => new StudyTimeSummaryResource((object) [
            'total_seconds' => $totalSeconds,
            'total_minutes' => (int) floor($totalSeconds / 60),
        ])]);
    }
}
