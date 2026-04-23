@extends('layouts.app')

@section('title', 'Verify Access')

@section('styles')
    <style>
        .verify-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background:
                linear-gradient(180deg, rgba(var(--primary-rgb), 0.04), transparent 18%),
                var(--bg-primary);
        }

        .verify-shell {
            width: 100%;
            max-width: 440px;
        }

        .verify-card {
            width: 100%;
            background: var(--glass-bg);
            border-radius: calc(var(--border-radius) + 0.75rem);
            box-shadow: var(--shadow-lg);
            padding: 2rem;
            text-align: left;
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .verify-icon {
            width: 3.5rem;
            height: 3.5rem;
            background: rgba(var(--primary-rgb), 0.12);
            color: var(--primary);
            border-radius: calc(var(--border-radius) + 0.1rem);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }

        .verify-title {
            font-size: 1.85rem;
            font-weight: 800;
            font-family: var(--font-heading);
            color: var(--text-primary);
            margin: 0 0 0.55rem 0;
            letter-spacing: -0.025em;
        }

        .verify-text {
            color: var(--text-secondary);
            margin: 0;
            font-size: 1rem;
            line-height: 1.6;
        }

        .verify-email {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            margin-top: 0.95rem;
            padding: 0.55rem 0.8rem;
            border-radius: 999px;
            background: rgba(var(--primary-rgb), 0.08);
            color: var(--text-primary);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .verify-meta-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
            margin: 1.35rem 0 1.15rem;
        }

        .verify-meta-card {
            padding: 0.95rem 1rem;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            background: var(--bg-secondary);
        }

        .verify-meta-label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .verify-meta-value {
            margin: 0;
            color: var(--text-primary);
            font-weight: 700;
            font-size: 1rem;
            line-height: 1.45;
        }

        .verify-meta-value.mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 0.95rem;
            word-break: break-word;
        }

        .verify-resent {
            margin: 0 0 1rem 0;
            padding: 0.75rem 0.9rem;
            border-radius: var(--border-radius);
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.16);
            color: #047857;
            font-size: 0.92rem;
            font-weight: 700;
        }

        .error-box {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ef4444;
            padding: 0.95rem 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            font-size: 0.9375rem;
            display: flex;
            align-items: center;
            gap: 0.55rem;
        }

        .otp-form {
            margin-top: 1rem;
        }

        .otp-label {
            display: block;
            margin-bottom: 0.7rem;
            color: var(--text-primary);
            font-size: 0.92rem;
            font-weight: 700;
        }

        .otp-input {
            width: 100%;
            padding: 1rem 1.15rem;
            letter-spacing: 0.45em;
            font-size: 1.9rem;
            text-align: center;
            font-weight: 800;
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: calc(var(--border-radius) + 0.2rem);
            transition: all 0.3s ease;
            margin-bottom: 0.9rem;
        }

        .otp-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.12);
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: var(--btn-gradient);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 700;
            font-size: 1.06rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
            opacity: 0.95;
        }

        .otp-help {
            margin: 0 0 1.15rem 0;
            color: var(--text-muted);
            font-size: 0.88rem;
            line-height: 1.55;
        }

        .remember-browser {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 0.8rem;
            align-items: start;
            margin: 0 0 1rem 0;
            padding: 0.95rem 1rem;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            background: rgba(var(--primary-rgb), 0.04);
        }

        .remember-browser input {
            width: 1rem;
            height: 1rem;
            margin-top: 0.15rem;
            accent-color: var(--primary);
        }

        .remember-browser strong {
            display: block;
            color: var(--text-primary);
            font-size: 0.95rem;
        }

        .remember-browser span {
            display: block;
            margin-top: 0.2rem;
            color: var(--text-secondary);
            font-size: 0.88rem;
            line-height: 1.5;
        }

        .resend-panel {
            margin-top: 1.35rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border-color);
            text-align: center;
        }

        .resend-copy {
            margin: 0 0 0.95rem 0;
            color: var(--text-secondary);
            font-size: 0.92rem;
            line-height: 1.55;
        }

        .resend-copy strong {
            color: var(--text-primary);
        }

        .resend-btn {
            width: 100%;
            padding: 0.92rem 1rem;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
        }

        .resend-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .verify-theme-toggle {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 100;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1.4rem;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9375rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--primary);
        }

        @media (max-width: 520px) {
            .verify-card {
                padding: 1.5rem;
                border-radius: calc(var(--border-radius) + 0.35rem);
            }

            .verify-meta-grid {
                grid-template-columns: 1fr;
            }

            .verify-title {
                font-size: 1.6rem;
            }

            .otp-input {
                font-size: 1.65rem;
                letter-spacing: 0.32em;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $successMessage = session('success');
        $showResentNotice = is_string($successMessage) && str_contains($successMessage, 'new 6-digit verification code');
    @endphp
    <div class="verify-page">
        <!-- Theme Toggle -->
        <button class="theme-toggle verify-theme-toggle" onclick="toggleTheme()" title="Toggle Theme">
            <i data-lucide="moon" class="moon-icon"></i>
            <i data-lucide="sun" class="sun-icon"></i>
        </button>

        @php
            $initialCountdown = null;
            if (($hasActiveCode ?? false) && $expiresAt instanceof \Illuminate\Support\Carbon) {
                $remainingSeconds = max(0, now()->diffInSeconds($expiresAt, false));
                $initialCountdown = sprintf('%02d:%02d', intdiv($remainingSeconds, 60), $remainingSeconds % 60);
            }
        @endphp

        <div class="verify-shell">
            <div class="verify-card">
                <div class="verify-icon">
                    <i data-lucide="shield-check" style="width: 24px; height: 24px;"></i>
                </div>

                <h1 class="verify-title">Verify Access</h1>
                <p class="verify-text">
                    Enter the 6-digit code we sent to continue.
                </p>

                @if(!empty($deliveryHint))
                    <div class="verify-email">
                        <i data-lucide="mail" style="width: 16px; height: 16px;"></i>
                        <span>{{ $deliveryHint }}</span>
                    </div>
                @endif

                <div class="verify-meta-grid">
                    @if(!empty($trackingId))
                        <div class="verify-meta-card">
                            <span class="verify-meta-label">Tracking request</span>
                            <p class="verify-meta-value mono">{{ $trackingId }}</p>
                        </div>
                    @endif

                    <div class="verify-meta-card">
                        <span class="verify-meta-label">{{ ($hasActiveCode ?? false) ? 'Code expires in' : 'Code status' }}</span>
                        <p class="verify-meta-value" id="otpCountdown"
                            data-expires-at="{{ $expiresAt instanceof \Illuminate\Support\Carbon ? $expiresAt->timestamp * 1000 : '' }}">
                            {{ $initialCountdown ?? 'Expired' }}
                        </p>
                    </div>
                </div>

                @if($showResentNotice)
                    <div class="verify-resent">New code sent.</div>
                @endif

                @if(session('error'))
                    <div class="error-box">
                        <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('public.tracking.verify.post') }}" class="otp-form">
                    @csrf
                    <label for="otp" class="otp-label">Verification code</label>
                    <input type="text" id="otp" name="otp" class="otp-input" placeholder="000000" maxlength="6" required autofocus
                        pattern="\d*" inputmode="numeric" autocomplete="one-time-code">
                    <p class="otp-help" id="otpCountdownHelp">
                        @if($hasActiveCode ?? false)
                            Check spam if the email is delayed. When the timer ends, request a new code.
                        @else
                            This code is no longer active. Request a new one below to continue.
                        @endif
                    </p>

                    <label class="remember-browser" for="remember_browser">
                        <input type="checkbox" id="remember_browser" name="remember_browser" value="1"
                            {{ old('remember_browser') ? 'checked' : '' }}>
                        <div>
                            <strong>Remember this browser for 30 days</strong>
                            <span>Skip the verification code on this device next time. Leave this off if you prefer to receive a code every time.</span>
                        </div>
                    </label>

                    <button type="submit" class="btn-submit">
                        <span>Verify Code</span>
                        <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
                    </button>
                </form>

                <div class="resend-panel">
                    <p class="resend-copy">
                        Did not receive it? <strong>Check spam</strong> or request another code.
                    </p>

                    <form method="POST" action="{{ route('public.tracking.verify.resend') }}">
                        @csrf
                        <button type="submit" class="resend-btn">
                            <i data-lucide="rotate-cw" style="width: 18px; height: 18px;"></i>
                            <span>Send New Code</span>
                        </button>
                    </form>
                </div>

                <a href="{{ route('public.tracking') }}" class="back-link">
                    <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                    Back to Tracking
                </a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const countdown = document.getElementById('otpCountdown');
            const countdownHelp = document.getElementById('otpCountdownHelp');

            if (!countdown) {
                return;
            }

            const expiresAt = Number(countdown.dataset.expiresAt || 0);
            if (!expiresAt) {
                return;
            }

            const updateCountdown = () => {
                const remainingMs = expiresAt - Date.now();

                if (remainingMs <= 0) {
                    countdown.textContent = 'Expired';
                    if (countdownHelp) {
                        countdownHelp.textContent = 'This code is no longer active. Request a new one below to continue.';
                    }
                    return false;
                }

                const remainingSeconds = Math.floor(remainingMs / 1000);
                const minutes = String(Math.floor(remainingSeconds / 60)).padStart(2, '0');
                const seconds = String(remainingSeconds % 60).padStart(2, '0');
                countdown.textContent = `${minutes}:${seconds}`;
                return true;
            };

            if (!updateCountdown()) {
                return;
            }

            const timer = setInterval(() => {
                if (!updateCountdown()) {
                    clearInterval(timer);
                }
            }, 1000);
        });
    </script>
@endsection
