<?php

namespace App\Services;

use App\Models\Request as RequestModel;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class BrowserLetterPdfService
{
    public function __construct(private LetterService $letterService)
    {
    }

    /**
     * Render the approved-letter HTML through a real browser print pipeline.
     *
     * @throws RuntimeException
     */
    public function renderRequestPdf(RequestModel $request): array
    {
        $html = $this->renderLetterHtml($request);
        $binary = $this->renderPdfFromHtml($html, $request->tracking_id);

        return [
            'binary' => $binary,
            'filename' => $this->pdfFilename($request),
            'tracking_id' => $request->tracking_id,
        ];
    }

    /**
     * Build a ZIP archive of exported approved letters.
     *
     * @param iterable<RequestModel> $requests
     *
     * @throws RuntimeException
     */
    public function buildZipArchive(iterable $requests): array
    {
        $exported = 0;
        $failed = [];
        $zipPath = $this->makeTempPath('admin-letter-export-', '.zip');
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create the ZIP archive for exported letters.');
        }

        try {
            foreach ($requests as $request) {
                try {
                    $pdf = $this->renderRequestPdf($request);
                    $zip->addFromString($pdf['filename'], $pdf['binary']);
                    $exported++;
                } catch (\Throwable $e) {
                    $failed[] = [
                        'request_id' => $request->id,
                        'tracking_id' => $request->tracking_id,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        } finally {
            $zip->close();
        }

        if ($exported === 0) {
            @unlink($zipPath);

            $firstError = $failed[0]['message'] ?? 'No approved letters could be exported.';
            throw new RuntimeException($firstError);
        }

        return [
            'path' => $zipPath,
            'filename' => 'Recommendation_Letters_' . now()->format('Y-m-d_His') . '.zip',
            'exported_count' => $exported,
            'failed' => $failed,
        ];
    }

    public function pdfFilename(RequestModel $request): string
    {
        return 'Recommendation_Letter_' . $request->tracking_id . '.pdf';
    }

    /**
     * @throws RuntimeException
     */
    private function renderLetterHtml(RequestModel $request): string
    {
        $content = $this->letterService->generateLetterContent($request);

        if ($content === [] || empty($content['template'])) {
            throw new RuntimeException('No active template found for this request.');
        }

        return view('public.letter', [
            'request' => $request,
            'layout' => $content['layout'],
            'header' => $this->letterService->sanitizeHtml($content['header'] ?? ''),
            'body' => $this->letterService->sanitizeHtml($content['body'] ?? ''),
            'footer' => $this->letterService->sanitizeHtml($content['footer'] ?? ''),
            'signature' => $content['signature'] ?? [],
            'qrCode' => $content['qrCode'] ?? '',
        ])->render();
    }

    /**
     * @throws RuntimeException
     */
    private function renderPdfFromHtml(string $html, string $trackingId): string
    {
        $browserBinary = $this->resolveBrowserBinary();
        $htmlPath = $this->makeTempPath('letter-export-', '.html');
        $pdfPath = $this->makeTempPath('letter-export-', '.pdf');

        file_put_contents($htmlPath, $html);

        $command = [
            $browserBinary,
            '--headless=new',
            '--disable-gpu',
            '--disable-dev-shm-usage',
            '--no-first-run',
            '--no-default-browser-check',
            '--allow-file-access-from-files',
            '--run-all-compositor-stages-before-draw',
            '--virtual-time-budget=6000',
            '--print-to-pdf-no-header',
            '--print-to-pdf=' . $pdfPath,
            'file://' . $htmlPath,
        ];

        if ($this->shouldDisableSandbox()) {
            $command[] = '--no-sandbox';
        }

        $process = new Process($command, base_path(), null, null, null);
        $process->start();

        $waitResult = $this->waitForPdfOutput($process, $pdfPath, 45);

        try {
            if (!$waitResult['ready']) {
                if ($process->isRunning()) {
                    $process->stop(3);
                }

                throw new RuntimeException(
                    'Browser PDF export failed for ' . $trackingId . ': ' . trim($process->getErrorOutput() . ' ' . $process->getOutput())
                );
            }

            if ($process->isRunning()) {
                $process->stop(3);
            }

            if (!$process->isSuccessful() && (!is_file($pdfPath) || filesize($pdfPath) === 0)) {
                throw new RuntimeException(
                    'Browser PDF export failed for ' . $trackingId . ': ' . trim($process->getErrorOutput() . ' ' . $process->getOutput())
                );
            }

            if (!is_file($pdfPath) || filesize($pdfPath) === 0) {
                throw new RuntimeException('The browser did not produce a PDF file for ' . $trackingId . '.');
            }

            $binary = file_get_contents($pdfPath);
            if ($binary === false || $binary === '') {
                throw new RuntimeException('The exported PDF for ' . $trackingId . ' could not be read.');
            }

            return $binary;
        } finally {
            @unlink($htmlPath);
            @unlink($pdfPath);
        }
    }

    private function waitForPdfOutput(Process $process, string $pdfPath, int $timeoutSeconds): array
    {
        $deadline = microtime(true) + $timeoutSeconds;
        $lastKnownSize = -1;
        $stablePolls = 0;

        while (microtime(true) < $deadline) {
            clearstatcache(true, $pdfPath);

            if (is_file($pdfPath)) {
                $size = filesize($pdfPath);
                if ($size !== false && $size > 0) {
                    if ($size === $lastKnownSize) {
                        $stablePolls++;
                    } else {
                        $lastKnownSize = $size;
                        $stablePolls = 0;
                    }

                    if ($stablePolls >= 2) {
                        return ['ready' => true];
                    }
                }
            }

            if (!$process->isRunning()) {
                break;
            }

            usleep(200000);
        }

        clearstatcache(true, $pdfPath);

        return [
            'ready' => is_file($pdfPath) && (filesize($pdfPath) ?: 0) > 0,
        ];
    }

    /**
     * @throws RuntimeException
     */
    private function resolveBrowserBinary(): string
    {
        $candidates = array_filter([
            env('LETTER_EXPORT_BROWSER_BINARY'),
            '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            '/Applications/Brave Browser.app/Contents/MacOS/Brave Browser',
            '/usr/bin/google-chrome',
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            trim((string) @shell_exec('command -v google-chrome 2>/dev/null')),
            trim((string) @shell_exec('command -v chromium 2>/dev/null')),
            trim((string) @shell_exec('command -v chromium-browser 2>/dev/null')),
        ]);

        foreach ($candidates as $candidate) {
            if ($candidate && is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException('No supported Chrome or Chromium browser binary is available for PDF export.');
    }

    private function shouldDisableSandbox(): bool
    {
        return PHP_OS_FAMILY === 'Linux' && function_exists('posix_geteuid') && posix_geteuid() === 0;
    }

    private function makeTempPath(string $prefix, string $extension): string
    {
        $base = tempnam(sys_get_temp_dir(), $prefix);
        if ($base === false) {
            throw new RuntimeException('Unable to create a temporary file for PDF export.');
        }

        @unlink($base);

        return $base . $extension;
    }
}
