<?php
// Save this as backend/public/diagnose_500.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Production Diagnostics</h1>";

// Define base path (adjust if needed depending on where this script runs)
$basePath = realpath(__DIR__ . '/..');
echo "<p>Base Path: $basePath</p>";

// 1. Check Directory Existence & Casing
$viewsPath = $basePath . '/resources/views/admin';
echo "<h2>1. Checking View Directories</h2>";

if (is_dir($viewsPath)) {
    echo "<p>✅ 'resources/views/admin' exists.</p>";
    $dirs = scandir($viewsPath);
    echo "<ul>";
    foreach ($dirs as $dir) {
        if ($dir === '.' || $dir === '..')
            continue;
        $fullPath = $viewsPath . '/' . $dir;
        $type = is_dir($fullPath) ? '[DIR]' : '[FILE]';
        echo "<li>$type $dir</li>";
    }
    echo "</ul>";
} else {
    echo "<p>❌ 'resources/views/admin' NOT FOUND.</p>";
}

// 2. Check Controller Content (Verify deployment)
$controllerPath = $basePath . '/app/Http/Controllers/Admin/EmailTemplateController.php';
echo "<h2>2. Checking Controller Content</h2>";
if (file_exists($controllerPath)) {
    $content = file_get_contents($controllerPath);
    // Check if it has 'email-templates' (the fix)
    if (strpos($content, "'admin.email-templates.index'") !== false) {
        echo "<p>✅ Controller has the FIX ('admin.email-templates.index').</p>";
    } else {
        echo "<p>❌ Controller implies OLD code (missing 'email-templates'). Deployment might have failed.</p>";
    }

    // Check if it has 'getSettings'
    if (strpos($content, "getSettings") !== false) {
        echo "<p>✅ Controller has 'getSettings' method.</p>";
    } else {
        echo "<p>❌ Controller MISSING 'getSettings'.</p>";
    }
} else {
    echo "<p>❌ EmailTemplateController.php NOT FOUND.</p>";
}

// 3. Attempt Bootstrap & Render (Optional - might fail if paths wrong)
echo "<h2>3. Laravel Environment Test</h2>";
require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

try {
    echo "<p>Fetching Settings...</p>";
    $settings = \App\Models\Settings::all()->pluck('value', 'key')->toArray();
    echo "<p>✅ Settings fetched (" . count($settings) . ").</p>";

    echo "<p>Attempting Review Render...</p>";
    // Attempt to render with dummy data
    $view = view('admin.email-templates.index', [
        'templates' => [],
        'settings' => $settings
    ]);
    $html = $view->render();
    echo "<p>✅ View Rendered Successfully! (Length: " . strlen($html) . " chars)</p>";
} catch (\Exception $e) {
    echo "<p style='color:red; font-weight:bold'>❌ Render Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
