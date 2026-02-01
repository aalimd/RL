@extends('layouts.app')

@section('title', 'Two-Factor Authentication')

@section('styles')
    <style>
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
        }

        /* Particles */
        .particles {
            position: absolute;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: var(--primary);
            border-radius: 50%;
            opacity: 0.3;
            animation: float-particle 15s infinite;
        }

        .particle:nth-child(1) {
            left: 10%;
            animation-delay: 0s;
        }

        .particle:nth-child(2) {
            left: 30%;
            animation-delay: 2s;
            background: var(--secondary);
        }

        .particle:nth-child(3) {
            left: 50%;
            animation-delay: 4s;
        }

        .particle:nth-child(4) {
            left: 70%;
            animation-delay: 6s;
            background: var(--accent);
        }

        .particle:nth-child(5) {
            left: 90%;
            animation-delay: 8s;
            background: var(--secondary);
        }

        @keyframes float-particle {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }

            10% {
                opacity: 0.4;
            }

            90% {
                opacity: 0.4;
            }

            100% {
                transform: translateY(-100vh) scale(1);
                opacity: 0;
            }
        }

        /* Glass Card */
        .auth-card {
            max-width: 420px;
            width: 100%;
            position: relative;
            z-index: 10;
            border-radius: 1.5rem;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            padding: 2.5rem 2rem;
            text-align: center;
        }

        [data-theme="dark"] .auth-card,
        body.dark-mode .auth-card {
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(255, 255, 255, 0.1);
        }

        /* Icon */
        .icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
            color: var(--primary);
            position: relative;
        }

        .icon-wrapper::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 2px solid currentColor;
            opacity: 0;
            animation: ring-pulse 2s infinite;
        }

        @keyframes ring-pulse {
            0% {
                transform: scale(0.9);
                opacity: 0.5;
            }

            100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }

        /* Headings */
        .auth-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .auth-desc {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        /* Input */
        .code-input {
            width: 100%;
            padding: 1rem;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.5rem;
            text-align: center;
            border: 2px solid var(--border-color);
            border-radius: 0.75rem;
            background: var(--bg-input, rgba(255, 255, 255, 0.5));
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }

        .code-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            transform: scale(1.02);
        }

        /* Button */
        .btn-verify {
            width: 100%;
            padding: 1rem;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
            transition: all 0.3s ease;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px -10px rgba(99, 102, 241, 0.5);
        }

        /* Links */
        .link-action {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 0.9rem;
            cursor: pointer;
            margin-top: 1.5rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.2s;
            padding: 0;
        }

        .link-action:hover {
            color: var(--primary);
        }

        /* Alerts */
        .alert {
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-align: left;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #EF4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #10B981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
    </style>
@endsection

@section('content')
    <div class="auth-page">
        <div class="particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>

        <div class="auth-card">
            <div class="icon-wrapper">
                <i data-lucide="lock" style="width: 32px; height: 32px;"></i>
            </div>

            <h1 class="auth-title">Two-Factor Authentication</h1>
            <p class="auth-desc">
                @if($method === 'app')
                    Enter the 6-digit code from your authenticator app to continue.
                @else
                    We sent a verification code to your email address. Please enter it below.
                @endif
            </p>

            @if(session('error') || $errors->any())
                <div class="alert alert-error">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                    <span>{{ session('error') ?? $errors->first() }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('admin.2fa.verify.post') }}" method="POST">
                @csrf
                <input type="text" name="code" class="code-input" placeholder="000000" required autofocus maxlength="6"
                    pattern="[0-9]*" inputmode="numeric" autocomplete="one-time-code">

                <button type="submit" class="btn-verify">
                    Verify Securely
                </button>
            </form>

            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                @if($method === 'email')
                    <form action="{{ route('admin.2fa.resend') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="link-action">
                            <i data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
                            Resend Code
                        </button>
                    </form>
                @endif

                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="link-action" style="color: var(--text-muted); opacity: 0.8;">
                        <i data-lucide="log-out" style="width: 14px; height: 14px;"></i>
                        Cancel & Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection