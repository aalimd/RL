@extends('layouts.app')

@section('title', $settings['trackingTitle'] ?? 'Track Your Request')

@section('styles')
    <style>
        .tracking-page {
            min-height: 100vh;
            padding: clamp(1.5rem, 3vw, 2.5rem) 1rem 3rem;
            background:
                linear-gradient(180deg, rgba(var(--primary-rgb), 0.04), transparent 18%),
                var(--bg-primary);
        }

        .tracking-shell {
            width: 100%;
            max-width: 720px;
            margin: 0 auto;
        }

        .tracking-head {
            margin-bottom: 1.1rem;
        }

        .tracking-head h1 {
            margin: 0.8rem 0 0.45rem;
            font-family: var(--font-heading);
            font-size: clamp(1.95rem, 4vw, 2.65rem);
            line-height: 1.05;
            letter-spacing: -0.045em;
            color: var(--text-primary);
            font-weight: 800;
        }

        .tracking-head p {
            margin: 0;
            max-width: 36rem;
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.6;
        }

        .tracking-label {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            border: 1px solid rgba(var(--primary-rgb), 0.14);
            background: rgba(var(--primary-rgb), 0.08);
            color: var(--primary);
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .tracking-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: calc(var(--border-radius) + 0.5rem);
            box-shadow: var(--shadow-lg);
            padding: 1.5rem;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .alert-box {
            display: flex;
            align-items: flex-start;
            gap: 0.7rem;
            padding: 0.95rem 1rem;
            border-radius: 1rem;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            line-height: 1.55;
        }

        .alert-box.success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.16);
            color: #047857;
        }

        .alert-box.error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #dc2626;
        }

        .search-form,
        .action-stack,
        .result-stack {
            display: grid;
            gap: 1rem;
        }

        .form-group {
            display: grid;
            gap: 0.5rem;
        }

        .form-label {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .form-input {
            width: 100%;
            padding: 1rem 1.05rem;
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            font-size: 1rem;
            background: var(--input-bg);
            color: var(--text-primary);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.12);
        }

        .form-input.error {
            border-color: #ef4444;
        }

        .field-help,
        .field-error,
        .card-note {
            font-size: 0.88rem;
            line-height: 1.5;
        }

        .field-help,
        .card-note {
            color: var(--text-muted);
        }

        .field-error {
            color: #ef4444;
            font-weight: 600;
        }

        .card-note {
            padding: 0.9rem 1rem;
            border-radius: var(--border-radius);
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: rgba(var(--primary-rgb), 0.04);
        }

        .card-note strong {
            color: var(--text-primary);
        }

        .track-btn,
        .primary-action,
        .secondary-action {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            padding: 1rem 1.1rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 700;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }

        .track-btn,
        .primary-action {
            border: none;
            background: var(--btn-gradient);
            color: white;
            box-shadow: var(--shadow-md);
            cursor: pointer;
        }

        .track-btn:hover,
        .primary-action:hover {
            transform: translateY(-1px);
            color: white;
            box-shadow: var(--shadow-lg);
        }

        .secondary-action {
            border: 1px solid var(--border-color);
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .secondary-action:hover {
            transform: translateY(-1px);
            border-color: var(--primary);
            color: var(--primary);
        }

        .result-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .result-title-block {
            display: grid;
            gap: 0.45rem;
        }

        .result-kicker,
        .result-panel-label,
        .meta-label,
        .history-label {
            display: block;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .result-headline {
            margin: 0;
            font-family: var(--font-heading);
            font-size: clamp(1.65rem, 3vw, 2.15rem);
            line-height: 1.1;
            letter-spacing: -0.04em;
            color: var(--text-primary);
            font-weight: 800;
        }

        .result-description {
            margin: 0;
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.6;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.55rem 0.9rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
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
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .status-review {
            background: rgba(139, 92, 246, 0.15);
            color: #8b5cf6;
            border: 1px solid rgba(139, 92, 246, 0.2);
        }

        .result-panel {
            padding: 1rem 1.05rem;
            border-radius: var(--border-radius);
            border: 1px solid rgba(148, 163, 184, 0.16);
            background: rgba(var(--bg-secondary-rgb), 0.46);
        }

        .result-panel p {
            margin: 0.45rem 0 0;
            color: var(--text-primary);
            font-size: 0.97rem;
            line-height: 1.6;
        }

        .admin-message-card {
            position: relative;
            display: grid;
            gap: 0.9rem;
            padding: 1.15rem 1.15rem 1.15rem 1.35rem;
            border-radius: calc(var(--border-radius) + 0.15rem);
            border: 1px solid rgba(var(--primary-rgb), 0.18);
            background: linear-gradient(180deg, rgba(var(--primary-rgb), 0.1), rgba(var(--bg-secondary-rgb), 0.52));
            overflow: hidden;
        }

        .admin-message-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 4px;
            background: var(--primary);
        }

        .admin-message-card.is-approved {
            border-color: rgba(16, 185, 129, 0.24);
            background: linear-gradient(180deg, rgba(16, 185, 129, 0.12), rgba(var(--bg-secondary-rgb), 0.52));
        }

        .admin-message-card.is-approved::before {
            background: #10b981;
        }

        .admin-message-card.is-rejected {
            border-color: rgba(239, 68, 68, 0.24);
            background: linear-gradient(180deg, rgba(239, 68, 68, 0.12), rgba(var(--bg-secondary-rgb), 0.52));
        }

        .admin-message-card.is-rejected::before {
            background: #ef4444;
        }

        .admin-message-card.is-revision {
            border-color: rgba(245, 158, 11, 0.24);
            background: linear-gradient(180deg, rgba(245, 158, 11, 0.13), rgba(var(--bg-secondary-rgb), 0.52));
        }

        .admin-message-card.is-revision::before {
            background: #f59e0b;
        }

        .admin-message-card.is-review {
            border-color: rgba(139, 92, 246, 0.22);
            background: linear-gradient(180deg, rgba(139, 92, 246, 0.12), rgba(var(--bg-secondary-rgb), 0.52));
        }

        .admin-message-card.is-review::before {
            background: #8b5cf6;
        }

        .admin-message-head {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
        }

        .admin-message-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: calc(var(--border-radius) + 0.05rem);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(var(--primary-rgb), 0.12);
            color: var(--primary);
        }

        .admin-message-card.is-approved .admin-message-icon {
            background: rgba(16, 185, 129, 0.14);
            color: #10b981;
        }

        .admin-message-card.is-rejected .admin-message-icon {
            background: rgba(239, 68, 68, 0.14);
            color: #ef4444;
        }

        .admin-message-card.is-revision .admin-message-icon {
            background: rgba(245, 158, 11, 0.14);
            color: #d97706;
        }

        .admin-message-card.is-review .admin-message-icon {
            background: rgba(139, 92, 246, 0.14);
            color: #8b5cf6;
        }

        .admin-message-copy {
            display: grid;
            gap: 0.2rem;
        }

        .admin-message-eyebrow {
            display: block;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .admin-message-title {
            margin: 0;
            color: var(--text-primary);
            font-size: 1.02rem;
            font-weight: 800;
            line-height: 1.3;
        }

        .admin-message-body {
            margin: 0;
            color: var(--text-primary);
            font-size: 1rem;
            line-height: 1.7;
            white-space: pre-line;
            word-break: break-word;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .meta-card {
            padding: 0.95rem 1rem;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            background: rgba(var(--bg-secondary-rgb), 0.55);
        }

        .meta-value {
            margin: 0.3rem 0 0;
            color: var(--text-primary);
            font-size: 0.98rem;
            font-weight: 700;
            line-height: 1.45;
        }

        .meta-value.mono,
        .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        .history-card {
            border: 1px solid var(--border-color);
            border-radius: calc(var(--border-radius) + 0.2rem);
            background: rgba(var(--bg-secondary-rgb), 0.55);
            overflow: hidden;
        }

        .history-card summary {
            list-style: none;
            cursor: pointer;
            padding: 1rem 1.1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            color: var(--text-primary);
            font-size: 0.97rem;
            font-weight: 700;
        }

        .history-card summary::-webkit-details-marker {
            display: none;
        }

        .history-card summary::after {
            content: '+';
            color: var(--text-muted);
            font-size: 1.2rem;
            line-height: 1;
        }

        .history-card[open] summary::after {
            content: '-';
        }

        .history-list {
            padding: 0 1.1rem 1.1rem;
            display: grid;
            gap: 0.9rem;
        }

        .history-item {
            display: grid;
            gap: 0.3rem;
            padding-top: 0.9rem;
            border-top: 1px solid rgba(148, 163, 184, 0.14);
        }

        .history-item:first-child {
            border-top: none;
            padding-top: 0.1rem;
        }

        .history-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.8rem;
        }

        .history-title {
            margin: 0;
            color: var(--text-primary);
            font-size: 0.98rem;
            font-weight: 700;
        }

        .history-text {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.94rem;
            line-height: 1.55;
        }

        .history-meta {
            color: var(--text-muted);
            font-size: 0.88rem;
            font-weight: 600;
        }

        .history-state {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .history-state.done {
            background: rgba(16, 185, 129, 0.12);
            color: #10b981;
        }

        .history-state.active {
            background: rgba(59, 130, 246, 0.12);
            color: #3b82f6;
        }

        .history-state.attention {
            background: rgba(245, 158, 11, 0.14);
            color: #d97706;
        }

        .history-state.pending {
            background: rgba(148, 163, 184, 0.15);
            color: var(--text-muted);
        }

        .tracking-links {
            margin-top: 1.15rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            flex-wrap: wrap;
        }

        .link-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.94rem;
            transition: color 0.2s ease;
        }

        .link-btn:hover {
            color: var(--primary);
        }

        @media (max-width: 640px) {
            .tracking-page {
                padding-top: 1.2rem;
            }

            .tracking-card {
                padding: 1.2rem;
                border-radius: calc(var(--border-radius) + 0.25rem);
            }

            .tracking-head h1 {
                font-size: 1.8rem;
            }

            .result-header,
            .history-title-row,
            .tracking-links {
                flex-direction: column;
                align-items: flex-start;
            }

            .meta-grid {
                grid-template-columns: 1fr;
            }

            .status-chip {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $hasRequest = isset($request) && $request;
        $trustedDeviceActive = $trustedDeviceActive ?? false;
    @endphp

    <div class="tracking-page">
        <div class="tracking-shell">
            @if(!$hasRequest)
                <div class="tracking-head">
                    <span class="tracking-label">Student tracker</span>
                    <h1>{{ $settings['trackingTitle'] ?? 'Track Your Request' }}</h1>
                    <p>Enter your Tracking ID and Student / National ID to check your request status.</p>
                </div>

                <div class="tracking-card">
                    @if(session('success'))
                        <div class="alert-box success">
                            <i data-lucide="check-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert-box error">
                            <i data-lucide="alert-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ url('/track') }}" class="search-form">
                        @csrf

                        <div class="form-group">
                            <label class="form-label" for="trackingId">Tracking ID</label>
                            <input type="text" id="trackingId" name="trackingId"
                                class="form-input @error('trackingId') error @enderror"
                                placeholder="REC-2026-AB12CD34"
                                value="{{ strtoupper((string) old('trackingId', $id ?? '')) }}" required maxlength="17"
                                autocapitalize="characters" autocomplete="off" spellcheck="false">
                            @if($errors->has('trackingId'))
                                <span class="field-error">{{ $errors->first('trackingId') }}</span>
                            @else
                                <span class="field-help">Example: REC-2026-AB12CD34</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="verificationToken">Student / National ID</label>
                            <input type="text" id="verificationToken" name="verificationToken"
                                class="form-input @error('verificationToken') error @enderror"
                                placeholder="Enter the same ID used in your request"
                                value="{{ old('verificationToken') }}" required maxlength="100" autocomplete="off">
                            @if($errors->has('verificationToken'))
                                <span class="field-error">{{ $errors->first('verificationToken') }}</span>
                            @endif
                        </div>

                        <p class="card-note">
                            <strong>Privacy:</strong> We’ll email a 6-digit verification code before showing your request details.
                        </p>

                        <button type="submit" class="track-btn">
                            <span>{{ $settings['trackingSearchBtn'] ?? 'Track Request' }}</span>
                            <i data-lucide="arrow-right" style="width: 20px; height: 20px;"></i>
                        </button>
                    </form>
                </div>

                <div class="tracking-links">
                    <a href="{{ url('/') }}" class="link-btn">
                        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                        Back to Home
                    </a>
                </div>
            @endif

            @if($hasRequest)
                @php
                    $statusBadgeClass = 'status-pending';
                    if ($request->status === 'Approved') {
                        $statusBadgeClass = 'status-approved';
                    } elseif ($request->status === 'Rejected') {
                        $statusBadgeClass = 'status-rejected';
                    } elseif ($request->status === 'Needs Revision') {
                        $statusBadgeClass = 'status-revision';
                    } elseif ($request->status === 'Under Review') {
                        $statusBadgeClass = 'status-review';
                    }

                    $studentStatusMessage = $request->status === 'Rejected'
                        ? ($request->rejection_reason ?? $request->admin_message)
                        : ($request->admin_message ?? $request->rejection_reason);

                    $studentMessageLabel = 'Message from administration';
                    $studentMessageTitle = 'A note from administration';

                    $summaryTone = 'pending';
                    $summaryHeadline = 'We received your request.';
                    $summaryDescription = 'Your request is in the queue and will move into review soon.';
                    $summaryNextStep = 'No action is needed yet. Keep your Tracking ID handy and check back for updates.';

                    if ($request->status === 'Under Review') {
                        $summaryTone = 'review';
                        $summaryHeadline = 'Your request is under review.';
                        $summaryDescription = 'Administration is checking your information and supporting details now.';
                        $summaryNextStep = 'No action is needed from you right now. Wait for another status update or message from administration.';
                    } elseif ($request->status === 'Needs Revision') {
                        $summaryTone = 'revision';
                        $summaryHeadline = 'Your request needs updates before it can continue.';
                        $summaryDescription = 'Administration reviewed your details and asked for corrections or missing information.';
                        $summaryNextStep = 'Use the edit button below, update the requested details, and resubmit your request.';
                    } elseif ($request->status === 'Approved') {
                        $summaryTone = 'approved';
                        $summaryHeadline = 'Your recommendation letter is ready.';
                        $summaryDescription = 'Your request has been approved and your official letter is now available.';
                        $summaryNextStep = 'Open your letter, review it, and download the PDF if you need a copy.';
                    } elseif ($request->status === 'Rejected') {
                        $summaryTone = 'rejected';
                        $summaryHeadline = 'This request was not approved.';
                        $summaryDescription = 'Administration recorded a final decision for this request.';
                        $summaryNextStep = 'Review the reason below. If anything is unclear, contact administration before creating a new request.';
                    }

                    $adminMessageTone = $summaryTone;
                    if ($request->status === 'Needs Revision') {
                        $adminMessageTone = 'revision';
                        $studentMessageLabel = 'Requested changes';
                        $studentMessageTitle = 'Please update your request';
                    } elseif ($request->status === 'Rejected') {
                        $adminMessageTone = 'rejected';
                        $studentMessageLabel = 'Decision note';
                        $studentMessageTitle = 'Reason provided by administration';
                    } elseif ($request->status === 'Under Review') {
                        $adminMessageTone = 'review';
                        $studentMessageTitle = 'Update from administration';
                    } elseif ($request->status === 'Approved') {
                        $adminMessageTone = 'approved';
                        $studentMessageTitle = 'Final note from administration';
                    }

                    $verifiedRequestId = session('tracking_verified_request_id');
                    $verifiedTrackingId = session('tracking_verified_tracking_id');
                    $verifiedUntil = (int) session('tracking_verified_until', 0);
                    $hasVerifiedSession = $verifiedRequestId
                        && $verifiedTrackingId
                        && $verifiedUntil >= now()->timestamp
                        && (int) $verifiedRequestId === (int) $request->id
                        && (string) $verifiedTrackingId === (string) $request->tracking_id;
                    $canEditRequest = $request->status === 'Needs Revision' && $hasVerifiedSession;

                    $reviewState = 'Pending';
                    $reviewTone = 'pending';
                    $reviewDescription = 'Checking your information and requirements.';
                    $reviewMeta = 'Waiting';

                    if ($request->status === 'Under Review') {
                        $reviewState = 'In Progress';
                        $reviewTone = 'active';
                        $reviewMeta = 'In Progress';
                    } elseif ($request->status === 'Needs Revision') {
                        $reviewState = 'Action Required';
                        $reviewTone = 'attention';
                        $reviewDescription = 'We reviewed your request and sent it back with requested changes.';
                        $reviewMeta = 'Action Required';
                    } elseif (in_array($request->status, ['Approved', 'Rejected'], true)) {
                        $reviewState = 'Completed';
                        $reviewTone = 'done';
                        $reviewMeta = 'Completed';
                    }

                    $decisionState = in_array($request->status, ['Approved', 'Rejected'], true) ? 'Completed' : 'Pending';
                    $decisionTone = in_array($request->status, ['Approved', 'Rejected'], true) ? 'done' : 'pending';
                    $decisionDescription = 'Awaiting final approval.';

                    if ($request->status === 'Approved') {
                        $decisionDescription = 'Congratulations! Your letter is ready.';
                    } elseif ($request->status === 'Rejected') {
                        $decisionDescription = 'Request declined. See admin message.';
                    }
                @endphp

                <div class="tracking-head">
                    <span class="tracking-label">Verified request</span>
                    <p>Tracking ID <span class="mono">{{ $request->tracking_id }}</span></p>
                </div>

                <div class="tracking-card">
                    <div class="result-stack">
                        <div class="result-header">
                            <div class="result-title-block">
                                <span class="result-kicker">Current status</span>
                                <h1 class="result-headline">{{ $summaryHeadline }}</h1>
                                <p class="result-description">{{ $summaryDescription }}</p>
                            </div>
                            <span class="status-chip {{ $statusBadgeClass }}">{{ $request->status }}</span>
                        </div>

                        <div class="result-panel">
                            <span class="result-panel-label">What happens next</span>
                            <p>{{ $summaryNextStep }}</p>
                        </div>

                        @if($studentStatusMessage)
                            <div class="admin-message-card is-{{ $adminMessageTone }}">
                                <div class="admin-message-head">
                                    <div class="admin-message-icon">
                                        <i data-lucide="message-square" style="width: 18px; height: 18px;"></i>
                                    </div>
                                    <div class="admin-message-copy">
                                        <span class="admin-message-eyebrow">{{ $studentMessageLabel }}</span>
                                        <h2 class="admin-message-title">{{ $studentMessageTitle }}</h2>
                                    </div>
                                </div>
                                <p class="admin-message-body">{{ $studentStatusMessage }}</p>
                            </div>
                        @endif

                        @if($canEditRequest)
                            <div class="action-stack">
                                <form method="POST" action="{{ route('public.request.edit') }}">
                                    @csrf
                                    <input type="hidden" name="tracking_id" value="{{ $request->tracking_id }}">
                                    <button type="submit" class="primary-action">
                                        <i data-lucide="square-pen" style="width: 18px; height: 18px;"></i>
                                        Edit Request
                                    </button>
                                </form>
                            </div>
                        @elseif($request->status === 'Approved')
                            <div class="action-stack">
                                <a href="{{ route('public.letter', ['tracking_id' => $request->tracking_id]) }}"
                                    class="primary-action" target="_blank">
                                    <i data-lucide="eye" style="width: 18px; height: 18px;"></i>
                                    Review Official Letter
                                </a>
                            </div>
                        @endif

                        <div class="meta-grid">
                            <div class="meta-card">
                                <span class="meta-label">Tracking ID</span>
                                <p class="meta-value mono">{{ $request->tracking_id }}</p>
                            </div>
                            <div class="meta-card">
                                <span class="meta-label">Submitted</span>
                                <p class="meta-value">{{ $request->created_at->format('M d, Y') }}</p>
                            </div>
                            <div class="meta-card">
                                <span class="meta-label">Last updated</span>
                                <p class="meta-value">{{ $request->updated_at->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <details class="history-card">
                            <summary>
                                <span>View request history</span>
                                <span class="history-label">3 updates</span>
                            </summary>
                            <div class="history-list">
                                <div class="history-item">
                                    <div class="history-title-row">
                                        <h3 class="history-title">Request Submitted</h3>
                                        <span class="history-state done">Completed</span>
                                    </div>
                                    <p class="history-text">Your request has been received.</p>
                                    <span class="history-meta">{{ $request->created_at->format('M d, Y h:i A') }}</span>
                                </div>

                                <div class="history-item">
                                    <div class="history-title-row">
                                        <h3 class="history-title">Details Review</h3>
                                        <span class="history-state {{ $reviewTone }}">{{ $reviewState }}</span>
                                    </div>
                                    <p class="history-text">{{ $reviewDescription }}</p>
                                    <span class="history-meta">{{ $reviewMeta }}</span>
                                </div>

                                <div class="history-item">
                                    <div class="history-title-row">
                                        <h3 class="history-title">Final Decision</h3>
                                        <span class="history-state {{ $decisionTone }}">{{ $decisionState }}</span>
                                    </div>
                                    <p class="history-text">{{ $decisionDescription }}</p>
                                    <span class="history-meta">
                                        @if(in_array($request->status, ['Approved', 'Rejected'], true))
                                            {{ $request->updated_at->format('M d, Y') }}
                                        @else
                                            Waiting
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </details>

                        @if($trustedDeviceActive)
                            <form method="POST" action="{{ route('public.tracking.verify.forget-browser') }}">
                                @csrf
                                <input type="hidden" name="tracking_id" value="{{ $request->tracking_id }}">
                                <button type="submit" class="secondary-action">
                                    <i data-lucide="shield-off" style="width: 18px; height: 18px;"></i>
                                    Require a code on this browser
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="tracking-links">
                        <a href="{{ route('public.tracking') }}" class="link-btn">
                            <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                            Track another request
                        </a>

                        <a href="{{ url('/') }}" class="link-btn">
                            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                            Back to Home
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
