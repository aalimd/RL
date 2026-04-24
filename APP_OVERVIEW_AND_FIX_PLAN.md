# App Overview And Fix Plan

Last updated: 2026-04-23

## Purpose

This file is the single source of truth for product enhancement work in this project.

Use it to:

- keep the active roadmap in one place
- prevent duplicate work across future passes
- preserve a stable reference for how the app is structured today
- separate historical completed foundation work from upcoming UX, UI, admin, and quality improvements

This project is an academic recommendation letter system built on Laravel. Students submit recommendation requests, receive a tracking ID, follow status updates through a verified tracking flow, and access approved letters. Admin users review requests, manage templates and settings, and operate the system through the admin panel.

## How To Use This File

- Check `Active Enhancement Roadmap` before starting any new work.
- Keep exactly one row per task ID. Update the existing row in place instead of adding a duplicate task.
- Leave `Owner` as `unassigned` until someone claims the work, then replace it before changing the task to `in_progress`.
- When work starts, update `Status`, `Owner`, and dependencies if they changed.
- When work finishes, move the task to `done` and add a short entry under the change log using the template at the bottom of this file.
- Keep `Current App Baseline` stable unless the actual architecture or product flow changed.
- Keep `Historical Completed Foundation` for already-shipped work only. Do not place new backlog items there.
- Keep unresolved carry-over items in `Open Technical Debt`, not inside the historical sections.

## Status Legend

| Status | Meaning |
| --- | --- |
| `todo` | Planned and ready to be claimed. |
| `in_progress` | Claimed and actively being worked. |
| `done` | Completed and verified well enough to leave the active roadmap. |
| `blocked` | Cannot move because of an external dependency, environment issue, or pending decision. |
| `deferred` | Intentionally postponed in favor of higher-value work. |

## Product Goals

- Make the student journey clear, trustworthy, and low-friction from request submission through final letter access.
- Remove ambiguity at every step, especially around validation, tracking, OTP verification, status updates, and next actions.
- Keep admin decisions consistent and student-facing communication predictable without adding unnecessary complexity.
- Improve visual quality and perceived professionalism without reinventing the app structure or brand direction.
- Strengthen accessibility, responsive behavior, analytics, and regression coverage around the highest-impact journeys.

## Current App Baseline

### Purpose And Scope

- Students or trainees submit recommendation requests from the public site.
- The system stores requests, generates tracking IDs, and supports verified tracking and approved-letter access.
- Admin users review, edit, approve, reject, or request revision for submissions.
- The app also includes templates, analytics, audit logs, email delivery, Telegram notifications, appearance settings, and a web installer.

### Stack And Structure

- PHP / Laravel application in `backend/`
- Root web entry through `index.php` and `.htaccess`
- Blade views in `backend/resources/views`
- Web routes in `backend/routes/web.php`
- API routes in `backend/routes/api.php`
- Installation wizard in `install/`

Important folders:

- `backend/app/Http/Controllers` for web and API request handling
- `backend/app/Services` for wizard logic, letters, AI, Telegram, template helpers, and shared business logic
- `backend/app/Http/Middleware` for auth, role, 2FA, and maintenance checks
- `backend/resources/views` for public pages, admin pages, emails, and PDF templates
- `backend/app/Models` for `Request`, `User`, `Settings`, `Template`, `AuditLog`, and related records

### Main App Flows

#### 1. Public Request Flow

Entry points:

- `GET /request`
- `POST /request`
- `POST /request/edit`

Main controller:

- `App\Http\Controllers\PageController`

Primary logic:

- `publicRequest()` loads the public wizard page
- `handleWizard()` processes multi-step request submission
- `initializeEdit()` opens a revision session for an existing request
- `WizardService` controls form configuration, content selection, and per-step validation

#### 2. Tracking And Letter Access Flow

Entry points:

- `GET /track/{id?}`
- `POST /track`
- `GET /tracking/verify`
- `POST /tracking/verify`
- `GET /letter/{tracking_id}`
- `GET /letter/{tracking_id}/pdf`

Main controller:

- `App\Http\Controllers\PageController`

