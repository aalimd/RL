<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Check if application is installed before attempting DB connection
        if (config('app.installed') !== true && config('app.installed') !== 'true') {
            return;
        }

        try {
            // Only run if table exists to avoid migration issues
            if (!Schema::hasTable('settings')) {
                return;
            }
            // Retrieve all mail settings in one query
            // keys: smtpHost, smtpPort, smtpUsername, smtpPassword, smtpEncryption, mailFromAddress, mailFromName
            $mailSettings = DB::table('settings')
                ->whereIn('key', [
                    'smtpHost',
                    'smtpPort',
                    'smtpUsername',
                    'smtpPassword',
                    'smtpEncryption',
                    'mailFromAddress',
                    'mailFromName'
                ])
                ->pluck('value', 'key');

            if ($mailSettings->isNotEmpty()) {
                $useSmtp = config('mail.default') === 'smtp';
                $config = [
                    'transport' => 'smtp',
                    'host' => $mailSettings['smtpHost'] ?? config('mail.mailers.smtp.host'),
                    'port' => $mailSettings['smtpPort'] ?? config('mail.mailers.smtp.port'),
                    'encryption' => $mailSettings['smtpEncryption'] ?? config('mail.mailers.smtp.encryption'),
                    'username' => $mailSettings['smtpUsername'] ?? config('mail.mailers.smtp.username'),
                    'password' => $mailSettings['smtpPassword'] ?? config('mail.mailers.smtp.password'),
                    'timeout' => null,
                ];

                if ($useSmtp) {
                    Config::set('mail.mailers.smtp', array_merge(config('mail.mailers.smtp', []), $config));
                }

                // Set From Address
                if (isset($mailSettings['mailFromAddress'])) {
                    Config::set('mail.from.address', $mailSettings['mailFromAddress']);
                }
                if (isset($mailSettings['mailFromName'])) {
                    Config::set('mail.from.name', $mailSettings['mailFromName']);
                }
            }
        } catch (\Exception $e) {
            // Log error but don't crash application
            Log::error('MailConfigServiceProvider failed to load settings: ' . $e->getMessage());
        }
    }
}
