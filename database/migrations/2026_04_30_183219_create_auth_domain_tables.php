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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('avatar_url')->nullable();
            $table->string('target_exam')->nullable();
            $table->integer('timezone_offset')->default(0);
            $table->timestamps();
        });

        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 20);
            $table->string('push_token')->nullable();
            $table->string('app_version', 20)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'platform']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan_code');
            $table->string('status', 30)->default('trial');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('provider', 30)->default('manual');
            $table->string('external_id')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('primary_board_id')->nullable();
            $table->integer('daily_goal_questions')->default(20);
            $table->integer('daily_goal_minutes')->default(45);
            $table->string('preferred_difficulty', 30)->default('mixed');
            $table->boolean('notifications_enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('profiles');
    }
};