Primary logic:

- `tracking()` renders the tracking form
- `doTracking()` starts verified request tracking
- `show2FAVerify()` shows the public OTP verification page
- `handle2FAVerify()` verifies OTP and unlocks request details
- `viewLetter()` and `downloadPdf()` serve approved letters after verified access

#### 3. Admin Web Panel

Primary areas:

- dashboard
- requests and request details
- templates and template editor
- settings, appearance, and form settings
- users
- analytics
- audit logs
- system tools

Main controller:

- `App\Http\Controllers\AdminController`

#### 4. API Layer

Primary areas:

- auth
- requests
- templates
- settings
- analytics
- audit logs

Main controllers:

- `AuthController`
- `RequestController`
- `TemplateController`
- `SettingsController`
- `AnalyticsController`
- `AuditLogController`

#### 5. Security, 2FA, Letters, And Notifications

- Admin 2FA is managed by `TwoFactorController` plus related middleware.
- Letter rendering is handled mainly by `LetterService` and template logic.
- Telegram notifications are handled by `TelegramController` and `TelegramService`.
- Transactional emails use Laravel mailables and shared Blade email layouts.
- Installer flow is handled by `install/index.php`.

## Active Enhancement Roadmap

### Roadmap Defaults

- `APP_OVERVIEW_AND_FIX_PLAN.md` is the only roadmap file that should be used for active enhancement tracking.
- Design direction is `improve, not reinvent`: preserve the current app structure and overall brand direction while simplifying, clarifying, and professionalizing the experience.
- Current recommended execution order for the next admin-operations and communications pass:
  `OPS-02`, `COM-01`, `ADM-01`, then `VIS-01`, `A11Y-01`, and `OPS-01`.

### Public Interfaces And Behavior Defaults For This Roadmap

- Add one new public interface:
  `POST /tracking/verify/resend`, throttled and tied to the current tracking-verification session.
- Keep these existing public routes stable:
  `POST /track`, `GET /tracking/verify`, `POST /tracking/verify`, `GET /request`, `POST /request`.
- Keep these existing letter routes stable:
  `GET /letter/{tracking_id}`, `GET /letter/{tracking_id}/pdf`.
- Keep `GET /letter/{tracking_id}` as the primary student-facing letter experience because the browser-rendered version currently preserves Arabic text, spacing, footer layout, and professional visual quality better than the PDF-first path.
- Keep `GET /letter/{tracking_id}/pdf` as a secondary/export route until the PDF renderer can truly match the browser-rendered letter quality.
- Do not reuse the current low-fidelity Dompdf path for new admin bulk-download or Drive-backup features unless the generated PDFs are first proven to match the existing browser-saved letter output in English and Arabic.
- Add one new optional form input:
  `data[purpose_other]`, shown only when `data[purpose] = Other`.
- Do not introduce a new database shape for revision and rejection guidance in the first enhancement pass.
- Continue using existing `admin_message` and `rejection_reason` fields for final student-facing decision text.
- Do not expand admin-configurable settings aggressively in v1; reuse current tracking and status settings where practical and ship clearer defaults in code first.

### Tracking Experience Focus - 2026-04-23

Current audit findings for the public tracking journey:

- The flow is functionally safer than before, but it still feels visually heavier than the task requires.
- The search page, OTP page, and verified results page each use ambient backgrounds, floating particles, glass cards, and hover motion, which makes the journey feel more decorative than trustworthy for an academic service.
- The student sees too much copy in too many layers:
  hero text, security explainer, session flash messages, status headline, next-step copy, admin message, portal guidance, a fixed footer note, and sometimes Telegram promotion.
- The verified results page still competes for attention with duplicate status badges, a full timeline, metadata rows, optional Telegram subscription, revision/edit actions, and approved-letter actions in the same vertical stack.
- The OTP step is secure, but the transition from search to verification still feels like a separate page instead of a guided continuation of the same task.
- Tracking copy is configurable in too many independent places today, which increases the risk of bloated or inconsistent messages across the tracker, emails, and Telegram.
- Approved users do reach the letter safely, but the handoff can feel abrupt because the status page and the letter page are visually and structurally very different experiences.

