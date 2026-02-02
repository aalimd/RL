@extends('layouts.app')

@section('title', 'Document Verification | ' . ($settings['siteName'] ?? 'AAMD'))

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
            /* Gradient Background matching Landing */
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

        /* ========================================
                                       GLASS CARD
                                       ======================================== */
        .verify-card {
            max-width: 500px;
            width: 100%;
            position: relative;
            z-index: 10;
            border-radius: 2rem;
            overflow: hidden;

            /* Standardized Glassmorphism */
            background: var(--glass-bg);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px -12px var(--shadow-color);
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

        /* Dark Mode Glass Support */
        [data-theme="dark"] .verify-card,
        body.dark-mode .verify-card {
            background: rgba(30, 41, 59, 0.85) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        [data-theme="dark"] .verify-header {
            border-bottom-color: rgba(255, 255, 255, 0.1) !important;
        }

        [data-theme="dark"] .status-title {
            color: #f1f5f9;
        }

        [data-theme="dark"] .status-desc {
            color: #94a3b8;
        }

        [data-theme="dark"] .info-label {
            color: #64748b;
        }

        [data-theme="dark"] .info-value {
            color: #e2e8f0;
        }

        [data-theme="dark"] .info-row {
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }

        .verify-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.2);
        }

        /* ========================================
                                       GLOW EFFECTS
                                       ======================================== */
        .glow-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.2) 0%, transparent 70%);
            opacity: 0.6;
            z-index: 0;
            pointer-events: none;
        }

        .status-valid .glow-bg {
            background: radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, transparent 70%);
        }

        .status-invalid .glow-bg {
            background: radial-gradient(circle, rgba(239, 68, 68, 0.25) 0%, transparent 70%);
        }

        /* ========================================
                                       HEADER & ICON
                                       ======================================== */
        .verify-header {
            padding: 2.5rem 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        [data-theme="dark"] .verify-header,
        body.dark-mode .verify-header {
            border-bottom-color: rgba(255, 255, 255, 0.05);
        }

        .status-icon-wrapper {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            position: relative;
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        [data-theme="dark"] .status-icon-wrapper,
        body.dark-mode .status-icon-wrapper {
            background: #1e293b;
        }

        .status-icon-wrapper svg {
            width: 48px;
            height: 48px;
            stroke-width: 2px;
        }

        /* Valid State Styles */
        .is-valid .status-icon-wrapper {
            color: #10B981;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
        }

        .is-valid .status-title {
            color: #059669;
        }

        /* Invalid State Styles */
        .is-invalid .status-icon-wrapper {
            color: #EF4444;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
        }

        .is-invalid .status-title {
            color: #DC2626;
        }

        .status-title {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            letter-spacing: -0.025em;
        }

        .status-desc {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.5;
        }

        /* ========================================
                                       BODY & CONTENT
                                       ======================================== */
        .verify-body {
            padding: 2rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px dashed var(--border-light);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .info-value {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1rem;
            text-align: right;
        }

        .badge-verified {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.825rem;
            font-weight: 700;
            background: rgba(16, 185, 129, 0.15);
            color: #10B981;
            border: 1px solid rgba(16, 185, 129, 0.2);
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.15);
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {

            0%,
            100% {
                box-shadow: 0 0 15px rgba(16, 185, 129, 0.15);
            }

            50% {
                box-shadow: 0 0 25px rgba(16, 185, 129, 0.3);
            }
        }

        /* ========================================
                                       BUTTONS
                                       ======================================== */
        .btn-return {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 0.875rem 2rem;
            width: 100%;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-top: 2rem;
            cursor: pointer;

            /* Primary Button Style from Landing */
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
            border: none;
        }

        .btn-return:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px -10px rgba(99, 102, 241, 0.5);
            color: white;
        }

        /* Pulse Ring Animation */
        .pulse-ring {
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
    </style>
@endsection

@section('content')
    <div class="verify-page {{ $status === 'valid' ? 'status-valid' : 'status-invalid' }}">
        <!-- Ambient Background -->
        <div class="ambient-bg"></div>

        <!-- Particles -->
        <div class="particles">
            @for($i = 0; $i < 10; $i++)
                <div class="particle"
                    style="left: {{ rand(0, 100) }}%; animation-delay: {{ rand(0, 10) }}s; animation-duration: {{ rand(15, 30) }}s;">
                </div>
            @endfor
        </div>

        <!-- Theme Toggle (Fixed Position) -->
        <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme"
            style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 50; background: var(--bg-card); padding: 0.5rem; border-radius: 99px; border: 1px solid var(--border-color); box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <i data-lucide="moon" class="moon-icon" style="width: 20px; height: 20px;"></i>
            <i data-lucide="sun" class="sun-icon" style="width: 20px; height: 20px;"></i>
        </button>

        <!-- Main Card -->
        <div class="verify-card {{ $status === 'valid' ? 'is-valid' : 'is-invalid' }}">

            @if($status === 'valid')
                <!-- VALID STATE -->
                <div class="verify-header">
                    <div class="status-icon-wrapper">
                        <i data-lucide="shield-check"></i>
                        <div class="pulse-ring"></div>
                    </div>
                    <h1 class="status-title">Official Document</h1>
                    <p class="status-desc">This recommendation letter has been successfully verified.</p>
                </div>

                <div class="verify-body">
                    <div class="space-y-4">
                        <div class="info-row">
                            <span class="info-label">Candidate Name</span>
                            <span class="info-value">{{ $request->student_name }} {{ $request->last_name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Issued Date</span>
                            <span class="info-value">{{ $request->updated_at->format('d M, Y') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Reference ID</span>
                            <span class="info-value"
                                style="font-family: monospace; letter-spacing: 0.5px;">{{ $request->tracking_id }}</span>
                        </div>
                        <div class="info-row" style="border-bottom: none;">
                            <span class="info-label">Verification</span>
                            <span class="badge-verified">
                                <i data-lucide="check-circle-2" style="width: 14px; height: 14px;"></i>
                                Authenticated
                            </span>
                        </div>
                    </div>

                    <div
                        style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-light); text-align: center;">
                        <p style="font-size: 0.8rem; color: var(--text-muted);">
                            Digitally signed and verified by<br>
                            <strong>{{ $settings['siteName'] ?? 'Academic Recommendation System' }}</strong>
                        </p>
                    </div>

                    <a href="{{ route('home') }}" class="btn-return">
                        <i data-lucide="home" style="width: 18px; height: 18px;"></i>
                        Return to Dashboard
                    </a>
                </div>

            @else
                <!-- INVALID STATE -->
                <div class="verify-header">
                    <div class="status-icon-wrapper">
                        <i data-lucide="shield-alert"></i>
                    </div>
                    <h1 class="status-title">Invalid Document</h1>
                    <p class="status-desc">Verification failed. This document does not exist or has expired.</p>
                </div>

                <div class="verify-body">
                    <div class="status-info-box"
                        style="background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); color: #ef4444; flex-direction: column;">
                        <p style="margin: 0; line-height: 1.6; text-align: center;">
                            We could not match the provided token to a valid recommendation letter in our records. Please ensure
                            you scanned the correct QR code or contact the administration.
                        </p>
                    </div>

                    <a href="{{ route('home') }}" class="btn-return"
                        style="background: var(--bg-secondary); color: var(--text-primary); border: 1px solid var(--border-color); box-shadow: none;">
                        <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                        Back to Home
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection