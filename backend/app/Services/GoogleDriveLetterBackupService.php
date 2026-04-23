<?php

namespace App\Services;

use App\Models\Request as RequestModel;
use App\Models\Settings;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleDriveLetterBackupService
{
    private const DRIVE_SCOPE = 'https://www.googleapis.com/auth/drive';
    private const DEFAULT_TOKEN_URI = 'https://oauth2.googleapis.com/token';

    public function __construct(private BrowserLetterPdfService $browserLetterPdfService)
    {
    }

    public function configurationSummary(): array
    {
        $enabled = filter_var((string) Settings::getValue('googleDriveEnabled', 'false'), FILTER_VALIDATE_BOOLEAN);
        $serviceAccountJson = trim((string) Settings::getValue('googleDriveServiceAccountJson', ''));
        $folderId = $this->normalizeFolderReference(Settings::getValue('googleDriveFolderId'));

        $serviceAccountEmail = null;
        if ($serviceAccountJson !== '') {
            try {
                $serviceAccountEmail = $this->parseServiceAccountJsonString($serviceAccountJson)['client_email'];
            } catch (\Throwable $e) {
                $serviceAccountEmail = null;
            }
        }

        return [
            'enabled' => $enabled,
            'configured' => $enabled && $serviceAccountEmail !== null && $folderId !== null,
            'service_account_email' => $serviceAccountEmail,
            'folder_id' => $folderId,
            'folder_url' => $folderId ? $this->folderUrl($folderId) : null,
        ];
    }

    public function parseServiceAccountJsonString(string $rawJson): array
    {
        $decoded = json_decode(trim($rawJson), true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Google Drive service account JSON is invalid.');
        }

        $clientEmail = trim((string) ($decoded['client_email'] ?? ''));
        $privateKey = trim((string) ($decoded['private_key'] ?? ''));

        if ($clientEmail === '' || $privateKey === '') {
            throw new RuntimeException('Google Drive service account JSON must include client_email and private_key.');
        }

        return [
            'client_email' => $clientEmail,
            'private_key' => str_replace(["\r\n", '\n'], "\n", $privateKey),
            'token_uri' => trim((string) ($decoded['token_uri'] ?? self::DEFAULT_TOKEN_URI)) ?: self::DEFAULT_TOKEN_URI,
        ];
    }

    public function normalizeFolderReference(null|string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('~/folders/([a-zA-Z0-9_-]+)~', $value, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('~[?&]id=([a-zA-Z0-9_-]+)~', $value, $matches) === 1) {
            return $matches[1];
        }

        return $value;
    }

    public function testConnection(): array
    {
        $config = $this->loadConfig();
        $accessToken = $this->fetchAccessToken($config);

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get('https://www.googleapis.com/drive/v3/files/' . $config['folder_id'], [
                'supportsAllDrives' => 'true',
                'fields' => 'id,name,mimeType,webViewLink',
            ]);

        if (!$response->successful()) {
            throw new RuntimeException($this->extractDriveError($response));
        }

        $folder = $response->json();
        if (($folder['mimeType'] ?? '') !== 'application/vnd.google-apps.folder') {
            throw new RuntimeException('The configured Google Drive destination is not a folder.');
        }

        return [
            'service_account_email' => $config['client_email'],
            'folder_id' => $folder['id'] ?? $config['folder_id'],
            'folder_name' => $folder['name'] ?? 'Google Drive folder',
            'folder_url' => $folder['webViewLink'] ?? $this->folderUrl($config['folder_id']),
        ];
    }

    public function syncRequest(RequestModel $request): array
    {
        if ($request->status !== 'Approved') {
            throw new RuntimeException('Only approved requests can be backed up to Google Drive.');
        }

        $config = $this->loadConfig();
        $accessToken = $this->fetchAccessToken($config);

        return $this->syncApprovedRequest($request, $config, $accessToken);
    }

    /**
     * @param iterable<RequestModel> $requests
     */
    public function syncMany(iterable $requests): array
    {
        $config = $this->loadConfig();
        $accessToken = $this->fetchAccessToken($config);
        $synced = [];
        $failed = [];

        foreach ($requests as $request) {
            try {
                $result = $this->syncApprovedRequest($request, $config, $accessToken);
                $synced[] = $result;
            } catch (\Throwable $e) {
                $failed[] = [
                    'request_id' => $request->id,
                    'tracking_id' => $request->tracking_id,
                    'message' => $e->getMessage(),
                ];
            }
        }

        if ($synced === []) {
            $firstError = $failed[0]['message'] ?? 'No approved letters could be synced to Google Drive.';
            throw new RuntimeException($firstError);
        }

        return [
            'synced_count' => count($synced),
            'failed' => $failed,
            'synced' => $synced,
            'folder_url' => $this->folderUrl($config['folder_id']),
            'folder_id' => $config['folder_id'],
        ];
    }

    private function syncApprovedRequest(RequestModel $request, array $config, string $accessToken): array
    {
        try {
            $pdf = $this->browserLetterPdfService->renderRequestPdf($request);
            $file = $this->uploadRequestPdf($request, $pdf, $config, $accessToken);

            $request->forceFill([
                'drive_backup_status' => 'synced',
                'drive_backup_file_id' => $file['id'] ?? null,
                'drive_backup_file_name' => $file['name'] ?? $pdf['filename'],
                'drive_backup_url' => $file['webViewLink'] ?? $this->fileUrl($file['id'] ?? null),
                'drive_backup_error' => null,
                'drive_backup_synced_at' => now(),
            ])->save();

            return [
                'request_id' => $request->id,
                'tracking_id' => $request->tracking_id,
                'file_id' => $request->drive_backup_file_id,
                'file_name' => $request->drive_backup_file_name,
                'file_url' => $request->drive_backup_url,
                'folder_url' => $this->folderUrl($config['folder_id']),
            ];
        } catch (\Throwable $e) {
            $request->forceFill([
                'drive_backup_status' => 'failed',
                'drive_backup_error' => $e->getMessage(),
            ])->save();

            throw $e;
        }
    }

    private function loadConfig(): array
    {
        $enabled = filter_var((string) Settings::getValue('googleDriveEnabled', 'false'), FILTER_VALIDATE_BOOLEAN);
        if (!$enabled) {
            throw new RuntimeException('Google Drive backup is disabled in settings.');
        }

        $serviceAccountJson = trim((string) Settings::getValue('googleDriveServiceAccountJson', ''));
        if ($serviceAccountJson === '') {
            throw new RuntimeException('Google Drive service account JSON is not configured.');
        }

        $folderId = $this->normalizeFolderReference(Settings::getValue('googleDriveFolderId'));
        if ($folderId === null) {
            throw new RuntimeException('Google Drive folder ID is not configured.');
        }

        return array_merge($this->parseServiceAccountJsonString($serviceAccountJson), [
            'folder_id' => $folderId,
        ]);
    }

    private function fetchAccessToken(array $config): string
    {
        $jwt = $this->createJwtAssertion($config);

        $response = Http::asForm()->post($config['token_uri'], [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('Google Drive authentication failed: ' . $this->extractDriveError($response));
        }

        $accessToken = $response->json('access_token');
        if (!is_string($accessToken) || trim($accessToken) === '') {
            throw new RuntimeException('Google Drive authentication did not return an access token.');
        }

        return $accessToken;
    }

    private function createJwtAssertion(array $config): string
    {
        $now = time();

        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], JSON_UNESCAPED_SLASHES));

        $claims = $this->base64UrlEncode(json_encode([
            'iss' => $config['client_email'],
            'scope' => self::DRIVE_SCOPE,
            'aud' => $config['token_uri'],
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_UNESCAPED_SLASHES));

        $payload = $header . '.' . $claims;
        $signature = '';

        $privateKey = openssl_pkey_get_private($config['private_key']);
        if ($privateKey === false) {
            throw new RuntimeException('The Google Drive private key could not be loaded.');
        }

        try {
            $signed = openssl_sign($payload, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        } finally {
            openssl_free_key($privateKey);
        }

        if ($signed !== true) {
            throw new RuntimeException('The Google Drive JWT assertion could not be signed.');
        }

        return $payload . '.' . $this->base64UrlEncode($signature);
    }

    private function uploadRequestPdf(RequestModel $request, array $pdf, array $config, string $accessToken): array
    {
        $metadata = [
            'name' => $pdf['filename'],
            'description' => 'Recommendation letter backup for ' . $request->tracking_id,
        ];

        $existingFileId = trim((string) ($request->drive_backup_file_id ?? ''));
        if ($existingFileId === '') {
            $metadata['parents'] = [$config['folder_id']];
            return $this->sendMultipartUpload('POST', 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&supportsAllDrives=true&fields=id,name,webViewLink,webContentLink', $metadata, $pdf['binary'], $accessToken);
        }

        try {
            return $this->sendMultipartUpload('PATCH', 'https://www.googleapis.com/upload/drive/v3/files/' . $existingFileId . '?uploadType=multipart&supportsAllDrives=true&fields=id,name,webViewLink,webContentLink', $metadata, $pdf['binary'], $accessToken);
        } catch (\Throwable $e) {
            if (!str_contains(strtolower($e->getMessage()), 'not found')) {
                throw $e;
            }

            $metadata['parents'] = [$config['folder_id']];
            return $this->sendMultipartUpload('POST', 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&supportsAllDrives=true&fields=id,name,webViewLink,webContentLink', $metadata, $pdf['binary'], $accessToken);
        }
    }

    private function sendMultipartUpload(string $method, string $url, array $metadata, string $binary, string $accessToken): array
    {
        $boundary = 'drive-boundary-' . bin2hex(random_bytes(8));
        $metadataJson = json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($metadataJson === false) {
            throw new RuntimeException('Google Drive metadata could not be encoded.');
        }

        $body =
            "--{$boundary}\r\n" .
            "Content-Type: application/json; charset=UTF-8\r\n\r\n" .
            $metadataJson . "\r\n" .
            "--{$boundary}\r\n" .
            "Content-Type: application/pdf\r\n\r\n" .
            $binary . "\r\n" .
            "--{$boundary}--";

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'multipart/related; boundary=' . $boundary,
            ])
            ->withBody($body, 'multipart/related; boundary=' . $boundary)
            ->send($method, $url);

        if (!$response->successful()) {
            throw new RuntimeException($this->extractDriveError($response));
        }

        $payload = $response->json();
        if (!is_array($payload) || empty($payload['id'])) {
            throw new RuntimeException('Google Drive did not return file metadata after upload.');
        }

        if (empty($payload['webViewLink'])) {
            $payload['webViewLink'] = $this->fileUrl($payload['id']);
        }

        return $payload;
    }

    private function extractDriveError($response): string
    {
        $payload = $response->json();
        if (is_array($payload)) {
            $message = $payload['error']['message'] ?? $payload['message'] ?? null;
            if (is_string($message) && trim($message) !== '') {
                return $message;
            }
        }

        $body = trim((string) $response->body());
        return $body !== '' ? $body : 'Unexpected Google Drive error.';
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function folderUrl(?string $folderId): ?string
    {
        return $folderId ? 'https://drive.google.com/drive/folders/' . $folderId : null;
    }

    private function fileUrl(?string $fileId): ?string
    {
        return $fileId ? 'https://drive.google.com/file/d/' . $fileId . '/view' : null;
    }
}
