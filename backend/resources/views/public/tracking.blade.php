@extends('layouts.app')

@section('title', $settings['trackingTitle'] ?? 'Track Your Request')

@section('styles')
    <style>
        .tracking-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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

        .tracking-wrapper {
            width: 100%;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 10;
        }

        /* Animated Icon */
        .tracking-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px -10px rgba(99, 102, 241, 0.4);
            animation: float-icon 3s ease-in-out infinite;
        }

        @keyframes float-icon {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .tracking-icon svg {
            width: 40px;
            height: 40px;
            color: white;
        }

        .tracking-header {
            text-align: center;
            margin-bottom: 2.5rem;
            animation: slide-up 0.6s ease-out;
        }

        .tracking-header h1 {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            letter-spacing: -0.025em;
        }

        .tracking-header p {
            color: var(--text-secondary);
            font-size: 1.125rem;
            line-height: 1.6;
            max-width: 400px;
            margin: 0 auto;
        }

        .tracking-card {
            width: 100%;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            padding: 2.5rem;
            border: 1px solid var(--glass-border);
            animation: slide-up 0.6s ease-out 0.1s backwards;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .tracking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 60px -15px var(--shadow-color);
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

        .form-group {
            margin-bottom: 1.75rem;
        }

        .form-label {
            display: block;
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.625rem;
            padding-left: 0.25rem;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid var(--border-color);
            border-radius: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--input-bg);
            color: var(--text-primary);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
        }

        .track-btn {
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

        .track-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 35px -8px rgba(99, 102, 241, 0.5);
            opacity: 0.95;
        }

        .track-btn:active {
            transform: translateY(-1px);
        }

        .status-info-box {
            background: rgba(99, 102, 241, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.1);
            border-radius: 1rem;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        html.dark .status-info-box {
            background: rgba(99, 102, 241, 0.1);
            border-color: rgba(99, 102, 241, 0.2);
        }

        .status-info-text {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.9375rem;
            line-height: 1.5;
        }

        .error-box {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ef4444;
            padding: 1.25rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9375rem;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        /* Result Section */
        .result-section {
            margin-top: 2.5rem;
            padding-top: 2.5rem;
            border-top: 2px dashed var(--border-color);
        }

        .result-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .result-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .telegram-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #0088cc, #00a2ed);
            color: white;
            border-radius: 1rem;
            text-decoration: none;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 20px -5px rgba(0, 136, 204, 0.3);
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .telegram-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 30px -8px rgba(0, 136, 204, 0.4);
            filter: brightness(1.05);
        }

        .telegram-btn:active {
            transform: translateY(0);
        }

        .telegram-icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .status-approved {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .status-rejected {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .status-pending {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .status-revision {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .status-review {
            background: rgba(139, 92, 246, 0.15);
            color: #8b5cf6;
            border: 1px solid rgba(139, 92, 246, 0.2);
        }

        /* Timeline */
        .timeline {
            position: relative;
            margin: 2.5rem 0;
            padding-right: 0.5rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--border-color);
            border-radius: 3px;
        }

        .timeline-item {
            position: relative;
            padding-left: 3.5rem;
            padding-bottom: 2.5rem;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-marker {
            position: absolute;
            left: 0;
            top: 0;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--bg-secondary);
            border: 3px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--text-muted);
        }

        .timeline-item.completed .timeline-marker {
            background: #10b981;
            border-color: #10b981;
            color: white;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
        }

        .timeline-item.active .timeline-marker {
            background: var(--bg-secondary);
            border-color: var(--primary);
            color: var(--primary);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.2);
        }

        .timeline-item.completed::after {
            content: '';
            position: absolute;
            left: 20px;
            top: 44px;
            bottom: -20px;
            width: 3px;
            background: #10b981;
            z-index: 1;
        }

        .timeline-content h4 {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .timeline-content p {
            margin: 0.375rem 0 0;
            font-size: 0.9375rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .timeline-date {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
            font-weight: 500;
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
            font-size: 0.9375rem;
            font-weight: 500;
        }

        .info-value {
            color: var(--text-primary);
            font-weight: 700;
            font-size: 0.9375rem;
        }

        .admin-message {
            background: rgba(99, 102, 241, 0.05);
            border-right: 4px solid var(--primary);
            padding: 1.25rem;
            border-radius: 1rem;
            margin-top: 1.5rem;
        }

        .admin-message-title {
            font-size: 0.8125rem;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .admin-message-text {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.6;
        }

        .view-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            padding: 1rem;
            border-radius: 1rem;
            text-decoration: none;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.4);
        }

        .btn-telegram {
            background: #0088cc;
            color: white;
            box-shadow: 0 10px 20px rgba(0, 136, 204, 0.3);
        }

        .btn-telegram:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 136, 204, 0.4);
        }

        .home-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            margin-top: 2.5rem;
            padding: 0.875rem 1.75rem;
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9375rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px var(--shadow-color);
        }

        .home-btn:hover {
            background: var(--bg-card);
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--shadow-color);
        }

        .note-text {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.875rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .view-letter-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            padding: 1.125rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 1rem;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.125rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
            margin-top: 1.5rem;
        }

        .view-letter-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 35px -8px rgba(99, 102, 241, 0.5);
            filter: brightness(1.05);
        }

        .view-letter-btn:active {
            transform: translateY(-1px);
        }

        /* Responsive */
        @media (max-width: 480px) {
            .tracking-card {
                padding: 1.75rem;
            }

            .tracking-header h1 {
                font-size: 1.875rem;
            }

            .tracking-icon {
                width: 70px;
                height: 70px;
            }

            .tracking-icon svg {
                width: 32px;
                height: 32px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="tracking-page">
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

        <!-- Theme Toggle -->
        <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme"
            style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 100;">
            <i data-lucide="moon" class="moon-icon"></i>
            <i data-lucide="sun" class="sun-icon"></i>
        </button>

        <div class="tracking-wrapper">
            <!-- Animated Icon -->
            <div class="tracking-icon">
                <i data-lucide="search"></i>
            </div>

            <div class="tracking-header">
                <h1>{{ $settings['trackingTitle'] ?? 'Track Your Request' }}</h1>
                <p>{{ $settings['trackingSubtitle'] ?? 'View the real-time status and progress of your academic recommendation letter.' }}
                </p>
            </div>

            <div class="tracking-card">
                @if(session('error'))
                    <div class="error-box">
                        <i data-lucide="alert-circle" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
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
                        <label class="form-label">ID Number</label>
                        <input type="text" name="verificationToken" class="form-input" placeholder="Enter your ID Number"
                            value="{{ old('verificationToken') }}" required>
                    </div>

                    <button type="submit" class="track-btn">
                        <span>{{ $settings['trackingSearchBtn'] ?? 'Track Request' }}</span>
                        <i data-lucide="arrow-right" style="width: 20px; height: 20px;"></i>
                    </button>
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
                            </span>
                        </div>

                        <!-- Visual Timeline -->
                        <div class="timeline">
                            <!-- Step 1: Submitted -->
                            <div class="timeline-item completed">
                                <div class="timeline-marker">
                                    <i data-lucide="check" style="width: 20px; height: 20px;"></i>
                                </div>
                                <div class="timeline-content">
                                    <h4>Request Submitted</h4>
                                    <p>Your request has been received.</p>
                                    <div class="timeline-date">{{ $request->created_at->format('M d, Y h:i A') }}</div>
                                </div>
                            </div>

                            <!-- Step 2: Under Review -->
                            @php
                                $isReviewActive = in_array($request->status, ['Under Review', 'Needs Revision']);
                                $isReviewCompleted = in_array($request->status, ['Approved', 'Rejected']);
                                $reviewClass = $isReviewCompleted ? 'completed' : ($isReviewActive ? 'active' : '');
                                $reviewIcon = $isReviewCompleted ? 'check' : 'search';
                            @endphp
                            <div class="timeline-item {{ $reviewClass }}">
                                <div class="timeline-marker">
                                    <i data-lucide="{{ $reviewIcon }}" style="width: 20px; height: 20px;"></i>
                                </div>
                                <div class="timeline-content">
                                    <h4>Details Review</h4>
                                    <p>Checking your information and requirements.</p>
                                    @if($isReviewActive || $isReviewCompleted)
                                        <div class="timeline-date">In Progress</div>
                                    @endif
                                </div>
                            </div>

                            <!-- Step 3: Final Decision -->
                            @php
                                $isDecisionActive = in_array($request->status, ['Approved', 'Rejected']);
                                $decisionClass = $isDecisionActive ? ($request->status == 'Approved' ? 'completed' : 'active') : ''; // Green if approved, Active if rejected/final
                                $decisionIcon = $request->status == 'Approved' ? 'check' : ($request->status == 'Rejected' ? 'x' : 'file-text');

                                // Override for Rejected to show red marker
                                $markerStyle = $request->status == 'Rejected' ? 'border-color: #ef4444; color: #ef4444;' : '';
                            @endphp
                            <div class="timeline-item {{ $decisionClass }}">
                                <div class="timeline-marker" style="{{ $markerStyle }}">
                                    <i data-lucide="{{ $decisionIcon }}" style="width: 20px; height: 20px;"></i>
                                </div>
                                <div class="timeline-content">
                                    <h4>Final Decision</h4>
                                    <p>
                                        @if($request->status == 'Approved')
                                            Congratulations! Your letter is ready.
                                        @elseif($request->status == 'Rejected')
                                            Request declined. See admin message.
                                        @else
                                            Awaiting final approval.
                                        @endif
                                    </p>
                                    @if($isDecisionActive)
                                        <div class="timeline-date">{{ $request->updated_at->format('M d, Y') }}</div>
                                    @endif
                                </div>
                            </div>
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
                            <div class="status-info-box">
                                <i data-lucide="info"
                                    style="width: 18px; height: 18px; color: var(--primary); flex-shrink: 0; margin-top: 2px;"></i>
                                <p class="status-info-text">{{ $statusMessage }}</p>
                            </div>
                        @endif

                        @if(isset($telegramBotUsername) && $telegramBotUsername && !$request->telegram_chat_id)
                            <a href="https://t.me/{{ $telegramBotUsername }}?start={{ $request->tracking_id }}" target="_blank"
                                class="telegram-btn">
                                <div class="telegram-icon-wrapper">
                                    <i data-lucide="send" style="width: 14px; height: 14px;"></i>
                                </div>
                                <span>Subscribe to Updates</span>
                            </a>
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