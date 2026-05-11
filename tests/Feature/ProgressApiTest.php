<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProgressApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_consolidated_progress(): void
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
            'name' => 'Interpretacao',
            'slug' => 'interpretacao',
            'sort_order' => 1,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('questions')->insert([
            [
                'id' => 1,
                'topic_id' => $topicId,
                'exam_board_id' => null,
                'difficulty' => 'medium',
                'stem_type' => 'multiple_choice',
                'stem' => 'Questao 1',
                'support_text' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'topic_id' => $topicId,
                'exam_board_id' => null,
                'difficulty' => 'medium',
                'stem_type' => 'multiple_choice',
                'stem' => 'Questao 2',
                'support_text' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('streaks')->insert([
            'user_id' => $user->id,
            'current_days' => 5,
            'best_days' => 12,
            'last_study_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('daily_goals')->insert([
            'user_id' => $user->id,
            'goal_date' => now()->toDateString(),
            'target_questions' => 20,
            'completed_questions' => 8,
            'target_minutes' => 45,
            'completed_minutes' => 15,
            'status' => 'in_progress',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('xp_logs')->insert([
            [
                'user_id' => $user->id,
                'source' => 'question_answer',
                'xp_delta' => 5,
                'source_ref_type' => 'question',
                'source_ref_id' => 1,
                'created_at' => now()->subDays(2),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user->id,
                'source' => 'question_answer',
                'xp_delta' => 5,
                'source_ref_type' => 'question',
                'source_ref_id' => 2,
                'created_at' => now()->subDay(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user->id,
                'source' => 'question_answer',
                'xp_delta' => 5,
                'source_ref_type' => 'question',
                'source_ref_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('question_attempts')->insert([
            [
                'user_id' => $user->id,
                'question_id' => 1,
                'study_session_id' => null,
                'selected_option_id' => null,
                'is_correct' => true,
                'response_time_ms' => 1200,
                'confidence_level' => 4,
                'answer_context' => 'lesson',
                'answered_at' => now()->subDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user->id,
                'question_id' => 2,
                'study_session_id' => null,
                'selected_option_id' => null,
                'is_correct' => false,
                'response_time_ms' => 1800,
                'confidence_level' => 3,
                'answer_context' => 'lesson',
                'answered_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/me/progress');

        $response
            ->assertOk()
            ->assertJsonPath('data.streak.current_days', 5)
            ->assertJsonPath('data.daily_goal.completed_questions', 8)
            ->assertJsonPath('data.xp.total', 15)
            ->assertJsonPath('data.xp.level', 1)
            ->assertJsonPath('data.xp.current_level_xp', 15)
            ->assertJsonPath('data.xp.next_level_xp', 100)
            ->assertJsonPath('data.xp.progress_percent_to_next_level', 15)
            ->assertJsonPath('data.xp.estimated_days_to_next_level', 17)
            ->assertJsonPath('data.consistency.score', 28)
            ->assertJsonPath('data.consistency.study_days_last_7_days', 2)
            ->assertJsonPath('data.consistency.classification', 'low')
            ->assertJsonPath('data.next_best_action.type', 'build_study_streak')
            ->assertJsonPath('data.next_best_action.message', 'Estude hoje para melhorar sua consistência semanal.')
            ->assertJsonPath('data.next_best_action.priority', 'medium')
            ->assertJsonPath('data.next_best_action.expires_at', now()->endOfDay()->toISOString())
            ->assertJsonPath('data.next_best_action.cta.route', '/study-session/start')
            ->assertJsonPath('data.next_best_action.cta.label', 'Estudar agora')
            ->assertJsonPath('data.next_best_action.cta.payload.mode', 'practice')
            ->assertJsonPath('data.performance.total_attempts', 2)
            ->assertJsonPath('data.performance.accuracy_rate', 50);
    }
}
