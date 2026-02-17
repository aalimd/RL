<?php
/**
 * Installation Wizard - Main Entry Point
 * Academic Recommendation System
 */

session_start();

// Check if already installed
if (file_exists(__DIR__ . '/../backend/.env')) {
    $envContent = file_get_contents(__DIR__ . '/../backend/.env');
    if (strpos($envContent, 'INSTALLED=true') !== false) {
        header('Location: ../');
        exit;
    }
}

$step = $_GET['step'] ?? 1;
$error = null;
$success = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'check_requirements':
            $_SESSION['install']['requirements_checked'] = true;
            header('Location: ?step=2');
            exit;

        case 'database':
            $dbHost = $_POST['db_host'] ?? 'localhost';
            $dbName = $_POST['db_name'] ?? '';
            $dbUser = $_POST['db_user'] ?? '';
            $dbPass = $_POST['db_pass'] ?? '';

            // Test connection
            try {
                $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Create database if not exists
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                $_SESSION['install']['db'] = [
                    'host' => $dbHost,
                    'name' => $dbName,
                    'user' => $dbUser,
                    'pass' => $dbPass
                ];

                header('Location: ?step=3');
                exit;
            } catch (PDOException $e) {
                $error = "Database connection failed: " . $e->getMessage();
            }
            break;

        case 'admin':
            $adminName = $_POST['admin_name'] ?? '';
            $adminEmail = $_POST['admin_email'] ?? '';
            $adminPassword = $_POST['admin_password'] ?? '';
            $adminPasswordConfirm = $_POST['admin_password_confirm'] ?? '';

            if (strlen($adminPassword) < 8) {
                $error = "Password must be at least 8 characters.";
            } elseif ($adminPassword !== $adminPasswordConfirm) {
                $error = "Passwords do not match.";
            } else {
                $_SESSION['install']['admin'] = [
                    'name' => $adminName,
                    'email' => $adminEmail,
                    'password' => password_hash($adminPassword, PASSWORD_DEFAULT)
                ];
                header('Location: ?step=4');
                exit;
            }
            break;

        case 'email':
            $_SESSION['install']['email'] = [
                'smtp_host' => $_POST['smtp_host'] ?? '',
                'smtp_port' => $_POST['smtp_port'] ?? '587',
                'smtp_user' => $_POST['smtp_user'] ?? '',
                'smtp_pass' => $_POST['smtp_pass'] ?? '',
                'smtp_encryption' => $_POST['smtp_encryption'] ?? 'tls',
                'mail_from' => $_POST['mail_from'] ?? '',
                'mail_name' => $_POST['mail_name'] ?? ''
            ];
            header('Location: ?step=5');
            exit;

        case 'skip_email':
            // Skip SMTP setup (application will use log mailer until configured in admin settings)
            $_SESSION['install']['email'] = null;
            header('Location: ?step=5');
            exit;

        case 'site':
            $_SESSION['install']['site'] = [
                'name' => $_POST['site_name'] ?? 'Recommendation System',
                'url' => $_POST['site_url'] ?? ''
            ];
            header('Location: ?step=6');
            exit;

        case 'finish':
            // Perform actual installation
            try {
                $result = performInstallation($_SESSION['install']);
                if ($result === true) {
                    session_destroy();
                    header('Location: ?step=7');
                    exit;
                } else {
                    $error = $result;
                }
            } catch (Exception $e) {
                $error = "Installation failed: " . $e->getMessage();
            }
            break;
    }
}

function performInstallation($data)
{
    $db = $data['db'];
    $admin = $data['admin'];
    $email = $data['email'] ?? null;
    $site = $data['site'];

    // Ensure storage directories exist with correct permissions
    $directories = [
        __DIR__ . '/../backend/storage/app/public',
        __DIR__ . '/../backend/storage/framework/cache/data',
        __DIR__ . '/../backend/storage/framework/sessions',
        __DIR__ . '/../backend/storage/framework/views',
        __DIR__ . '/../backend/storage/logs',
    ];

    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        } else {
            @chmod($dir, 0775);
        }
    }

    // Connect to database
    $pdo = new PDO(
        "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4",
        $db['user'],
        $db['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Import SQL schema
    $sqlFile = __DIR__ . '/database/fresh_install.sql';
    if (!file_exists($sqlFile)) {
        return "SQL file not found: $sqlFile";
    }

    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);

    // Insert admin user
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES (?, ?, ?, 'admin', NOW(), NOW())");
    $stmt->execute([$admin['name'], $admin['email'], $admin['password']]);

    // Update siteName with user-provided value (SQL already has defaults)
    $stmt = $pdo->prepare("UPDATE settings SET `value` = ? WHERE `key` = 'siteName'");
    $stmt->execute([$site['name']]);

    // Override email settings if user provided custom ones
    if ($email) {
        $emailSettings = [
            ['smtpHost', $email['smtp_host']],
            ['smtpPort', $email['smtp_port']],
            ['smtpUsername', $email['smtp_user']],
            ['smtpPassword', $email['smtp_pass']],
            ['smtpEncryption', $email['smtp_encryption']],
            ['mailFromAddress', $email['mail_from']],
            ['mailFromName', $email['mail_name']],
        ];

        $stmt = $pdo->prepare("UPDATE settings SET `value` = ? WHERE `key` = ?");
        foreach ($emailSettings as $setting) {
            $stmt->execute([$setting[1], $setting[0]]);
        }
    }

    // Create .env file
    $envContent = generateEnvFile($db, $site, $email);
    file_put_contents(__DIR__ . '/../backend/.env', $envContent);

    return true;
}

