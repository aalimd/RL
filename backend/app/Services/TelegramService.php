<?php

namespace App\Services;

use App\Models\Settings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $apiUrl = 'https://api.telegram.org/bot';
    protected $botToken;
    protected $chatId;

    public function __construct()
    {
        // Load settings from database
        // Using get() instead of pluck() to trigger model accessors (encryption)
        $this->botToken = Settings::where('key', 'telegram_bot_token')->first()?->value;
        $this->chatId = Settings::where('key', 'telegram_chat_id')->first()?->value;
    }

    /**
     * Send a message to the configured Telegram chat
     */
    public function sendMessage($message, $keyboard = null)
    {
        if (!$this->botToken || !$this->chatId) {
            Log::warning('Telegram settings not configured');
            return false;
        }

        $data = [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ];

        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }

        try {
            $response = Http::post($this->apiUrl . $this->botToken . '/sendMessage', $data);

            if (!$response->successful()) {
                Log::error('Telegram API Error: ' . $response->body());
                return false;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Telegram Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a request notification with action buttons
     */
    public function sendRequestNotification($request)
    {
        $message = "<b>📬 New Recommendation Request</b>\n\n";
        $message .= "<b>Student:</b> {$request->student_name} {$request->last_name}\n";
        $message .= "<b>Email:</b> {$request->student_email}\n";
        $message .= "<b>University:</b> " . ($request->university ?: 'Not specified') . "\n";
        $message .= "<b>Purpose:</b> {$request->purpose}\n";
        $message .= "<b>Tracking ID:</b> {$request->tracking_id}\n";

        // Inline keyboard with actions
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Approve', 'callback_data' => "approve_{$request->id}"],
                    ['text' => '❌ Reject', 'callback_data' => "reject_{$request->id}"]
                ]
            ]
        ];

        // Only add View Details button if URL is HTTPS (production)
        $detailsUrl = url("/admin/requests/{$request->id}");
        if (strpos($detailsUrl, 'https://') === 0) {
            $keyboard['inline_keyboard'][] = [
                ['text' => '👁️ View Details', 'url' => $detailsUrl]
            ];
        }

        return $this->sendMessage($message, $keyboard);
    }

    /**
     * Set the webhook for the bot
     */
    public function setWebhook($url)
    {
        if (!$this->botToken) {
            return ['ok' => false, 'description' => 'Bot token not set'];
        }

        $response = Http::post($this->apiUrl . $this->botToken . '/setWebhook', [
            'url' => $url
        ]);

        return $response->json();
    }
}