Design direction for the next pass:

- Keep the secure multi-step flow and existing routes, but make the experience feel like one calm guided journey.
- Prioritize one primary action per screen.
- Reduce motion and decorative layers in tracking-related pages.
- Move secondary details into collapsible or lower-priority sections.
- Standardize student-facing status language across page, email, and Telegram touchpoints.
- Preserve security guardrails, throttling, verified-session checks, and approved-letter access protections while simplifying the presentation.

### Admin Operations Focus - 2026-04-23

Current audit findings for the admin operations request:

- The admin requests list already has a bulk endpoint, but it only supports `approve`, `reject`, and `delete`, which is too narrow for real operations and too risky for large batches without clearer confirmation and message handling.
- Bulk status changes currently act only on manually checked rows from the visible page. There is no `select all filtered results` flow, no dry-run summary, and no reusable modal for statuses such as `Under Review`, `Needs Revision`, or `Archived`.
- Request export today is CSV-only. There is no admin action to download one generated letter as a stored PDF from the list, no ZIP export of many letters, and no durable archive pipeline.
- The current experimental Dompdf route cannot be treated as the source for admin archive/export because it already regressed Arabic rendering, spacing, watermark balance, and footer integrity compared with the browser-saved letter the product owner prefers.
- There is no Google Drive integration in the app today. `backend/composer.json` does not include a Google Drive client, and `backend/config/filesystems.php` only defines `local`, `public`, and `s3` disks.
- The app already has a good base for secure settings and auditing: sensitive settings can be encrypted in `Settings`, and request status changes already flow through `RequestStatusService`, which should remain the single transition and notification path.
- There is no queue or batch-job workflow in use yet for large exports, so any high-volume PDF or Drive export needs explicit background processing, chunking, progress reporting, and cleanup rules to avoid timeouts.

Design direction for the next pass:

- Treat bulk operations as `selected rows` and `all rows matching current filters`, not just the visible page.
- Keep status changes safe by requiring preview counts, clear confirmation, and required student-facing messages for `Needs Revision` and `Rejected`.
- Build admin letter export around browser-faithful rendering, because the user explicitly prefers the old browser-saved result over the current generated PDF.
- Make Google Drive backup private by default and admin-controlled, with share links copied intentionally when needed for disaster recovery.
- Store enough backup metadata per request to let admins find a student's letter quickly even if the app is unavailable.
- Audit every bulk change, archive generation, Drive upload, and share-link action.

### Master Tracker

