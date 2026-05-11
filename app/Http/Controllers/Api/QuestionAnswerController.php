<?php

namespace App\Http\Controllers\Api;

use App\Actions\Learning\AnswerQuestionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Study\StoreQuestionAnswerRequest;

class QuestionAnswerController extends Controller
{
    public function __construct(
        private readonly AnswerQuestionAction $answerQuestionAction
    ) {
    }

    public function store(StoreQuestionAnswerRequest $request)
    {
        $result = $this->answerQuestionAction->execute(
            userId: $request->user()->id,
            payload: $request->validated()
        );

        return response()->json($result);
    }
}
