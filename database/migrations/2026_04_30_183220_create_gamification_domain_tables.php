<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('current_days')->default(0);
            $table->unsignedInteger('best_days')->default(0);
            $table->date('last_study_date')->nullable();
            $table->timestamps();
        });

        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('xp_reward')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->timestamp('unlocked_at');
            $table->timestamps();
            $table->unique(['user_id', 'achievement_id']);
        });

        Schema::create('xp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source', 50);
            $table->integer('xp_delta');
            $table->string('source_ref_type', 50)->nullable();
            $table->unsignedBigInteger('source_ref_id')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('daily_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('goal_date');
            $table->unsignedInteger('target_questions')->default(20);
            $table->unsignedInteger('completed_questions')->default(0);
            $table->unsignedInteger('target_minutes')->default(45);
            $table->unsignedInteger('completed_minutes')->default(0);
            $table->string('status', 20)->default('in_progress');
            $table->timestamps();
            $table->unique(['user_id', 'goal_date']);
        });

        Schema::create('leaderboards', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 20)->default('global');
            $table->string('period', 20)->default('weekly');
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamps();
            $table->index(['scope', 'period', 'period_start']);
        });

        Schema::create('leaderboard_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leaderboard_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('rank_position');
            $table->integer('score')->default(0);
            $table->timestamps();
            $table->unique(['leaderboard_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboard_entries');
        Schema::dropIfExists('leaderboards');
        Schema::dropIfExists('daily_goals');
        Schema::dropIfExists('xp_logs');
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('streaks');
    }
};
