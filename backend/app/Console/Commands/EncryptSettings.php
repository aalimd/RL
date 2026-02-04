<?php
// backend/encrypt_settings_migration.php
// Run this via: php backend/encrypt_settings_migration.php
// Or place it in a route or command. Since we are local, a script in root or route is fine.
// But better to use artisan tinker or a temporary command.
// We will create a temporary artisan command file.

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Settings;
use Illuminate\Support\Facades\Crypt;

class EncryptSettings extends Command
{
    protected $signature = 'settings:encrypt';
    protected $description = 'Encrypt sensitive settings';

    public function handle()
    {
        $sensitiveKeys = [
            'telegram_bot_token',
            'geminiApiKey',
            'smtpPassword',
            'telegram_webhook_secret'
        ];

        $count = 0;
        foreach ($sensitiveKeys as $key) {
            $setting = Settings::where('key', $key)->first();
            if ($setting && $setting->value) {
                // Check if already encrypted by trying to decrypt
                try {
                    Crypt::decryptString($setting->value);
                    $this->info("Key '$key' is already encrypted.");
                } catch (\Exception $e) {
                    // Decryption failed = It is plain text
                    // We need to re-save it.
                    // The Mutator setValueAttribute will handle encryption.
                    $plainValue = $setting->value;
                    $setting->value = $plainValue; // Triggers mutator
                    $setting->save();
                    $this->info("Encrypted key: '$key'");
                    $count++;
                }
            }
        }
        $this->info("Migration completed. Encrypted $count settings.");
    }
}
