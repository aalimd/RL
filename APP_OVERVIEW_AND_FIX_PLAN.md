# App Overview And Fix Plan

Last updated: 2026-04-22

## Purpose

This project is an academic recommendation letter system built on Laravel.

Main idea:

- Students or trainees submit a recommendation request from the public site.
- The system stores the request, generates a tracking ID, and supports status tracking.
- Admin users review, edit, approve, reject, or request revision for submissions.
- Approved requests can produce a rendered letter and PDF output.
- The app also includes templates, analytics, audit logs, email delivery, Telegram notifications, appearance settings, and a web installer.

## Stack And Structure

Main stack:

- PHP / Laravel app in `backend/`
- Root web entry through `index.php` and `.htaccess`
- Blade views in `backend/resources/views`
- Web routes in `backend/routes/web.php`
- API routes in `backend/routes/api.php`
- Installation wizard in `install/`

Important folders:

- `backend/app/Http/Controllers` - request handling for web and API
- `backend/app/Services` - letter generation, wizard validation, AI, Telegram, template helpers
- `backend/app/Http/Middleware` - auth, role, 2FA, maintenance checks
- `backend/resources/views` - public pages, admin pages, PDF templates
- `backend/app/Models` - database models such as `Request`, `User`, `Settings`, `Template`, `AuditLog`

## Main App Flows

### 1. Public Request Flow

Entry points:

- `GET /request`
- `POST /request`
- `POST /request/edit`

Main controller:

- `App\Http\Controllers\PageController`

Main functions:

- `publicRequest()` - loads the public wizard page
- `handleWizard()` - processes multi-step request submission
- `initializeEdit()` - opens an edit session for an existing request

Related services:

- `WizardService::getFormConfig()`
- `WizardService::getTemplates()`
- `WizardService::resolveContentData()`
- `WizardService::validateStep1()`
- `WizardService::validateStep2()`
- `WizardService::validateStep3()`

### 2. Tracking And Letter Access Flow

Entry points:

- `GET /track/{id?}`
- `POST /track`
- `GET /tracking/verify`
- `POST /tracking/verify`
- `GET /letter/{tracking_id}`
- `GET /letter/{tracking_id}/pdf`

Main controller:

- `App\Http\Controllers\PageController`

Main functions:

- `tracking()` - renders tracking page
- `doTracking()` - starts request tracking
- `show2FAVerify()` - shows tracking verification page
- `handle2FAVerify()` - verifies tracking code
- `viewLetter()` - displays approved letter
- `downloadPdf()` - downloads approved letter as PDF

### 3. Admin Web Panel

Entry points:

- `/admin/dashboard`
- `/admin/requests`
- `/admin/templates`
- `/admin/settings`
- `/admin/appearance`
- `/admin/users`
- `/admin/analytics`
- `/admin/audit-logs`
- `/admin/form-settings`
- `/admin/system-tools`

Main controller:

- `App\Http\Controllers\AdminController`

Important functions:

- `dashboard()` - dashboard metrics and summaries
- `requests()` - request listing and filtering
- `requestDetails()` - single request details page
- `updateRequestStatus()` - admin status changes
- `updateRequest()` - admin request editing
- `previewLetter()` - preview generated letter
- `downloadDocument()` - serves uploaded document attachment
- `templates()`, `createTemplate()`, `storeTemplate()`, `editTemplate()`, `updateTemplate()`, `deleteTemplate()`, `resetTemplate()`, `autoSaveTemplate()`
- `users()`, `storeUser()`, `updateUser()`, `deleteUser()`
- `settings()`, `updateSettings()`, `sendTestEmail()`
- `appearance()`, `updateAppearance()`, `resetAppearance()`
- `auditLogs()`
- `formSettings()`, `updateFormSettings()`
- `systemTools()`, `runMigrations()`, `clearCache()`
- `bulkAction()` - bulk approve, reject, archive, or delete requests

### 4. API Layer

Entry points:

- `POST /api/auth/login`
- `GET /api/auth/me`
- `POST /api/auth/logout`
- `POST /api/requests`
- protected request, template, settings, analytics, and audit-log APIs

Main controllers:

