<?php

namespace App\Jobs;

use App\Mail\RequestStatusUpdated;
use App\Models\Request as RequestModel;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRequestStatusUpdatedNotifications
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(public int $requestId)
    {
    }

    public function handle(TelegramService $telegramService): void
    {
        $request = RequestModel::find($this->requestId);
        if (!$request) {
            return;
        }

        try {
            Mail::to($request->student_email)->send(new RequestStatusUpdated($request));
        } catch (\Throwable $e) {
            Log::error('Status update email failed: ' . $e->getMessage(), [
                'request_id' => $request->id,
                'tracking_id' => $request->tracking_id,
                'status' => $request->status,
            ]);
        }

        if (!$request->telegram_chat_id) {
            return;
        }

        $message = "🔔 <b>Update on your Request</b>\n\n";
        $message .= "Your request status has been updated to: <b>{$request->status}</b>\n";

        if ($request->status === 'Approved') {
            $message .= "✅ Congratulations! Check your email for the recommendation letter.";
        } elseif ($request->status === 'Rejected') {
            $message .= "❌ We are sorry, but your request has been declined. Check your email for details.";
        } elseif ($request->status === 'Needs Revision') {
            $message .= "📝 Additional information is needed. Please check your email.";
        }

        $studentMessage = $this->studentMessage($request);
        if ($studentMessage) {
            $message .= "\n\n🗒️ <b>Message:</b> " . e($studentMessage);
        }

        try {
            $telegramService->sendMessageToChat($request->telegram_chat_id, $message);
        } catch (\Throwable $e) {
            Log::error('Failed to send Telegram update to student: ' . $e->getMessage(), [
                'request_id' => $request->id,
                'tracking_id' => $request->tracking_id,
                'status' => $request->status,
            ]);
        }
    }

    private function studentMessage(RequestModel $request): ?string
    {
        $message = $request->status === 'Rejected'
            ? ($request->rejection_reason ?? null)
            : ($request->admin_message ?? null);

        $message = trim((string) $message);

        return $message === '' ? null : $message;
    }
}