| ID | Area | Priority | Status | Owner | Summary | Dependencies | Acceptance |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `DOC-01` | Documentation / Roadmap | P0 | `done` | Codex | Rewrite this file into the master enhancement roadmap and move prior audit content into baseline and history sections. | - | File has the required top-level sections, a live tracker table, stable baseline notes, and historical completed foundation separated from active work. |
| `DOC-02` | Documentation / Tracking Audit | P1 | `done` | Codex | Analyze the full tracking journey, document the major UI, UX, communication, and trust issues, and add a dedicated simplification roadmap without duplicating earlier tracking fixes. | `DOC-01` | The roadmap contains a focused tracking-experience audit, prioritized follow-up tasks, and a clear execution order for simplification work. |
| `STU-01` | Student UX / Tracking | P0 | `done` | Codex | Add strict tracking ID validation, keep one consistent `Student / National ID` label, explain the OTP step before submit, and replace vague lookup failures with exact format, no-match, and state messages. | `DOC-01` | Tracking rejects malformed IDs before lookup, preserves current stable routes, and shows specific format, no-match, and success-state messaging. |
| `STU-02` | Student UX / OTP | P0 | `done` | Codex | Add a resend flow, expiry countdown, masked delivery hint, and recovery guidance for spam, junk, and wrong-email scenarios. | `STU-01` | `POST /tracking/verify/resend` exists with throttling, the verify page supports resend and countdown behavior, and wrong, expired, and success OTP states are clearly handled. |
| `STU-03` | Student UX / Status Results | P1 | `done` | Codex | Replace the fragmented tracking result layout with one strong current-status and next-action experience, and fix misleading timeline language after final decisions. | `STU-01`, `STU-02` | `Submitted`, `Under Review`, `Needs Revision`, `Approved`, and `Rejected` each show an accurate primary next action and no timeline copy suggests the wrong state. |
| `STU-04` | Student UX / Request Wizard | P1 | `done` | Codex | Reorganize the wizard so the final step is read-only review, move `purpose` and `deadline` earlier, add inline errors, clarify required versus optional fields, add `data[purpose_other]`, and make phone clearly optional. | `DOC-01` | The last step is review-only, inline field errors render under the correct field, `purpose_other` appears only for `Other` and is validated, and optional fields are visually distinct from required ones. |
| `STU-05` | Student UX / Tracking Flow Simplification | P1 | `done` | Codex | Redesign the search and OTP journey as one calmer guided experience with lighter copy, clearer step framing, fewer decorative effects, and stronger trust cues without changing the secure route structure. Shipped a cleaner OTP verification card and a simplified tracker search screen with shorter copy, quieter layout, and one clear primary action. | `STU-01`, `STU-02`, `DOC-02` | Search and OTP pages present one main task each, optional help is secondary, reduced-motion and lighter visual treatment are in place, and students clearly understand `Search -> Verify -> View status` as one journey. |
| `STU-06` | Student UX / Tracking Results Simplification | P1 | `done` | Codex | Reduce density on the verified status page by keeping one primary outcome card, one main CTA, a lighter progress/history treatment, and secondary details that do not compete with the main answer. Shipped a calmer verified-status layout with Telegram removed, duplicate status framing removed, compact metadata, and collapsible history. | `STU-03`, `STU-05`, `DOC-02` | Verified tracking results show the current answer, next action, and primary CTA above the fold; secondary details are visually quieter; and mobile scanning is faster and clearer. |
| `STU-07` | Student UX / Trusted Browser | P1 | `done` | Codex | Add a student-controlled trusted-browser option so the tracker can skip OTP on the same browser after a successful verification, while still allowing students to keep receiving codes if they leave the option off or later disable it. | `STU-02`, `STU-05`, `STU-06` | The OTP page offers a clear remember-browser choice, the tracker can safely skip OTP on the same trusted browser for a limited period, and students can turn the behavior off on that browser. |
| `PDF-01` | Student Document Delivery / Canonical PDF | P1 | `deferred` | Codex | A PDF-first student letter flow was trialed, but it was rolled back from the main student path after real output comparison showed the browser-rendered letter preserved Arabic text, footer layout, spacing, and overall professionalism better. Keep the browser-rendered letter as primary until PDF quality can truly match it. | `STU-06`, `VIS-01` | Students continue to open the browser-rendered letter from tracking, and no PDF-first change returns to the main student flow until the generated PDF visually matches the browser-saved output in English and Arabic. |
| `PDF-02` | Letter Rendering / One-Page A4 Fit Engine | P1 | `deferred` | Codex | The backend one-page PDF fit work remains experimental and secondary. It should not control the main student letter experience or approval flow until it can preserve professional spacing, complete Arabic rendering, and footer integrity as well as the browser-rendered version. | `PDF-01` | Any future PDF-fit pass must match browser-rendered letter quality before becoming student-facing or approval-blocking. |
| `COM-01` | Student Communication | P1 | `in_progress` | Codex | Align tracking-page text, OTP copy, request-status emails, submission emails, and Telegram wording so students see the same state names, next actions, and tone everywhere, while reducing the current message-setting sprawl. The first pass fixes a status-transition bug so optional admin notes now survive approved and other non-rejected updates, and the tracking page now renders them as a distinct highlighted message card with clearer labels and preserved line breaks. | `STU-05`, `STU-06`, `DOC-02` | Student-facing copy uses consistent state names and next actions across tracking, OTP, email, and Telegram channels, and tracking message configuration is simpler and less duplicative. |
| `ADM-01` | Admin Workflow | P2 | `todo` | `unassigned` | Improve revision and rejection handling in the admin request-details flow with clearer prompts, operator guidance, and a preview of the student-facing message before sending. | `COM-01`, `STU-06` | The admin decision flow makes required guidance clear, previews the final student-facing message, and continues using existing message fields without a database migration. |
| `ADM-02` | Admin Workflow / Template Editor | P1 | `done` | Codex | Fix variable insertion targeting in the template editor, prevent silent inserts into the wrong field, add a variable manager to focus or remove placeholders anywhere, and deduplicate singleton inline signature and QR placeholders during rendering. | `DOC-01` | Variable chips only insert into the actively selected valid field, protected URL fields reject variable insertion clearly, stored placeholders can be located and removed from any field, and repeated inline `{{signature}}` or `{{qrCode}}` placeholders no longer render multiple copies in the final letter. |
| `ADM-03` | Admin Workflow / Letter Fit Diagnostics | P1 | `deferred` | Codex | Do not let PDF-fit diagnostics or PDF overflow checks control approval while the browser-rendered letter remains the primary student output. Revisit this only after the PDF route can match the browser-rendered document visually and structurally. | `PDF-02`, `ADM-01` | Admin approval is based on the real student-facing browser-rendered letter experience, not a lower-quality PDF-first experiment. |
| `ADM-04` | Admin Workflow / Bulk Status Management | P1 | `done` | Codex | Replace the current approve-reject-delete-only bulk bar with a real bulk status workflow that supports selected rows or all rows matching the current filters, uses `RequestStatusService` for every transition, and requires a shared student-facing message when the chosen status demands one. Shipped a modal-based bulk update flow with selected-vs-filtered scope, shared-message enforcement, safer confirmations, reusable request filtering in the controller, and audit-covered regression tests. | `ADM-01`, `COM-01` | From the admin requests page, staff can bulk-change requests to any allowed status with a confirmation summary, filter-aware `select all` behavior, required message enforcement for `Needs Revision` and `Rejected`, notification consistency, and a full audit log of what changed. |
| `PDF-03` | Admin Documents / Browser-Faithful PDF Export | P1 | `done` | Codex | Add admin-side single-letter and bulk-letter PDF export that matches the current browser-saved letter quality instead of relying on the experimental low-fidelity Dompdf path. Shipped a browser-driven export service that prints the existing approved-letter HTML through headless Chrome/Brave, added single-request PDF download from request details and the requests table, added selected-vs-filtered ZIP export from the admin requests page, and hardened Browserless for Hostinger shared hosting with automatic production preference, `.env` fallback configuration, PDF-byte validation, clearer admin errors, renderer status diagnostics, longer export execution windows, and a PHP 8.2-compatible dependency lock. | `PDF-01`, `ADM-04` | Admin can download one approved letter as PDF from request details, export selected or filtered approved letters as a ZIP of PDFs to the local machine, and switch the export driver to Browserless on shared hosting so production export still works without a server-side Chrome installation or PHP 8.3 runtime. |
| `INT-01` | Integrations / Google Drive Letter Backup | P1 | `done` | Codex | Added an admin-controlled Google Drive backup flow for approved letters using encrypted service-account JSON settings, a shared-folder destination, request-level sync metadata, single-request Drive sync from request details, selected-vs-filtered bulk Drive export from the requests page, and Drive link/status visibility in the admin UI. | `PDF-03` | Admin can configure Drive backup securely in settings, test the connection, export selected or filtered approved letters to Drive, see per-request sync status and errors, open or copy the stored Drive link when needed, and all sensitive Drive credentials remain encrypted in settings. |
| `OPS-02` | Operations / Bulk Export Jobs And Retention | P2 | `todo` | `unassigned` | Add background processing, progress feedback, retry handling, temporary archive cleanup, and audit coverage for large PDF exports and Google Drive sync jobs so admin bulk actions stay reliable under real volume. | `ADM-04`, `PDF-03`, `INT-01` | Large exports and Drive syncs can run without request timeouts, report success and failure counts back to the admin UI, clean up expired ZIP artifacts automatically, and retain enough audit detail to investigate issues later. |
| `VIS-01` | Visual Simplification | P2 | `in_progress` | Codex | Reduce decorative weight, replace overly decorative student-facing headings with more trustworthy typography, and specifically calm the tracking and OTP pages by reducing glass, particle, hover, and ambient effects. The first follow-up pass now makes the tracking and OTP screens inherit admin-selected fonts, button gradients, radius, shadows, and shared theme tokens more consistently. | `STU-05`, `STU-06` | Student pages keep the same structure and brand direction but use calmer hierarchy, clearer typography, reduced decorative motion, and less visual competition around primary actions. |
| `A11Y-01` | Accessibility / Responsive UX | P2 | `todo` | `unassigned` | Improve focus states, keyboard flow, reduced-motion behavior, inline validation visibility, and mobile layout sanity across landing, request, tracking, OTP, and verified status pages. | `STU-05`, `STU-06`, `VIS-01` | Landing, request, tracking, OTP, and verified-status pages are usable with keyboard-only navigation, visible focus, reduced motion support, and stable mobile layouts. |
| `OPS-01` | Analytics / QA | P2 | `todo` | `unassigned` | Track failure points in request and tracking journeys, especially tracker lookup failures, OTP resend and expiry behavior, verified-status CTA usage, and add regression coverage for the simplified tracking experience. | `STU-05`, `STU-06`, `COM-01`, `ADM-01` | Tests cover simplified tracker states, OTP resend and expiry, status next actions, and primary CTA behavior; analytics better expose student friction and admin turnaround. |

