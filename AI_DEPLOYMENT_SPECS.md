# 🤖 AI Deployment Specifications & Constraints

> **CRITICAL FOR ALL FUTURE AI AGENTS:**
> Read this file before modifying `.github/workflows/deploy.yml` or `index.php`.

## 1. Architecture Overview
- **Deployment Strategy:** `backend/` Folder Isolation.
- **Root Directory (`/`):** Contains only the entry point `index.php` and `.htaccess` which proxy requests to `backend/public`.
- **Backend Directory (`/backend`):** Contains the entire Laravel application.

## 2. Deployment Workflow (`deploy.yml`)
### Critical constraints to maintain:
1.  **Extraction Path:**
    - **MUST** extract the zip contents into the `backend/` directory.
    - **NEVER** extract to root (`/`). This will cause "Ghost Code" where the server runs old code from `backend/` while new code sits unused in root.
    - Code: `$zip->extractTo(__DIR__ . '/backend');` (inside the PHP unzip script).

2.  **Automated Migrations:**
    - The script **MUST** run `php artisan migrate --force` automatically after extraction.
    - This prevents "database incompatibility" errors.

3.  **FTP Settings (Hostinger):**
    - Protocol: `ftps`
    - Security: `loose` (Required to prevent "Timeout (control socket)").
    - Timeout: `300000` (5 minutes) or higher.

## 3. Environment Differences
- **Local:** You (the agent) work in `backend/`.
- **Production:** The server executes from `backend/public`, but the URL is the root domain.
- **Asset Links:** Always use Laravel's `asset()` helper or relative paths that respect the proxy.

## 4. Updates & Maintenance
- If you modify `deploy.yml`, **YOU MUST** verify that lines relating to `backend/` extraction path are preserved.
- **Do not** revert to a "root extraction" strategy unless the entire project structure (`index.php`) is changed first.
