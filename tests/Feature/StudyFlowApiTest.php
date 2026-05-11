<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudyFlowApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_study_session(): void
    {
        $user = User::factory()->create();
        $subjectId = DB::table('subjects')->insertGetId([
            'name' => 'Portugues',
            'slug' => 'portugues',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/study-session/start', [
                'subject_id' => $subjectId,
                'mode' => 'practice',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('session.user_id', $user->id)
            ->assertJsonPath('session.subject_id', $subjectId);

        $this->assertDatabaseHas('study_sessions', [
            'user_id' => $user->id,
            'subject_id' => $subjectId,
            'mode' => 'practice',
        ]);
    }

    public function test_answering_question_updates_attempt_and_review_queue(): void
    {
        $user = User::factory()->create();
        $data = $this->createQuestionData();

        $sessionId = DB::table('study_sessions')->insertGetId([
            'user_id' => $user->id,
            'subject_id' => $data['subject_id'],
            'mode' => 'practice',
            'status' => 'started',
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/question/answer', [
                'study_session_id' => $sessionId,
                'question_id' => $data['question_id'],
                'selected_option_id' => $data['correct_option_id'],
                'response_time_ms' => 1800,
                'confidence_level' => 5,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('question_attempts', [
            'user_id' => $user->id,
            'question_id' => $data['question_id'],
            'study_session_id' => $sessionId,
            'selected_option_id' => $data['correct_option_id'],
            'is_correct' => true,
        ]);

        $this->assertDatabaseHas('review_queue', [
            'user_id' => $user->id,
            'question_id' => $data['question_id'],
            'interval_days' => 7,
            'state' => 'pending',
            'consecutive_correct' => 1,
        ]);

        $this->assertDatabaseHas('user_topic_stats', [
            'user_id' => $user->id,
            'topic_id' => $data['topic_id'],
            'attempts_count' => 1,
            'correct_count' => 1,
        ]);

        $this->assertDatabaseHas('memory_scores', [
            'user_id' => $user->id,
            'topic_id' => $data['topic_id'],
            'samples' => 1,
        ]);

        $this->assertDatabaseHas('xp_logs', [
            'user_id' => $user->id,
            'source' => 'question_answer',
            'xp_delta' => 10,
            'source_ref_type' => 'question',
            'source_ref_id' => $data['question_id'],
        ]);
    }

    public function test_review_queue_returns_due_items_only(): void
    {
        $user = User::factory()->create();
        $data = $this->createQuestionData();

        DB::table('review_queue')->insert([
            [
                'user_id' => $user->id,
                'question_id' => $data['question_id'],
                'due_date' => now()->subDay()->toDateString(),
                'interval_days' => 1,
                'ease_factor' => 2.5,
                'lapse_count' => 0,
                'consecutive_correct' => 0,
                'state' => 'pending',
                'last_reviewed_at' => null,
                'next_review_at' => now()->subDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $futureQuestionId = DB::table('questions')->insertGetId([
            'topic_id' => $data['topic_id'],
            'exam_board_id' => null,
            'difficulty' => 'easy',
            'stem_type' => 'multiple_choice',
            'stem' => 'Questao futura',
            'support_text' => null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('review_queue')->insert([
            'user_id' => $user->id,
            'question_id' => $futureQuestionId,
            'due_date' => now()->addDays(2)->toDateString(),
            'interval_days' => 2,
            'ease_factor' => 2.5,
            'lapse_count' => 0,
            'consecutive_correct' => 0,
            'state' => 'pending',
            'last_reviewed_at' => null,
            'next_review_at' => now()->addDays(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/review-queue');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($data['question_id'], $response->json('data.0.question_id'));
    }

    private function createQuestionData(): array
    {
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

        $questionId = DB::table('questions')->insertGetId([
            'topic_id' => $topicId,
            'exam_board_id' => null,
            'difficulty' => 'medium',
            'stem_type' => 'multiple_choice',
            'stem' => 'Qual alternativa esta correta?',
            'support_text' => null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $correctOptionId = DB::table('question_options')->insertGetId([
            'question_id' => $questionId,
            'label' => 'A',
            'content' => 'Opcao correta',
            'is_correct' => true,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('question_options')->insert([
            'question_id' => $questionId,
            'label' => 'B',
            'content' => 'Opcao incorreta',
            'is_correct' => false,
            'sort_order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'subject_id' => $subjectId,
            'topic_id' => $topicId,
            'question_id' => $questionId,
            'correct_option_id' => $correctOptionId,
        ];
    }
}
