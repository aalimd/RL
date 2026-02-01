<?php
// email_debug.php
// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

echo "<h1>Email Debugger</h1>";
echo "<pre>";

// 1. Check Configuration
echo "<h2>1. Current Configuration</h2>";
$config = Config::get('mail');
// Hide sensitive data
if (isset($config['mailers']['smtp']['password'])) {
    $config['mailers']['smtp']['password'] = '********';
}
if (isset($config['mailers']['zeptomail']['token'])) { // if it existed
    // handle hiding
}
print_r($config);

// 2. Test Sending
echo "\n<h2>2. Sending Test Email...</h2>";
try {
    Mail::raw('This is a test email from the Debugger.', function ($message) {
        $message->to('info@aamd.sa') // Sending to info@aamd.sa
            ->subject('Test Email from Debugger');
    });
    echo "✅ Email sent successfully (or queued). Check your inbox/spam.\n";
} catch (\Exception $e) {
    echo "❌ Error Sending Email:\n";
    echo $e->getMessage() . "\n";
}

echo "</pre>";
?>