<?php
// unzip.php
$file = 'release.zip';

// Ensure required directories exist with correct permissions
$dirs = [
    './backend/storage/app/public',
    './backend/storage/framework/cache/data',
    './backend/storage/framework/sessions',
    './backend/storage/framework/views',
    './backend/storage/logs',
    './backend/bootstrap/cache',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
if (!file_exists($file)) {
    die('Error: release.zip not found!');
}

$zip = new ZipArchive;
if ($zip->open($file) === TRUE) {
    if (!is_dir('./backend')) {
        mkdir('./backend', 0755, true);
    }
    $zip->extractTo('./backend/');
    $zip->close();

    // Delete the zip file after successful extraction
    unlink($file);

    // Optional: Delete this script as well for security
    // unlink(__FILE__);

    echo "Success: Files extracted and zip deleted.";
} else {
    echo "Error: Failed to unzip file.";
}
?>