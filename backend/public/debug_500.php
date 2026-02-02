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
        body { font-family: system-ui, -apple-system, sans-serif; background: #f3f4f6; padding: 20px; color: #1f2937; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; color: #111827; display: flex; align-items: center; gap: 10px; }
        h3 { border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; margin-top: 30px; color: #374151; }
        .status-ok { color: #059669; font-weight: bold; }
        .status-err { color: #dc2626; font-weight: bold; }
        pre { background: #1f2937; color: #e5e7eb; padding: 15px; border-radius: 8px; overflow-x: auto; font-size: 13px; line-height: 1.5; }
        .log-entry { margin-bottom: 2px; }
        .log-error { color: #f87171; font-weight: bold; }
        .log-stack { color: #9ca3af; }
        .btn { display: inline-block; background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: 500; margin-bottom: 20px; }
        .btn:hover { background: #1d4ed8; }
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

        <h3>2. Latest Log Entries</h3>
        <p>Displaying last 200 lines from <code>storage/logs/laravel.log</code></p>
        
        <?php
        $logFile = $baseDir . '/storage/logs/laravel.log';
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $lines = array_slice($lines, -200); 
            
            echo '<pre>';
            foreach ($lines as $line) {
                $cleanLine = htmlspecialchars($line);
                if (strpos($line, 'ERROR') !== false || strpos($line, 'Exception') !== false) {
                    echo '<div class="log-entry log-error">' . $cleanLine . '</div>';
                } elseif (preg_match('/^#\d+/', $line)) {
                     echo '<div class="log-entry log-stack">' . $cleanLine . '</div>';
                } else {
                    echo '<div class="log-entry">' . $cleanLine . '</div>';
                }
            }
            echo '</pre>';
        } else {
            echo '<div class="status-err">❌ Log file not found at: ' . htmlspecialchars($logFile) . '</div>';
        }
        ?>
    </div>
</body>
</html>