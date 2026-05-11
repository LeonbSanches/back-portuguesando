<?php

namespace App\Actions\Learning;

use App\Services\SpacedRepetitionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnswerQuestionAction
{
    public function __construct(
        private readonly SpacedRepetitionService $spacedRepetitionService
    ) {
    }

    /**
     * @param array{
     *   question_id:int,
     *   selected_option_id:int,
     *   study_session_id?:int|null,
     *   response_time_ms?:int|null,
     *   confidence_level?:int|null,
     *   answer_context?:string|null
     * } $payload
     *
     * @return array{
     *   is_correct:bool,
     *   next_review_at:string,
     *   interval_days:int,
     *   state:string
     * }
     */
    public function execute(int $userId, array $payload): array
    {
        $question = DB::table('questions')
            ->select('id', 'topic_id')
            ->where('id', $payload['question_id'])
            ->first();

        if (! $question) {
            throw ValidationException::withMessages([
                'question_id' => ['Questão inválida.'],
            ]);
        }

        $selectedOption = DB::table('question_options')
            ->where('id', $payload['selected_option_id'])
            ->where('question_id', $payload['question_id'])
            ->first();

        if (! $selectedOption) {
            throw ValidationException::withMessages([
                'selected_option_id' => ['A opção selecionada não pertence a essa questão.'],
            ]);
        }

        $isCorrect = (bool) $selectedOption->is_correct;

        DB::table('question_attempts')->insert([
            'user_id' => $userId,
            'question_id' => $payload['question_id'],
            'study_session_id' => $payload['study_session_id'] ?? null,
            'selected_option_id' => $payload['selected_option_id'],
            'is_correct' => $isCorrect,
            'response_time_ms' => $payload['response_time_ms'] ?? null,
            'confidence_level' => $payload['confidence_level'] ?? null,
            'answer_context' => $payload['answer_context'] ?? 'lesson',
            'answered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (! empty($payload['study_session_id'])) {
            DB::table('study_sessions')
                ->where('id', $payload['study_session_id'])
                ->where('user_id', $userId)
                ->increment('total_questions');

            if ($isCorrect) {
                DB::table('study_sessions')
                    ->where('id', $payload['study_session_id'])
                    ->where('user_id', $userId)
                    ->increment('correct_answers');
            }
        }

        $currentQueue = DB::table('review_queue')
            ->where('user_id', $userId)
            ->where('question_id', $payload['question_id'])
            ->first();

        $next = $this->spacedRepetitionService->calculate(
            currentQueue: $currentQueue,
            isCorrect: $isCorrect,
            confidenceLevel: $payload['confidence_level'] ?? null
        );

        DB::table('review_queue')->updateOrInsert(
            [
                'user_id' => $userId,
                'question_id' => $payload['question_id'],
            ],
            [
                'due_date' => $next['next_review_at']->toDateString(),
                'interval_days' => $next['interval_days'],
                'ease_factor' => $next['next_ease_factor'],
                'lapse_count' => $next['next_lapse_count'],
                'consecutive_correct' => $next['next_consecutive'],
                'state' => $next['next_state'],
                'last_reviewed_at' => now(),
                'next_review_at' => $next['next_review_at'],
                'updated_at' => now(),
                'created_at' => $currentQueue ? $currentQueue->created_at : now(),
            ]
        );

        $currentTopicStats = DB::table('user_topic_stats')
            ->where('user_id', $userId)
            ->where('topic_id', $question->topic_id)
            ->first();

        $attemptsCount = (int) ($currentTopicStats?->attempts_count ?? 0) + 1;
        $correctCount = (int) ($currentTopicStats?->correct_count ?? 0) + ($isCorrect ? 1 : 0);
        $previousAvg = (int) ($currentTopicStats?->avg_response_time_ms ?? 0);
        $responseTime = (int) ($payload['response_time_ms'] ?? 0);
        $avgResponseTime = $attemptsCount > 0
            ? (int) floor((($previousAvg * ($attemptsCount - 1)) + $responseTime) / $attemptsCount)
            : 0;
        $accuracyRate = $attemptsCount > 0 ? round(($correctCount / $attemptsCount) * 100, 2) : 0;

        DB::table('user_topic_stats')->updateOrInsert(
            [
                'user_id' => $userId,
                'topic_id' => $question->topic_id,
            ],
            [
                'attempts_count' => $attemptsCount,
                'correct_count' => $correctCount,
                'avg_response_time_ms' => $avgResponseTime,
                'accuracy_rate' => $accuracyRate,
                'updated_at' => now(),
                'created_at' => $currentTopicStats ? $currentTopicStats->created_at : now(),
            ]
        );

        $currentMemoryScore = DB::table('memory_scores')
            ->where('user_id', $userId)
            ->where('topic_id', $question->topic_id)
            ->first();

        $samples = (int) ($currentMemoryScore?->samples ?? 0) + 1;
        $previousScore = (float) ($currentMemoryScore?->score ?? 0);
        $attemptScore = $isCorrect ? 100 : 30;
        $nextScore = round((($previousScore * ($samples - 1)) + $attemptScore) / $samples, 2);

        DB::table('memory_scores')->updateOrInsert(
            [
                'user_id' => $userId,
                'topic_id' => $question->topic_id,
            ],
            [
                'score' => $nextScore,
                'samples' => $samples,
                'last_activity_at' => now(),
                'updated_at' => now(),
                'created_at' => $currentMemoryScore ? $currentMemoryScore->created_at : now(),
            ]
        );

        DB::table('xp_logs')->insert([
            'user_id' => $userId,
            'source' => 'question_answer',
            'xp_delta' => $isCorrect ? 10 : 2,
            'source_ref_type' => 'question',
            'source_ref_id' => $payload['question_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'is_correct' => $isCorrect,
            'next_review_at' => $next['next_review_at']->toISOString(),
            'interval_days' => $next['interval_days'],
            'state' => $next['next_state'],
        ];
    }
}
