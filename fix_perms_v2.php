<?php
// Disable caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

echo "Script User: " . get_current_user() . "<br>";
echo "Script UID: " . getmyuid() . "<br>";
echo "Current Dir: " . __DIR__ . "<br>";

$backend = __DIR__ . '/backend';
echo "Backend Target: $backend<br>";

if (!is_dir($backend)) {
    echo "CRITICAL: Backend directory NOT found.<br>";
    $dirs = scandir(__DIR__);
    echo "Contents of " . __DIR__ . ": " . implode(", ", $dirs) . "<br>";
} else {
    echo "Backend found.<br>";
}

// Helper function to recursively chmod
function recursiveChmod($path, $fileMode, $dirMode)
{
    if (is_dir($path)) {
        if (!chmod($path, $dirMode)) {
            echo "Failed to chmod dir: $path<br>";
        } else {
            // echo "Fixed dir: $path<br>";
        }
        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' && $file != '..') {
                $fullpath = $path . '/' . $file;
                recursiveChmod($fullpath, $fileMode, $dirMode);
            }
        }
        closedir($dh);
    } else {
        if (file_exists($path)) {
            if (!chmod($path, $fileMode)) {
                echo "Failed to chmod file: $path<br>";
            }
        }
    }
}

$dirs = [
    $backend . '/storage',
    $backend . '/bootstrap/cache'
];

foreach ($dirs as $dir) {
    if (file_exists($dir)) {
        echo "Processing $dir ...<br>";
        recursiveChmod($dir, 0666, 0777);
    } else {
        echo "Directory not found: $dir<br>";
    }
}

echo "Done.";