## Historical Completed Foundation

This section records work that already shipped and should not be re-added to the active roadmap unless it regresses.

### Archived Completed Phases

- Phase 1: Security fixes first
  - enforced 2FA for privileged API access
  - aligned API auth flow with web security expectations
  - strengthened approved-letter access controls
  - added public letter route throttling
- Phase 2: Normalize status transitions
  - moved status rules into shared logic
  - reused shared transition behavior across admin UI, API, Telegram, and bulk actions
  - generated required tokens on approval
  - cleared stale revision and rejection messages on status changes
  - aligned notifications across status update paths
- Phase 3: Fix confirmed broken pages and rendering
  - fixed the admin attachment route name
  - repaired malformed PDF CSS
  - tightened `training_period` validation and hardened rendering
- Phase 4: Improve installer and runtime resilience
  - stopped blocking `/install` only because `.env` existed
  - changed installation completion to a validated lock flow
  - aligned PHP requirements across docs, installer, and Composer
  - made maintenance checks fail safely during DB outages
- Phase 5: Add regression tests
  - added feature and unit coverage for security, status behavior, installer behavior, and compatibility fixes
- Phase 6: Stabilize QR runtime on PHP 8.4
  - added the current compatibility patch path and regression coverage

### Historical Shipped Work By Area

