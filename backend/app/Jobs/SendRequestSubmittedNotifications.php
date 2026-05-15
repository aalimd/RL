<?php

namespace App\Jobs;

use App\Mail\RequestSubmittedToAdmin;
use App\Mail\RequestSubmittedToStudent;
use App\Models\Request as RequestModel;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRequestSubmittedNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public int $requestId,
        public bool $notifyStudent = true,
        public bool $notifyAdmins = true,
        public bool $notifyTelegram = true,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(TelegramService $telegramService): void
    {
        $request = RequestModel::find($this->requestId);
        if (!$request) {
            return;
        }

        if ($this->notifyStudent) {
            try {
                Mail::to($request->student_email)->send(new RequestSubmittedToStudent($request));
            } catch (\Throwable $e) {
                Log::error('Student submission email failed: ' . $e->getMessage(), [
                    'request_id' => $request->id,
                    'tracking_id' => $request->tracking_id,
                    'student_email' => $request->student_email,
                ]);
            }
        }

        if ($this->notifyAdmins) {
            try {
                foreach (User::where('role', 'admin')->cursor() as $admin) {
                    try {
                        Mail::to($admin->email)->send(new RequestSubmittedToAdmin($request));
                    } catch (\Throwable $e) {
                        Log::error('Admin submission email failed: ' . $e->getMessage(), [
                            'request_id' => $request->id,
                            'tracking_id' => $request->tracking_id,
                            'admin_email' => $admin->email,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Admin submission notification lookup failed: ' . $e->getMessage(), [
                    'request_id' => $request->id,
                    'tracking_id' => $request->tracking_id,
                ]);
            }
        }

        if (!$this->notifyTelegram) {
            return;
        }

        try {
            $telegramService->sendRequestNotification($request);
        } catch (\Throwable $e) {
            Log::error('Telegram submission notification failed: ' . $e->getMessage(), [
                'request_id' => $request->id,
                'tracking_id' => $request->tracking_id,
            ]);
        }
    }
}
