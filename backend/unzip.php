<?php
// unzip.php - Robust Deployment Script
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$zipFile = 'release.zip';
$extractPath = './backend/';

echo "<h1>Deployment Log</h1>";
echo "<pre>";

// 1. Check for Zip File
if (!file_exists($zipFile)) {
    echo "⚠️ Warning: '$zipFile' not found. Skipping extraction and proceeding to directory repair...\n";
} else {
    echo "✅ Found '$zipFile'.\n";

    // 2. Prepare Extraction Directory
    if (!is_dir($extractPath)) {
        echo "⚠️ '$extractPath' does not exist. Creating it...\n";
        if (!mkdir($extractPath, 0755, true)) {
            die("❌ Error: Failed to create '$extractPath'. Check permissions.");
        }
    }

    // 3. Extract Zip
    $zip = new ZipArchive;
    if ($zip->open($zipFile) === TRUE) {
        echo "⏳ Extracting to '$extractPath'...\n";
        if ($zip->extractTo($extractPath)) {
            echo "✅ Extraction successful.\n";
            $zip->close();
            // Cleanup Zip
            unlink($zipFile);
            echo "🗑 Deleted '$zipFile'.\n";
        } else {
            echo "❌ Error: Extraction failed.\n";
            $zip->close();
        }
    } else {
        echo "❌ Error: Could not open zip file.\n";
    }
}

// 4. Create Critical Laravel Directories & Set Permissions
$requiredDirs = [
    'storage/app/public',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache'
];

echo "\n🛠 Checking critical directories...\n";

foreach ($requiredDirs as $relDir) {
    $fullPath = $extractPath . $relDir;

    // Create if missing
    if (!is_dir($fullPath)) {
        echo "   Creating '$relDir'...\n";
        if (!mkdir($fullPath, 0775, true)) {
            echo "   ❌ Failed to create '$fullPath'.\n";
            continue;
        }
    }

    // Fix Permissions (Try to set to 775 or 777 usually needed for shared hosting)
    if (chmod($fullPath, 0775)) {
        echo "   ✅ Permissions set for '$relDir'.\n";
    } else {
        echo "   ⚠️ Warning: Could not chmod '$relDir'.\n";
    }
}

// 5. Cleanup
if (unlink($zipFile)) {
    echo "\n🗑 Deleted '$zipFile'.\n";
} else {
    echo "\n⚠️ Warning: Could not delete '$zipFile'.\n";
}

echo "\n🎉 Deployment Complete! <a href='/RL/'>Go to Site</a>";
echo "</pre>";
?>