- `App\Http\Controllers\AuthController`
- `App\Http\Controllers\RequestController`
- `App\Http\Controllers\TemplateController`
- `App\Http\Controllers\SettingsController`
- `App\Http\Controllers\AnalyticsController`
- `App\Http\Controllers\AuditLogController`

Important functions:

- `AuthController::login()`, `me()`, `logout()`
- `RequestController::index()`, `store()`, `show()`, `update()`, `updateStatus()`, `archive()`
- `TemplateController::index()`, `show()`, `store()`, `update()`, `destroy()`
- `SettingsController::index()`, `update()`, `publicSettings()`

### 5. Security And 2FA Flow

Main controller:

- `App\Http\Controllers\TwoFactorController`

Main functions:

- `index()` - shows security settings page
- `enable()` - starts 2FA setup
- `confirm()` - confirms setup
- `disable()` - disables 2FA
- `verify()` - shows verification form
- `verifyPost()` - validates 2FA code
- `resend()` - resends email code

Middleware involved:

- `TwoFactorMiddleware`
- `EnsureUserIsActive`
- `CheckRole`

### 6. Letter Rendering And Template Logic

Main services:

- `App\Services\LetterService`
- `App\Services\TemplateService`

Important functions:

- `LetterService::sanitizeHtml()`
- `LetterService::generateLetterContent()`
- `LetterService::getVariables()`
- `LetterService::generateQrCodeHtml()`
- `TemplateService::getActiveTemplate()`
- `TemplateService::getAllTemplates()`
- `TemplateService::clearCache()`

Main related views:

- `backend/resources/views/pdf/letter.blade.php`
- `backend/resources/views/admin/request-preview.blade.php`

### 7. Telegram And Email Notifications

Main controller:

- `App\Http\Controllers\TelegramController`

Main functions:

- `handleWebhook()`
- `setupWebhook()`
- `testNotification()`

Main service:

- `App\Services\TelegramService`

Important functions:

- `sendMessage()`
- `sendMessageToChat()`
- `sendRequestNotification()`
- `setWebhook()`
- `getBotUsername()`

### 8. Installer

Main entry:

- `install/index.php`

Purpose:

- checks server requirements
- collects DB settings
- creates admin account
- writes `backend/.env`
- runs install steps and post-install actions

## Current Confirmed Problems

These are confirmed from code review and local checks. They should be treated as the current fix scope.

### P0 - Critical

1. API login bypasses 2FA
- File area: `backend/routes/api.php`, `backend/app/Http/Controllers/AuthController.php`
- Current issue: the API can mint Sanctum tokens with only email and password, while privileged API routes do not enforce the same 2FA gate as the web admin panel.
- Impact: admin/editor users can access protected API features without completing 2FA.

2. Public letter access uses `verification_token` as the gate
- File area: `backend/app/Http/Controllers/PageController.php`, `backend/routes/web.php`, `backend/app/Services/WizardService.php`
- Current issue: approved letter access trusts a token value that is effectively treated as the student's ID number, not a strong secret or signed one-time access token.
- Impact: approved letters are not protected strongly enough, and the HTML letter view route is not throttled.

### P1 - High

3. Installer can lock itself out
- File area: `.htaccess`, `install/index.php`, `backend/composer.json`
- Current issue: `/install` gets blocked if `backend/.env` exists, even when installation is incomplete or failed.
- Impact: failed installs can become hard to recover without manual file edits.

4. Attachment link on admin request details page points to a missing route
- File area: `backend/resources/views/admin/request-details.blade.php`
- Current issue: the Blade file calls the wrong route name for document download.
- Impact: request details page breaks when a request has an attachment.

5. API status updates leave request state inconsistent
- File area: `backend/app/Http/Controllers/RequestController.php`
- Current issue: approval flow does not generate `verify_token`, and stale `admin_message` values can survive status changes.
- Impact: tracking and approval state can become inconsistent or misleading.

### P2 - Medium

6. Bulk actions skip normal side effects
- File area: `backend/app/Http/Controllers/AdminController.php`
- Current issue: bulk request updates do not fully reuse the single-request status transition logic.
- Impact: notifications and cleanup behavior can drift depending on which path changed the status.

7. PDF stylesheet is malformed
- File area: `backend/resources/views/pdf/letter.blade.php`
- Current issue: CSS blocks are nested incorrectly.
- Impact: watermark/footer styling may render incorrectly in browser or PDF output.