function generateEnvFile($db, $site, $email = null)
{
    $appKey = 'base64:' . base64_encode(random_bytes(32));
    $appUrl = rtrim($site['url'], '/') ?: 'http://localhost';
    $mailMailer = $email ? 'smtp' : 'log';
    $mailHost = $email['smtp_host'] ?? '';
    $mailPort = $email['smtp_port'] ?? '';
    $mailUsername = $email['smtp_user'] ?? '';
    $mailPassword = $email['smtp_pass'] ?? '';
    $mailEncryption = $email['smtp_encryption'] ?? '';
    $mailFromAddress = $email['mail_from'] ?? '';
    $mailFromName = $email['mail_name'] ?? '';

    return <<<ENV
APP_NAME="{$site['name']}"
APP_ENV=production
APP_KEY={$appKey}
APP_DEBUG=false
APP_URL={$appUrl}

INSTALLED=true

DB_CONNECTION=mysql
DB_HOST={$db['host']}
DB_PORT=3306
DB_DATABASE={$db['name']}
DB_USERNAME={$db['user']}
DB_PASSWORD={$db['pass']}

SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER={$mailMailer}
MAIL_HOST={$mailHost}
MAIL_PORT={$mailPort}
MAIL_USERNAME={$mailUsername}
MAIL_PASSWORD="{$mailPassword}"
MAIL_ENCRYPTION={$mailEncryption}
MAIL_FROM_ADDRESS={$mailFromAddress}
MAIL_FROM_NAME="{$mailFromName}"
ENV;
}

// Check requirements
function checkRequirements()
{
    $requirements = [];

    $requirements['PHP >= 8.2'] = version_compare(PHP_VERSION, '8.2.0', '>=');
    $requirements['PDO Extension'] = extension_loaded('pdo');
    $requirements['PDO MySQL'] = extension_loaded('pdo_mysql');
    $requirements['OpenSSL'] = extension_loaded('openssl');
    $requirements['Mbstring'] = extension_loaded('mbstring');
    $requirements['Tokenizer'] = extension_loaded('tokenizer');
    $requirements['JSON'] = extension_loaded('json');
    $requirements['cURL'] = extension_loaded('curl');

    $requirements['Backend .env writable'] = is_writable(__DIR__ . '/../backend') || is_writable(__DIR__ . '/../backend/.env');
    $requirements['Storage writable'] = is_writable(__DIR__ . '/../backend/storage');

    return $requirements;
}

