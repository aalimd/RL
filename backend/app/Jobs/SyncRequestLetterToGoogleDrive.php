<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\Request as RequestModel;
use App\Services\GoogleDriveLetterBackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncRequestLetterToGoogleDrive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public int $requestId,
        public ?int $initiatedByUserId = null,
        public ?string $source = null,
    ) {
        $this->onQueue('integrations');
    }

    public function handle(GoogleDriveLetterBackupService $googleDriveLetterBackupService): void
    {
        $request = RequestModel::find($this->requestId);
        if (!$request || $request->status !== 'Approved') {
            return;
        }

        $request->forceFill([
            'drive_backup_status' => 'syncing',
            'drive_backup_error' => null,
        ])->save();

        $result = $googleDriveLetterBackupService->syncRequest($request);

        AuditLog::create([
            'user_id' => $this->initiatedByUserId,
            'action' => 'admin_sync_letter_google_drive_completed',
            'target_type' => 'request',
            'target_id' => $request->id,
            'details' => json_encode([
                'tracking_id' => $request->tracking_id,
                'source' => $this->source,
                'file_id' => $result['file_id'] ?? null,
                'file_name' => $result['file_name'] ?? null,
            ]),
        ]);
    }

    public function failed(\Throwable $e): void
    {
        $request = RequestModel::find($this->requestId);
        if ($request) {
            $request->forceFill([
                'drive_backup_status' => 'failed',
                'drive_backup_error' => $e->getMessage(),
            ])->save();

            AuditLog::create([
                'user_id' => $this->initiatedByUserId,
                'action' => 'admin_sync_letter_google_drive_failed',
                'target_type' => 'request',
                'target_id' => $request->id,
                'details' => json_encode([
                    'tracking_id' => $request->tracking_id,
                    'source' => $this->source,
                    'error' => $e->getMessage(),
                ]),
            ]);
        }
    }
}
