<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ContentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_subjects(): void
    {
        $user = User::factory()->create();

        DB::table('subjects')->insert([
            [
                'name' => 'Portugues',
                'slug' => 'portugues',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Direito Constitucional',
                'slug' => 'direito-constitucional',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/subjects');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'direito-constitucional');
    }

    public function test_authenticated_user_can_get_topic_with_lessons_and_questions_count(): void
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

        DB::table('lessons')->insert([
            'topic_id' => $topicId,
            'title' => 'Concordancia Nominal',
            'content_md' => 'Conteudo de teste',
            'estimated_minutes' => 8,
            'published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('questions')->insert([
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

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson("/api/topics/{$topicId}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $topicId)
            ->assertJsonPath('data.lessons_count', 1)
            ->assertJsonPath('data.questions_count', 1);
    }

    public function test_authenticated_user_can_get_lesson_details(): void
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
            'name' => 'Acentuacao',
            'slug' => 'acentuacao',
            'sort_order' => 1,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $lessonId = DB::table('lessons')->insertGetId([
            'topic_id' => $topicId,
            'title' => 'Regras de Acentuacao',
            'content_md' => 'Texto da licao',
            'estimated_minutes' => 10,
            'published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson("/api/lesson/{$lessonId}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $lessonId)
            ->assertJsonPath('data.title', 'Regras de Acentuacao')
            ->assertJsonPath('data.topic.id', $topicId);
    }
}
