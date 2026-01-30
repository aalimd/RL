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

        // Log incoming update for debug (temporarily)
        // Log::info('Telegram Webhook: ', $update);

        if (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
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
        $messageId = $callback['message']['message_id'];
        $chatId = $callback['message']['chat']['id'];

        // Verify this allows only authorized chat ID
        $authorizedChatId = Settings::where('key', 'telegram_chat_id')->value('value');
        if ($chatId != $authorizedChatId) {
            Log::warning("Unauthorized Telegram attempt from Chat ID: $chatId");
            $this->answerCallback($callbackId, '⛔ Unauthorized');
            return;
        }

        if (strpos($data, 'approve_') === 0) {
            $requestId = substr($data, 8);
            $this->approveRequest($requestId, $chatId);
            $this->answerCallback($callbackId, '✅ Approved!');
        } elseif (strpos($data, 'reject_') === 0) {
            $requestId = substr($data, 7);
            $this->rejectRequest($requestId, $chatId);
            $this->answerCallback($callbackId, '❌ Rejected!');
        } else {
            $this->answerCallback($callbackId);
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

    protected function approveRequest($requestId, $chatId)
    {
        $req = RequestModel::find($requestId);
        if (!$req)
            return;

        $req->status = 'Approved';

        // Generate verify_token if missing (for QR code)
        if (!$req->verify_token) {
            $req->verify_token = \Str::random(32);
        }

        $req->save();

        // Send email to student
        try {
            Mail::to($req->student_email)
                ->send(new \App\Mail\RequestStatusUpdated($req));
        } catch (\Exception $e) {
            Log::error('Telegram approve email failed: ' . $e->getMessage());
        }

        // Audit Log
        \App\Models\AuditLog::create([
            'action' => 'telegram_approve_request',
            'details' => "Approved request #{$req->id} ({$req->tracking_id}) via Telegram",
        ]);

        // Send feedback to Telegram
        $this->telegram->sendMessage("✅ Request for {$req->student_name} has been APPROVED.\n📧 Email sent to student.");
    }

    protected function rejectRequest($requestId, $chatId)
    {
        $req = RequestModel::find($requestId);
        if (!$req)
            return;

        $req->status = 'Rejected';
        $req->save();

        // Send email to student
        try {
            Mail::to($req->student_email)
                ->send(new \App\Mail\RequestStatusUpdated($req));
        } catch (\Exception $e) {
            Log::error('Telegram reject email failed: ' . $e->getMessage());
        }

        // Audit Log
        \App\Models\AuditLog::create([
            'action' => 'telegram_reject_request',
            'details' => "Rejected request #{$req->id} ({$req->tracking_id}) via Telegram",
        ]);

        $this->telegram->sendMessage("❌ Request for {$req->student_name} has been REJECTED.\n📧 Email sent to student.");
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
