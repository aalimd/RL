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
        $webhookSecret = Settings::where('key', 'telegram_webhook_secret')->value('value');
        if ($webhookSecret && $request->query('secret') !== $webhookSecret) {
            Log::warning('Telegram webhook rejected: Invalid secret token');
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
        $text = $message['text'] ?? '';
        $chatId = $message['chat']['id'] ?? null;

        if (!$chatId)
            return;

        // Check for /start command
        if (strpos($text, '/start') === 0) {
            $parts = explode(' ', $text);
            $trackingId = $parts[1] ?? null;

            if ($trackingId) {
                // Link Student
                $req = RequestModel::where('tracking_id', $trackingId)->first();
                if ($req) {
                    $req->telegram_chat_id = $chatId;
                    $req->save();

                    $this->telegram->sendMessageToChat($chatId, "✅ <b>Subscribed!</b>\n\nYou will now receive instant updates for your request <b>#$trackingId</b> right here.");
                } else {
                    $this->telegram->sendMessageToChat($chatId, "❌ Request not found. Please check your link.");
                }
            } else {
                // Default welcome or Admin welcome
                $this->telegram->sendMessageToChat($chatId, "👋 Welcome to the Recommendation Letter Bot.\n\nTo track a request, please use the 'Subscribe to Updates' button from the website.");
            }
        }
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

        $url = url('/api/telegram/webhook') . '?secret=' . $webhookSecret;

        // Ensure HTTPS
        if (strpos($url, 'http://') === 0) {
            $url = str_replace('http://', 'https://', $url);
        }

        $result = $this->telegram->setWebhook($url);

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
