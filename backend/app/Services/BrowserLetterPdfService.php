<?php

namespace App\Services;

use App\Models\Settings;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use App\Models\Request as RequestModel;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class BrowserLetterPdfService
{
    private const DRIVER_LOCAL_BROWSER = 'local_browser';
    private const DRIVER_BROWSERLESS = 'browserless';
    private const DEFAULT_BROWSERLESS_BASE_URL = 'https://production-sfo.browserless.io';

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
        $this->extendExecutionTime();

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

    public function configurationSummary(): array
    {
        $driver = $this->preferredDriver();
        $browserlessBaseUrl = $this->browserlessBaseUrl();
        $browserlessToken = trim((string) Settings::getValue('browserlessToken', env('BROWSERLESS_TOKEN', '')));
        $localBrowserAvailable = $this->localBrowserAvailable();
        $browserlessConfigured = $browserlessBaseUrl !== '' && $browserlessToken !== '';

        $statusMessage = match (true) {
            $driver === self::DRIVER_BROWSERLESS && $browserlessConfigured => 'Browserless is ready for shared hosting PDF export.',
            $driver === self::DRIVER_BROWSERLESS => 'Browserless is selected but the token or base URL is missing.',
            $driver === self::DRIVER_LOCAL_BROWSER && $localBrowserAvailable => 'Local Chrome or Chromium is available.',
            $driver === self::DRIVER_LOCAL_BROWSER && $browserlessConfigured => 'Local browser is selected, but Browserless can be used as fallback.',
            default => 'No PDF renderer is ready. Configure Browserless for shared hosting.',
        };

        return [
            'driver' => $driver,
            'browserless_configured' => $browserlessConfigured,
            'browserless_base_url' => $browserlessBaseUrl,
            'browserless_token_configured' => $browserlessToken !== '',
            'local_browser_available' => $localBrowserAvailable,
            'status_message' => $statusMessage,
        ];
    }

    public function testBrowserlessConnection(): array
    {
        $binary = $this->renderPdfViaBrowserless(
            '<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:Arial,sans-serif;padding:32px;}h1{margin:0 0 12px;}</style></head><body><h1>Browserless PDF Export Test</h1><p>This confirms Browserless can generate PDFs for this app.</p></body></html>',
            'browserless-test'
        );

        return [
            'base_url' => $this->browserlessBaseUrl(),
            'response_bytes' => strlen($binary),
        ];
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
        $preferredDriver = $this->preferredDriver();

        if ($preferredDriver === self::DRIVER_BROWSERLESS) {
            return $this->renderPdfViaBrowserless($html, $trackingId);
        }

        try {
            return $this->renderPdfViaLocalBrowser($html, $trackingId);
        } catch (\Throwable $e) {
            if (!$this->isMissingLocalBrowserError($e) || !$this->isBrowserlessConfigured()) {
                throw $e;
            }

            return $this->renderPdfViaBrowserless($html, $trackingId);
        }
    }

    /**
     * @throws RuntimeException
     */
    private function renderPdfViaLocalBrowser(string $html, string $trackingId): string
    {
        $this->extendExecutionTime();

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

    /**
     * @throws RuntimeException
     */
    private function renderPdfViaBrowserless(string $html, string $trackingId): string
    {
        $this->extendExecutionTime();

        $config = $this->browserlessConfig(true);

        $response = Http::timeout(120)
            ->accept('application/pdf')
            ->withHeaders([
                'Cache-Control' => 'no-cache',
                'Content-Type' => 'application/json',
            ])
            ->post($config['endpoint'], [
                'html' => $html,
                'options' => [
                    'printBackground' => true,
                    'format' => 'A4',
                    'preferCSSPageSize' => true,
                ],
            ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                'Browserless PDF export failed for ' . $trackingId . ': ' . $this->browserlessErrorMessage($response)
            );
        }

        $binary = $response->body();
        if (!is_string($binary) || $binary === '') {
            throw new RuntimeException('Browserless did not return a PDF file for ' . $trackingId . '.');
        }

        if (!str_starts_with(ltrim($binary), '%PDF')) {
            throw new RuntimeException(
                'Browserless returned a response, but it was not a valid PDF for ' . $trackingId . '. Please test Browserless in admin settings and verify the endpoint/token.'
            );
        }

        return $binary;
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
        $commandCandidates = [];
        if (function_exists('shell_exec')) {
            $commandCandidates = [
                trim((string) @shell_exec('command -v google-chrome 2>/dev/null')),
                trim((string) @shell_exec('command -v chromium 2>/dev/null')),
                trim((string) @shell_exec('command -v chromium-browser 2>/dev/null')),
            ];
        }

        $candidates = array_filter([
            env('LETTER_EXPORT_BROWSER_BINARY'),
            '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            '/Applications/Brave Browser.app/Contents/MacOS/Brave Browser',
            '/usr/bin/google-chrome',
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            ...$commandCandidates,
        ]);

        foreach ($candidates as $candidate) {
            if ($candidate && is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException(
            'No supported Chrome or Chromium browser binary is available for PDF export. On shared hosting, set PDF Export Driver to Browserless in Admin Settings and save a Browserless token.'
        );
    }

    private function preferredDriver(): string
    {
        $savedDriver = Settings::getValue('pdfExportDriver');
        $envDriver = env('LETTER_EXPORT_DRIVER');
        $driver = trim((string) ($savedDriver ?: $envDriver ?: ''));

        if (in_array($driver, [self::DRIVER_LOCAL_BROWSER, self::DRIVER_BROWSERLESS], true)) {
            return $driver;
        }

        if ($this->isProductionEnvironment() && $this->isBrowserlessConfigured()) {
            return self::DRIVER_BROWSERLESS;
        }

        return self::DRIVER_LOCAL_BROWSER;
    }

    private function browserlessBaseUrl(): string
    {
        return rtrim(trim((string) Settings::getValue('browserlessBaseUrl', env('BROWSERLESS_BASE_URL', self::DEFAULT_BROWSERLESS_BASE_URL))), '/');
    }

    private function isBrowserlessConfigured(): bool
    {
        $baseUrl = $this->browserlessBaseUrl();
        $token = trim((string) Settings::getValue('browserlessToken', env('BROWSERLESS_TOKEN', '')));

        return $baseUrl !== '' && $token !== '';
    }

    private function isProductionEnvironment(): bool
    {
        return config('app.env') === 'production' || app()->environment('production');
    }

    /**
     * @return array{base_url:string,token:string,endpoint:string}
     */
    private function browserlessConfig(bool $require = false): array
    {
        $baseUrl = $this->browserlessBaseUrl();
        $token = trim((string) Settings::getValue('browserlessToken', env('BROWSERLESS_TOKEN', '')));

        if ($require && ($baseUrl === '' || $token === '')) {
            throw new RuntimeException('Browserless is not fully configured. In Admin Settings > PDF Export Renderer, choose Browserless, save the Browserless base URL and token, then click Test Browserless.');
        }

        return [
            'base_url' => $baseUrl,
            'token' => $token,
            'endpoint' => $baseUrl . '/pdf?token=' . urlencode($token),
        ];
    }

    private function browserlessErrorMessage(Response $response): string
    {
        $payload = $response->json();

        if (is_array($payload)) {
            $message = $payload['error'] ?? $payload['message'] ?? $payload['data'] ?? null;
            if (is_string($message) && trim($message) !== '') {
                return trim($message);
            }
        }

        $body = trim((string) $response->body());

        return $body !== '' ? $body : ('HTTP ' . $response->status());
    }

    private function isMissingLocalBrowserError(\Throwable $e): bool
    {
        return str_contains($e->getMessage(), 'No supported Chrome or Chromium browser binary is available for PDF export.');
    }

    private function localBrowserAvailable(): bool
    {
        try {
            $this->resolveBrowserBinary();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function extendExecutionTime(): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }
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