8. Public pages fail hard when DB is unavailable
- File area: `backend/app/Http/Middleware/CheckMaintenance.php`
- Current issue: maintenance middleware queries the database before route handling and does not degrade safely on DB connection failure.
- Impact: `/`, `/request`, and `/track` can return `500` instead of a controlled fallback.

9. `training_period` validation is looser than page rendering assumptions
- File area: `backend/app/Http/Controllers/RequestController.php`, `backend/app/Http/Controllers/AdminController.php`, `backend/resources/views/admin/request-details.blade.php`
- Current issue: write paths accept arbitrary strings, while display code assumes `YYYY-MM`.
- Impact: malformed data can break request details rendering.

10. PHP version requirements are inconsistent
- File area: `README.md`, `install/index.php`, `backend/composer.json`
- Current issue: docs, installer, and Composer do not agree on the required PHP version.
- Impact: deployment can pass one check and still fail later.

11. Legacy QR vendor stack still carries long-term maintenance debt
- File area: `backend/app/Services/LetterService.php`, `backend/vendor/simplesoftwareio/simple-qrcode`, `backend/vendor/bacon/bacon-qr-code`
- Current issue: the active Composer advisory was fixed by upgrading `league/commonmark` to `2.8.2`, and the QR deprecation path is now mitigated locally with a Composer-run compatibility patch script plus regression coverage. The upstream QR packages are still old enough that a future library replacement would be cleaner than carrying a local patch forever.
- Impact: there is no active Composer security advisory and no current PHP 8.4 deprecation output in the app path, but the QR stack still represents managed technical debt.

## Planned Changes

This is the recommended implementation order.

### Phase 1 - Security Fixes First

- [x] Enforce 2FA for privileged API access
- [x] Review API auth flow so token issuance matches web security expectations
- [x] Replace public letter access with a stronger gate
- [x] Add rate limiting to the public letter view route

### Phase 2 - Normalize Status Transitions

- [x] Move request status change rules into one shared service or shared method
- [x] Reuse the same logic for admin UI, API, Telegram callbacks, and bulk actions
- [x] Always generate required tokens on approval
- [x] Always clear stale revision or rejection messages when status changes
- [x] Keep notifications consistent across all status update paths

### Phase 3 - Fix Confirmed Broken Pages And Rendering

- [x] Fix the attachment route name in the admin request details view
- [x] Repair malformed CSS in the PDF letter template
- [x] Tighten `training_period` validation to a single safe format

### Phase 4 - Improve Installer And Runtime Resilience

- [x] Stop blocking `/install` only because `.env` exists
- [x] Mark installation complete only after migrations and required setup succeed
- [x] Align PHP requirements across docs, installer, and Composer
- [x] Make maintenance checks fail safely when the database is down

### Phase 5 - Add Regression Tests

- [x] Add feature tests for API 2FA enforcement
- [x] Add tests for approved letter access rules
- [x] Add tests for status transition consistency
- [x] Add a test for request details with an uploaded attachment
- [x] Add installer lockout coverage

### Phase 6 - Stabilize QR Runtime On PHP 8.4

- [x] Add a Composer-run compatibility patch for the current QR vendor stack
- [x] Add regression coverage so QR generator boot stays free of PHP 8.4 deprecations
- [ ] Replace the legacy QR stack with a maintained upstream path in a future cleanup pass

## Changes Made

Current completed work:

- [x] Deep code review and architecture mapping completed
- [x] Confirmed bug list created and prioritized
- [x] This Markdown project overview and fix plan file created

Code fixes completed so far:

