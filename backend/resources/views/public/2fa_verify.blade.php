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
            position: relative;
            overflow: hidden;
            background: var(--bg-primary);
        }

        /* Ambient Background Effect */
        .ambient-bg {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: 0;
        }

        .ambient-bg::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 120%;
            height: 120%;
            background: radial-gradient(ellipse at center, var(--primary) 0%, var(--secondary) 30%, transparent 70%);
            opacity: 0.08;
            animation: float-ambient 15s infinite ease-in-out;
        }

        html.dark .ambient-bg::before {
            opacity: 0.15;
        }

        @keyframes float-ambient {

            0%,
            100% {
                transform: translate(-50%, -50%) scale(1);
            }

            50% {
                transform: translate(-48%, -52%) scale(1.1);
            }
        }

        /* Particles */
        .particles {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: var(--primary);
            border-radius: 50%;
            opacity: 0.3;
            animation: float-particle 20s infinite linear;
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
                transform: translateY(-10vh) scale(1);
                opacity: 0;
            }
        }

        .verify-card {
            max-width: 450px;
            width: 100%;
            position: relative;
            z-index: 10;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 2rem;
            box-shadow: 0 25px 50px -12px var(--shadow-color);
            padding: 3rem 2.5rem;
            text-align: center;
            border: 1px solid var(--glass-border);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: slide-up 0.6s ease-out;
        }

        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .verify-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 60px -15px var(--shadow-color);
        }

        .verify-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--bg-primary), var(--bg-secondary));
            border: 1px solid var(--border-color);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            box-shadow: 0 10px 20px var(--shadow-color);
            position: relative;
        }

        .icon-pulse {
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 2px solid var(--primary);
            opacity: 0;
            animation: ring-pulse 2s infinite;
        }

        @keyframes ring-pulse {
            0% {
                transform: scale(0.9);
                opacity: 0.3;
            }

            100% {
                transform: scale(1.4);
                opacity: 0;
            }
        }

        .verify-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            letter-spacing: -0.025em;
        }

        .verify-text {
            color: var(--text-secondary);
            margin-bottom: 2.5rem;
            font-size: 1rem;
            line-height: 1.6;
        }

        .otp-input {
            width: 100%;
            padding: 1.125rem;
            letter-spacing: 0.5em;
            font-size: 1.75rem;
            text-align: center;
            font-weight: 800;
            background: var(--input-bg);
            border: 2px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 1rem;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .otp-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            transform: translateY(-2px);
        }

        .btn-submit {
            width: 100%;
            padding: 1.125rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 1rem;
            font-weight: 700;
            font-size: 1.125rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 35px -8px rgba(99, 102, 241, 0.5);
            opacity: 0.95;
        }

        .error-box {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ef4444;
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.9375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9375rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--primary);
        }
    </style>
@endsection

@section('content')
    <div class="verify-page">
        <!-- Ambient Background -->
        <div class="ambient-bg"></div>

        <!-- Particles -->
        <div class="particles">
            @for($i = 0; $i < 8; $i++)
                <div class="particle"
                    style="left: {{ rand(0, 100) }}%; animation-delay: {{ rand(0, 10) }}s; animation-duration: {{ rand(15, 30) }}s;">
                </div>
            @endfor
        </div>

        <!-- Theme Toggle -->
        <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme"
            style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 100; background: var(--bg-card); padding: 0.625rem; border-radius: 12px; border: 1px solid var(--border-color);">
            <i data-lucide="moon" class="moon-icon"></i>
            <i data-lucide="sun" class="sun-icon"></i>
        </button>

        <div class="verify-card">
            <div class="verify-icon">
                <i data-lucide="shield-check" style="width: 32px; height: 32px;"></i>
                <div class="icon-pulse"></div>
            </div>

            <h1 class="verify-title">Two-Factor Auth</h1>
            <p class="verify-text">
                @if(!empty($deliveryHint))
                    We've sent a 6-digit verification code to your email ({{ $deliveryHint }}). Please enter it below.
                @else
                    We've sent a 6-digit verification code to your email address. Please enter it below.
                @endif
            </p>

            @if(session('error'))
                <div class="error-box">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('public.tracking.verify.post') }}">
                @csrf
                <input type="text" name="otp" class="otp-input" placeholder="000000" maxlength="6" required autofocus
                    pattern="\d*" inputmode="numeric" autocomplete="one-time-code">

                <button type="submit" class="btn-submit">
                    <span>Verify Code</span>
                    <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
                </button>
            </form>

            <a href="{{ route('public.tracking') }}" class="back-link">
                <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                Back to Tracking
            </a>
        </div>
    </div>
@endsection
