<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LessonResource;
use App\Http\Resources\TopicResource;
use Illuminate\Support\Facades\DB;

class ContentController extends Controller
{
    public function subjects()
    {
        $subjects = DB::table('subjects')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $subjects]);
    }

    public function topic(int $id)
    {
        $topic = DB::table('topics')
            ->where('id', $id)
            ->first();

        abort_if(! $topic, 404);

        $lessonsCount = DB::table('lessons')
            ->where('topic_id', $id)
            ->count();

        $questionsCount = DB::table('questions')
            ->where('topic_id', $id)
            ->count();

        $topic->lessons_count = $lessonsCount;
        $topic->questions_count = $questionsCount;

        return response()->json(['data' => new TopicResource($topic)]);
    }

    public function lesson(int $id)
    {
        $lesson = DB::table('lessons')
            ->join('topics', 'topics.id', '=', 'lessons.topic_id')
            ->select(
                'lessons.id',
                'lessons.topic_id',
                'lessons.title',
                'lessons.content_md',
                'lessons.estimated_minutes',
                'lessons.published',
                'topics.id as topic_ref_id',
                'topics.name as topic_name',
                'topics.slug as topic_slug'
            )
            ->where('lessons.id', $id)
            ->first();

        abort_if(! $lesson, 404);

        return response()->json(['data' => new LessonResource($lesson)]);
    }
}