- [x] Added API 2FA enforcement for privileged Sanctum access
- [x] Changed public approved-letter access to require a verified tracking session instead of the student ID token in the URL
- [x] Added a shared `RequestStatusService` so admin UI, API, Telegram, and bulk actions use the same status rules and notifications
- [x] Fixed the admin attachment route in the request details page
- [x] Fixed malformed CSS in the PDF letter template
- [x] Tightened `training_period` validation and made admin rendering safe for malformed legacy values
- [x] Added the missing Sanctum `personal_access_tokens` migration for clean databases
- [x] Fixed MySQL-only index migrations so the Laravel test suite works on the configured SQLite test database
- [x] Added regression tests for the new security and status behavior
- [x] Reworked installer completion around a validated install lock file instead of raw `.env` existence
- [x] Changed the installer to write `INSTALLED=false` until post-install migrations succeed, then lock the installer only after successful completion
- [x] Hardened maintenance mode and public page boot paths so DB outages fall back safely instead of throwing `500`
- [x] Aligned the documented and installer PHP requirement to PHP 8.3+
- [x] Added installer helper tests and runtime resilience tests
- [x] Updated `league/commonmark` from `2.8.1` to `2.8.2` with the required `symfony/polyfill-php80` patch update, clearing the active Composer audit advisory
- [x] Added a Composer patch script for the QR vendor signatures that trigger PHP 8.4 deprecations, and added regression coverage to keep that compatibility fix in place
- [x] Pinned Laravel's Symfony layer, including the dev-side `symfony/yaml` dependency, back to `7.4.x` so Composer no longer drifts the installed vendor tree to PHP 8.4-only packages
- [x] Fixed the live Apache installer deny rule so `/install` now returns `403` when `backend/storage/app/install.lock` exists
- [x] Added a Hostinger-safe public asset fallback route so uploaded logos and appearance images still load even without `storage:link`
- [x] Changed framework fallback defaults to `SESSION_DRIVER=file`, `CACHE_STORE=file`, and `QUEUE_CONNECTION=sync` so incomplete shared-hosting env files fail more safely
- [x] Updated installer/env/deployment docs to match Hostinger shared hosting expectations
- [x] Added Hostinger compatibility regression tests for public asset delivery and public settings URL rewriting
- [x] Rebuilt the transactional email system with a shared branded layout, plain-text alternatives, and cleaner professional copy
- [x] Replaced raw login / 2FA / test emails with proper Laravel mailables
- [x] Added live preview and deliverability guidance to the admin email template editor
- [x] Upgraded the admin email template preview to render the full final email shell so every template has consistent visual UX during editing
- [x] Applied the refreshed transactional email templates to the local app database through a new migration
- [x] Added email rendering regression tests for branding, security-code mails, and admin test-email delivery

### Current Pass Summary

- Security:
  API login for admin/editor users with 2FA now requires a second factor before issuing a privileged token.
  Protected approved-letter pages now require the existing OTP-verified tracking session.
  Login and 2FA email codes now use real branded mailables instead of raw plain-text sends.

- Consistency:
  Status transitions are now normalized in one shared service.
  Approval now consistently generates `verify_token`, and stale `admin_message` / `rejection_reason` values are cleared on status changes.
  All application emails now share one transactional layout, consistent branding, and matching plain-text parts.

- Email UX:
  Student, admin, tracking verification, status update, backup, login code, and SMTP test emails now use modern professional templates with clear copy and visible actions.
  The email template editor now includes a live preview, sample data rendering, and subject-line guidance so future edits stay aligned.

- Verified:
  `composer audit` reports no active advisories.
  The QR generator now boots under PHP 8.4 without deprecation output.
  `php artisan test` passes with 23 tests / 89 assertions after these changes.
  Live Apache smoke now confirms `/install` is blocked with `403 Forbidden` when the installer lock exists.
  Hostinger compatibility tests now confirm public disk assets can be served through `/media/...` without requiring a storage symlink.

- Remaining priority:
  No immediate repo-code blocker remains from this review and fix pass.
  The local Apache stack on this machine is still serving PHP `8.2.4`, while the app now correctly requires PHP `8.3+`, so live public pages will stay blocked until Apache is upgraded to a supported PHP version.
  The only long-term code follow-up left is replacing the locally patched QR vendor stack with a maintained upstream path when we want to retire that compatibility shim.

## How To Use This File

Recommended workflow:

1. Before changing code, confirm which phase is currently active.
2. After each fix, move the checkbox from pending to done.
3. Add a short note under "Changes Made" with the file names and what changed.
4. If a new bug is confirmed during work, add it under "Current Confirmed Problems" with its impact.
5. Keep this file updated so the app review, fix scope, and completed work stay in one place.

## Suggested Change Log Format

Use this section format for future updates:

### Change Entry Template

- Date:
- Area:
- Files:
- Reason:
- What changed:
- Testing:
- Result:
