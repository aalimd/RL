<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Settings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class MailConfigProvider extends ServiceProvider
{
    public function boot()
    {
        try {
            // Only configure if the settings table exists
            if (!Schema::hasTable('settings')) {
                return;
            }

            // Cache settings for 5 minutes to reduce DB queries
            $settings = cache()->remember('mail_settings', 300, function () {
                return Settings::whereIn('key', [
                    'smtpHost',
                    'smtpPort',
                    'smtpUsername',
                    'smtpPassword',
                    'smtpEncryption',
                    'mailFromAddress',
                    'mailFromName'
                ])->pluck('value', 'key')->toArray();
            });

            $useSmtp = config('mail.default') === 'smtp';
            if ($useSmtp && !empty($settings['smtpHost'])) {
                Config::set('mail.mailers.smtp.host', $settings['smtpHost']);
                Config::set('mail.mailers.smtp.port', $settings['smtpPort'] ?? 587);
                Config::set('mail.mailers.smtp.username', $settings['smtpUsername'] ?? null);
                Config::set('mail.mailers.smtp.password', $settings['smtpPassword'] ?? null);
                Config::set('mail.mailers.smtp.encryption', $settings['smtpEncryption'] ?? 'tls');
            }
            if (!empty($settings['mailFromAddress'])) {
                Config::set('mail.from.address', $settings['mailFromAddress']);
            }
            if (!empty($settings['mailFromName'])) {
                Config::set('mail.from.name', $settings['mailFromName']);
            }
        } catch (\Exception $e) {
            // Silently fail if database is not available
        }
    }

    public function register()
    {
        //
    }
}
