<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\GamificationController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\QuestionAnswerController;
use App\Http\Controllers\Api\ReviewQueueController;
use App\Http\Controllers\Api\StudySessionController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);
Route::get('/user', [AuthController::class, 'me'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/subjects', [ContentController::class, 'subjects']);
    Route::get('/topics/{id}', [ContentController::class, 'topic']);
    Route::get('/lesson/{id}', [ContentController::class, 'lesson']);
    Route::get('/leaderboard', [GamificationController::class, 'leaderboard']);
    Route::get('/achievements', [GamificationController::class, 'achievements']);
    Route::get('/daily-goal', [GamificationController::class, 'dailyGoal']);
    Route::get('/performance-report', [AnalyticsController::class, 'performanceReport']);
    Route::get('/weak-topics', [AnalyticsController::class, 'weakTopics']);
    Route::get('/study-time', [AnalyticsController::class, 'studyTime']);
    Route::get('/me/progress', [ProgressController::class, 'show']);

    Route::post('/study-session/start', [StudySessionController::class, 'start']);
    Route::post('/question/answer', [QuestionAnswerController::class, 'store']);
    Route::get('/review-queue', [ReviewQueueController::class, 'index']);
});
