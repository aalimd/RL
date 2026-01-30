<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AdminController;

if (config('app.env') === 'production') {
    URL::forceScheme('https');
}

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Pages
Route::get('/', [PageController::class, 'landing'])->name('home');
Route::get('/login', [PageController::class, 'showLogin'])->name('login');
Route::post('/login', [PageController::class, 'login'])->middleware('throttle:6,1');
Route::post('/logout', [PageController::class, 'logout'])->name('logout');
Route::get('/request', [PageController::class, 'publicRequest'])->name('public.request');
Route::post('/request', [PageController::class, 'handleWizard'])->middleware('throttle:10,1')->name('public.request.wizard');
Route::get('/track/{id?}', [PageController::class, 'tracking'])->name('public.tracking');
Route::post('/track', [PageController::class, 'doTracking'])->middleware('throttle:10,1')->name('public.tracking.post');
Route::get('/letter/{tracking_id}', [PageController::class, 'viewLetter'])->name('public.letter');
Route::get('/letter/{tracking_id}/pdf', [PageController::class, 'downloadPdf'])->middleware('throttle:10,1')->name('public.letter.pdf');
Route::get('/verify/{token}', [App\Http\Controllers\VerificationController::class, 'verify'])->name('public.verify');

// Admin Panel (protected by auth)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {

    // ============================================
    // Routes accessible by ALL authenticated users (admin, editor, viewer)
    // ============================================
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/requests', [AdminController::class, 'requests'])->name('requests');
    Route::get('/requests/{id}', [AdminController::class, 'requestDetails'])->name('requests.show');
    Route::get('/requests/{id}/document', [AdminController::class, 'downloadDocument'])->name('requests.document');
    Route::get('/requests/{id}/preview', [AdminController::class, 'previewLetter'])->name('requests.preview');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    Route::get('/templates', [AdminController::class, 'templates'])->name('templates');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::get('/appearance', [AdminController::class, 'appearance'])->name('appearance');
    Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/form-settings', [AdminController::class, 'formSettings'])->name('form-settings');

    // Telegram Admin Actions
    Route::post('/settings/test-telegram', [App\Http\Controllers\TelegramController::class, 'testNotification'])->name('settings.test-telegram');
    Route::post('/settings/setup-webhook', [App\Http\Controllers\TelegramController::class, 'setupWebhook'])->name('settings.setup-webhook');

    // ============================================
    // Routes for ADMIN and EDITOR only
    // ============================================
    Route::middleware('role:admin,editor')->group(function () {
        // Request management
        Route::post('/requests/bulk', [AdminController::class, 'bulkAction'])->name('requests.bulk');
        Route::patch('/requests/{id}/status', [AdminController::class, 'updateRequestStatus'])->name('requests.update-status');
        Route::put('/requests/{id}', [AdminController::class, 'updateRequest'])->name('requests.update');
        Route::post('/requests/{id}/rewrite-ai', [AdminController::class, 'rewriteWithAi'])->name('requests.rewrite-ai');

        // Template management
        Route::get('/templates/create', [AdminController::class, 'createTemplate'])->name('templates.create');
        Route::post('/templates', [AdminController::class, 'storeTemplate'])->name('templates.store');
        Route::get('/templates/{id}/edit', [AdminController::class, 'editTemplate'])->name('templates.edit');
        Route::put('/templates/{id}', [AdminController::class, 'updateTemplate'])->name('templates.update');
        Route::post('/templates/{id}/reset', [AdminController::class, 'resetTemplate'])->name('templates.reset');
        Route::post('/templates/{id}/autosave', [AdminController::class, 'autoSaveTemplate'])->name('templates.autosave');
        Route::delete('/templates/{id}', [AdminController::class, 'deleteTemplate'])->name('templates.destroy');
    });

    // ============================================
    // Routes for ADMIN only
    // ============================================
    Route::middleware('role:admin')->group(function () {
        // User management
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.destroy');

        // System settings
        Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::put('/appearance', [AdminController::class, 'updateAppearance'])->name('appearance.update');
        Route::post('/settings/test-email', [AdminController::class, 'sendTestEmail'])->name('settings.test-email');
        Route::put('/form-settings', [AdminController::class, 'updateFormSettings'])->name('form-settings.update');
    });
});


