<?php

namespace App\Http\Controllers\Api;

use App\Actions\Learning\StartStudySessionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Study\StartStudySessionRequest;
use App\Http\Resources\StudySessionResource;

class StudySessionController extends Controller
{
    public function __construct(
        private readonly StartStudySessionAction $startStudySessionAction
    ) {
    }

    public function start(StartStudySessionRequest $request)
    {
        $session = $this->startStudySessionAction->execute(
            userId: $request->user()->id,
            payload: $request->validated()
        );

        return response()->json(['session' => new StudySessionResource($session)], 201);
    }
}
