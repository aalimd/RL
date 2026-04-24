# Safe Deploy Checklist

Use this checklist when deploying the current RL changes to production.

## Confidence Summary

- Local automated verification passed: `72 tests, 345 assertions`
- Email system was refactored and verified with rendering tests
- Hostinger compatibility work is already included
- The main remaining deployment risk is environment configuration, not application logic

## Migrations Expected In Production

If production has not received the recent changes yet, expect these migrations to run:

- `2026_04_22_000001_create_personal_access_tokens_table`
- `2026_04_22_120000_refresh_transactional_email_templates`

The first adds Sanctum token storage.
The second refreshes stored transactional email template content.

## Pre-Deploy

1. Confirm website PHP version in hPanel is `8.2.30+`
2. Confirm `backend/storage` is writable
3. Confirm `backend/bootstrap/cache` is writable
4. Back up the production database before deploy
5. Back up the production `backend/.env`
6. Confirm production `.env` uses:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `DB_CONNECTION=mysql`
   - `DB_HOST=localhost`
   - `DB_DATABASE=...`
   - `DB_USERNAME=...`
   - `DB_PASSWORD=...`
   - `SESSION_DRIVER=file`
   - `CACHE_STORE=file`
   - `QUEUE_CONNECTION=sync`
   - `LOG_CHANNEL=single`
   - `LETTER_EXPORT_DRIVER=browserless`
   - `BROWSERLESS_BASE_URL=https://production-sfo.browserless.io`
   - `BROWSERLESS_TOKEN=...`
7. Confirm production mail settings are real and valid:
   - `MAIL_FROM_ADDRESS`
   - `MAIL_FROM_NAME`
   - SMTP or ZeptoMail credentials

## Deploy Order

1. Upload the new code
2. Run:

```bash
cd backend
rm -f bootstrap/cache/*.php
php artisan migrate --force
php artisan optimize:clear
```

3. If you have SSH and want the standard symlink:

```bash
php artisan storage:link
```

If not, the app already has the `/media/...` fallback for shared hosting.

## Immediate Smoke Checks

After deploy, verify these pages first:

1. `/`
2. `/request`
3. `/track`
4. `/admin/login`
5. `/admin/email-templates`

Then verify these workflows:

1. Submit a test request
2. Confirm student “request received” email arrives
3. Confirm admin “new request” email arrives
4. Log in as admin and verify 2FA still works
5. Change a request status and confirm the status-update email arrives
6. Open a tracking verification flow and confirm the OTP email arrives
7. In Admin > General > PDF Export Renderer, click `Test Browserless`
8. Export one approved letter PDF, then export a small selected ZIP

## If Something Goes Wrong

Rollback order:

1. Restore the previous code
2. Restore `.env` if it was changed incorrectly
3. Restore database backup only if the issue is data-related and cannot be fixed forward

Do not restore the database casually if production already accepted new requests after deploy.

## High-Risk Mistakes To Avoid

- Uploading an old `vendor` folder that still requires PHP `8.3`
- Forgetting to configure Browserless before testing PDF export on Hostinger shared hosting
- Forgetting writable permissions on `backend/storage` or `backend/bootstrap/cache`
- Deploying with wrong `APP_URL`
- Running with broken mail credentials and assuming emails are fine
- Skipping the DB backup before `migrate --force`

## Recommended Release Style

Best practice for this app:

1. Back up DB
2. Deploy code
3. Run migrations
4. Clear caches
5. Test one real request end to end
6. Only then announce the release as complete
