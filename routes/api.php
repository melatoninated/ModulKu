<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\QuizAttemptController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // Authentication
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Subjects
    Route::apiResource('subjects', SubjectController::class);

    // Materials
    Route::apiResource('materials', MaterialController::class);

    // Quizzes
    Route::apiResource('quizzes', QuizController::class);

    // Questions
    Route::post('/quizzes/{quiz}/questions', [QuestionController::class, 'store']);
    Route::put('/questions/{question}', [QuestionController::class, 'update']);
    Route::patch('/questions/{question}', [QuestionController::class, 'update']);
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy']);

    // Quiz Attempts
    Route::get('/attempts', [QuizAttemptController::class, 'index']);
    Route::post('/quizzes/{quiz}/attempts', [QuizAttemptController::class, 'start']);
    Route::post('/attempts/{attempt}/submit', [QuizAttemptController::class, 'submit']);
    Route::get('/attempts/{attempt}', [QuizAttemptController::class, 'show']);
});
