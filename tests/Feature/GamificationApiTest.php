<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GamificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_leaderboard(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $leaderboardId = DB::table('leaderboards')->insertGetId([
            'scope' => 'global',
            'period' => 'weekly',
            'period_start' => now()->startOfWeek()->toDateString(),
            'period_end' => now()->endOfWeek()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('leaderboard_entries')->insert([
            [
                'leaderboard_id' => $leaderboardId,
                'user_id' => $otherUser->id,
                'rank_position' => 1,
                'score' => 200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'leaderboard_id' => $leaderboardId,
                'user_id' => $user->id,
                'rank_position' => 2,
                'score' => 150,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/leaderboard');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.rank_position', 1);
    }

    public function test_authenticated_user_can_list_achievements_with_unlock_status(): void
    {
        $user = User::factory()->create();

        $achievementUnlockedId = DB::table('achievements')->insertGetId([
            'code' => 'first-session',
            'title' => 'Primeira Sessao',
            'description' => 'Complete uma sessao de estudos',
            'xp_reward' => 50,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('achievements')->insert([
            'code' => 'seven-day-streak',
            'title' => 'Sete Dias',
            'description' => 'Mantenha 7 dias de sequencia',
            'xp_reward' => 100,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('user_achievements')->insert([
            'user_id' => $user->id,
            'achievement_id' => $achievementUnlockedId,
            'unlocked_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/achievements');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        $this->assertTrue((bool) $response->json('data.0.unlocked'));
        $this->assertFalse((bool) $response->json('data.1.unlocked'));
    }

    public function test_authenticated_user_can_fetch_daily_goal(): void
    {
        $user = User::factory()->create();

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

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/daily-goal');

        $response
            ->assertOk()
            ->assertJsonPath('data.target_questions', 20)
            ->assertJsonPath('data.completed_questions', 8)
            ->assertJsonPath('data.progress_percent', 40);
    }
}