#### Security And Auth

- Added API 2FA enforcement for privileged Sanctum access.
- Changed approved-letter access to require a verified tracking session instead of trusting the student ID token in the URL.
- Replaced raw login, 2FA, and related security emails with proper Laravel mailables.

#### Request State And Consistency

- Introduced a shared `RequestStatusService` so admin UI, API, Telegram, and bulk actions use the same status rules and notifications.
- Made approval consistently generate `verify_token`.
- Cleared stale `admin_message` and `rejection_reason` values on status changes.
- Fixed the admin attachment route in the request-details page.

#### Installer, Hosting, And Runtime Resilience

- Reworked installer completion around a validated install-lock file instead of raw `.env` existence.
- Wrote `INSTALLED=false` until post-install migrations succeeded, then locked the installer only after success.
- Hardened maintenance mode and public boot paths so DB outages degrade safely instead of throwing `500`.
- Added a Hostinger-safe public asset fallback route so uploaded logos and appearance images still load without `storage:link`.
- Changed fallback defaults to safer shared-hosting values for sessions, cache, and queue configuration.
- Updated installer, environment, and deployment docs to match Hostinger shared-hosting expectations.
- Pinned Composer resolution to PHP `8.2.30` and Laravel 12 so Hostinger shared hosting can boot without Composer platform-check failures.

