<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuditLogController;
use Illuminate\Support\Facades\URL;

if (config('app.env') === 'production') {
    URL::forceScheme('https');
}

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
//    // DISABLED: Registration is admin-only. See PageController login notes.
// Route::post('/auth/register', [AuthController::class, 'register']); // DISABLED: Critical Security Risk. Use Admin Panel to create users.
Route::get('/settings/public', [SettingsController::class, 'publicSettings']);
Route::post('/requests', [RequestController::class, 'store'])->middleware('throttle:10,1'); // Public submission - Rate limited

// Telegram Webhook
Route::any('/telegram/webhook', [App\Http\Controllers\TelegramController::class, 'handleWebhook']);

// Protected Routes
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Requests
    Route::get('/requests', [RequestController::class, 'index']);
    Route::get('/requests/{id}', [RequestController::class, 'show']);
    Route::put('/requests/{id}', [RequestController::class, 'update']);
    Route::put('/requests/{id}/status', [RequestController::class, 'updateStatus']);
    Route::put('/requests/{id}/archive', [RequestController::class, 'archive']);

    // Templates
    Route::apiResource('templates', TemplateController::class);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::put('/settings', [SettingsController::class, 'update']);

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index']);

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
});
