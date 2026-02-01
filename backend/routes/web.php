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
Route::middleware(['auth', 'twofactor'])->prefix('admin')->name('admin.')->group(function () {

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

        // Export
        Route::get('/requests/export', [AdminController::class, 'exportRequests'])->name('requests.export');
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

        // Email Templates
        // Email Templates
        Route::get('/email-templates', [App\Http\Controllers\Admin\EmailTemplateController::class, 'index'])->name('email-templates.index');
        Route::get('/email-templates/{id}/edit', [App\Http\Controllers\Admin\EmailTemplateController::class, 'edit'])->name('email-templates.edit');
        Route::put('/email-templates/{id}', [App\Http\Controllers\Admin\EmailTemplateController::class, 'update'])->name('email-templates.update');

        // System Tools
        Route::get('/system-tools', [AdminController::class, 'systemTools'])->name('system-tools');
        Route::post('/system-tools/migrate', [AdminController::class, 'runMigrations'])->name('system-tools.migrate');
        Route::post('/system-tools/clear-cache', [AdminController::class, 'clearCache'])->name('system-tools.clear-cache');
    });
    // 2FA Settings
    Route::get('/settings/security', [App\Http\Controllers\TwoFactorController::class, 'index'])->name('settings.security');
    Route::post('/settings/security/enable', [App\Http\Controllers\TwoFactorController::class, 'enable'])->name('settings.security.enable');
    Route::post('/settings/security/confirm', [App\Http\Controllers\TwoFactorController::class, 'confirm'])->name('settings.security.confirm');
    Route::delete('/settings/security', [App\Http\Controllers\TwoFactorController::class, 'disable'])->name('settings.security.disable');

    // 2FA Verification (Accessible even if 2fa_verified is false)
    Route::get('/2fa/verify', [App\Http\Controllers\TwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::post('/2fa/verify', [App\Http\Controllers\TwoFactorController::class, 'verifyPost'])->middleware('throttle:5,1')->name('2fa.verify.post');
    Route::post('/2fa/resend', [App\Http\Controllers\TwoFactorController::class, 'resend'])->middleware('throttle:3,1')->name('2fa.resend');
});
