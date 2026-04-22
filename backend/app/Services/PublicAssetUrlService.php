<?php

namespace App\Services;

class PublicAssetUrlService
{
    /**
     * Settings keys that may point to files stored on the public disk.
     *
     * @var string[]
     */
    private array $publicAssetKeys = [
        'logoUrl',
        'loginBackgroundImage',
        'backgroundImage',
    ];

    /**
     * Normalize a public asset URL so shared hosting still works without storage:link.
     */
    public function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return $value;
        }

        if ($this->isExternalUrl($value) || str_starts_with($value, 'data:')) {
            return $value;
        }

        $relativePath = $this->publicDiskRelativePath($value);
        if ($relativePath === null) {
            return $value;
        }

        if ($this->isPublishedUnderPublicStorage($relativePath)) {
            return $this->storageUrl($relativePath);
        }

        return route('public.media', ['path' => $relativePath], false);
    }

    /**
     * Normalize all public asset settings in a settings array.
     */
    public function normalizeSettings(array $settings, ?array $keys = null): array
    {
        $keys ??= $this->publicAssetKeys;

        foreach ($keys as $key) {
            if (array_key_exists($key, $settings)) {
                $settings[$key] = $this->normalize($settings[$key]);
            }
        }

        return $settings;
    }

    /**
     * Extract the storage/app/public relative path from a stored URL.
     */
    public function publicDiskRelativePath(string $value): ?string
    {
        $path = parse_url($value, PHP_URL_PATH);
        if (!is_string($path) || trim($path) === '') {
            return null;
        }

        $path = '/' . ltrim($path, '/');

        foreach (['/storage/', '/media/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $relative = ltrim(substr($path, strlen($prefix)), '/');
                return $relative === '' ? null : $relative;
            }
        }

        return null;
    }

    /**
     * Check if the file is already reachable through public/storage.
     */
    private function isPublishedUnderPublicStorage(string $relativePath): bool
    {
        return is_file(public_path('storage/' . $relativePath));
    }

    /**
     * Build the canonical /storage URL for a public disk file.
     */
    private function storageUrl(string $relativePath): string
    {
        return '/' . ltrim('storage/' . $relativePath, '/');
    }

    /**
     * Detect remote URLs that should be left untouched.
     */
    private function isExternalUrl(string $value): bool
    {
        return preg_match('#^(?:https?:)?//#i', $value) === 1;
    }
}
