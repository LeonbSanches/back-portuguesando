<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'accuracy_rate' => (float) $this->accuracy_rate,
            'solved_questions' => $this->solved_questions,
            'reviewed_questions' => $this->reviewed_questions,
            'study_time_seconds' => $this->study_time_seconds,
            'period_start' => $this->period_start ?? null,
            'period_end' => $this->period_end ?? null,
        ];
    }
}
