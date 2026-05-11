<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AnalyticsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_performance_report(): void
    {
        $user = User::factory()->create();

        DB::table('performance_reports')->insert([
            'user_id' => $user->id,
            'period_start' => now()->subDays(7)->toDateString(),
            'period_end' => now()->toDateString(),
            'accuracy_rate' => 82.50,
            'solved_questions' => 120,
            'reviewed_questions' => 75,
            'study_time_seconds' => 3600,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/performance-report');

        $response
            ->assertOk()
            ->assertJsonPath('data.accuracy_rate', 82.5)
            ->assertJsonPath('data.solved_questions', 120);
    }

    public function test_authenticated_user_can_fetch_weak_topics(): void
    {
        $user = User::factory()->create();

        $subjectId = DB::table('subjects')->insertGetId([
            'name' => 'Portugues',
            'slug' => 'portugues',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $topicId = DB::table('topics')->insertGetId([
            'subject_id' => $subjectId,
            'parent_topic_id' => null,
            'name' => 'Concordancia',
            'slug' => 'concordancia',
            'sort_order' => 1,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('weak_topics')->insert([
            'user_id' => $user->id,
            'topic_id' => $topicId,
            'weakness_score' => 91.2,
            'window_attempts' => 23,
            'detected_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/weak-topics');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.topic_id', $topicId)
            ->assertJsonPath('data.0.window_attempts', 23);
    }

    public function test_authenticated_user_can_fetch_study_time_summary(): void
    {
        $user = User::factory()->create();

        DB::table('study_time_logs')->insert([
            [
                'user_id' => $user->id,
                'study_session_id' => null,
                'subject_id' => null,
                'topic_id' => null,
                'duration_seconds' => 1200,
                'logged_at' => now()->subDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user->id,
                'study_session_id' => null,
                'subject_id' => null,
                'topic_id' => null,
                'duration_seconds' => 1800,
                'logged_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/study-time');

        $response
            ->assertOk()
            ->assertJsonPath('data.total_seconds', 3000)
            ->assertJsonPath('data.total_minutes', 50);
    }
}
