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
        Schema::create('exam_boards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('acronym', 20)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_topic_id')->nullable()->constrained('topics')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['subject_id', 'slug']);
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('content_md');
            $table->unsignedInteger('estimated_minutes')->default(5);
            $table->boolean('published')->default(false);
            $table->timestamps();
            $table->index(['topic_id', 'published']);
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_board_id')->nullable()->constrained('exam_boards')->nullOnDelete();
            $table->string('difficulty', 20)->default('medium');
            $table->string('stem_type', 20)->default('multiple_choice');
            $table->longText('stem');
            $table->longText('support_text')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index(['topic_id', 'exam_board_id', 'difficulty', 'active']);
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('label', 5);
            $table->text('content');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['question_id', 'label']);
        });

        Schema::create('explanations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->unique()->constrained()->cascadeOnDelete();
            $table->longText('explanation_md');
            $table->text('quick_tip')->nullable();
            $table->string('source_type', 30)->default('editorial');
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('question_tags', function (Blueprint $table) {
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['question_id', 'tag_id']);
        });

        Schema::table('user_preferences', function (Blueprint $table) {
            $table->foreign('primary_board_id')->references('id')->on('exam_boards')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropForeign(['primary_board_id']);
        });

        Schema::dropIfExists('question_tags');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('explanations');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('topics');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('exam_boards');
    }
};
