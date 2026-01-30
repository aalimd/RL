@extends('layouts.app')

@section('title', 'Track Your Request')

@section('styles')
    <style>
        .tracking-page {
            min-height: 100vh;
            background: var(--bg-primary);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow: hidden;
        }

        /* Floating Particles */
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
            left: 15%;
            animation-delay: 0s;
        }

        .particle:nth-child(2) {
            left: 35%;
            animation-delay: 3s;
            background: var(--secondary);
        }

        .particle:nth-child(3) {
            left: 55%;
            animation-delay: 6s;
        }

        .particle:nth-child(4) {
            left: 75%;
            animation-delay: 9s;
            background: var(--accent);
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

        .tracking-wrapper {
            width: 100%;
            max-width: 480px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 10;
        }

        /* Animated Icon */
        .tracking-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 15px 35px -10px rgba(99, 102, 241, 0.5);
            animation: float 3s ease-in-out infinite;
        }

        .tracking-icon svg {
            width: 36px;
            height: 36px;
            color: white;
        }

        .tracking-header {
            text-align: center;
            margin-bottom: 2rem;
            animation: slide-up 0.5s ease-out;
        }

        .tracking-header h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .tracking-header p {
            color: var(--text-muted);
            font-size: 1rem;
        }

        .tracking-card {
            width: 100%;
            background: var(--bg-secondary);
            border-radius: 1.25rem;
            box-shadow: 0 10px 40px -15px var(--shadow-color);
            padding: 2rem;
            border: 1px solid var(--border-light);
            animation: slide-up 0.5s ease-out 0.1s backwards;
        }

        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.2s;
            background: var(--input-bg);
            color: var(--text-primary);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .track-btn {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px -3px rgba(99, 102, 241, 0.4);
        }

        .track-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -5px rgba(99, 102, 241, 0.5);
        }

        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .result-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }

        .result-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .result-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-approved {
            background: #d1fae5;
            color: #059669;
        }

        .status-rejected {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-pending {
            background: #dbeafe;
            color: #2563eb;
        }

        .status-revision {
            background: #ffedd5;
            color: #ea580c;
        }

        .status-review {
            background: #fef3c7;
            color: #d97706;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-light);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .info-value {
            font-weight: 500;
            color: var(--text-primary);
            font-size: 0.875rem;
        }

        .admin-message {
            background: #eff6ff;
            border-left: 3px solid #6366f1;
            padding: 1rem;
            border-radius: 0.25rem;
            margin-top: 1rem;
        }

        .admin-message-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6366f1;
            margin-bottom: 0.25rem;
        }

        .admin-message-text {
            color: #374151;
            font-size: 0.875rem;
        }

        .view-letter-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border-radius: 0.75rem;
            text-decoration: none;
            font-weight: 600;
            margin-top: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px -3px rgba(16, 185, 129, 0.4);
        }

        .view-letter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -5px rgba(16, 185, 129, 0.5);
        }

        .home-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .home-btn:hover {
            background: var(--bg-card);
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .note-text {
            margin-top: 1rem;
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-muted);
        }
    </style>
@endsection

@section('content')
    <div class="tracking-page">
        <div class="hero-bg"></div>

        <!-- Floating Particles -->
        <div class="particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>

        <!-- Theme Toggle Button - Fixed Position -->
        <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Dark Mode"
            style="position: fixed; top: 1rem; right: 1rem; z-index: 100;">
            <i data-lucide="moon" class="moon-icon"></i>
            <i data-lucide="sun" class="sun-icon"></i>
        </button>

        <div class="tracking-wrapper">
            <!-- Animated Icon -->
            <div class="tracking-icon">
                <i data-lucide="search"></i>
            </div>

            <div class="tracking-header">
                <h1>Track Your Request</h1>
                <p>Enter your tracking ID and verification code to see real-time updates.</p>
            </div>

            <div class="tracking-card">
                @if(session('error'))
                    <div class="error-box">
                        <i data-lucide="alert-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ url('/track') }}">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Tracking ID</label>
                        <input type="text" name="trackingId" class="form-input" placeholder="REC-2026-XXXXX"
                            value="{{ old('trackingId', $id ?? '') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Verification Code</label>
                        <input type="text" name="verificationToken" class="form-input" placeholder="Student ID / Last Name"
                            value="{{ old('verificationToken') }}" required>
                    </div>

                    <button type="submit" class="track-btn">Track Request</button>
                </form>

                @if(isset($request) && $request)
                    <div class="result-section">
                        <div class="result-header">
                            <span class="result-title">Request Status</span>
                            <span
                                class="status-badge 
                                                                                                    @if($request->status === 'Approved') status-approved
                                                                                                    @elseif($request->status === 'Rejected') status-rejected
                                                                                                    @elseif($request->status === 'Needs Revision') status-revision
                                                                                                    @elseif($request->status === 'Under Review') status-review
                                                                                                    @else status-pending @endif">
                                {{ $request->status }}
                            </span>
                        </div>

                        <div>
                            <div class="info-row">
                                <span class="info-label">Tracking ID</span>
                                <span class="info-value" style="font-family: monospace;">{{ $request->tracking_id }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Submitted</span>
                                <span class="info-value">{{ $request->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Last Updated</span>
                                <span class="info-value">{{ $request->updated_at->format('M d, Y') }}</span>
                            </div>
                        </div>

                        @if($request->admin_message)
                            <div class="admin-message">
                                <div class="admin-message-title">Message from Admin</div>
                                <p class="admin-message-text">{{ $request->admin_message }}</p>
                            </div>
                        @endif

                        {{-- Status-Based Message from Settings --}}
                        @php
                            $statusMessage = '';
                            if ($request->status === 'Approved') {
                                $statusMessage = $settings['trackingApprovedMessage'] ?? '';
                            } elseif ($request->status === 'Rejected') {
                                $statusMessage = $settings['trackingRejectedMessage'] ?? '';
                            } elseif ($request->status === 'Under Review') {
                                $statusMessage = $settings['trackingReviewMessage'] ?? '';
                            } elseif ($request->status === 'Needs Revision') {
                                $statusMessage = $settings['trackingRevisionMessage'] ?? '';
                            } else {
                                $statusMessage = $settings['trackingPendingMessage'] ?? '';
                            }
                        @endphp

                        @if($statusMessage)
                            <div
                                style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem;">
                                <p style="margin: 0; color: #0369a1; font-size: 0.9rem;">{{ $statusMessage }}</p>
                            </div>
                        @endif

                        @if($request->status === 'Approved')
                            <a href="{{ route('public.letter', ['tracking_id' => $request->tracking_id, 'token' => $request->verification_token]) }}"
                                class="view-letter-btn" target="_blank">
                                <i data-lucide="file-text" style="width: 18px; height: 18px;"></i>
                                View Recommendation Letter
                            </a>
                        @endif

                        {{-- Fixed Message from Settings --}}
                        @php
                            $fixedMessage = $settings['trackingFixedMessage'] ?? 'If you need to submit additional documents, please wait for the "Needs Revision" status.';
                        @endphp
                        @if($fixedMessage)
                            <p class="note-text">{{ $fixedMessage }}</p>
                        @endif
                    </div>
                @endif
            </div>

            <a href="{{ url('/') }}" class="home-btn">
                <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                Back to Home
            </a>
        </div>
    </div>
@endsection