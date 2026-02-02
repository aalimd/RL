<?php
// backend/public/debug_500.php

// 1. Force Enable Error Display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🛠️ Deep Debugger Tool</h1>";

// 2. Check PHP Version
echo "<h3>1. Environment Check</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Current File: " . __FILE__ . "<br>";

echo "<h3>2. Critical Paths Permissions & Files</h3>";
$baseDir = dirname(__DIR__); // Up one level from public
echo "Backend Root: " . $baseDir . "<br>";

$envFile = $baseDir . '/.env'; // Restoring the missing variable!
if (file_exists($envFile)) {
    echo "<b>.env File:</b> <span style='color:green'>FOUND</span> (Perms: " . substr(sprintf('%o', fileperms($envFile)), -4) . ")<br>";

    // Content Check (Safe)
    $envContent = file_get_contents($envFile);
    if (strpos($envContent, 'APP_KEY=') !== false) {
        echo "<b>APP_KEY:</b> <span style='color:green'>FOUND in .env</span><br>";
    } else {
        echo "<b>APP_KEY:</b> <span style='color:red'>MISSING in .env content</span> (Check file content!)<br>";
    }
} else {
    echo "<b>.env File:</b> <span style='color:red; font-size:1.2em; font-weight:bold'>MISSING (CRITICAL)</span><br>";
}

// 2.5 Check & Clear Config Cache
echo "<h3>2.5 Configuration Cache Check</h3>";
$configCache = $baseDir . '/bootstrap/cache/config.php';
if (file_exists($configCache)) {
    echo "<b>Config Cache:</b> <span style='color:orange'>FOUND</span> - This might be blocking .env updates.<br>";
    if (unlink($configCache)) {
        echo "<b>Action:</b> <span style='color:green'>DELETED (Cache Cleared)</span>. Please refresh the page!<br>";
    } else {
        echo "<b>Action:</b> <span style='color:red'>FAILED TO DELETE</span>. Please delete 'bootstrap/cache/config.php' manually via File Manager.<br>";
    }
} else {
    echo "<b>Config Cache:</b> CLEAN (No stale config found).<br>";
}

$pathsToCheck = [
    $baseDir . '/storage',
    $baseDir . '/storage/logs',
    $baseDir . '/storage/framework',
    $baseDir . '/storage/framework/views',
    $baseDir . '/bootstrap/cache',
];

foreach ($pathsToCheck as $path) {
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $writable = is_writable($path) ? '<span style="color:green">YES</span>' : '<span style="color:red">NO</span>';
        echo "Path: <b>$path</b> | Perms: $perms | Writable: $writable<br>";
    } else {
        echo "Path: $path (<span style='color:red'>NOT FOUND</span>)<br>";
    }
}

// 4. Laravel Boot Test & DB Auto-Fix
echo "<h3>3. Laravel Boot Test & Database Repair</h3>";
try {
    if (!file_exists($baseDir . '/vendor/autoload.php')) {
        throw new Exception("Vendor autoload not found.");
    }
    require $baseDir . '/vendor/autoload.php';
    if (!file_exists($baseDir . '/bootstrap/app.php')) {
        throw new Exception("bootstrap/app.php not found.");
    }

    // Bootstrap the application (Standard Way)
    $app = require_once $baseDir . '/bootstrap/app.php';

    // Resolve Console Kernel to run Artisan commands
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "✅ Application Bootstrapped.<br>";

    // Attempt Database Connection
    try {
        Illuminate\Support\Facades\DB::connection()->getPdo();
        echo "✅ Database Connection: SUCCESS<br>";

        // AUTO-FIX: Run Migrations FORCEFULLY
        echo "<div style='background:#e0f2fe; padding:15px; border:2px solid #0ea5e9; margin:10px 0;'>";
        echo "<h3 style='color:#0284c7; margin-top:0'>🚀 Attempting Full Database Migration...</h3>";

        try {
            // Check if we need to migrate
            echo "Running: <code>php artisan migrate --force</code>...<br>";

            // Capture output
            Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $output = Illuminate\Support\Facades\Artisan::output();

            echo "<b>Result:</b><pre style='background:#fff; padding:10px; border:1px solid #ccc;'>" . ($output ?: 'Migration command ran (no output). Check if errors exist below.') . "</pre>";
            echo "<h3 style='color:green'>✅ Operations Completed.</h3>";
            echo "Please refresh the Admin Panel now to check if it works!";

        } catch (Throwable $artisanEx) {
            echo "<h3 style='color:red'>❌ Migration Failed:</h3>";
            echo $artisanEx->getMessage() . "<br>";
        }
        echo "</div>";

    } catch (Throwable $dbEx) {
        echo "❌ Database Connection Failed: " . $dbEx->getMessage() . "<br>";
        echo "Ensure .env has correct DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD.<br>";
    }

    // Simulate a request to the failing route (Admin)
    echo "Attempting to handle request...<br>";
    $request = Illuminate\Http\Request::create('/admin/dashboard', 'GET');
    $response = $kernel->handle($request);

    echo "<h2>🎉 SUCCESS: Response Generated</h2>";
    echo "Status Code: " . $response->getStatusCode() . "<br>";

} catch (Throwable $e) {
    echo "<h2 style='color:red'>🔥 CRASH DETECTED</h2>";
    echo "<b>Exception:</b> " . get_class($e) . "<br>";
    echo "<b>Message:</b> " . $e->getMessage() . "<br>";
    echo "<b>File:</b> " . $e->getFile() . " on line " . $e->getLine() . "<br>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// 5. Read Latest Logs
echo "<h3>4. Latest Log Entries (storage/logs/laravel.log)</h3>";
$logFile = $baseDir . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    echo "<textarea style='width:100%; height:500px; font-family:monospace; background:#f0f0f0; padding:10px;'>";
    $lines = file($logFile);
    $lastLines = array_slice($lines, -400); // Increased to 400 to see the TOP of the error
    foreach ($lastLines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</textarea>";
} else {
    echo "Log file not found at: $logFile";
}
