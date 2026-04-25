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

        .pdf-frame {
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

            .pdf-frame {
                height: 72vh;
                min-height: 520px;
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
                    This is the official PDF version of your recommendation letter. Review it first, then download the PDF when everything looks correct.
                </p>
            </div>
            <div class="viewer-actions">
                <a href="{{ $trackingUrl }}" class="btn btn-secondary">Back to Tracking</a>
                <a href="{{ $downloadUrl }}" class="btn btn-primary">Download PDF</a>
            </div>
        </section>

        <section class="viewer-card" aria-label="Official recommendation letter PDF viewer">
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

            <iframe class="pdf-frame" src="{{ $pdfUrl }}#toolbar=1&navpanes=0" title="Official recommendation letter PDF"></iframe>

            <p class="viewer-fallback">
                If the PDF viewer does not open on your device, use
                <a href="{{ $downloadUrl }}">Download PDF</a>.
            </p>
        </section>
    </main>
</body>

</html>
