@extends('layouts.app')

@section('title', $settings['requestTitle'] ?? 'Request Recommendation')

@section('styles')
    <style>
        .request-page {
            min-height: 100vh;
            background: var(--bg-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow: hidden;
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

        .wizard-container {
            max-width: 700px;
            width: 100%;
            position: relative;
            z-index: 10;
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            padding: 2.5rem;
        }

        /* Header */
        .page-header {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--text-primary);
            animation: slide-up 0.5s ease-out;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 1rem;
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

        /* Progress Steps */
        .steps-indicator {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.875rem;
            border: 2px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-muted);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 2;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .step-circle.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-color: var(--primary);
            box-shadow: 0 4px 15px -3px rgba(var(--primary-rgb), 0.4), 0 0 0 4px rgba(var(--primary-rgb), 0.15);
            transform: scale(1.1);
        }

        .step-circle.completed {
            background: var(--success, #10b981);
            color: white;
            border-color: var(--success, #10b981);
            box-shadow: 0 2px 10px -2px rgba(16, 185, 129, 0.3);
        }

        .step-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #6b7280;
        }

        .step-item.active .step-label {
            color: #111827;
        }

        .step-connector {
            width: 60px;
            height: 3px;
            background: var(--border-color);
            border-radius: 2px;
            transition: background 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .step-connector.completed {
            background: var(--success, #10b981);
        }

        .step-connector.completed::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: shimmer-line 2s infinite;
        }

        @keyframes shimmer-line {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Form Card */
        .form-card {
            background: var(--bg-secondary);
            border-radius: 1.5rem;
            box-shadow: 0 4px 6px -1px var(--shadow-color), 0 2px 4px -1px var(--shadow-color);
            padding: 2.5rem;
            border: 1px solid var(--border-color);
        }

        .form-section-title {
            font-size: 1.375rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        /* Form Fields */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .form-label .required {
            color: #ef4444;
        }

        .optional-badge {
            display: inline-flex;
            align-items: center;
            margin-left: 0.45rem;
            padding: 0.18rem 0.45rem;
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--text-muted);
            background: rgba(148, 163, 184, 0.14);
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.25s ease;
            background: var(--input-bg);
            color: var(--text-primary);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        html.dark .form-input, html.dark .form-select, html.dark .form-textarea {
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.2);
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.15), inset 0 1px 2px rgba(0, 0, 0, 0.02);
            background: var(--bg-secondary);
        }

        .form-input::placeholder,
        .form-textarea::placeholder {
            color: var(--text-muted);
            opacity: 0.7;
        }

        .btn-next,
        .btn-submit {
            background: #6366f1;
            color: white;
            border: none;
        }

        .btn-next:hover,
        .btn-submit:hover {
            background: #4f46e5;
        }

        .back-link {
            color: #6b7280;
        }

        .back-link:hover {
            color: #111827;
        }

        .form-input.error,
        .form-select.error,
        .form-textarea.error {
            border-color: #ef4444;
        }

        .field-help,
        .field-error {
            display: block;
            margin-top: 0.45rem;
            font-size: 0.82rem;
            line-height: 1.5;
        }

        .field-help {
            color: var(--text-muted);
        }

        .field-error {
            color: #ef4444;
            font-weight: 600;
        }

        .section-note {
            margin: -0.5rem 0 1.5rem;
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.65;
        }

        .section-divider {
            margin: 2rem 0 1rem;
        }

        .section-divider-title {
            margin: 0 0 0.35rem 0;
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 700;
        }

        .section-divider-text {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.55;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .step-label {
                display: none;
            }

            .step-connector {
                width: 30px;
            }
        }

        /* Template Selection */
        .template-option {
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1.25rem;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--bg-secondary);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        html.dark .template-option {
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.02);
        }

        .template-option:hover {
            border-color: var(--primary);
            background: rgba(var(--primary-rgb), 0.02);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px -6px rgba(0,0,0,0.1);
        }

        .template-option.selected {
            border-color: var(--primary);
            background: rgba(var(--primary-rgb), 0.08);
            box-shadow: 0 0 0 1px var(--primary);
        }

        .template-option .template-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1.05rem;
        }

        .template-option .template-lang {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            background: var(--bg-tertiary);
            padding: 0.35rem 0.65rem;
            border-radius: 2rem;
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }

        .template-option.admin-fixed {
            cursor: default;
            border-color: var(--accent, #10b981);
            background: rgba(16, 185, 129, 0.15);
        }

        .template-option.admin-fixed .template-name {
            color: var(--text-primary);
        }

        html.dark .template-option.admin-fixed {
            background: rgba(16, 185, 129, 0.2);
            border-color: #10b981;
        }

        /* Content Option Cards */
        .content-option {
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1.25rem;
            cursor: pointer;
            transition: all 0.25s ease;
            background: var(--bg-secondary);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        html.dark .content-option {
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.02);
            box-shadow: none;
        }

        .content-option:hover {
            border-color: var(--primary);
            background: rgba(var(--primary-rgb), 0.02);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px -6px rgba(0,0,0,0.1);
        }

        .content-option.selected {
            border-color: var(--primary);
            background: rgba(var(--primary-rgb), 0.08);
            box-shadow: 0 0 0 1px var(--primary);
        }

        .content-option .option-title {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1.05rem;
        }

        .content-option .option-desc {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-left: 2rem;
            line-height: 1.4;
        }

        .radio-circle {
            width: 20px;
            height: 20px;
            border: 2px solid var(--border-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .content-option.selected .radio-circle {
            border-color: #667eea;
        }

        .content-option.selected .radio-circle::after {
            content: '';
            width: 10px;
            height: 10px;
            background: #667eea;
            border-radius: 50%;
        }

        /* Summary Box */
        .summary-box {
            background: var(--bg-primary);
            border-radius: 0.75rem;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-label {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .summary-value {
            font-weight: 600;
            color: var(--text-primary);
        }

        .summary-value.multiline {
            text-align: right;
            white-space: pre-wrap;
            max-width: 58%;
        }

        .review-callout {
            margin-bottom: 1.5rem;
            padding: 1rem 1.1rem;
            border-radius: 1rem;
            border: 1px solid rgba(var(--primary-rgb), 0.18);
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.1), rgba(var(--primary-rgb), 0.04));
            color: var(--text-secondary);
            line-height: 1.6;
            font-size: 0.93rem;
        }

        .summary-card {
            margin-top: 1.25rem;
            padding: 1.25rem;
            border-radius: 1rem;
            border: 1px solid var(--border-color);
            background: var(--bg-primary);
        }

        .summary-card:first-of-type {
            margin-top: 0;
        }

        .summary-card-title {
            margin: 0 0 0.85rem 0;
            font-size: 0.98rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .summary-card-copy {
            margin: 0;
            color: var(--text-secondary);
            line-height: 1.7;
            white-space: pre-wrap;
        }

        /* Navigation Buttons */
        .form-nav {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.75rem;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-back {
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            color: var(--text-secondary);
        }

        .btn-back:hover {
            background: var(--bg-card);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        .btn-next,
        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            color: white;
            box-shadow: 0 4px 15px -3px rgba(var(--primary-rgb), 0.4);
            position: relative;
            overflow: hidden;
        }

        /* Subtle shine effect on primary buttons */
        .btn-next::after,
        .btn-submit::after {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transform: skewX(-20deg);
            transition: all 0.5s ease;
        }

        .btn-next:hover::after,
        .btn-submit:hover::after {
            left: 150%;
        }

        .btn-next:hover,
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -5px rgba(var(--primary-rgb), 0.5);
            color: white;
        }

        /* Success Screen */
        .success-screen {
            text-align: center;
            padding: 3rem 2rem;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4);
        }

        .success-icon svg {
            width: 40px;
            height: 40px;
            color: white;
        }

        .success-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .success-subtitle {
            color: var(--text-secondary);
        }

        .tracking-box {
            background: var(--bg-primary);
            padding: 1.25rem;
            border-radius: 0.75rem;
            margin: 1.5rem 0;
            border: 1px solid var(--border-color);
        }

        .email-guidance-card {
            margin: 1.25rem auto 1.75rem;
            max-width: 560px;
            padding: 1rem 1.1rem;
            border-radius: 1rem;
            border: 1px solid rgba(var(--primary-rgb), 0.18);
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.1), rgba(var(--primary-rgb), 0.04));
            text-align: left;
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            box-shadow: 0 16px 30px -22px rgba(var(--primary-rgb), 0.45);
        }

        .email-guidance-icon {
            flex-shrink: 0;
            width: 42px;
            height: 42px;
            border-radius: 0.9rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 24px -16px rgba(var(--primary-rgb), 0.5);
        }

        .email-guidance-title {
            margin: 0 0 0.3rem 0;
            font-size: 0.98rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .email-guidance-text {
            margin: 0;
            color: var(--text-secondary);
            line-height: 1.65;
            font-size: 0.93rem;
        }

        .tracking-label {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .tracking-id {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .error-box {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ef4444;
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            opacity: 0.9;
        }

        .back-link:hover {
            opacity: 1;
        }

        /* Premium Stepper Icons */
        .step-circle {
            width: 40px;
            height: 40px;
            font-size: 1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 2;
        }

        .step-circle.active {
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
            transform: scale(1.1);
        }

        /* Pulse Animation for Active Step */
        .step-circle.active::after {
            content: '';
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            border-radius: 50%;
            border: 2px solid var(--primary);
            animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
            pointer-events: none;
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.9);
                opacity: 0.8;
            }

            70%, 100% {
                transform: scale(1.4);
                opacity: 0;
            }
        }

        /* Copy Button */
        .copy-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            padding: 4px;
            transition: color 0.2s;
        }

        .copy-btn:hover {
            color: var(--primary);
        }

        /* Loading Spinner */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .btn-loading .btn-text {
            display: none;
        }

        .btn-loading .spinner {
            display: inline-block;
        }

        /* Shake Animation */
        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }

        .shake {
            animation: shake 0.5s cubic-bezier(.36, .07, .19, .97) both;
            border-color: #ef4444 !important;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@26.0.9/build/css/intlTelInput.css">
    <style>
        .iti {
            width: 100%;
        }

        /* Ensure list has no bullets - Critical Fix */
        .iti__country-list {
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
            border: 1px solid var(--border-color) !important;
            background-color: var(--bg-secondary) !important;
            z-index: 9999 !important;
            /* Ensure it's on top */
        }

        .iti__country-name {
            color: var(--text-primary);
        }

        /* Fix flag position */
        .iti__flag {
            box-shadow: none !important;
        }

        /* Fix dropdown arrow color */
        .iti__arrow {
            border-top-color: var(--text-muted);
        }

        .iti__arrow--up {
            border-bottom-color: var(--text-muted);
        }
    </style>
@endsection

@section('content')
    @php
        $isEditMode = session('wizard_mode') === 'edit';
    @endphp
    <div class="request-page">
        <!-- Ambient Background -->
        <div class="ambient-bg"></div>

        <!-- Floating Particles -->
        <div class="particles">
            @for($i = 0; $i < 10; $i++)
                <div class="particle"
                    style="left: {{ rand(0, 100) }}%; animation-delay: {{ rand(0, 10) }}s; animation-duration: {{ rand(15, 30) }}s;">
                </div>
            @endfor
        </div>

        <!-- Theme Toggle Button - Fixed Position -->
        <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Dark Mode"
            style="position: fixed; top: 1rem; right: 1rem; z-index: 100;">
            <i data-lucide="moon" class="moon-icon"></i>
            <i data-lucide="sun" class="sun-icon"></i>
        </button>

            <div class="wizard-container">
                <!-- Header -->
                <div class="page-header">
                    @if($settings['logoUrl'] ?? false)
                        <img src="{{ $settings['logoUrl'] }}" alt="Logo" style="height: 50px; margin-bottom: 1rem;">
                    @endif
                    <h1>{{ $isEditMode ? 'Update Recommendation Request' : ($settings['requestTitle'] ?? 'Request Recommendation Letter') }}
                    </h1>
                    <p>{{ $isEditMode ? 'Please complete the requested changes and submit your revisions for review.' : ($settings['requestSubtitle'] ?? 'Submit your recommendation letter request') }}
                    </p>
                </div>

                @if(session('info'))
                    <div
                        style="margin-bottom: 1rem; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); color: #92400e; border-radius: 0.75rem; padding: 0.875rem 1rem;">
                        {{ session('info') }}
                    </div>
                @endif

                @if(session('success'))
                    <!-- Success Screen -->
                    <div class="form-card">
                        <div class="success-screen">
                        <div class="success-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h2 class="success-title">{{ session('success_title', 'Request Submitted Successfully!') }}</h2>
                        <p class="success-subtitle">
                            {{ session('success_subtitle', 'Your recommendation request has been received.') }}</p>

                        <div class="email-guidance-card">
                            <div class="email-guidance-icon">
                                <i data-lucide="mail-search" style="width: 20px; height: 20px;"></i>
                            </div>
                            <div>
                                <p class="email-guidance-title">Confirmation email reminder</p>
                                @if(session('confirmation_email_sent'))
                                    <p class="email-guidance-text">
                                        A confirmation email was sent to
                                        <strong>{{ session('confirmation_email_hint', 'your registered email address') }}</strong>.
                                        If it does not appear within a few minutes, please check your spam or junk folder
                                        and mark the message as safe.
                                    </p>
                                @else
                                    <p class="email-guidance-text">
                                        Please watch your inbox for a confirmation email. If it is not visible within a few
                                        minutes, check your spam or junk folder first. You can still use your tracking ID
                                        below to follow your request.
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="tracking-box"
                            style="display: flex; align-items: center; justify-content: center; gap: 1rem;">
                            <div>
                                <p class="tracking-label">Your Tracking ID:</p>
                                <p class="tracking-id" id="trackingIdDisplay">{{ session('tracking_id') }}</p>
                            </div>
                            <button class="copy-btn" onclick="copyTrackingId()" title="Copy ID">
                                <i data-lucide="copy" style="width: 20px; height: 20px;"></i>
                            </button>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.75rem; max-width: 300px; margin: 0 auto;">
                            @if(session('telegram_bot_username'))
                                <a href="https://t.me/{{ session('telegram_bot_username') }}?start={{ session('tracking_id') }}"
                                    target="_blank" class="btn-nav"
                                    style="justify-content: center; background: #0088cc; color: white; border: none;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        style="margin-right: 8px;">
                                        <path
                                            d="M21.198 2.433a2.242 2.242 0 0 0-1.022.215l-8.609 3.33c-2.068.8-4.133 1.598-5.724 2.21a405.15 405.15 0 0 1-2.849 1.09c-.42.147-.99.332-1.473.901-.728.968.193 1.798.919 2.286 1.61.516 3.275 1.009 3.816 1.177l.176.056c.112.037.162.145.148.25l-.29 3.392a1.867 1.867 0 0 0 1.29 1.9c.706.182 1.98.486 2.502.584.453.085.803-.255 1.026-.52.545-.583 1.258-1.353 1.776-1.93.072-.08.183-.09.261-.023 1.54 1.154 3.352 2.515 4.966 3.722.652.486 1.611.332 1.956-.563.858-2.617 2.458-7.94 3.633-11.996a2.288 2.288 0 0 0-.256-1.897 2.27 2.27 0 0 0-1.246-.994z" />
                                    </svg>
                                    Subscribe to Updates
                                </a>
                            @endif
                            <a href="{{ route('public.tracking') }}" class="btn-nav btn-next" style="justify-content: center;">
                                Track Request
                            </a>
                            <a href="{{ route('home') }}" style="color: var(--text-secondary); text-decoration: none;">Return
                                Home</a>
                        </div>
                    </div>
                </div>
            @else
                <!-- Progress Steps -->
                <div class="steps-indicator">
                    <div class="step-item {{ $step >= 1 ? 'active' : '' }}">
                        <div class="step-circle {{ $step >= 1 ? ($step > 1 ? 'completed' : 'active') : 'inactive' }}">
                            @if($step > 1) <i data-lucide="check" style="width: 20px;"></i> @else <i data-lucide="user"
                            style="width: 18px;"></i> @endif
                        </div>
                        <span class="step-label">Details</span>
                    </div>
                    <div class="step-connector {{ $step > 1 ? 'completed' : '' }}"></div>
                    <div class="step-item {{ $step >= 2 ? 'active' : '' }}">
                        <div class="step-circle {{ $step >= 2 ? ($step > 2 ? 'completed' : 'active') : 'inactive' }}">
                            @if($step > 2) <i data-lucide="check" style="width: 20px;"></i> @else <i data-lucide="file-text"
                            style="width: 18px;"></i> @endif
                        </div>
                        <span class="step-label">Content</span>
                    </div>
                    <div class="step-connector {{ $step > 2 ? 'completed' : '' }}"></div>
                    <div class="step-item {{ $step >= 3 ? 'active' : '' }}">
                        <div class="step-circle {{ $step >= 3 ? 'active' : 'inactive' }}">
                            <i data-lucide="check-circle" style="width: 18px;"></i>
                        </div>
                        <span class="step-label">Review</span>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="form-card">
                    <form method="POST" action="{{ route('public.request.wizard') }}" id="wizardForm">
                        @csrf
                        <input type="hidden" name="step" value="{{ $step }}">
                        <input type="hidden" name="action" value="next" id="formAction">

                        @if($isEditMode && !empty($formData['admin_message']))
                            <div
                                style="margin-bottom: 1rem; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); color: #92400e; border-radius: 0.75rem; padding: 1rem;">
                                <div style="font-weight: 700; margin-bottom: 0.35rem;">Admin Revision Note</div>
                                <div style="line-height: 1.5;">{{ $formData['admin_message'] }}</div>
                            </div>
                        @endif

                        <!-- Preserve data between steps -->
                        @if($step !== 3)
                            @foreach($formData as $key => $value)
                                @if(!is_array($value))
                                    <input type="hidden" name="data[{{ $key }}]" value="{{ $value }}">
                                @endif
                            @endforeach
                        @endif

                        @if($step == 1)
                            <!-- STEP 1: Personal Details -->
                            <h2 class="form-section-title">Student & Request Information</h2>
                            <p class="section-note">
                                Complete your student details and tell us what this recommendation is for now. Step 3 will be
                                a final review only, so you will not need to enter new information there.
                            </p>

                            @php
                                $fieldConfig = $formConfig['fields'] ?? [];
                                $isVisible = fn($key) => ($fieldConfig[$key]['visible'] ?? true);
                                $isRequired = fn($key) => ($fieldConfig[$key]['required'] ?? in_array($key, ['student_name', 'last_name', 'student_email', 'gender', 'university', 'verification_token', 'training_period', 'purpose', 'deadline']));
                                $requirementTag = fn($key) => $isRequired($key)
                                    ? '<span class="required">*</span>'
                                    : '<span class="optional-badge">Optional</span>';
                                $selectedPurpose = old('data.purpose', $formData['purpose'] ?? '');
                            @endphp

                            <div class="form-grid">
                                @if($isVisible('student_name'))
                                    <div class="form-group">
                                        <label class="form-label">First Name {!! $requirementTag('student_name') !!}</label>
                                        <input type="text" name="data[student_name]"
                                            class="form-input @error('data.student_name') error @enderror"
                                            value="{{ old('data.student_name', $formData['student_name'] ?? '') }}"
                                            placeholder="Enter your first name" {{ $isRequired('student_name') ? 'required' : '' }}>
                                        @error('data.student_name')
                                            <span class="field-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif

                                @if($isVisible('middle_name'))
                                    <div class="form-group">
                                        <label class="form-label">Middle Name {!! $requirementTag('middle_name') !!}</label>
                                        <input type="text" name="data[middle_name]"
                                            class="form-input @error('data.middle_name') error @enderror"
                                            value="{{ old('data.middle_name', $formData['middle_name'] ?? '') }}"
                                            placeholder="Enter your middle name" {{ $isRequired('middle_name') ? 'required' : '' }}>
                                        @error('data.middle_name')
                                            <span class="field-error">{{ $message }}</span>
                                        @else
                                            <span class="field-help">Leave this blank if you do not use a middle name.</span>
                                        @enderror
                                    </div>
                                @endif

                                @if($isVisible('last_name'))
                                    <div class="form-group">
                                        <label class="form-label">Last Name {!! $requirementTag('last_name') !!}</label>
                                        <input type="text" name="data[last_name]"
                                            class="form-input @error('data.last_name') error @enderror"
                                            value="{{ old('data.last_name', $formData['last_name'] ?? '') }}"
                                            placeholder="Enter your last name" {{ $isRequired('last_name') ? 'required' : '' }}>
                                        @error('data.last_name')
                                            <span class="field-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif

                                @if($isVisible('gender'))
                                    <div class="form-group">
                                        <label class="form-label">Gender {!! $requirementTag('gender') !!}</label>
                                        <select name="data[gender]" class="form-select @error('data.gender') error @enderror" {{ $isRequired('gender') ? 'required' : '' }}>
                                            <option value="">Select Gender</option>
                                            <option value="male" {{ old('data.gender', $formData['gender'] ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ old('data.gender', $formData['gender'] ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                        @error('data.gender')
                                            <span class="field-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif

                                @if($isVisible('student_email'))
                                    <div class="form-group">
                                        <label class="form-label">Email Address {!! $requirementTag('student_email') !!}</label>
                                        <input type="email" name="data[student_email]"
                                            class="form-input @error('data.student_email') error @enderror"
                                            value="{{ old('data.student_email', $formData['student_email'] ?? '') }}"
                                            placeholder="example@email.com" {{ $isRequired('student_email') ? 'required' : '' }}>
                                        @error('data.student_email')
                                            <span class="field-error">{{ $message }}</span>
                                        @else
                                            <span class="field-help">We will send submission confirmation and tracking verification codes to this email.</span>
                                        @enderror
                                    </div>
                                @endif

                                @if($isVisible('university'))
                                    <div class="form-group">
                                        <label class="form-label">University / Institution {!! $requirementTag('university') !!}</label>
                                        <input type="text" name="data[university]"
                                            class="form-input @error('data.university') error @enderror"
                                            value="{{ old('data.university', $formData['university'] ?? '') }}"
                                            placeholder="University or institution name" {{ $isRequired('university') ? 'required' : '' }}>
                                        @error('data.university')
                                            <span class="field-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif

                                @if($isVisible('verification_token'))
                                    <div class="form-group">
                                        <label class="form-label">Student / National ID {!! $requirementTag('verification_token') !!}</label>
                                        <input type="text" name="data[verification_token]"
                                            class="form-input @error('data.verification_token') error @enderror"
                                            value="{{ old('data.verification_token', $formData['verification_token'] ?? '') }}"
                                            placeholder="Use this same ID later for tracking" {{ $isRequired('verification_token') ? 'required' : '' }}>
                                        @error('data.verification_token')
                                            <span class="field-error">{{ $message }}</span>
                                        @else
                                            <span class="field-help">Use the same Student / National ID later when you track this request.</span>
                                        @enderror
                                    </div>
                                @endif

                                @if($isVisible('training_period'))
                                    <div class="form-group">
                                        <label class="form-label">Training Period {!! $requirementTag('training_period') !!}</label>
                                        <input type="month" name="data[training_period]"
                                            class="form-input @error('data.training_period') error @enderror"
                                            value="{{ old('data.training_period', $formData['training_period'] ?? '') }}" {{ $isRequired('training_period') ? 'required' : '' }}>
                                        @error('data.training_period')
                                            <span class="field-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif

                                @if($isVisible('phone'))
                                    <div class="form-group">
                                        <label class="form-label">Phone Number {!! $requirementTag('phone') !!}</label>
                                        <input type="tel" name="data[phone]" class="form-input @error('data.phone') error @enderror" id="phone"
                                            value="{{ old('data.phone', $formData['phone'] ?? '') }}" placeholder="50 123 4567" {{ $isRequired('phone') ? 'required' : '' }}>
                                        @error('data.phone')
                                            <span class="field-error">{{ $message }}</span>
                                        @else
                                            <span class="field-help">Optional. Email remains the main update channel. Add a phone number only if you want administration to have another contact method.</span>
                                        @enderror
                                    </div>
                                @endif

                                @if($isVisible('major'))
                                    <div class="form-group">
                                        <label class="form-label">Major / Field of Study {!! $requirementTag('major') !!}</label>
                                        <input type="text" name="data[major]" class="form-input @error('data.major') error @enderror"
                                            value="{{ old('data.major', $formData['major'] ?? '') }}" placeholder="Your academic major"
                                            {{ $isRequired('major') ? 'required' : '' }}>
                                        @error('data.major')
                                            <span class="field-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            <div class="section-divider">
                                <h3 class="section-divider-title">Request Details</h3>
                                <p class="section-divider-text">
                                    These details help administration understand the purpose and urgency of your recommendation
                                    before you reach the final review step.
                                </p>
                            </div>

                            <div class="form-grid">
                                @if($isVisible('purpose'))
                                    <div class="form-group">
                                        <label class="form-label">Purpose of Recommendation {!! $requirementTag('purpose') !!}</label>
                                        <select name="data[purpose]" class="form-select @error('data.purpose') error @enderror" {{ $isRequired('purpose') ? 'required' : '' }}>
                                            <option value="">Select Purpose</option>
                                            <option value="Residency" {{ $selectedPurpose === 'Residency' ? 'selected' : '' }}>Residency</option>
                                            <option value="Master's Application" {{ $selectedPurpose === "Master's Application" ? 'selected' : '' }}>Master's Application</option>
                                            <option value="PhD Application" {{ $selectedPurpose === 'PhD Application' ? 'selected' : '' }}>PhD Application</option>
                                            <option value="Job Application" {{ $selectedPurpose === 'Job Application' ? 'selected' : '' }}>Job Application</option>
                                            <option value="Internship" {{ $selectedPurpose === 'Internship' ? 'selected' : '' }}>Internship</option>
                                            <option value="Scholarship" {{ $selectedPurpose === 'Scholarship' ? 'selected' : '' }}>Scholarship</option>
                                            <option value="Other" {{ $selectedPurpose === 'Other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('data.purpose')
                                            <span class="field-error">{{ $message }}</span>
                                        @else
                                            <span class="field-help">Choose the main use for this recommendation so the reviewer understands the context.</span>
                                        @enderror
                                    </div>
                                @endif

                                @if($isVisible('deadline'))
                                    <div class="form-group">
                                        <label class="form-label">Deadline Date {!! $requirementTag('deadline') !!}</label>
                                        <input type="date" name="data[deadline]" class="form-input @error('data.deadline') error @enderror"
                                            value="{{ old('data.deadline', $formData['deadline'] ?? '') }}" min="{{ date('Y-m-d') }}" {{ $isRequired('deadline') ? 'required' : '' }}>
                                        @error('data.deadline')
                                            <span class="field-error">{{ $message }}</span>
                                        @else
                                            <span class="field-help">Set the real deadline so we can prioritize your request appropriately.</span>
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            @if($isVisible('purpose'))
                                <div class="form-group" id="purposeOtherGroup"
                                    style="{{ $selectedPurpose === 'Other' ? '' : 'display: none;' }}">
                                    <label class="form-label">Purpose Details <span class="required">*</span></label>
                                    <input type="text" name="data[purpose_other]"
                                        class="form-input @error('data.purpose_other') error @enderror"
                                        value="{{ old('data.purpose_other', $formData['purpose_other'] ?? '') }}"
                                        placeholder="Tell us what this recommendation is for">
                                    @error('data.purpose_other')
                                        <span class="field-error">{{ $message }}</span>
                                    @else
                                        <span class="field-help">Describe the specific program, opportunity, or use case when you choose Other.</span>
                                    @enderror
                                </div>
                            @endif

                            @if($isVisible('notes'))
                                <div class="form-group">
                                    <label class="form-label">Additional Notes {!! $requirementTag('notes') !!}</label>
                                    <textarea name="data[notes]" class="form-textarea @error('data.notes') error @enderror" rows="3"
                                        placeholder="Anything the reviewer should keep in mind, such as special focus areas or submission notes." {{ $isRequired('notes') ? 'required' : '' }}>{{ old('data.notes', $formData['notes'] ?? '') }}</textarea>
                                    @error('data.notes')
                                        <span class="field-error">{{ $message }}</span>
                                    @else
                                        <span class="field-help">Optional. Add context that will help the reviewer tailor the recommendation.</span>
                                    @enderror
                                </div>
                            @endif

                            <div class="form-nav">
                                <a href="{{ route('home') }}" class="btn-nav btn-back">
                                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                                    Home
                                </a>
                                <button type="submit" class="btn-nav btn-next">
                                    Next
                                    <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                                </button>
                            </div>

                        @elseif($step == 2)
                            <!-- STEP 2: Content Selection -->
                            <h2 class="form-section-title">Letter Content</h2>
                            <p class="section-note">
                                Choose how the letter should be drafted. After this step, you will only review and confirm
                                your request before submission.
                            </p>

                            @php
                                $templateMode = $formConfig['templateMode'] ?? 'student_choice';
                                $allowCustom = $formConfig['allowCustomContent'] ?? true;
                                $showOptions = $templateMode === 'student_choice' && $allowCustom;
                                $selectedContentOption = old('data.content_option', $formData['content_option'] ?? ($templateMode === 'custom_only' ? 'custom' : 'template'));
                                $selectedTemplateId = old('data.template_id', $formData['template_id'] ?? ($templateMode === 'admin_fixed' && $templates->count() ? $templates->first()->id : ''));
                                if ($templateMode === 'student_choice' && !$selectedTemplateId && $templates->count() === 1) {
                                    $selectedTemplateId = $templates->first()->id;
                                }
                            @endphp

                            @if($showOptions)
                                <div class="form-group">
                                    <label class="form-label">How would you like the letter to be drafted?</label>
                                    <div class="form-grid" style="margin-top: 0.75rem;">
                                        <div class="content-option {{ $selectedContentOption == 'template' ? 'selected' : '' }}"
                                            onclick="selectContentOption('template')">
                                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                                <div class="radio-circle"></div>
                                                <span class="option-title">Use Professional Template</span>
                                            </div>
                                            <p class="option-desc">Select from our verified templates</p>
                                        </div>

                                        <div class="content-option {{ $selectedContentOption == 'custom' ? 'selected' : '' }}"
                                            onclick="selectContentOption('custom')">
                                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                                <div class="radio-circle"></div>
                                                <span class="option-title">Custom Content</span>
                                            </div>
                                            <p class="option-desc">Write your own requirements</p>
                                        </div>
                                    </div>
                                    <input type="hidden" name="data[content_option]" id="contentOption"
                                        value="{{ $selectedContentOption }}">
                                </div>
                            @else
                                <!-- Force mode based on admin settings -->
                                @if($templateMode === 'custom_only')
                                    <input type="hidden" name="data[content_option]" id="contentOption" value="custom">
                                @else
                                    <input type="hidden" name="data[content_option]" id="contentOption" value="template">
                                @endif
                            @endif

                            <!-- Template Selection (shown for student_choice or admin_fixed modes) -->
                            @if($templateMode !== 'custom_only')
                                <div id="templateSection"
                                    style="{{ $selectedContentOption == 'template' || !$showOptions ? '' : 'display: none;' }}">
                                    <div class="form-group">
                                        <label class="form-label">
                                            @if($templateMode === 'admin_fixed')
                                                Assigned Template
                                            @else
                                                Select Template <span class="required">*</span>
                                            @endif
                                        </label>
                                        <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 0.75rem;">
                                            @forelse($templates as $template)
                                                <div class="template-option {{ (string) $selectedTemplateId === (string) $template->id ? 'selected' : '' }} {{ $templateMode === 'admin_fixed' ? 'admin-fixed' : '' }}"
                                                    @if($templateMode !== 'admin_fixed') onclick="selectTemplate(event, {{ $template->id }})"
                                                    @endif>
                                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                                        <span class="template-name">{{ $template->name }}</span>
                                                        <span class="template-lang">{{ strtoupper($template->language) }}</span>
                                                    </div>
                                                </div>
                                            @empty
                                                <p style="color: #ef4444; font-size: 0.875rem;">No templates available. Please contact the
                                                    administrator.</p>
                                            @endforelse
                                        </div>
                                        <input type="hidden" name="data[template_id]" id="templateId"
                                            value="{{ $selectedTemplateId }}">
                                        @error('template')
                                            <span class="field-error">{{ $message }}</span>
                                        @else
                                            <span class="field-help">
                                                {{ $templateMode === 'admin_fixed'
                                                    ? 'This template is assigned by administration for all requests.'
                                                    : 'Choose the template that best matches the recommendation style you need.' }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            <!-- Custom Content (shown only if allowed) -->
                            @if($allowCustom)
                                <div id="customSection"
                                    style="{{ $selectedContentOption == 'custom' || $templateMode === 'custom_only' ? '' : 'display: none;' }}">
                                    <div class="form-group">
                                        <label class="form-label">Custom Content <span class="required">*</span></label>
                                        <textarea name="data[custom_content]" class="form-textarea @error('custom_content') error @enderror" rows="6"
                                            placeholder="Write the requirements or points you want included in your recommendation letter...">{{ old('data.custom_content', $formData['custom_content'] ?? '') }}</textarea>
                                        @error('custom_content')
                                            <span class="field-error">{{ $message }}</span>
                                        @else
                                            <span class="field-help">Describe the focus, strengths, or experiences you want highlighted in the letter.</span>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            <div class="form-nav">
                                <button type="submit" name="action" value="back" class="btn-nav btn-back"
                                    onclick="document.getElementById('formAction').value='back'">
                                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                                    Back
                                </button>
                                <button type="submit" class="btn-nav btn-next">
                                    Next
                                    <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                                </button>
                            </div>

                        @elseif($step == 3)
                            <!-- STEP 3: Review & Submit -->
                            <h2 class="form-section-title">Review & Submit</h2>
                            <p class="section-note">
                                Review everything below before you submit. If anything looks wrong, go back and update it
                                before sending your request.
                            </p>
                            @php
                                $trainingPeriodDisplay = '-';
                                $trainingPeriodRaw = $formData['training_period'] ?? null;
                                if (!empty($trainingPeriodRaw)) {
                                    try {
                                        $trainingPeriodDisplay = \Carbon\Carbon::parse($trainingPeriodRaw . '-01')->format('F, Y');
                                    } catch (\Exception $e) {
                                        $trainingPeriodDisplay = $trainingPeriodRaw;
                                    }
                                }
                                $purposeDisplay = $formData['purpose'] ?? '-';
                                if (($formData['purpose'] ?? null) === 'Other' && !empty($formData['purpose_other'])) {
                                    $purposeDisplay = 'Other: ' . $formData['purpose_other'];
                                }
                                $deadlineDisplay = !empty($formData['deadline'])
                                    ? \Carbon\Carbon::parse($formData['deadline'])->format('M d, Y')
                                    : '-';
                                $selectedTemplate = $templates->firstWhere('id', (int) ($formData['template_id'] ?? 0));
                                $contentOption = $formData['content_option'] ?? 'template';
                                $contentSummary = $contentOption === 'template'
                                    ? ($selectedTemplate ? $selectedTemplate->name : 'Professional Template')
                                    : 'Custom Content';
                            @endphp

                            <div class="review-callout">
                                Step 3 is read-only. Use <strong>Back</strong> if you want to change your request details or
                                letter content before submitting.
                            </div>

                            <div class="summary-box">
                                <div class="summary-row">
                                    <span class="summary-label">Full Name</span>
                                    <span class="summary-value">{{ trim(($formData['student_name'] ?? '') . ' ' . ($formData['middle_name'] ?? '') . ' ' . ($formData['last_name'] ?? '')) ?: '-' }}</span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Email</span>
                                    <span class="summary-value">{{ $formData['student_email'] ?? '-' }}</span>
                                </div>
                                @if(!empty($formData['gender']))
                                    <div class="summary-row">
                                        <span class="summary-label">Gender</span>
                                        <span class="summary-value">{{ ucfirst($formData['gender']) }}</span>
                                    </div>
                                @endif
                                <div class="summary-row">
                                    <span class="summary-label">University</span>
                                    <span class="summary-value">{{ $formData['university'] ?? '-' }}</span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Student / National ID</span>
                                    <span class="summary-value">{{ $formData['verification_token'] ?? '-' }}</span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Training Period</span>
                                    <span class="summary-value">{{ $trainingPeriodDisplay }}</span>
                                </div>
                                @if(!empty($formData['major']))
                                    <div class="summary-row">
                                        <span class="summary-label">Major / Field of Study</span>
                                        <span class="summary-value">{{ $formData['major'] }}</span>
                                    </div>
                                @endif
                                @if(!empty($formData['phone']))
                                    <div class="summary-row">
                                        <span class="summary-label">Phone Number</span>
                                        <span class="summary-value">{{ $formData['phone'] }}</span>
                                    </div>
                                @endif
                                <div class="summary-row">
                                    <span class="summary-label">Purpose</span>
                                    <span class="summary-value">{{ $purposeDisplay }}</span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Deadline</span>
                                    <span class="summary-value">{{ $deadlineDisplay }}</span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Letter Content</span>
                                    <span class="summary-value" style="color: #667eea;">{{ $contentSummary }}</span>
                                </div>
                            </div>

                            @if(!empty($formData['notes']))
                                <div class="summary-card">
                                    <h3 class="summary-card-title">Additional Notes</h3>
                                    <p class="summary-card-copy">{{ $formData['notes'] }}</p>
                                </div>
                            @endif

                            @if($contentOption === 'custom' && !empty($formData['custom_content']))
                                <div class="summary-card">
                                    <h3 class="summary-card-title">Custom Content</h3>
                                    <p class="summary-card-copy">{{ $formData['custom_content'] }}</p>
                                </div>
                            @endif

                            <div class="form-nav">
                                <button type="submit" name="action" value="back" class="btn-nav btn-back"
                                    onclick="document.getElementById('formAction').value='back'">
                                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                                    Back
                                </button>
                                <button type="submit" name="action" value="submit" class="btn-nav btn-submit"
                                    onclick="document.getElementById('formAction').value='submit'">
                                    <i data-lucide="send" style="width: 16px; height: 16px;"></i>
                                    {{ $isEditMode ? 'Submit Revisions' : ($settings['requestSubmitBtn'] ?? 'Submit Request') }}
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            @endif


        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@26.0.9/build/js/intlTelInput.min.js"></script>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        document.addEventListener('DOMContentLoaded', function () {
            const input = document.querySelector("#phone");
            if (input) {
                const iti = window.intlTelInput(input, {
                    initialCountry: "sa",
                    preferredCountries: ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                    separateDialCode: true,
                    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@26.0.9/build/js/utils.js",
                });

                // Set initial phone number if available
                const phoneInput = document.querySelector('input[name="data[phone]"]');
                if (phoneInput && phoneInput.value) {
                    iti.setNumber(phoneInput.value);
                }

                // Update input value with full number on form submit
                const form = document.querySelector("#wizardForm");
                if (form) {
                    form.addEventListener('submit', function () {
                        if (iti.isValidNumber()) {
                            input.value = iti.getNumber();
                        }
                    });
                }
            }

            updatePurposeOtherVisibility();

            const purposeSelect = document.querySelector('select[name="data[purpose]"]');
            if (purposeSelect) {
                purposeSelect.addEventListener('change', updatePurposeOtherVisibility);
            }
        });

        function selectContentOption(option) {
            // Visual update
            document.querySelectorAll('.content-option').forEach(el => el.classList.remove('selected'));
            document.querySelector(`.content-option[onclick="selectContentOption('${option}')"]`).classList.add('selected');

            // Update hidden input
            document.getElementById('contentOption').value = option;

            if (option === 'template') {
                document.getElementById('templateSection').style.display = 'block';
                document.getElementById('customSection').style.display = 'none';
            } else {
                document.getElementById('templateSection').style.display = 'none';
                document.getElementById('customSection').style.display = 'block';
            }

            // Reinitialize icons
            lucide.createIcons();
        }

        function selectTemplate(event, id) {
            document.getElementById('templateId').value = id;
            document.querySelectorAll('.template-option').forEach(el => el.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
        }

        function updatePurposeOtherVisibility() {
            const purposeSelect = document.querySelector('select[name="data[purpose]"]');
            const purposeOtherGroup = document.getElementById('purposeOtherGroup');
            const purposeOtherInput = document.querySelector('input[name="data[purpose_other]"]');

            if (!purposeSelect || !purposeOtherGroup || !purposeOtherInput) {
                return;
            }

            const shouldShow = purposeSelect.value === 'Other';
            purposeOtherGroup.style.display = shouldShow ? '' : 'none';
            purposeOtherInput.required = shouldShow;

            if (!shouldShow) {
                purposeOtherInput.value = '';
            }
        }

        // Copy Tracking ID
        function copyTrackingId() {
            const id = document.getElementById('trackingIdDisplay').innerText;
            navigator.clipboard.writeText(id).then(() => {
                // Ideally show a toast here, but for now we can change the icon or color temporarily
                const btn = document.querySelector('.copy-btn');
                const originalColor = btn.style.color;
                btn.style.color = '#10b981'; // Green
                setTimeout(() => btn.style.color = originalColor, 2000);

                // If you have a toast function:
                // showToast('Tracking ID copied!', 'success');
            });
        }

        // Loading State for Buttons
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function (e) {
                // Client-side validation shake
                const inputs = this.querySelectorAll('input[required], select[required], textarea[required]');
                let isValid = true;

                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('shake');
                        input.addEventListener('animationend', () => {
                            input.classList.remove('shake');
                        });
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    // showToast('Please fill in all required fields.', 'error'); // If toast exists
                    return;
                }

                const btn = this.querySelector('button[type="submit"]');
                if (btn) {
                    btn.classList.add('btn-loading');
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '<span class="spinner"></span> <span class="btn-text">' + originalText + '</span>';
                    btn.disabled = true;
                }
            });
        });
    </script>
@endsection
