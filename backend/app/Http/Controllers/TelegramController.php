<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use App\Models\Settings;
use App\Services\TelegramService;
use App\Services\LetterService;
use App\Services\RequestStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    protected $telegram;
    protected $letterService;
    protected $requestStatusService;

    public function __construct(TelegramService $telegram, LetterService $letterService, RequestStatusService $requestStatusService)
    {
        $this->telegram = $telegram;
        $this->letterService = $letterService;
        $this->requestStatusService = $requestStatusService;
    }

    /**
     * Handle incoming webhooks from Telegram
     */
    public function handleWebhook(Request $request)
    {
        // Security: Fail closed - secret must be configured for webhook processing.
        $webhookSecret = Settings::getValue('telegram_webhook_secret');
        if (!$webhookSecret) {
            Log::warning('Telegram webhook rejected: secret not configured');
            return response('Forbidden', 403);
        }

        // Verify Telegram webhook header using constant-time comparison.
        $headerSecret = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');
        if (!hash_equals((string) $webhookSecret, $headerSecret)) {
            Log::warning('Telegram webhook rejected: Invalid secret token header');
            return response('Forbidden', 403);
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
        $authorizedChatId = (string) Settings::getValue('telegram_chat_id', '');
        if ($authorizedChatId === '' || (string) $chatId !== $authorizedChatId) {
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
            return;
        }

        // Always answer callback to stop loading animation
        $this->answerCallback($callbackId);
    }

    /**
     * Unified method to update request status
     */
    protected function updateRequestStatus($requestId, $status, $feedbackMessage, $auditAction)
    {
        $requestId = (int) $requestId;
        if ($requestId <= 0) {
            return;
        }

        $req = RequestModel::find($requestId);
        if (!$req)
            return;

        $transitionContext = [];
        if ($status === 'Needs Revision') {
            $transitionContext['admin_message'] = 'Please review your request details and submit the requested revisions.';
        } elseif ($status === 'Rejected') {
            $transitionContext['rejection_reason'] = 'Your request has been declined. Please contact support if you need more details.';
        }

        $req = $this->requestStatusService->transition($req, $status, $transitionContext);
        $this->requestStatusService->notifyStudent($req);

        // Audit Log
        \App\Models\AuditLog::create([
            'action' => $auditAction,
            'details' => "$status request #{$req->id} ({$req->tracking_id}) via Telegram",
            'ip_address' => request()->ip(), // Capture webhook IP (Telegram server)
        ]);

        // Send feedback to Admin via Telegram
        $this->telegram->sendMessage("$feedbackMessage\n👤 Student: {$req->student_name}\n📧 Email notification sent.");
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
        } else {
            $requestTail = strlen($requestPhone) > 9 ? substr($requestPhone, -9) : $requestPhone;
            $telegramTail = strlen($telegramPhone) > 9 ? substr($telegramPhone, -9) : $telegramPhone;
            if (
                $requestTail !== '' &&
                $telegramTail !== '' &&
                (str_ends_with($telegramPhone, $requestTail) || str_ends_with($requestPhone, $telegramTail))
            ) {
                $match = true;
            }
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
        $botToken = Settings::getValue('telegram_bot_token');
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
    public function setupWebhook(Request $request)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['ok' => false, 'description' => 'Unauthorized'], 403);
        }

        // Get or generate webhook secret
        $webhookSecret = Settings::getValue('telegram_webhook_secret');
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

        Log::info('Setting up Telegram Webhook:', [
            'url' => $url,
            'has_secret' => !empty($webhookSecret)
        ]);

        // Set Webhook with Secret Token Header
        $result = $this->telegram->setWebhook($url, $webhookSecret);

        if (isset($result['ok']) && !$result['ok']) {
            Log::error('Telegram setWebhook failed:', $result);
        }

        return response()->json($result);
    }

    /**
     * Test Notification
     */
    public function testNotification(Request $request)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $result = $this->telegram->sendMessage("🔔 This is a test notification from your Recommendation System.");
        return response()->json(['success' => (bool) $result, 'response' => $result]);
    }
}
