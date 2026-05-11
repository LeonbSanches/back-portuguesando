<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'topic_id' => $this->topic_id,
            'title' => $this->title,
            'content_md' => $this->content_md,
            'estimated_minutes' => $this->estimated_minutes,
            'published' => (bool) $this->published,
            'topic' => [
                'id' => $this->topic_ref_id,
                'name' => $this->topic_name,
                'slug' => $this->topic_slug,
            ],
        ];
    }
}
