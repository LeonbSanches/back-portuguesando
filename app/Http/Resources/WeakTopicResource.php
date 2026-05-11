<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WeakTopicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'topic_id' => $this->topic_id,
            'weakness_score' => (float) $this->weakness_score,
            'window_attempts' => $this->window_attempts,
            'detected_at' => $this->detected_at,
        ];
    }
}
