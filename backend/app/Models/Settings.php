<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Settings extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    /**
     * List of sensitive keys that could be encrypted in the future
     * Currently disabled to maintain compatibility with existing data
     */
    protected static $sensitiveKeys = [
        'telegram_bot_token',
        'geminiApiKey',
        'smtpPassword',
        'telegram_webhook_secret',
    ];

    /**
     * Check if a key is sensitive
     */
    public static function isSensitive($key): bool
    {
        return in_array($key, self::$sensitiveKeys);
    }

    /**
     * Accessor: Decrypt value if sensitive
     */
    public function getValueAttribute($value)
    {
        if ($value && self::isSensitive($this->key)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value; // Return plain text if decryption fails (migration support)
            }
        }
        return $value;
    }

    /**
     * Mutator: Encrypt value if sensitive
     */
    public function setValueAttribute($value)
    {
        if ($value && self::isSensitive($this->key)) {
            $this->attributes['value'] = Crypt::encryptString($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    /**
     * Helper to get a setting value
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}

