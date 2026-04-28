@php
    $code = (int) ($code ?? (isset($exception) && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500));
    $title = $title ?? 'Request interrupted';
    $message = $message ?? 'We could not complete this request. Please try again or return to a safe page.';
    $badge = $badge ?? 'Application notice';
    $contextLabel = $contextLabel ?? null;
    $contextValue = $contextValue ?? null;
    $primaryLabel = $primaryLabel ?? 'Go home';
    $primaryUrl = $primaryUrl ?? (\Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/'));
    $secondaryLabel = $secondaryLabel ?? 'Track request';
    $secondaryUrl = $secondaryUrl ?? (\Illuminate\Support\Facades\Route::has('public.tracking') ? route('public.tracking') : $primaryUrl);
    $showSecondary = $showSecondary ?? $secondaryUrl !== $primaryUrl;
    $supportText = $supportText ?? 'If the problem continues, contact the administration team with the error code shown here.';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - {{ $code }}</title>
    <style>
        :root {
            --ink: #111827;
            --muted: #5f6b7a;
            --line: #d9e1ea;
            --soft: #f6f8fb;
            --surface: #ffffff;
            --accent: #2563eb;
            --accent-strong: #1d4ed8;
            --steady: #0f766e;
            --warn: #b45309;
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
            width: min(760px, 100%);
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--surface);
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.14);
        }

        .notice-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 22px;
            border-bottom: 1px solid var(--line);
            background: linear-gradient(90deg, rgba(37, 99, 235, 0.10), rgba(15, 118, 110, 0.08));
        }

        .badge {
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
            padding: 34px;
        }

        h1 {
            margin: 0;
            font-size: 2.65rem;
            line-height: 1.05;
            letter-spacing: 0;
        }

        .message {
            max-width: 640px;
            margin: 18px 0 0;
            color: var(--muted);
            font-size: 1.05rem;
            line-height: 1.7;
        }

        .context {
            margin: 22px 0 0;
            padding: 13px 15px;
            border: 1px solid var(--line);
            border-radius: 8px;
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
            background: linear-gradient(135deg, var(--accent), var(--steady));
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.22);
        }

        .button-secondary {
            border-color: var(--line);
            background: #ffffff;
            color: #334155;
        }

        .support {
            margin: 26px 0 0;
            color: #7a8796;
            font-size: 0.92rem;
            line-height: 1.6;
        }

        @media (max-width: 560px) {
            .notice-top {
                align-items: flex-start;
                flex-direction: column;
            }

            .notice-body {
                padding: 28px 20px 24px;
            }

            h1 {
                font-size: 2rem;
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
                <span class="badge">{{ $badge }}</span>
                <span class="code">Error {{ $code }}</span>
            </div>

            <div class="notice-body">
                <h1 id="error-title">{{ $title }}</h1>
                <p class="message">{{ $message }}</p>

                @if($contextLabel && $contextValue)
                    <div class="context">{{ $contextLabel }}: {{ $contextValue }}</div>
                @endif

                <div class="actions">
                    <a class="button button-primary" href="{{ $primaryUrl }}">{{ $primaryLabel }}</a>
                    @if($showSecondary)
                        <a class="button button-secondary" href="{{ $secondaryUrl }}">{{ $secondaryLabel }}</a>
                    @endif
                </div>

                <p class="support">{{ $supportText }}</p>
            </div>
        </section>
    </main>
</body>

</html>
