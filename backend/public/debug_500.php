<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$baseDir = __DIR__ . '/..';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deep Debugger - Log Viewer Check</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f3f4f6;
            padding: 20px;
            color: #1f2937;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        h1 {
            margin-top: 0;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h3 {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
            margin-top: 30px;
            color: #374151;
        }

        .status-ok {
            color: #059669;
            font-weight: bold;
        }

        .status-err {
            color: #dc2626;
            font-weight: bold;
        }

        pre {
            background: #1f2937;
            color: #e5e7eb;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.5;
        }

        .log-entry {
            margin-bottom: 2px;
        }

        .log-error {
            color: #f87171;
            font-weight: bold;
        }

        .log-stack {
            color: #9ca3af;
        }

        .btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .btn:hover {
            background: #1d4ed8;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🛠️ Deep Debugger: Log Viewer</h1>
        <a href="debug_500.php" class="btn">🔄 Refresh Logs</a>

        <h3>1. Environment Quick Check</h3>
        <div>
            <strong>PHP Version:</strong> <?php echo phpversion(); ?> <br>
            <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?> <br>
            <strong>.env File:</strong>
            <?php
            if (file_exists($baseDir . '/.env')) {
                echo '<span class="status-ok">FOUND</span>';
                $envContent = file_get_contents($baseDir . '/.env');
                if (strpos($envContent, 'APP_KEY=') !== false) {
                    echo ' <span class="status-ok">(APP_KEY Set)</span>';
                } else {
                    echo ' <span class="status-err">(APP_KEY Missing)</span>';
                }
            } else {
                echo '<span class="status-err">NOT FOUND</span>';
            }
            ?>
        </div>

        // 4. Schema Auto-Correction (Hotfix)
        echo "<h3>3. Schema Verification & Auto-Fix</h3>";
        try {
        if (!file_exists($baseDir . '/vendor/autoload.php')) {
        throw new Exception("Vendor autoload not found.");
        }
        require $baseDir . '/vendor/autoload.php';
        $app = require_once $baseDir . '/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        echo "✅ Application Bootstrapped.<br>";

        // Fix Requests Table
        if (Illuminate\Support\Facades\Schema::hasTable('requests')) {
        if (!Illuminate\Support\Facades\Schema::hasColumn('requests', 'deleted_at')) {
        echo "⚠️ 'requests' table missing 'deleted_at'. Fixing...<br>";
        Illuminate\Support\Facades\Schema::table('requests', function ($table) {
        $table->softDeletes();
        });
        echo "✅ 'requests' table fixed.<br>";
        } else {
        echo "✅ 'requests' table already has 'deleted_at'.<br>";
        }
        } else {
        echo "❌ 'requests' table does not exist.<br>";
        }

        // Fix Templates Table
        if (Illuminate\Support\Facades\Schema::hasTable('templates')) {
        if (!Illuminate\Support\Facades\Schema::hasColumn('templates', 'deleted_at')) {
        echo "⚠️ 'templates' table missing 'deleted_at'. Fixing...<br>";
        Illuminate\Support\Facades\Schema::table('templates', function ($table) {
        $table->softDeletes();
        });
        echo "✅ 'templates' table fixed.<br>";
        } else {
        echo "✅ 'templates' table already has 'deleted_at'.<br>";
        }
        } else {
        echo "❌ 'templates' table does not exist.<br>";
        }

        // Fix Users Table (just in case)
        if (Illuminate\Support\Facades\Schema::hasTable('users')) {
        if (!Illuminate\Support\Facades\Schema::hasColumn('users', 'deleted_at')) {
        echo "⚠️ 'users' table missing 'deleted_at'. Fixing...<br>";
        Illuminate\Support\Facades\Schema::table('users', function ($table) {
        $table->softDeletes();
        });
        echo "✅ 'users' table fixed.<br>";
        } else {
        echo "✅ 'users' table already has 'deleted_at'.<br>";
        }
        }

        echo "<h3>🎉 Schema Repair Complete. Please Refresh Admin Panel.</h3>";

        } catch (Throwable $e) {
        echo "<h3 style='color:red'>❌ Schema Fix Failed:</h3>";
        echo $e->getMessage();
        }

        // 5. Read Logs
        echo "<h3>4. Latest Log Entries</h3>";
    </div>
</body>

</html>