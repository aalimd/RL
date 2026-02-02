<?php
// Disable caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "User: " . get_current_user() . "<br>";

$logFile = __DIR__ . '/backend/storage/logs/test_write.log';
echo "Trying to write to $logFile ...<br>";

if (file_put_contents($logFile, "Test write " . date('Y-m-d H:i:s'))) {
    echo "SUCCESS: Wrote to logs.<br>";
    unlink($logFile);
} else {
    echo "FAILURE: Could not write to logs.<br>";
    echo "Last Error: " . print_r(error_get_last(), true) . "<br>";
}

echo "<hr>";
echo "Attempting to boot Laravel and catch error...<br>";

try {
    require __DIR__ . '/backend/vendor/autoload.php';
    $app = require_once __DIR__ . '/backend/bootstrap/app.php';

    // Create kernel
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "Kernel created.<br>";

    // Mock request to /request
    $uri = '/request'; // Change this to test different routes
    echo "Simulating request to $uri ...<br>";

    $request = Illuminate\Http\Request::create($uri, 'GET');

    $response = $kernel->handle($request);
    echo "Request handled. Status: " . $response->getStatusCode() . "<br>";

} catch (Throwable $e) {
    echo "<b>CAUGHT EXCEPTION:</b> " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
