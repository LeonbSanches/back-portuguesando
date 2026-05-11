<?php

namespace App\Actions\Learning;

use Illuminate\Support\Facades\DB;

class StartStudySessionAction
{
    /**
     * @param array{subject_id?:int|null,mode?:string|null} $payload
     */
    public function execute(int $userId, array $payload): object
    {
        $sessionId = DB::table('study_sessions')->insertGetId([
            'user_id' => $userId,
            'subject_id' => $payload['subject_id'] ?? null,
            'mode' => $payload['mode'] ?? 'practice',
            'status' => 'started',
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('study_sessions')->where('id', $sessionId)->first();
    }
}
