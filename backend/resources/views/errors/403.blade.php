@php
    $rawMessage = isset($exception) ? trim((string) $exception->getMessage()) : '';
    $isVerificationError = str_contains($rawMessage, 'verify your request') || str_contains($rawMessage, 'OTP');
    $trackingId = request()->route('tracking_id') ?: request()->route('id');
    $trackingId = is_string($trackingId) ? trim($trackingId) : '';
    $trackingUrl = route('public.tracking', $trackingId !== '' ? ['id' => $trackingId] : []);
    $homeUrl = route('home');

    $title = $isVerificationError ? 'Verification required' : 'Access restricted';
    $body = $isVerificationError
        ? 'For your privacy, approved letters stay protected until the request is verified with the one-time code. Open tracking, complete verification, then download the PDF again.'
        : ($rawMessage !== '' ? $rawMessage : 'You do not have permission to view this page.');
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        :root {
            --ink: #111827;
            --muted: #5f6b7a;
            --line: #d9e1ea;
            --soft: #f6f8fb;
            --surface: #ffffff;
            --accent: #2563eb;
            --accent-strong: #1d4ed8;
            --success: #0f766e;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                linear-gradient(135deg, rgba(37, 99, 235, 0.10), rgba(15, 118, 110, 0.08)),
                var(--soft);
        }

        .page {
            display: grid;
            min-height: 100vh;
            place-items: center;
            padding: 32px 18px;
        }

        .notice {
            width: min(720px, 100%);
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: var(--surface);
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.14);
        }

        .notice-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 22px;
            border-bottom: 1px solid var(--line);
            background: linear-gradient(90deg, rgba(37, 99, 235, 0.10), rgba(15, 118, 110, 0.08));
        }

        .status {
            display: inline-flex;
            align-items: center;
            min-height: 34px;
            padding: 0 12px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.12);
            color: var(--accent-strong);
            font-size: 0.88rem;
            font-weight: 800;
            letter-spacing: 0;
        }

        .code {
            color: var(--muted);
            font-size: 0.95rem;
            font-weight: 800;
        }

        .notice-body {
            padding: 34px 34px 30px;
        }

        h1 {
            margin: 0;
            font-size: clamp(2rem, 6vw, 3.1rem);
            line-height: 1;
            letter-spacing: 0;
        }

        .message {
            max-width: 620px;
            margin: 18px 0 0;
            color: var(--muted);
            font-size: 1.05rem;
            line-height: 1.7;
        }

        .request-id {
            margin: 22px 0 0;
            padding: 13px 15px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fbfdff;
            color: #334155;
            font-size: 0.95rem;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 18px;
            border: 1px solid transparent;
            border-radius: 999px;
            color: inherit;
            font-size: 0.95rem;
            font-weight: 800;
            text-decoration: none;
        }

        .button-primary {
            color: #ffffff;
            background: linear-gradient(135deg, var(--accent), var(--success));
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.22);
        }

        .button-secondary {
            border-color: var(--line);
            background: #ffffff;
            color: #334155;
        }

        @media (max-width: 560px) {
            .notice-top {
                align-items: flex-start;
                flex-direction: column;
            }

            .notice-body {
                padding: 28px 20px 24px;
            }

            .actions {
                flex-direction: column;
            }

            .button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <main class="page">
        <section class="notice" aria-labelledby="error-title">
            <div class="notice-top">
                <span class="status">{{ $isVerificationError ? 'Security check' : 'Access control' }}</span>
                <span class="code">403</span>
            </div>

            <div class="notice-body">
                <h1 id="error-title">{{ $title }}</h1>
                <p class="message">{{ $body }}</p>

                @if($trackingId !== '')
                    <div class="request-id">Tracking ID: {{ $trackingId }}</div>
                @endif

                <div class="actions">
                    <a class="button button-primary" href="{{ $trackingUrl }}">
                        {{ $isVerificationError ? 'Track and verify request' : 'Back to tracking' }}
                    </a>
                    <a class="button button-secondary" href="{{ $homeUrl }}">Go home</a>
                </div>
            </div>
        </section>
    </main>
</body>

</html>