#### Email System

- Rebuilt the transactional email system with a shared branded layout, plain-text alternatives, and more professional copy.
- Added live preview and deliverability guidance to the admin email template editor.
- Upgraded preview rendering so templates are shown inside the final email shell during editing.
- Applied refreshed transactional email templates to the local database through a migration.
- Added regression tests for branding, security-code mails, and admin test-email delivery.

#### Letter, PDF, And Arabic Rendering

- Reworked the public recommendation-letter view into a safer fixed A4 layout.
- Rebuilt the PDF letter template into a compact one-page print layout and changed the public PDF route to open inline.
- Added regression coverage for inline public PDF delivery and direct page-count behavior.
- Shaped Arabic text specifically for the PDF path so letters do not render broken Arabic glyphs.
- Restored the PDF letter frame closer to the earlier stable layout while keeping one-page regression checks.

#### Template Editor

- Fixed duplicated broken markup in the template-editor settings area.
- Made autosave persist the full draft state instead of partial fields only.
- Cleared stale draft data on manual save so the saved version becomes the source of truth.
- Improved preview fidelity for signature, stamp, QR visibility, watermark, digital footer, and layout styling.
- Added regression tests to verify the public recommendation letter changes when the active template changes.
- Normalized template inline styles before purification so richer markup survives editor saves more reliably.

#### Dependencies And Compatibility

- Updated `league/commonmark` to clear the active Composer advisory.
- Added a Composer patch script for QR-vendor signatures that triggered PHP 8.4 deprecations.
- Pinned Laravel's Symfony layer, including the dev-side YAML dependency, back to a compatible `7.4.x` line to avoid PHP 8.4-only drift.
- Added the missing Sanctum `personal_access_tokens` migration for clean databases.
- Fixed MySQL-specific index migrations so the test suite works on the configured SQLite test database.

#### Historical Verification Snapshot

- `composer audit` reported no active advisories at the time of the previous foundation pass.
- The QR generator booted under PHP 8.4 without deprecation output after the compatibility patch.
- `php artisan test` passed with `29 tests / 119 assertions` at the time of that note.
- Live Apache smoke confirmed `/install` returned `403 Forbidden` when the installer lock existed.
- Hostinger compatibility tests confirmed public disk assets could be served through `/media/...` without requiring a storage symlink.

## Open Technical Debt

These items are not part of the active student-experience roadmap, but they remain unresolved and should stay visible.

| ID | Status | Summary | Impact |
| --- | --- | --- | --- |
| `TD-01` | `todo` | Replace the locally patched legacy QR stack with a maintained upstream path instead of carrying a permanent compatibility shim. | Reduces long-term maintenance risk around QR rendering and PHP compatibility. |
| `TD-02` | `blocked` | Local Apache on this machine is still serving PHP `8.2.4` while the app now correctly requires PHP `8.3+`. | Local smoke behavior on that stack remains operationally blocked until Apache is upgraded. |

## Change Log Template

Use this template for future roadmap updates after work lands:

### 2026-04-22 - Task Update

- Task ID: `STU-01`
- Area: Student UX / Tracking
- Owner: Codex
- Status: `done`
- Files: `backend/app/Http/Controllers/PageController.php`, `backend/resources/views/public/tracking.blade.php`, `backend/resources/views/public/2fa_verify.blade.php`, `backend/resources/views/public/request.blade.php`, `backend/app/Services/WizardService.php`, `backend/tests/Feature/SecurityRegressionTest.php`
- Summary: Added strict tracking ID format validation, normalized lowercase tracking IDs, clarified Student / National ID labeling, explained the OTP step before submit, and replaced vague tracking failures with specific format, no-match, archived-state, and success-path messages.
- Testing: `php artisan test --filter=SecurityRegressionTest`
- Result: Tracking now blocks malformed IDs before lookup, preserves valid lowercase input safely, starts OTP verification with clearer messaging, and keeps regression coverage around the upgraded flow.

### 2026-04-22 - Task Update

