<?php
// unzip.php
$file = 'release.zip';

if (!file_exists($file)) {
    die('Error: release.zip not found!');
}

$zip = new ZipArchive;
if ($zip->open($file) === TRUE) {
    $zip->extractTo('./');
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