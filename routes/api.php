<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SubTaskController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskerController;
use App\Http\Controllers\TaskerDashboardController;
use App\Http\Controllers\TaskerRecommendationController;
use App\Http\Controllers\TaskerRegistrationController;
use App\Http\Controllers\TaskRequestController;
use App\Http\Controllers\TestimonialController;
use Illuminate\Support\Facades\Route;

// ========================================
// AUTH
// ========================================
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Generic email verification — works for any role (customer + tasker).
// Tasker-specific aliases below are kept for frontend back-compat.
Route::post('auth/verify-code', [AuthController::class, 'verifyCode']);
Route::post('auth/request-verification', [AuthController::class, 'requestVerification']);

// Password reset — both endpoints always return 200 to prevent email enumeration.
Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:api')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('logout', [AuthController::class, 'logout']); // back-compat
});

// ========================================
// PUBLIC READS
// ========================================
Route::prefix('open')->group(function () {
    Route::get('/faqs', [FaqController::class, 'index']);
    Route::get('/testimonials', [TestimonialController::class, 'index']);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/taskers', [TaskerController::class, 'index']);
    Route::get('/taskers/search', [TaskerController::class, 'search']);

    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::get('/sub-tasks', [SubTaskController::class, 'index']);
    Route::get('/tasks/{taskId}/sub-tasks', [SubTaskController::class, 'getByTask']);
});

// ========================================
// PUBLIC WRITES
// ========================================
Route::post('/task-requests', [TaskRequestController::class, 'store']);

Route::post('/taskers', [TaskerRegistrationController::class, 'register']);
Route::post('/taskers/request-verification', [TaskerRegistrationController::class, 'requestVerification']);
Route::post('/taskers/verify-code', [TaskerRegistrationController::class, 'verifyCode']);

// ========================================
// TASKER (authenticated, role=tasker)
// ========================================
Route::middleware(['auth:api', 'role:tasker'])->prefix('tasker')->group(function () {
    Route::get('/userdata', [AuthController::class, 'me']);
    Route::put('/profile', [TaskerController::class, 'update']);

    Route::get('/dashboard/overview', [TaskerDashboardController::class, 'overview']);
    Route::get('/dashboard/tasks', [TaskerDashboardController::class, 'assignedTasks']);
    Route::get('/dashboard/tasks/pending', [TaskerDashboardController::class, 'pendingTasks']);
    Route::get('/dashboard/tasks/completed', [TaskerDashboardController::class, 'completedTasks']);
    Route::get('/dashboard/analytics', [TaskerDashboardController::class, 'analytics']);
    Route::post('/dashboard/tasks/{id}/complete', [TaskerDashboardController::class, 'completeTask']);
});

// ========================================
// ADMIN
// ========================================
Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);

    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/{id}', [TaskController::class, 'show']);
        Route::put('/{id}', [TaskController::class, 'update']);
        Route::delete('/{id}', [TaskController::class, 'destroy']);
        Route::put('/{id}/toggle-status', [TaskController::class, 'toggleStatus']);
    });

    Route::prefix('sub-tasks')->group(function () {
        Route::get('/', [SubTaskController::class, 'index']);
        Route::post('/', [SubTaskController::class, 'store']);
        Route::get('/{id}', [SubTaskController::class, 'show']);
        Route::put('/{id}', [SubTaskController::class, 'update']);
        Route::delete('/{id}', [SubTaskController::class, 'destroy']);
        Route::put('/{id}/toggle-status', [SubTaskController::class, 'toggleStatus']);
    });

    Route::prefix('task-requests')->group(function () {
        Route::get('/', [TaskRequestController::class, 'index']);
        Route::get('/{id}', [TaskRequestController::class, 'show']);
        Route::put('/{id}', [TaskRequestController::class, 'update']);
        Route::delete('/{id}', [TaskRequestController::class, 'destroy']);

        Route::post('/{id}/assign', [TaskRequestController::class, 'assignTasker']);
        Route::post('/{id}/approve', [TaskRequestController::class, 'approve']);
        Route::post('/{id}/reject', [TaskRequestController::class, 'reject']);
        Route::post('/{id}/complete', [TaskRequestController::class, 'complete']);
        Route::post('/{id}/cancel', [TaskRequestController::class, 'cancel']);
    });

    Route::prefix('recommendations')->group(function () {
        Route::get('/task-request/{id}', [TaskerRecommendationController::class, 'getRecommendations']);
        Route::get('/task-request/{id}/quick-match', [TaskerRecommendationController::class, 'quickMatch']);
    });

    Route::prefix('analytics')->group(function () {
        Route::get('/overview', [AnalyticsController::class, 'overview']);
        Route::get('/most-requested', [AnalyticsController::class, 'mostRequested']);
        Route::get('/requests-over-time', [AnalyticsController::class, 'requestsOverTime']);
        Route::get('/tasker-performance', [AnalyticsController::class, 'taskerPerformance']);
        Route::get('/status-breakdown', [AnalyticsController::class, 'statusBreakdown']);
        Route::get('/full-report', [AnalyticsController::class, 'fullReport']);
    });

    Route::prefix('taskers')->group(function () {
        Route::get('/', [TaskerController::class, 'index']);
        Route::get('/{id}', [TaskerController::class, 'show']);
        Route::put('/{id}', [TaskerController::class, 'update']);
        Route::delete('/{id}', [TaskerController::class, 'destroy']);
        Route::put('/{id}/approve', [TaskerController::class, 'approve']);
        Route::put('/{id}/reject', [TaskerController::class, 'reject']);
    });

    Route::prefix('services')->group(function () {
        Route::get('/', [ServiceController::class, 'index']);
        Route::post('/', [ServiceController::class, 'store']);
        Route::put('/{service}', [ServiceController::class, 'update']);
        Route::delete('/{service}', [ServiceController::class, 'destroy']);
    });

    Route::prefix('faqs')->group(function () {
        Route::get('/', [FaqController::class, 'index']);
        Route::post('/', [FaqController::class, 'store']);
        Route::get('/{id}', [FaqController::class, 'show']);
        Route::put('/{id}', [FaqController::class, 'update']);
        Route::delete('/{id}', [FaqController::class, 'destroy']);
    });

    Route::prefix('testimonials')->group(function () {
        Route::get('/', [TestimonialController::class, 'index']);
        Route::post('/', [TestimonialController::class, 'store']);
        Route::get('/{id}', [TestimonialController::class, 'show']);
        Route::put('/{id}', [TestimonialController::class, 'update']);
        Route::delete('/{id}', [TestimonialController::class, 'destroy']);
    });
});
