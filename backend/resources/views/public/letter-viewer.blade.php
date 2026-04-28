@php
    $settings = $settings ?? [];
    $primaryColor = $settings['primaryColor'] ?? '#2563eb';
    $secondaryColor = $settings['secondaryColor'] ?? '#1e40af';
    $fontFamily = $settings['fontFamily'] ?? 'Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
    $headingFont = $settings['headingFont'] ?? $fontFamily;
    $siteName = $settings['siteName'] ?? 'AAMD';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Official Letter - {{ $siteName }}</title>
    <style>
        :root {
            --primary: {{ $primaryColor }};
            --secondary: {{ $secondaryColor }};
            --ink: #111827;
            --muted: #64748b;
            --line: rgba(148, 163, 184, 0.28);
            --surface: rgba(255, 255, 255, 0.92);
            --shadow: 0 24px 70px rgba(15, 23, 42, 0.16);
            --font-family: {!! json_encode($fontFamily) !!};
            --heading-font: {!! json_encode($headingFont) !!};
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: var(--font-family);
            color: var(--ink);
            background:
                radial-gradient(circle at top left, color-mix(in srgb, var(--primary) 18%, transparent), transparent 34rem),
                linear-gradient(135deg, #f8fafc 0%, #e9eef5 100%);
        }

        .viewer-shell {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 28px 0 40px;
        }

        .viewer-header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 18px;
            align-items: center;
            margin-bottom: 18px;
            padding: 20px 22px;
            border: 1px solid var(--line);
            border-radius: 24px;
            background: var(--surface);
            box-shadow: 0 14px 42px rgba(15, 23, 42, 0.10);
            backdrop-filter: blur(16px);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            color: var(--primary);
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-family: var(--heading-font);
            font-size: clamp(1.65rem, 4vw, 2.35rem);
            letter-spacing: -0.04em;
            line-height: 1.05;
        }

        .viewer-copy {
            margin: 10px 0 0;
            max-width: 720px;
            color: var(--muted);
            font-size: 0.98rem;
            line-height: 1.65;
        }

        .viewer-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 46px;
            padding: 0 18px;
            border: 1px solid transparent;
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.9rem;
            text-decoration: none;
            transition: transform 160ms ease, box-shadow 160ms ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn[aria-disabled="true"] {
            cursor: wait;
            opacity: 0.84;
            pointer-events: none;
        }

        .btn-spinner {
            display: none;
            width: 15px;
            height: 15px;
            border: 2px solid rgba(255, 255, 255, 0.42);
            border-top-color: #fff;
            border-radius: 999px;
            animation: spin 800ms linear infinite;
        }

        .btn.is-loading .btn-spinner {
            display: inline-block;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            box-shadow: 0 14px 30px color-mix(in srgb, var(--primary) 26%, transparent);
        }

        .btn-secondary {
            color: #334155;
            background: #fff;
            border-color: var(--line);
        }

        .pdf-status {
            grid-column: 1 / -1;
            padding: 11px 14px;
            border: 1px solid color-mix(in srgb, var(--primary) 20%, transparent);
            border-radius: 14px;
            background: color-mix(in srgb, var(--primary) 9%, #fff);
            color: #334155;
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1.45;
        }

        .pdf-status[hidden] {
            display: none;
        }

        .pdf-status[data-tone="success"] {
            border-color: rgba(22, 163, 74, 0.28);
            background: #f0fdf4;
            color: #166534;
        }

        .pdf-status[data-tone="error"] {
            border-color: rgba(220, 38, 38, 0.28);
            background: #fef2f2;
            color: #991b1b;
        }

        .viewer-card {
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 28px;
            background: #fff;
            box-shadow: var(--shadow);
        }

        .viewer-meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1px;
            border-bottom: 1px solid var(--line);
            background: var(--line);
        }

        .meta-item {
            padding: 16px 18px;
            background: #fff;
        }

        .meta-label {
            display: block;
            margin-bottom: 4px;
            color: var(--muted);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.10em;
            text-transform: uppercase;
        }

        .meta-value {
            margin: 0;
            font-weight: 800;
        }

        .preview-frame {
            display: block;
            width: 100%;
            height: min(78vh, 980px);
            min-height: 680px;
            border: 0;
            background: #f1f5f9;
        }

        .viewer-fallback {
            padding: 16px 20px;
            color: var(--muted);
            font-size: 0.9rem;
            border-top: 1px solid var(--line);
            background: #f8fafc;
        }

        .viewer-fallback a {
            color: var(--primary);
            font-weight: 800;
        }

        @media (max-width: 760px) {
            .viewer-shell {
                width: min(100% - 18px, 1180px);
                padding-top: 10px;
            }

            .viewer-header {
                grid-template-columns: 1fr;
                border-radius: 20px;
            }

            .viewer-actions {
                justify-content: stretch;
            }

            .btn {
                width: 100%;
            }

            .viewer-meta {
                grid-template-columns: 1fr;
            }

            .preview-frame {
                height: min(74vh, 640px);
                min-height: 460px;
            }
        }
    </style>
