<?php

namespace App\Services;

use App\Models\Settings;
use Illuminate\Support\Facades\Schema;

class EmailBrandingService
{
    public function __construct(private PublicAssetUrlService $publicAssetUrlService)
    {
    }

    public function getBranding(): array
    {
        $settings = $this->loadSettings();
        $settings = $this->publicAssetUrlService->normalizeSettings($settings);

        $siteName = $this->firstNonEmpty(
            $settings['siteName'] ?? null,
            config('app.name'),
            'Application'
        );

        $fromName = $this->firstNonEmpty(
            $settings['mailFromName'] ?? null,
            config('mail.from.name'),
            $siteName
        );

        if ($fromName === 'Dr. Alzahrani EM') {
            $fromName = $siteName;
        }

        $fromAddress = $this->normalizeEmail(
            $settings['mailFromAddress'] ?? config('mail.from.address')
        );

        $appUrl = rtrim((string) config('app.url', url('/')), '/');
        $host = (string) parse_url($appUrl, PHP_URL_HOST);
        $displayHost = preg_replace('/^www\./i', '', $host) ?: null;

        return [
            'site_name' => $siteName,
            'from_name' => $fromName,
            'from_address' => $fromAddress,
            'support_email' => $fromAddress,
            'logo_url' => $settings['logoUrl'] ?? null,
            'primary_color' => $this->normalizeColor($settings['primaryColor'] ?? '#1d4ed8'),
            'primary_color_dark' => $this->darkenColor($this->normalizeColor($settings['primaryColor'] ?? '#1d4ed8'), 0.18),
            'primary_color_soft' => $this->lightenColor($this->normalizeColor($settings['primaryColor'] ?? '#1d4ed8'), 0.88),
            'app_url' => $appUrl,
            'app_host' => $displayHost,
            'year' => (string) now()->year,
        ];
    }

    public function templateVariables(array $variables = []): array
    {
        $branding = $this->getBranding();

        return $this->mergeVariables($variables, $branding);
    }

    public function mergeVariables(array $variables = [], ?array $branding = null): array
    {
        $branding ??= $this->getBranding();

        return array_merge([
            '{site_name}' => $branding['site_name'],
            '{from_name}' => $branding['from_name'],
            '{support_email}' => $branding['support_email'] ?? '',
            '{app_url}' => $branding['app_url'],
            '{app_host}' => $branding['app_host'] ?? '',
            '{year}' => $branding['year'],
        ], $this->normalizeTemplateVariables($variables));
    }

    public function replaceVariables(?string $content, array $variables = []): ?string
    {
        if ($content === null) {
            return null;
        }

        $resolved = $this->templateVariables($variables);

        return str_replace(array_keys($resolved), array_values($resolved), $content);
    }

    private function loadSettings(): array
    {
        try {
            if (!Schema::hasTable('settings')) {
                return [];
            }

            return Settings::whereIn('key', [
                'siteName',
                'logoUrl',
                'primaryColor',
                'mailFromAddress',
                'mailFromName',
            ])->pluck('value', 'key')->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    private function normalizeTemplateVariables(array $variables): array
    {
        $normalized = [];

        foreach ($variables as $key => $value) {
            $token = str_starts_with((string) $key, '{') ? (string) $key : '{' . trim((string) $key, '{}') . '}';
            $normalized[$token] = is_scalar($value) || $value === null ? (string) ($value ?? '') : '';
        }

        return $normalized;
    }

    private function normalizeEmail(?string $email): ?string
    {
        $email = trim((string) $email);

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function normalizeColor(?string $color): string
    {
        $color = trim((string) $color);

        return preg_match('/^#[0-9a-f]{6}$/i', $color) ? $color : '#1d4ed8';
    }

    private function darkenColor(string $hex, float $amount): string
    {
        return $this->adjustColor($hex, -abs($amount));
    }

    private function lightenColor(string $hex, float $amount): string
    {
        return $this->adjustColor($hex, abs($amount));
    }

    private function adjustColor(string $hex, float $amount): string
    {
        $hex = ltrim($this->normalizeColor($hex), '#');
        $channels = str_split($hex, 2);

        $adjusted = array_map(function (string $channel) use ($amount) {
            $value = hexdec($channel);

            if ($amount >= 0) {
                $value = (int) round($value + ((255 - $value) * $amount));
            } else {
                $value = (int) round($value * (1 + $amount));
            }

            $value = max(0, min(255, $value));

            return str_pad(dechex($value), 2, '0', STR_PAD_LEFT);
        }, $channels);

        return '#' . implode('', $adjusted);
    }

    private function firstNonEmpty(?string ...$values): string
    {
        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($value !== '') {
                return $value;
            }
        }

        return 'Application';
    }
}
