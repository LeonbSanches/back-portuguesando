<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyGoalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'goal_date' => $this->goal_date,
            'target_questions' => $this->target_questions,
            'completed_questions' => $this->completed_questions,
            'target_minutes' => $this->target_minutes,
            'completed_minutes' => $this->completed_minutes,
            'status' => $this->status,
            'progress_percent' => $this->progress_percent,
        ];
    }
}
