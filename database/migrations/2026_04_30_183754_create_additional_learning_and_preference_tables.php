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
        Schema::create('user_exam_boards', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_board_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->primary(['user_id', 'exam_board_id']);
        });

        Schema::create('user_subjects', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->primary(['user_id', 'subject_id']);
        });

        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('not_started');
            $table->unsignedTinyInteger('completion_percent')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->unsignedInteger('total_time_seconds')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'lesson_id']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('user_question_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('last_attempt_id')->nullable()->constrained('question_attempts')->nullOnDelete();
            $table->unsignedInteger('attempts_count')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('wrong_count')->default(0);
            $table->boolean('last_was_correct')->nullable();
            $table->timestamp('first_answered_at')->nullable();
            $table->timestamp('last_answered_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'question_id']);
            $table->index(['user_id', 'last_answered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_question_history');
        Schema::dropIfExists('lesson_progress');
        Schema::dropIfExists('user_subjects');
        Schema::dropIfExists('user_exam_boards');
    }
};
