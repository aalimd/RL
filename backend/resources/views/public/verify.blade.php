@extends('layouts.app')

@section('title', 'Document Verification | ' . ($settings['siteName'] ?? 'AAMD'))

@section('styles')
    <style>
        .verify-page {
            min-height: 100vh;
            background: var(--bg-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow: hidden;
        }

        /* Floating Particles (Consistent with other pages) */
        .particles {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
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

        .verify-card {
            max-width: 480px;
            width: 100%;
            background: var(--bg-secondary);
            border-radius: 1.5rem;
            box-shadow: 0 10px 40px var(--shadow-color);
            border: 1px solid var(--border-color);
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        .verify-header {
            padding: 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid var(--border-light);
            background: linear-gradient(to bottom, var(--bg-secondary), var(--bg-primary));
        }

        .verify-body {
            padding: 2rem;
        }

        .status-icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            position: relative;
        }

        .status-valid {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.1));
            color: #10B981;
        }

        .status-invalid {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1));
            color: #EF4444;
        }

        .status-icon-wrapper svg {
            width: 40px;
            height: 40px;
        }

        .status-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .status-desc {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-light);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .info-value {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.95rem;
            text-align: right;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.15);
            color: #10B981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .verified-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 0.75rem;
            color: #10B981;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .return-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
            color: var(--text-muted);
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s;
        }

        .return-link:hover {
            color: var(--primary);
        }

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
    <div class="verify-page">
        <!-- Background Elements -->
        <div class="hero-bg"></div>
        <div class="particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>

        <!-- Theme Toggle -->
        <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme"
            style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 50;">
            <i data-lucide="moon" class="moon-icon"></i>
            <i data-lucide="sun" class="sun-icon"></i>
        </button>

        <div class="verify-card animate-fade-in-up">
            @if($status === 'valid')
                <!-- Valid State -->
                <div class="verify-header">
                    <div class="status-icon-wrapper status-valid">
                        <i data-lucide="shield-check"></i>
                        <div class="pulse-ring"></div>
                    </div>
                    <h1 class="status-title">Verified Document</h1>
                    <p class="status-desc">This recommendation letter is authentic and valid.</p>
                </div>

                <div class="verify-body">
                    <div class="space-y-4">
                        <div class="info-row">
                            <span class="info-label">Student Name</span>
                            <span class="info-value">{{ $request->student_name }} {{ $request->last_name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Issue Date</span>
                            <span class="info-value">{{ $request->updated_at->format('d M, Y') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tracking ID</span>
                            <span class="info-value" style="font-family: monospace;">{{ $request->tracking_id }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Verification Status</span>
                            <span class="badge badge-success">
                                <i data-lucide="check" style="width: 12px; height: 12px; margin-right: 4px;"></i>
                                Verified
                            </span>
                        </div>
                    </div>

                    <div
                        style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-light);">
                        <p style="font-size: 0.75rem; color: var(--text-muted);">
                            Verified by {{ $settings['siteName'] ?? 'Academic System' }}<br>
                            Official Digital Verification
                        </p>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('home') }}" class="return-link">
                            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                            Return to Home
                        </a>
                    </div>
                </div>

            @else
                <!-- Invalid State -->
                <div class="verify-header">
                    <div class="status-icon-wrapper status-invalid">
                        <i data-lucide="shield-alert"></i>
                    </div>
                    <h1 class="status-title">Invalid Document</h1>
                    <p class="status-desc">The verification link you used is invalid or has expired.</p>
                </div>

                <div class="verify-body">
                    <p style="text-align: center; color: var(--text-secondary); line-height: 1.6;">
                        We could not verify the authenticity of this document. It may have been modified, expired, or does not
                        exist in our records.
                    </p>

                    <div class="text-center" style="margin-top: 2rem;">
                        <a href="{{ route('home') }}" class="return-link">
                            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                            Return to Home
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection