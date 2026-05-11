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
        Schema::create('performance_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('accuracy_rate', 5, 2)->default(0);
            $table->unsignedInteger('solved_questions')->default(0);
            $table->unsignedInteger('reviewed_questions')->default(0);
            $table->unsignedInteger('study_time_seconds')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'period_start', 'period_end']);
        });

        Schema::create('weak_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->decimal('weakness_score', 5, 2)->default(0);
            $table->unsignedInteger('window_attempts')->default(0);
            $table->timestamp('detected_at');
            $table->timestamps();
            $table->unique(['user_id', 'topic_id']);
        });

        Schema::create('study_time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('duration_seconds');
            $table->timestamp('logged_at');
            $table->timestamps();
            $table->index(['user_id', 'logged_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_time_logs');
        Schema::dropIfExists('weak_topics');
        Schema::dropIfExists('performance_reports');
    }
};
