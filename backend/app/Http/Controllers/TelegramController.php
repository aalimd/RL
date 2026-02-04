<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use App\Models\Settings;
use App\Services\TelegramService;
use App\Services\LetterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TelegramController extends Controller
{
    protected $telegram;
    protected $letterService;

    public function __construct(TelegramService $telegram, LetterService $letterService)
    {
        $this->telegram = $telegram;
        $this->letterService = $letterService;
    }

    /**
     * Handle incoming webhooks from Telegram
     */
    public function handleWebhook(Request $request)
    {
        // Security: Verify webhook secret if configured
        // Security: Verify webhook secret via Header (New Standard)
        $webhookSecret = Settings::getValue('telegram_webhook_secret');

        // Strict Check: Check header First
        if ($webhookSecret) {
            $headerSecret = $request->header('X-Telegram-Bot-Api-Secret-Token');
            if ($headerSecret !== $webhookSecret) {
                Log::warning('Telegram webhook rejected: Invalid secret token header');
                return response('Forbidden', 403);
            }
        }

        $update = $request->all();

        if (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
        } elseif (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }

        return response('OK', 200);
    }

    /**
     * Handle button clicks (Callback Queries)
     */
    protected function handleCallback($callback)
    {
        $data = $callback['data'];
        $callbackId = $callback['id'];
        $chatId = $callback['message']['chat']['id'];

        // Verify this allows only authorized chat ID
        $authorizedChatId = Settings::where('key', 'telegram_chat_id')->value('value');
        if ($chatId != $authorizedChatId) {
            Log::warning("Unauthorized Telegram attempt from Chat ID: $chatId");
            $this->answerCallback($callbackId, '⛔ Unauthorized');
            return;
        }

        if (strpos($data, 'approve_') === 0) {
            $this->updateRequestStatus(substr($data, 8), 'Approved', '✅ Approved!', 'telegram_approve_request');
        } elseif (strpos($data, 'reject_') === 0) {
            $this->updateRequestStatus(substr($data, 7), 'Rejected', '❌ Rejected!', 'telegram_reject_request');
        } elseif (strpos($data, 'under_review_') === 0) {
            $this->updateRequestStatus(substr($data, 13), 'Under Review', '👀 Marked Under Review!', 'telegram_review_request');
        } elseif (strpos($data, 'needs_revision_') === 0) {
            $this->updateRequestStatus(substr($data, 15), 'Needs Revision', '🔄 Marked Needs Revision!', 'telegram_revision_request');
        } else {
            $this->answerCallback($callbackId);
        }

        // Always answer callback to stop loading animation
        $this->answerCallback($callbackId);
    }

    /**
     * Unified method to update request status
     */
    protected function updateRequestStatus($requestId, $status, $feedbackMessage, $auditAction)
    {
        $req = RequestModel::find($requestId);
        if (!$req)
            return;

        $req->status = $status;

        // Generate verify_token if missing (for Approved requests mostly, but good integration)
        if ($status === 'Approved' && !$req->verify_token) {
            $req->verify_token = \Str::random(32);
        }

        $req->save();

        // Send email to student
        try {
            Mail::to($req->student_email)
                ->send(new \App\Mail\RequestStatusUpdated($req));
        } catch (\Exception $e) {
            Log::error("Telegram $status email failed: " . $e->getMessage());
        }

        // Audit Log
        \App\Models\AuditLog::create([
            'action' => $auditAction,
            'details' => "$status request #{$req->id} ({$req->tracking_id}) via Telegram",
        ]);

        // Send feedback to Admin via Telegram
        $this->telegram->sendMessage("$feedbackMessage\n👤 Student: {$req->student_name}\n📧 Email notification sent.");

        // Send Notification to Student (if subscribed)
        if ($req->telegram_chat_id) {
            $studentMsg = "🔔 <b>Update on your Request</b>\n\n";
            $studentMsg .= "Your request status has been updated to: <b>$status</b>\n";
            if ($status === 'Approved') {
                $studentMsg .= "✅ Congratulations! Check your email for the recommendation letter.";
            } elseif ($status === 'Rejected') {
                $studentMsg .= "❌ We are sorry, but your request has been declined. Check your email for details.";
            } elseif ($status === 'Needs Revision') {
                $studentMsg .= "📝 Additional information is needed. Please check your email.";
            }

            // Send to student
            try {
                // Determine student bot token/chat ID? 
                // Wait, we use the SAME bot. So we just send to $req->telegram_chat_id
                // We need to use a clean sendMessage method that accepts chatId
                $this->telegram->sendMessageToChat($req->telegram_chat_id, $studentMsg);
            } catch (\Exception $e) {
                Log::error("Failed to send Telegram update to student: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle incoming text messages (e.g. /start TRACKING_ID)
     */
    protected function handleMessage($message)
    {
        $chatId = $message['chat']['id'] ?? null;
        if (!$chatId)
            return;

        // 1. Check for Contact Share (Verification Step)
        if (isset($message['contact'])) {
            $this->handleContactVerification($chatId, $message['contact']);
            return;
        }

        $text = $message['text'] ?? '';

        // 2. Check for /start command with Tracking ID
        if (strpos($text, '/start') === 0) {
            $parts = explode(' ', $text);
            $trackingId = $parts[1] ?? null;

            if ($trackingId) {
                // Find Request
                $req = RequestModel::where('tracking_id', $trackingId)->first();

                if (!$req) {
                    $this->telegram->sendMessageToChat($chatId, "❌ Request <b>#$trackingId</b> not found.\nPlease check the link and try again.");
                    return;
                }

                if ($req->telegram_chat_id == $chatId) {
                    $this->telegram->sendMessageToChat($chatId, "✅ You are already subscribed to this request.");
                    return;
                }

                if (empty($req->phone)) {
                    // Fallback for old requests without phone numbers
                    // OR explicit policy: No phone = No telegram (safer)
                    // For now, let's allow it but warn, OR strictly require it.
                    // Strict is better given the user request: "Strict Verification".
                    // But wait, if they didn't supply phone, they can't verify.
                    // Let's assume new requests have phone. For old ones, maybe ask Admin to update?
                    // Let's reject for now to be safe.
                    $this->telegram->sendMessageToChat($chatId, "⚠️ <b>Security Alert</b>\n\nThis request does not have a registered phone number for verification.\nPlease contact support to update your profile.");
                    return;
                }

                // Store intent in Cache for 5 minutes
                \Illuminate\Support\Facades\Cache::put("telegram_auth_{$chatId}", $trackingId, now()->addMinutes(5));

                // Ask for Contact
                $keyboard = [
                    'keyboard' => [
                        [
                            [
                                'text' => '📱 Verify My Phone Number',
                                'request_contact' => true
                            ]
                        ]
                    ],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ];

                $this->telegram->sendMessageToChat($chatId, "🔒 <b>Security Verification</b>\n\nTo subscribe to updates for <b>#$trackingId</b>, please verify your identity by sharing your phone number.\n\nClick the button below to verify.", $keyboard);

            } else {
                // Default welcome
                $this->telegram->sendMessageToChat($chatId, "👋 Welcome to the Recommendation Letter Bot.\n\nTo track a request, please use the 'Subscribe to Updates' button from the website.");
            }
        }
    }

    /**
     * Handle Contact Verification
     */
    protected function handleContactVerification($chatId, $contact)
    {
        // 1. Retrieve pending tracking ID
        $trackingId = \Illuminate\Support\Facades\Cache::get("telegram_auth_{$chatId}");

        if (!$trackingId) {
            $this->telegram->sendMessageToChat($chatId, "⏳ Session expired. Please click the 'Subscribe' button on the website again.");
            return;
        }

        // 2. Validate User Identity (Anti-Spoofing)
        // Telegram contact object has 'user_id'. It MUST match match the sender's ID.
        if (isset($contact['user_id']) && $contact['user_id'] != $chatId) {
            $this->telegram->sendMessageToChat($chatId, "⛔ <b>Security Violation</b>\nYou cannot verify using someone else's contact.");
            return;
        }

        // 3. Find Request
        $req = RequestModel::where('tracking_id', $trackingId)->first();
        if (!$req) {
            $this->telegram->sendMessageToChat($chatId, "❌ Request not found.");
            return;
        }

        // 4. Compare Phone Numbers
        $telegramPhone = $this->normalizePhone($contact['phone_number']);
        $requestPhone = $this->normalizePhone($req->phone);

        // Check if one contains the other (to handle country code differences)
        // e.g. T: 96650... R: 050... OR T: 96650... R: +96650...
        // Safest: Check last 9 digits
        $match = false;

        if ($telegramPhone === $requestPhone) {
            $match = true;
        } elseif (str_ends_with($telegramPhone, substr($requestPhone, -9)) || str_ends_with($requestPhone, substr($telegramPhone, -9))) {
            $match = true;
        }

        if ($match) {
            // Success! Link them.
            $req->telegram_chat_id = $chatId;
            $req->save();

            // Clear Cache
            \Illuminate\Support\Facades\Cache::forget("telegram_auth_{$chatId}");

            // Improve UX: Remove the keyboard
            $removeKeyboard = ['remove_keyboard' => true];

            $this->telegram->sendMessageToChat($chatId, "✅ <b>Verification Successful!</b>\n\nYou are now subscribed to updates for request <b>#$trackingId</b>.", $removeKeyboard);
        } else {
            $this->telegram->sendMessageToChat($chatId, "❌ <b>Verification Failed</b>\n\nThe number you shared ($telegramPhone) does not match the number registered in the request.\n\nPlease ensure you are using the same Telegram account.");
        }
    }

    /**
     * Normalize phone number for comparison
     * Keeps only digits
     */
    private function normalizePhone($phone)
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Answer callback query to stop loading indicator
     */
    protected function answerCallback($callbackId, $text = null)
    {
        $botToken = Settings::where('key', 'telegram_bot_token')->value('value');
        if (!$botToken)
            return;

        $data = ['callback_query_id' => $callbackId];
        if ($text) {
            $data['text'] = $text;
            $data['show_alert'] = false;
        }

        try {
            \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$botToken}/answerCallbackQuery", $data);
        } catch (\Exception $e) {
            Log::error('answerCallbackQuery failed: ' . $e->getMessage());
        }
    }

    /**
     * Set Webhook Route (Utility)
     */
    public function setupWebhook()
    {
        // Get or generate webhook secret
        $webhookSecret = Settings::where('key', 'telegram_webhook_secret')->value('value');
        if (!$webhookSecret) {
            $webhookSecret = \Str::random(32);
            Settings::updateOrCreate(
                ['key' => 'telegram_webhook_secret'],
                ['value' => $webhookSecret]
            );
        }



        // Clean URL (Remove query params if any)
        $url = url('/api/telegram/webhook');

        // Ensure HTTPS
        if (strpos($url, 'http://') === 0) {
            $url = str_replace('http://', 'https://', $url);
        }

        // Set Webhook with Secret Token Header
        $result = $this->telegram->setWebhook($url, $webhookSecret);

        return response()->json($result);
    }

    /**
     * Test Notification
     */
    public function testNotification()
    {
        $result = $this->telegram->sendMessage("🔔 This is a test notification from your Recommendation System.");
        return response()->json(['success' => (bool) $result, 'response' => $result]);
    }
}
