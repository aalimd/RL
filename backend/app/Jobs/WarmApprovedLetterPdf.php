<?php

namespace App\Jobs;

use App\Models\Request as RequestModel;
use App\Services\BrowserLetterPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WarmApprovedLetterPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 180;

    public function __construct(public int $requestId)
    {
        $this->onQueue('documents');
    }

    public function handle(BrowserLetterPdfService $browserLetterPdfService): void
    {
        $request = RequestModel::find($this->requestId);
        if (!$request || $request->status !== 'Approved') {
            return;
        }

        try {
            $browserLetterPdfService->renderRequestPdf($request);
        } catch (\Throwable $e) {
            Log::warning('Approved letter PDF warmup failed: ' . $e->getMessage(), [
                'request_id' => $request->id,
                'tracking_id' => $request->tracking_id,
            ]);

            throw $e;
        }
    }
}