$requirements = checkRequirements();
$allRequirementsMet = !in_array(false, $requirements, true);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Wizard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            opacity: 0.9;
        }

        .steps {
            display: flex;
            justify-content: center;
            padding: 1.5rem;
            background: #f8f9fa;
            border-bottom: 1px solid #e5e7eb;
        }

        .step {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #9ca3af;
            margin: 0 0.5rem;
            font-size: 0.875rem;
        }

        .step.active {
            background: #4F46E5;
            color: white;
        }

        .step.done {
            background: #10B981;
            color: white;
        }

        .content {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background: #4F46E5;
            color: white;
        }

        .btn-primary:hover {
            background: #4338CA;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .btn-success {
            background: #10B981;
            color: white;
        }

        .requirement {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .requirement:last-child {
            border-bottom: none;
        }

        .check {
            color: #10B981;
            font-weight: bold;
        }

        .cross {
            color: #EF4444;
            font-weight: bold;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Installation Wizard</h1>
            <p>Academic Recommendation System</p>
        </div>

        <div class="steps">
            <?php for ($i = 1; $i <= 7; $i++): ?>
                <div class="step <?= $i < $step ? 'done' : ($i == $step ? 'active' : '') ?>">
                    <?= $i ?>
                </div>
            <?php endfor; ?>
        </div>

        <div class="content">
            <?php if ($error): ?>
                <div class="error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <!-- Step 1: Requirements -->
                <h2 style="margin-bottom: 1rem;">System Requirements</h2>
                <div style="background: #f8f9fa; border-radius: 8px; margin-bottom: 1.5rem;">
                    <?php foreach ($requirements as $name => $met): ?>
                        <div class="requirement">
                            <span>
                                <?= $name ?>
                            </span>
                            <span class="<?= $met ? 'check' : 'cross' ?>">
                                <?= $met ? '✓' : '✗' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="check_requirements">
                    <button type="submit" class="btn btn-primary" <?= !$allRequirementsMet ? 'disabled' : '' ?>>
                        Continue →
                    </button>
                </form>

            <?php elseif ($step == 2): ?>
                <!-- Step 2: Database -->
                <h2 style="margin-bottom: 1rem;">Database Configuration</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="database">

                    <div class="form-group">
                        <label class="form-label">Database Host</label>
                        <input type="text" name="db_host" class="form-input" value="localhost" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Database Name</label>
                        <input type="text" name="db_name" class="form-input" placeholder="recommendation_db" required>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Database Username</label>
                            <input type="text" name="db_user" class="form-input" placeholder="root" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Database Password</label>
                            <input type="password" name="db_pass" class="form-input">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Test Connection & Continue →</button>
                </form>

            <?php elseif ($step == 3): ?>
                <!-- Step 3: Admin Account -->
                <h2 style="margin-bottom: 1rem;">Create Admin Account</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="admin">

                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="admin_name" class="form-input" placeholder="Administrator" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="admin_email" class="form-input" placeholder="admin@example.com" required>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <input type="password" name="admin_password" class="form-input" minlength="8" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="admin_password_confirm" class="form-input" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Continue →</button>
                </form>

            <?php elseif ($step == 4): ?>
                <!-- Step 4: Email Settings -->
                <h2 style="margin-bottom: 1rem;">Email Configuration</h2>
                <p style="color: #6b7280; margin-bottom: 1.5rem;">Configure SMTP now, or skip this step and set it later from
                    the admin panel.</p>

                <form method="POST">
                    <input type="hidden" name="action" value="email">

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">SMTP Host</label>
                            <input type="text" name="smtp_host" class="form-input" value="">
                        </div>
                        <div class="form-group">
                            <label class="form-label">SMTP Port</label>
                            <input type="number" name="smtp_port" class="form-input" value="587">
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">SMTP Username</label>
                            <input type="text" name="smtp_user" class="form-input" value="">
                        </div>
                        <div class="form-group">
                            <label class="form-label">SMTP Password</label>
                            <input type="password" name="smtp_pass" class="form-input" value="">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Encryption</label>
                        <select name="smtp_encryption" class="form-input">
                            <option value="tls" selected>TLS</option>
                            <option value="ssl">SSL</option>
                            <option value="">None</option>
                        </select>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">From Email</label>
                            <input type="email" name="mail_from" class="form-input" value="">
                        </div>
                        <div class="form-group">
                            <label class="form-label">From Name</label>
                            <input type="text" name="mail_name" class="form-input" value="">
                        </div>
                    </div>

                    <div class="actions">
                        <button type="submit" name="action" value="skip_email" class="btn btn-secondary">Skip For Now &
                            Continue</button>
                        <button type="submit" class="btn btn-primary">Save Changes & Continue →</button>
                    </div>
                </form>

            <?php elseif ($step == 5): ?>
                <!-- Step 5: Site Settings -->
                <h2 style="margin-bottom: 1rem;">Site Configuration</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="site">

                    <div class="form-group">
                        <label class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-input" value="Academic Recommendations" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Site URL</label>
                        <input type="url" name="site_url" class="form-input" placeholder="https://yourdomain.com" required>
                        <small style="color: #6b7280;">The full URL where this application is hosted</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Continue →</button>
                </form>

            <?php elseif ($step == 6): ?>
                <!-- Step 6: Confirm & Install -->
                <h2 style="margin-bottom: 1rem;">Ready to Install</h2>
                <p style="color: #6b7280; margin-bottom: 1.5rem;">Review your configuration and click Install to complete
                    the setup.</p>

                <div style="background: #f8f9fa; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 1rem; color: #374151;">Configuration Summary</h4>
                    <p><strong>Database:</strong>
                        <?= htmlspecialchars($_SESSION['install']['db']['name'] ?? 'Not configured') ?>
                    </p>
                    <p><strong>Admin:</strong>
                        <?= htmlspecialchars($_SESSION['install']['admin']['email'] ?? 'Not configured') ?>
                    </p>
                    <p><strong>Email:</strong>
                        <?= $_SESSION['install']['email'] ? 'Configured' : 'Skipped (can configure later)' ?>
                    </p>
                    <p><strong>Site Name:</strong>
                        <?= htmlspecialchars($_SESSION['install']['site']['name'] ?? 'Not configured') ?>
                    </p>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="finish">
                    <button type="submit" class="btn btn-success" style="width: 100%; font-size: 1.125rem;">
                        🚀 Install Now
                    </button>
                </form>

            <?php elseif ($step == 7): ?>
                <!-- Step 7: Success -->
                <div style="text-align: center; padding: 2rem 0;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🎉</div>
                    <h2 style="color: #10B981; margin-bottom: 1rem;">Installation Complete!</h2>
                    <p style="color: #6b7280; margin-bottom: 2rem;">Your recommendation system is now ready to use.</p>

                    <div
                        style="background: #fef3c7; color: #92400e; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: left;">
                        <strong>⚠️ Important:</strong> Delete or rename the <code>/install</code> folder for security.
                    </div>

                    <a href="../" class="btn btn-primary" style="text-decoration: none;">Go to Website →</a>
                    <a href="../login" class="btn btn-secondary" style="text-decoration: none; margin-left: 1rem;">Admin
                        Login →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
