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

// 4. Read Logs (CRITICAL)
echo "<h3>3. Latest Log Entries</h3>";
$logFile = $baseDir . '/storage/logs/laravel.log';

if (file_exists($logFile)) {
    echo "Reading: " . $logFile . "<br>";
    $lines = file($logFile);
    $lines = array_slice($lines, -50); // Show last 50 lines

    echo "<div style='background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 5px; overflow-x: auto; font-family: monospace; font-size: 13px;'>";
    foreach ($lines as $line) {
        $line = htmlspecialchars($line);
        // Highlight errors
        if (strpos($line, 'ERROR') !== false || strpos($line, 'Exception') !== false) {
            echo "<span style='color: #f87171; font-weight: bold;'>" . $line . "</span>";
        } elseif (strpos($line, 'Stack trace') !== false || strpos($line, '#') !== false) {
            echo "<span style='color: #a3a3a3;'>" . $line . "</span>";
        } else {
            echo $line;
        }
        echo "<br>";
    }
    echo "</div>";
} else {
    echo "❌ Log file not found at: " . $logFile;
}

// 5. Check Environment File
echo "<h3>4. Environment File Check</h3>";
if (file_exists($envFile)) {
    echo "✅ .env found.<br>";
    $envContent = file_get_contents($envFile);
    if (strpos($envContent, 'APP_KEY=') !== false && strlen($envContent) > 50) {
        echo "✅ APP_KEY appears to be set.<br>";
    } else {
        echo "❌ APP_KEY might be missing or empty.<br>";
    }
} else {
    echo "❌ .env file NOT FOUND.<br>";
}
?>