- Task ID: `STU-02`
- Area: Student UX / OTP
- Owner: Codex
- Status: `done`
- Files: `backend/routes/web.php`, `backend/app/Http/Controllers/PageController.php`, `backend/resources/views/public/2fa_verify.blade.php`, `backend/tests/Feature/SecurityRegressionTest.php`
- Summary: Added a public resend route for tracking verification, reused a shared OTP-issuing helper, kept request context after code expiry, surfaced masked delivery hints, added a live countdown and resend guidance on the verify page, and improved expired and wrong-code recovery messaging.
- Testing: `php artisan test --filter=SecurityRegressionTest`
- Result: Students can now request a fresh tracking code without restarting the whole flow, expired codes guide them toward resend instead of a dead end, and regression coverage now protects resend, expiry, wrong-code, and verify-page behaviors.

### 2026-04-22 - Task Update

- Task ID: `STU-03`
- Area: Student UX / Status Results
- Owner: Codex
- Status: `done`
- Files: `backend/resources/views/public/tracking.blade.php`, `backend/tests/Feature/SecurityRegressionTest.php`
- Summary: Reworked the verified tracking result into a single status summary card with a clearer current-state explanation, one primary next-step message for each student status, consolidated admin and portal guidance into the same area, and corrected the review timeline so final decisions no longer still read as in progress.
- Testing: `php artisan test --filter=SecurityRegressionTest`
- Result: Verified tracking now gives students one clear answer about what their status means and what to do next, while the timeline better reflects completed review work after approval or rejection.

### 2026-04-22 - Task Update

- Task ID: `STU-04`
- Area: Student UX / Request Wizard
- Owner: Codex
- Status: `done`
- Files: `backend/app/Http/Controllers/PageController.php`, `backend/app/Services/WizardService.php`, `backend/resources/views/public/request.blade.php`, `backend/resources/views/admin/form-settings.blade.php`, `backend/tests/Feature/SecurityRegressionTest.php`
- Summary: Moved purpose and deadline into step 1, added validated `purpose_other` details for `Other`, made the phone field clearly optional, added inline field errors and field help across the wizard, preserved the correct wizard step after validation failures, and converted step 3 into a true read-only review before submit.
- Testing: `php artisan test --filter=SecurityRegressionTest`
- Result: Students now complete the request context before they reach review, see clearer required-versus-optional guidance, stay on the correct wizard step when something is wrong, and submit from a real confirmation screen instead of entering surprise fields at the end.

### 2026-04-22 - Task Update

- Task ID: `ADM-02`
- Area: Admin Workflow / Template Editor
- Owner: Codex
- Status: `done`
- Files: `backend/resources/views/admin/template-editor.blade.php`, `backend/app/Services/LetterService.php`, `backend/tests/Feature/SecurityRegressionTest.php`
- Summary: Reworked template-variable insertion so the editor tracks the actual selected target, blocks insertions into protected URL fields or the wrong tab, adds a variable manager that can focus fields and remove duplicates from anywhere in the template, and normalizes repeated inline `{{signature}}` and `{{qrCode}}` placeholders before rendering the final letter.
- Testing: `php artisan test --filter=SecurityRegressionTest`
- Result: Admin users can now safely insert, inspect, and remove variables without silently corrupting the letter, and older broken templates no longer render repeated signature or QR blocks in the generated output.

### 2026-04-23 - Task Update

- Task ID: `DOC-02`
- Area: Documentation / Tracking Audit
- Owner: Codex
- Status: `done`
- Files: `APP_OVERVIEW_AND_FIX_PLAN.md`
- Summary: Reviewed the current tracking lookup, OTP verification, verified status, communication, and appearance-setting layers, documented why the journey still feels heavy, and added a focused roadmap for tracking simplification, communication cleanup, calmer visuals, accessibility, and measurement.
- Testing: Review and roadmap update only.
- Result: The project now has a dedicated tracking-experience plan that keeps the secure flow intact while prioritizing a calmer, simpler, and more professional student experience in the next implementation pass.

### YYYY-MM-DD - Task Update

- Task ID:
- Area:
- Owner:
- Status:
- Files:
- Summary:
- Testing:
- Result:
