<?php

namespace App\Http\Controllers\Api;

use App\Actions\Learning\GetReviewQueueAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReviewQueueController extends Controller
{
    public function __construct(
        private readonly GetReviewQueueAction $getReviewQueueAction
    ) {
    }

    public function index(Request $request)
    {
        $items = $this->getReviewQueueAction->execute($request->user()->id);

        return response()->json(['data' => $items]);
    }
}
