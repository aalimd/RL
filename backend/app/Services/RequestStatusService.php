<?php

namespace App\Services;

use App\Mail\RequestStatusUpdated;
use App\Models\Request as RequestModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RequestStatusService
{
    public function __construct(private TelegramService $telegramService)
    {
    }

    /**
     * Apply a normalized status transition to a request.
     *
     * @throws ValidationException
     */
    public function transition(RequestModel $request, string $status, array $context = []): RequestModel
    {
        $adminMessage = $this->normalizeMessage($context['admin_message'] ?? null);
        $rejectionReason = $this->normalizeMessage($context['rejection_reason'] ?? null);

        if ($status === 'Needs Revision' && $adminMessage === null) {
            throw ValidationException::withMessages([
                'admin_message' => 'Admin message is required when status is Needs Revision.',
            ]);
        }

        $resolvedRejectionReason = $rejectionReason ?? $adminMessage;

        if ($status === 'Rejected' && $resolvedRejectionReason === null) {
            $errorKey = array_key_exists('rejection_reason', $context) ? 'rejection_reason' : 'admin_message';
            throw ValidationException::withMessages([
                $errorKey => 'Rejection reason is required when status is Rejected.',
            ]);
        }

        $request->status = $status;

        if ($status === 'Approved' && !$request->verify_token) {
            $request->verify_token = Str::random(32);
        }

        if ($status === 'Needs Revision') {
            $request->admin_message = $adminMessage;
            $request->rejection_reason = null;
        } elseif ($status === 'Rejected') {
            $request->admin_message = null;
            $request->rejection_reason = $resolvedRejectionReason;
        } else {
            // Preserve optional student-facing notes for statuses like Approved or Under Review.
            $request->admin_message = $adminMessage;
            $request->rejection_reason = null;
        }

        $request->save();

        return $request->fresh();
    }

    /**
     * Send consistent student notifications after a status transition.
     */
    public function notifyStudent(RequestModel $request): void
    {
        try {
            Mail::to($request->student_email)->send(new RequestStatusUpdated($request));
        } catch (\Exception $e) {
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
            $this->telegramService->sendMessageToChat($request->telegram_chat_id, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram update to student: ' . $e->getMessage(), [
                'request_id' => $request->id,
                'tracking_id' => $request->tracking_id,
                'status' => $request->status,
            ]);
        }
    }

    public function studentMessage(RequestModel $request): ?string
    {
        return match ($request->status) {
            'Rejected' => $this->normalizeMessage($request->rejection_reason),
            'Needs Revision' => $this->normalizeMessage($request->admin_message),
            default => $this->normalizeMessage($request->admin_message) ?? $this->normalizeMessage($request->rejection_reason),
        };
    }

    private function normalizeMessage(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
