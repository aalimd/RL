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

// 3. Permission Check
echo "<h3>2. Critical Paths Permissions</h3>";
$baseDir = dirname(__DIR__); // Up one level from public
echo "Backend Root: " . $baseDir . "<br>";

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

// 4. Try Booting Laravel
echo "<h3>3. Laravel Boot Test</h3>";
try {
    if (!file_exists($baseDir . '/vendor/autoload.php')) {
        throw new Exception("Vendor autoload not found. Did 'composer install' run?");
    }

    require $baseDir . '/vendor/autoload.php';
    echo "✅ Vendor Autoloaded<br>";

    if (!file_exists($baseDir . '/bootstrap/app.php')) {
        throw new Exception("bootstrap/app.php not found.");
    }

    $app = require_once $baseDir . '/bootstrap/app.php';
    echo "✅ App Required<br>";

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "✅ Kernel Made<br>";

    echo "Attempting to handle request...<br>";

    // Simulate a request to the failing route (Admin)
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
