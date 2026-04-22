<?php

if (!function_exists('installer_env_path')) {
    function installer_env_path(string $installDir): string
    {
        return rtrim($installDir, DIRECTORY_SEPARATOR) . '/../backend/.env';
    }
}

if (!function_exists('installer_lock_path')) {
    function installer_lock_path(string $installDir): string
    {
        return rtrim($installDir, DIRECTORY_SEPARATOR) . '/../backend/storage/app/install.lock';
    }
}

if (!function_exists('installer_parse_env_file')) {
    function installer_parse_env_file(string $envPath): array
    {
        if (!is_file($envPath)) {
            return [];
        }

        $contents = file_get_contents($envPath);
        if ($contents === false) {
            return [];
        }

        $values = [];

        foreach (preg_split("/\r\n|\n|\r/", $contents) as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $values[trim($key)] = trim(trim($value), "\"'");
        }

        return $values;
    }
}

if (!function_exists('installer_has_installed_flag')) {
    function installer_has_installed_flag(array $envValues): bool
    {
        return strtolower((string) ($envValues['INSTALLED'] ?? 'false')) === 'true';
    }
}

if (!function_exists('installer_validate_existing_installation')) {
    function installer_validate_existing_installation(array $envValues): bool
    {
        if (($envValues['DB_CONNECTION'] ?? 'mysql') !== 'mysql') {
            return false;
        }

        $host = trim((string) ($envValues['DB_HOST'] ?? ''));
        $port = trim((string) ($envValues['DB_PORT'] ?? '3306'));
        $database = trim((string) ($envValues['DB_DATABASE'] ?? ''));
        $username = (string) ($envValues['DB_USERNAME'] ?? '');
        $password = (string) ($envValues['DB_PASSWORD'] ?? '');

        if ($host === '' || $database === '' || $username === '') {
            return false;
        }

        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            foreach (['users', 'requests', 'settings', 'migrations'] as $table) {
                $statement = $pdo->prepare('SHOW TABLES LIKE ?');
                $statement->execute([$table]);

                if (!$statement->fetchColumn()) {
                    return false;
                }
            }

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('installer_write_lock')) {
    function installer_write_lock(string $lockPath): bool
    {
        $directory = dirname($lockPath);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            return false;
        }

        $payload = json_encode([
            'installed_at' => gmdate('c'),
        ], JSON_PRETTY_PRINT);

        return file_put_contents($lockPath, ($payload ?: '{}') . PHP_EOL) !== false;
    }
}

if (!function_exists('installer_set_installed_flag')) {
    function installer_set_installed_flag(string $envPath, bool $installed): bool
    {
        $contents = is_file($envPath) ? file_get_contents($envPath) : '';

        if ($contents === false) {
            return false;
        }

        $flagLine = 'INSTALLED=' . ($installed ? 'true' : 'false');

        if (preg_match('/^INSTALLED=.*$/m', $contents)) {
            $updatedContents = preg_replace('/^INSTALLED=.*$/m', $flagLine, $contents, 1);
        } else {
            $separator = $contents !== '' && !str_ends_with($contents, PHP_EOL) ? PHP_EOL : '';
            $updatedContents = $contents . $separator . $flagLine . PHP_EOL;
        }

        return file_put_contents($envPath, $updatedContents) !== false;
    }
}

if (!function_exists('installer_mark_complete')) {
    function installer_mark_complete(string $envPath, string $lockPath): bool
    {
        if (!installer_set_installed_flag($envPath, true)) {
            return false;
        }

        if (!installer_write_lock($lockPath)) {
            installer_set_installed_flag($envPath, false);
            return false;
        }

        return true;
    }
}

if (!function_exists('installer_is_complete')) {
    function installer_is_complete(string $envPath, string $lockPath, ?callable $validator = null): bool
    {
        if (is_file($lockPath)) {
            return true;
        }

        $envValues = installer_parse_env_file($envPath);
        if (!installer_has_installed_flag($envValues)) {
            return false;
        }

        $validator ??= 'installer_validate_existing_installation';
        if (!(bool) $validator($envValues)) {
            return false;
        }

        installer_write_lock($lockPath);

        return true;
    }
}