</head>

<body>
    <main class="viewer-shell">
        <section class="viewer-header" aria-labelledby="letter-title">
            <div>
                <span class="eyebrow">Official letter review</span>
                <h1 id="letter-title">Review your letter before download</h1>
                <p class="viewer-copy">
                    This is the official recommendation letter preview. Review it first, then download the PDF when everything looks correct.
                </p>
            </div>
            <div class="viewer-actions">
                <a href="{{ $trackingUrl }}" class="btn btn-secondary">Back to Tracking</a>
                <a href="{{ $downloadUrl }}"
                    class="btn btn-primary"
                    id="downloadPdfBtn"
                    data-download-url="{{ $downloadUrl }}"
                    data-prepare-url="{{ $prepareUrl }}"
                    data-tracking-id="{{ $request->tracking_id }}"
                    data-filename="Recommendation_Letter_{{ $request->tracking_id }}.pdf">
                    <span class="btn-spinner" aria-hidden="true"></span>
                    <span class="btn-label">Download PDF</span>
                </a>
            </div>
            <div id="pdfStatus" class="pdf-status" role="status" aria-live="polite" hidden></div>
        </section>

        <section class="viewer-card" aria-label="Official recommendation letter preview">
            <div class="viewer-meta">
                <div class="meta-item">
                    <span class="meta-label">Student</span>
                    <p class="meta-value">{{ $request->student_name }}</p>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Tracking ID</span>
                    <p class="meta-value">{{ $request->tracking_id }}</p>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Status</span>
                    <p class="meta-value">{{ $request->status }}</p>
                </div>
            </div>

            <iframe class="preview-frame" src="{{ $previewUrl }}" title="Official recommendation letter preview"></iframe>

            <p class="viewer-fallback">
                If the preview does not open on your device, use
                <a href="{{ $downloadUrl }}">Download PDF</a>.
            </p>
        </section>
    </main>

    <script>
        (function() {
            const downloadButton = document.getElementById('downloadPdfBtn');
            const previewFrame = document.querySelector('.preview-frame');
            const statusBox = document.getElementById('pdfStatus');

            if (!downloadButton || !statusBox) {
                return;
            }

            const downloadUrl = downloadButton.dataset.downloadUrl;
            const prepareUrl = downloadButton.dataset.prepareUrl;
            const trackingId = downloadButton.dataset.trackingId || window.location.pathname;
            const fallbackFilename = downloadButton.dataset.filename || 'Recommendation_Letter.pdf';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const prepareStorageKey = `rl:pdf-prepare:${trackingId}`;
            const prepareCooldownMs = 90 * 1000;
            let preparePromise = null;
            let statusTimer = null;

            function setStatus(message, tone) {
                if (statusTimer) {
                    window.clearTimeout(statusTimer);
                    statusTimer = null;
                }

                statusBox.textContent = message;
                statusBox.dataset.tone = tone || 'info';
                statusBox.hidden = false;
            }

            function hideStatusAfter(delay) {
                if (statusTimer) {
                    window.clearTimeout(statusTimer);
                }

                statusTimer = window.setTimeout(function() {
                    statusBox.hidden = true;
                }, delay);
            }

            function setLoading(isLoading) {
                downloadButton.classList.toggle('is-loading', isLoading);
                downloadButton.setAttribute('aria-disabled', isLoading ? 'true' : 'false');
                downloadButton.querySelector('.btn-label').textContent = isLoading ? 'Preparing PDF' : 'Download PDF';
            }

            function filenameFromResponse(response) {
                const disposition = response.headers.get('Content-Disposition') || '';
                const utfMatch = disposition.match(/filename\*=UTF-8''([^;]+)/i);
                if (utfMatch) {
                    return decodeURIComponent(utfMatch[1].replace(/"/g, ''));
                }

                const match = disposition.match(/filename="?([^"]+)"?/i);
                return match ? match[1] : fallbackFilename;
            }

            function startBlobDownload(blob, filename) {
                const objectUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = objectUrl;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.setTimeout(function() {
                    URL.revokeObjectURL(objectUrl);
                }, 1000);
            }

            function hasRecentPrepareAttempt() {
                try {
                    const lastAttempt = Number(window.sessionStorage.getItem(prepareStorageKey) || 0);
                    return lastAttempt > 0 && Date.now() - lastAttempt < prepareCooldownMs;
                } catch (error) {
                    return false;
                }
            }

            function rememberPrepareAttempt() {
                try {
                    window.sessionStorage.setItem(prepareStorageKey, String(Date.now()));
                } catch (error) {
                    // Session storage is optional; PDF preparation still works without it.
                }
            }

            async function preparePdfInBackground() {
                if (preparePromise || !prepareUrl) {
                    return preparePromise;
                }

                if (hasRecentPrepareAttempt()) {
                    return null;
                }

                rememberPrepareAttempt();
                setStatus('Preparing the official PDF version in the background so your download starts faster.', 'info');

                preparePromise = fetch(prepareUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                }).then(async function(response) {
                    const payload = await response.json().catch(function() {
                        return {};
                    });

                    if (!response.ok) {
                        const error = new Error(payload.message || 'The PDF could not be prepared yet.');
                        error.status = response.status;
                        throw error;
                    }

                    setStatus(payload.message || 'The official PDF is ready to download.', 'success');
                    hideStatusAfter(6000);

                    return payload;
                }).catch(function(error) {
                    if (error.status === 409) {
                        setStatus(error.message, 'error');
                        return null;
                    }

                    if (error.status === 429) {
                        setStatus('The official PDF will be prepared when you click Download PDF.', 'info');
                        hideStatusAfter(5000);
                        return null;
                    }

                    setStatus('The PDF will be generated when you click Download PDF. It may take a few seconds the first time.', 'info');
                    hideStatusAfter(7000);

                    return null;
                });

                return preparePromise;
            }

            function scheduleBackgroundPreparation() {
                const start = function() {
                    preparePdfInBackground();
                };

                if ('requestIdleCallback' in window) {
                    window.requestIdleCallback(start, { timeout: 2000 });
                } else {
                    window.setTimeout(start, 900);
                }
            }

            previewFrame?.addEventListener('load', scheduleBackgroundPreparation, { once: true });
            window.addEventListener('load', function() {
                window.setTimeout(function() {
                    if (!preparePromise) {
                        scheduleBackgroundPreparation();
                    }
                }, 1200);
            });

            downloadButton.addEventListener('click', async function(event) {
                event.preventDefault();

                setLoading(true);
                setStatus('Preparing your official PDF version. This can take a few seconds the first time.', 'info');

                try {
                    const response = await fetch(downloadUrl, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/pdf',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        const message = await response.text();
                        throw new Error(message || 'The official PDF could not be downloaded right now.');
                    }

                    const blob = await response.blob();
                    startBlobDownload(blob, filenameFromResponse(response));
                    setStatus('PDF is ready. Your download has started.', 'success');
                    hideStatusAfter(6000);
                } catch (error) {
                    setStatus(error.message || 'The official PDF could not be downloaded right now. Please try again.', 'error');
                } finally {
                    setLoading(false);
                }
            });
        })();
    </script>
</body>

</html>
