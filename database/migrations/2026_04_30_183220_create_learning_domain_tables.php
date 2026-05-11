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
        Schema::create('study_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mode', 30)->default('practice');
            $table->string('status', 30)->default('started');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('correct_answers')->default(0);
            $table->unsignedInteger('total_time_seconds')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'started_at']);
        });

        Schema::create('question_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('selected_option_id')->nullable()->constrained('question_options')->nullOnDelete();
            $table->boolean('is_correct');
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->unsignedTinyInteger('confidence_level')->nullable();
            $table->string('answer_context', 30)->default('lesson');
            $table->timestamp('answered_at');
            $table->timestamps();
            $table->index(['user_id', 'answered_at']);
            $table->index(['user_id', 'question_id', 'answered_at']);
        });

        Schema::create('review_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->date('due_date')->nullable();
            $table->unsignedInteger('interval_days')->default(1);
            $table->decimal('ease_factor', 4, 2)->default(2.50);
            $table->unsignedInteger('lapse_count')->default(0);
            $table->unsignedInteger('consecutive_correct')->default(0);
            $table->string('state', 20)->default('pending');
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamp('next_review_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'question_id']);
            $table->index(['user_id', 'due_date', 'state']);
            $table->index(['user_id', 'next_review_at']);
        });

        Schema::create('memory_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2)->default(0);
            $table->unsignedInteger('samples')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'topic_id']);
        });

        Schema::create('user_topic_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('attempts_count')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('avg_response_time_ms')->default(0);
            $table->decimal('accuracy_rate', 5, 2)->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'topic_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_topic_stats');
        Schema::dropIfExists('memory_scores');
        Schema::dropIfExists('review_queue');
        Schema::dropIfExists('question_attempts');
        Schema::dropIfExists('study_sessions');
    }
};
