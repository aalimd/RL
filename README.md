# Academic Recommendation System

## Installation Guide

### Requirements
- PHP 8.3 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite enabled
- Required PHP Extensions: PDO, PDO_MySQL, OpenSSL, Mbstring, Tokenizer, JSON, cURL

---

## Installation Steps

### 1. Upload Files
Upload all files to your hosting:
- Upload the entire project to your web root, such as `public_html`
- This app is designed so the root `index.php` forwards requests into `backend/public`
- Ensure `backend/storage` and `backend/bootstrap/cache` have write permissions (775 recommended)

### 2. Run Installation Wizard
Navigate to: `https://yourdomain.com/install/`

The wizard will guide you through:
1. **Requirements Check** - Verify server compatibility
2. **Database Setup** - Enter MySQL credentials
3. **Admin Account** - Create your administrator account
4. **Email Settings** - Configure SMTP (optional, can be done later)
5. **Site Settings** - Set site name and URL
6. **Installation** - Complete the setup

### 3. Post-Installation Security
⚠️ **Important**: After successful installation, the installer is auto-locked. Deleting or renaming `/install` is optional extra hardening, not a required recovery step.

---

## Manual Installation (Alternative)

If the wizard doesn't work, you can install manually:

### 1. Create Database
```sql
CREATE DATABASE recommendation_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Import SQL
Import `install/database/fresh_install.sql` into your database.

### 3. Configure Environment
Copy `backend/.env.example` to `backend/.env` and update:
```
APP_URL=https://yourdomain.com
DB_HOST=localhost
DB_DATABASE=recommendation_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=single
```

### 4. Create Admin User
Insert admin user into database:
```sql
INSERT INTO users (name, email, password, created_at, updated_at) 
VALUES ('Admin', 'admin@example.com', '$2y$10$...hashed_password...', NOW(), NOW());
```

---

## Folder Structure
```
/
├── install/           # Installation wizard (DELETE after install)
├── backend/           # Laravel application
│   ├── app/
│   ├── config/
│   ├── routes/
│   ├── resources/
│   ├── storage/       # Must be writable
│   └── public/        # Web-accessible files
├── index.php          # Entry point
└── .htaccess          # URL rewriting
```

---

## Features

### Email Notifications
- **New Request**: Student receives tracking ID, Admin receives notification
- **Status Update**: Student receives update notification

Configure email in: Admin Panel → Settings → Email Settings

### Templates
- Pre-installed: NGHA Emergency Medicine template
- Create custom templates in: Admin Panel → Templates

### Available Template Variables
| Variable | Description |
|----------|-------------|
| `{{fullName}}` | Full name (First + Middle + Last) |
| `{{firstName}}` | First name only |
| `{{lastName}}` | Last name only |
| `{{studentId}}` | Student/Employee ID |
| `{{university}}` | University name |
| `{{purpose}}` | Purpose of recommendation |
| `{{trainingPeriod}}` | Training period (formatted) |
| `{{currentDate}}` | Current date |

---

## Troubleshooting

### 500 Internal Server Error
- Check `backend/storage/logs/laravel.log` for errors
- If `LOG_CHANNEL=errorlog`, check your server PHP/Apache error log instead.
- Ensure storage folder is writable: `chmod -R 775 backend/storage`
- Ensure `backend/bootstrap/cache` is writable too
- Ensure cache store is file-based on shared hosting: `CACHE_STORE=file`
- Ensure the website itself is using PHP 8.3 or higher, not just the SSH CLI version

### Email Not Sending
- Verify SMTP settings in Admin → Settings
- Check spam folder
- Test with "Send Test Email" button
- In production, set `MAIL_FAILOVER_MAILERS=zeptomail,smtp` (avoid `log` fallback for real delivery)

### Page Not Found
- Ensure mod_rewrite is enabled
- Check .htaccess files are properly uploaded

### Uploaded Images Not Showing
- The app can serve uploaded public assets through `/media/...` even if `storage:link` is unavailable
- If you do have SSH and prefer direct `/storage/...` URLs, run `php artisan storage:link`

---

## Support

For issues and questions, please check:
1. Server error logs
2. Laravel logs in `backend/storage/logs/`

---

© 2024 Academic Recommendation System
