<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$targets = [
    $root . '/vendor/simplesoftwareio/simple-qrcode/src/Generator.php' => [
        'public function generate(string $text, string $filename = null)' => 'public function generate(string $text, ?string $filename = null)',
    ],
    $root . '/vendor/bacon/bacon-qr-code/src/Encoder/Encoder.php' => [
        'private static function chooseMode(string $content, string $encoding = null) : Mode' => 'private static function chooseMode(string $content, ?string $encoding = null) : Mode',
    ],
];

$patchedAny = false;

foreach ($targets as $file => $replacements) {
    if (!is_file($file)) {
        continue;
    }

    $contents = file_get_contents($file);

    if ($contents === false) {
        fwrite(STDERR, "Unable to read vendor patch target: {$file}\n");
        exit(1);
    }

    $updated = $contents;

    foreach ($replacements as $search => $replace) {
        if (str_contains($updated, $replace)) {
            continue;
        }

        if (!str_contains($updated, $search)) {
            fwrite(STDERR, "Expected QR vendor signature not found in {$file}\n");
            exit(1);
        }

        $updated = str_replace($search, $replace, $updated);
    }

    if ($updated !== $contents) {
        file_put_contents($file, $updated);
        $patchedAny = true;
    }
}

if ($patchedAny) {
    fwrite(STDOUT, "Patched QR vendor PHP 8.4 compatibility signatures.\n");
}
