<?php

namespace App\Services;

use App\Jobs\SendRequestStatusUpdatedNotifications;
use App\Jobs\WarmApprovedLetterPdf;
use App\Models\Request as RequestModel;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RequestStatusService
{
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
        $request = $request->fresh();

        if ($request->status === 'Approved' && config('queue.default') !== 'sync') {
            WarmApprovedLetterPdf::dispatch($request->id);
        }

        return $request;
    }

    /**
     * Send consistent student notifications after a status transition.
     */
    public function notifyStudent(RequestModel $request): void
    {
        SendRequestStatusUpdatedNotifications::dispatch($request->id);
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
