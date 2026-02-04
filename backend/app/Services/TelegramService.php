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
        $this->botToken = Settings::getValue('telegram_bot_token');
        $this->chatId = Settings::getValue('telegram_chat_id');
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
     * Send a message to a specific chat ID
     */
    public function sendMessageToChat($chatId, $message, $keyboard = null)
    {
        if (!$this->botToken) {
            return false;
        }

        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ];

        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }

        try {
            $response = Http::post($this->apiUrl . $this->botToken . '/sendMessage', $data);
            return $response->successful();
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
                ],
                [
                    ['text' => '👀 Under Review', 'callback_data' => "under_review_{$request->id}"],
                    ['text' => '🔄 Needs Revision', 'callback_data' => "needs_revision_{$request->id}"]
                ]
            ]
        ];

        // Always add View Details button, ensuring HTTPS manually if needed
        $detailsUrl = url("/admin/requests/{$request->id}");
        $detailsUrl = url("/admin/requests/{$request->id}");

        // Ensure HTTPS for production URLs (exclude localhost)
        $isLocalhost = str_contains($detailsUrl, 'localhost') || str_contains($detailsUrl, '127.0.0.1');

        if (!$isLocalhost) {
            if (strpos($detailsUrl, 'http://') === 0) {
                $detailsUrl = str_replace('http://', 'https://', $detailsUrl);
            }
            // Only add button if not localhost (Telegram rejects localhost URLs)
            $keyboard['inline_keyboard'][] = [
                ['text' => '👁️ View Details', 'url' => $detailsUrl]
            ];
        } else {
            $message .= "\n<b>Note:</b> Access details on your local server.";
        }

        return $this->sendMessage($message, $keyboard);
    }

    /**
     * Set the webhook for the bot
     */
    public function setWebhook($url, $secretToken = null)
    {
        if (!$this->botToken) {
            return ['ok' => false, 'description' => 'Bot token not set'];
        }

        $data = ['url' => $url];
        if ($secretToken) {
            $data['secret_token'] = $secretToken;
        }

        $response = Http::post($this->apiUrl . $this->botToken . '/setWebhook', $data);

        return $response->json();
    }

    /**
     * Get Bot Username (from cache or API)
     */
    public function getBotUsername()
    {
        // Check DB first
        $username = Settings::getValue('telegram_bot_username');
        if ($username) {
            return $username;
        }

        if (!$this->botToken) {
            return null;
        }

        // Fetch from API
        try {
            $response = Http::get($this->apiUrl . $this->botToken . '/getMe');
            if ($response->successful()) {
                $data = $response->json();
                $username = $data['result']['username'] ?? null;

                if ($username) {
                    Settings::updateOrCreate(
                        ['key' => 'telegram_bot_username'],
                        ['value' => $username]
                    );
                    return $username;
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to get bot username: ' . $e->getMessage());
        }

        return null;
    }
}
