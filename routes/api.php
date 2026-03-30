<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskerController;
use App\Http\Controllers\TestimonialController;

// New Controllers
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SubTaskController;
use App\Http\Controllers\TaskRequestController;
use App\Http\Controllers\TaskerRecommendationController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\TaskerDashboardController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/check-upload-limits', function() {
    return [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
    ];
});

Route::get('/check-test-cloudinary', function() {
    return [
        'cloud_name' => config('cloudinary.cloud_name') ?? env('CLOUDINARY_CLOUD_NAME'),
        'api_key' => config('cloudinary.api_key') ?? env('CLOUDINARY_API_KEY'),
        'has_secret' => (config('cloudinary.api_secret') ?? env('CLOUDINARY_API_SECRET')) ? 'yes' : 'no'
    ];
});

// Authentication routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('logout', [AuthController::class, 'logout']);
});

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// ========================================
// PUBLIC FRONTEND API ROUTES
// ========================================
Route::prefix('open')->group(function () {
    Route::get('/faqs', [FaqController::class, 'index']);
    Route::get('/testimonials', [TestimonialController::class, 'index']);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/taskers', [TaskerController::class, 'index']);

    Route::get('/taskers/search', [TaskerController::class, 'search']);

    // New: Public task browsing
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::get('/sub-tasks', [SubTaskController::class, 'index']);
    Route::get('/tasks/{taskId}/sub-tasks', [SubTaskController::class, 'getByTask']);
});

// ========================================
// TASK REQUEST SUBMISSION (Public)
// ========================================
Route::post('/task-requests', [TaskRequestController::class, 'store']);

// ========================================
// TASKER ROUTES (Public signup & verification)
// ========================================
Route::post('/taskers', [TaskerController::class, 'store']);
Route::post('/taskers/request-verification', [TaskerController::class, 'requestVerification']);
Route::post('/taskers/verify-code', [TaskerController::class, 'verifyCode']);

// Tasker authenticated routes (using access token)
Route::middleware(['tasker.auth'])->prefix('tasker')->group(function () {
    Route::get('/userdata', [TaskerController::class, 'getTasker']);
    Route::put('/profile', [TaskerController::class, 'updates']);

    // Tasker Dashboard
    Route::get('/dashboard/overview', [TaskerDashboardController::class, 'overview']);
    Route::get('/dashboard/tasks', [TaskerDashboardController::class, 'assignedTasks']);
    Route::get('/dashboard/tasks/pending', [TaskerDashboardController::class, 'pendingTasks']);
    Route::get('/dashboard/tasks/completed', [TaskerDashboardController::class, 'completedTasks']);
    Route::get('/dashboard/analytics', [TaskerDashboardController::class, 'analytics']);
    Route::post('/dashboard/tasks/{id}/complete', [TaskerDashboardController::class, 'completeTask']);
});

// ========================================
// ADMIN ROUTES
// ========================================
Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // User Profile
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);

    // ========================================
    // TASKS CRUD
    // ========================================
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/{id}', [TaskController::class, 'show']);
        Route::put('/{id}', [TaskController::class, 'update']);
        Route::delete('/{id}', [TaskController::class, 'destroy']);
        Route::put('/{id}/toggle-status', [TaskController::class, 'toggleStatus']);
    });

    // ========================================
    // SUB-TASKS CRUD
    // ========================================
    Route::prefix('sub-tasks')->group(function () {
        Route::get('/', [SubTaskController::class, 'index']);
        Route::post('/', [SubTaskController::class, 'store']);
        Route::get('/{id}', [SubTaskController::class, 'show']);
        Route::put('/{id}', [SubTaskController::class, 'update']);
        Route::delete('/{id}', [SubTaskController::class, 'destroy']);
        Route::put('/{id}/toggle-status', [SubTaskController::class, 'toggleStatus']);
    });

    // ========================================
    // TASK REQUESTS MANAGEMENT
    // ========================================
    Route::prefix('task-requests')->group(function () {
        Route::get('/', [TaskRequestController::class, 'index']);
        Route::get('/{id}', [TaskRequestController::class, 'show']);
        Route::put('/{id}', [TaskRequestController::class, 'update']);
        Route::delete('/{id}', [TaskRequestController::class, 'destroy']);

        // Assignment actions
        Route::post('/{id}/assign', [TaskRequestController::class, 'assignTasker']);
        Route::post('/{id}/complete', [TaskRequestController::class, 'complete']);
        Route::post('/{id}/cancel', [TaskRequestController::class, 'cancel']);
    });

    // ========================================
    // TASKER RECOMMENDATIONS
    // ========================================
    Route::prefix('recommendations')->group(function () {
        Route::get('/task-request/{id}', [TaskerRecommendationController::class, 'getRecommendations']);
        Route::get('/task-request/{id}/quick-match', [TaskerRecommendationController::class, 'quickMatch']);
    });

    // ========================================
    // ANALYTICS
    // ========================================
    Route::prefix('analytics')->group(function () {
        Route::get('/overview', [AnalyticsController::class, 'overview']);
        Route::get('/most-requested', [AnalyticsController::class, 'mostRequested']);
        Route::get('/requests-over-time', [AnalyticsController::class, 'requestsOverTime']);
        Route::get('/tasker-performance', [AnalyticsController::class, 'taskerPerformance']);
        Route::get('/status-breakdown', [AnalyticsController::class, 'statusBreakdown']);
        Route::get('/full-report', [AnalyticsController::class, 'fullReport']);
    });

    // ========================================
    // TASKER MANAGEMENT
    // ========================================
    Route::prefix('taskers')->group(function () {
        Route::get('/', [TaskerController::class, 'index']);
        Route::get('/{id}', [TaskerController::class, 'show']);
        Route::put('/{id}', [TaskerController::class, 'update']);
        Route::delete('/{id}', [TaskerController::class, 'destroy']);
        Route::put('/{id}/approve', [TaskerController::class, 'approve']);
        Route::put('/{id}/reject', [TaskerController::class, 'reject']);
    });

    // ========================================
    // SERVICES
    // ========================================
    Route::prefix('services')->group(function () {
        Route::get('/', [ServiceController::class, 'index']);
        Route::post('/', [ServiceController::class, 'store']);
        Route::put('/{service}', [ServiceController::class, 'update']);
        Route::delete('/{service}', [ServiceController::class, 'destroy']);
    });

    // ========================================
    // FAQs
    // ========================================
    Route::prefix('faqs')->group(function () {
        Route::get('/', [FaqController::class, 'index']);
        Route::post('/', [FaqController::class, 'store']);
        Route::get('/{id}', [FaqController::class, 'show']);
        Route::put('/{id}', [FaqController::class, 'update']);
        Route::delete('/{id}', [FaqController::class, 'destroy']);
    });

    // ========================================
    // TESTIMONIALS
    // ========================================
    Route::prefix('testimonials')->group(function () {
        Route::get('/', [TestimonialController::class, 'index']);
        Route::post('/', [TestimonialController::class, 'store']);
        Route::get('/{id}', [TestimonialController::class, 'show']);
        Route::put('/{id}', [TestimonialController::class, 'update']);
        Route::delete('/{id}', [TestimonialController::class, 'destroy']);
    });
});
