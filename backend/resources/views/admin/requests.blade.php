@extends('layouts.admin')

@section('page-title', 'Requests')

@section('styles')
    <style>
        .filters-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 1px solid var(--border-color);
            background: var(--input-bg);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-main);
        }

        .search-box svg {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            width: 18px;
            height: 18px;
        }

        .status-filters {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .advanced-filters {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: center;
            margin-left: auto;
        }

        .filter-input {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            min-width: 160px;
            background: var(--input-bg);
            color: var(--text-main);
        }

        .btn-reset {
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            background: transparent;
            color: var(--text-main);
            cursor: pointer;
        }

        .status-btn {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            border: 1px solid var(--border-color);
            background: transparent;
            color: var(--text-main);
            cursor: pointer;
            transition: all 0.2s;
        }

        .status-btn:hover {
            background: var(--bg-color);
        }

        .status-btn.active {
            background: var(--primary, #4f46e5);
            color: white;
            border-color: var(--primary, #4f46e5);
        }

        .results-info {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        .table-container {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: var(--bg-color);
            padding: 0.75rem 1rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.875rem;
        }

        .data-table tbody tr:hover {
            background: var(--bg-color);
        }

        .tracking-id {
            font-family: monospace;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary, #4f46e5);
            background: rgba(var(--primary-rgb, 79, 70, 229), 0.1);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        .student-info .name {
            font-weight: 500;
            color: var(--text-main);
        }

        .student-info .email {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .student-info .university {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .actions-cell {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            padding: 0.5rem;
            border-radius: 0.375rem;
            border: none;
            background: transparent;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-btn:hover {
            background: var(--bg-color);
        }

        .action-btn.view {
            color: var(--primary, #4f46e5);
        }

        .action-btn.approve {
            color: var(--success-text);
        }

        .action-btn.reject {
            color: var(--error-text);
        }

        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-top: 1px solid var(--border-color);
            background: transparent;
        }

        .pagination-info {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .pagination-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .pagination-buttons a,
        .pagination-buttons span {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            font-size: 0.875rem;
            text-decoration: none;
            color: var(--text-main);
            background: var(--card-bg);
        }

        .pagination-buttons a:hover {
            background: var(--bg-color);
        }

        .pagination-buttons .current {
            background: var(--primary, #4f46e5);
            color: white;
            border-color: var(--primary, #4f46e5);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }

        .empty-state svg {
            width: 48px;
            height: 48px;
            margin: 0 auto 1rem;
            opacity: 0.5;
        }

        .bulk-action-bar {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #1f2937;
            color: white;
            padding: 1rem 2rem;
            border-radius: 9999px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            z-index: 50;
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .bulk-action-bar.visible {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        .count-badge {
            background: #374151;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .bulk-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .bulk-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .bulk-btn.danger:hover {
            background: #ef4444;
            border-color: #ef4444;
        }

        .bulk-selection-meta {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .bulk-selection-meta small {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.75rem;
        }

        .alert-stack {
            display: grid;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .alert-box {
            padding: 1rem 1.125rem;
            border-radius: 0.85rem;
            border: 1px solid var(--border-color);
            background: var(--card-bg);
            color: var(--text-main);
        }

        .alert-box.success {
            border-color: var(--success-border);
            background: var(--success-bg);
            color: var(--success-text);
        }

        .alert-box.error {
            border-color: var(--error-border);
            background: var(--error-bg);
            color: var(--error-text);
        }

        .alert-box ul {
            margin: 0.5rem 0 0;
            padding-left: 1.25rem;
        }

        .bulk-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.68);
            backdrop-filter: blur(6px);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            z-index: 70;
        }

        .bulk-modal.visible {
            display: flex;
        }

        .bulk-modal-card {
            width: min(720px, 100%);
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 1.25rem;
            box-shadow: 0 30px 70px rgba(15, 23, 42, 0.28);
            overflow: hidden;
        }

        .bulk-modal-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .bulk-modal-header h3 {
            margin: 0 0 0.35rem;
            font-size: 1.15rem;
            color: var(--text-main);
        }

        .bulk-modal-header p {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.92rem;
            line-height: 1.55;
        }

        .bulk-modal-close {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 999px;
            border: 1px solid var(--border-color);
            background: transparent;
            color: var(--text-main);
            cursor: pointer;
            flex-shrink: 0;
        }

        .bulk-modal-body {
            padding: 1.5rem;
            display: grid;
            gap: 1.25rem;
        }

        .bulk-scope-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.875rem;
        }

        .bulk-scope-option {
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1rem;
            background: var(--bg-color);
            cursor: pointer;
            text-align: left;
            transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
        }

        .bulk-scope-option.active {
            border-color: var(--primary, #4f46e5);
            background: rgba(var(--primary-rgb, 79, 70, 229), 0.08);
            transform: translateY(-1px);
        }

        .bulk-scope-option .scope-label {
            display: block;
            color: var(--text-main);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .bulk-scope-option .scope-count {
            display: block;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.35rem;
        }

        .bulk-scope-option .scope-help {
            display: block;
            color: var(--text-muted);
            font-size: 0.84rem;
            line-height: 1.45;
        }

        .bulk-field label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-main);
        }

        .bulk-field select,
        .bulk-field textarea {
            width: 100%;
            border: 1px solid var(--border-color);
            background: var(--input-bg);
            color: var(--text-main);
            border-radius: 0.9rem;
            padding: 0.875rem 1rem;
            font-size: 0.94rem;
        }

        .bulk-field textarea {
            min-height: 110px;
            resize: vertical;
            line-height: 1.55;
        }

        .bulk-field .field-help {
            margin-top: 0.5rem;
            color: var(--text-muted);
            font-size: 0.83rem;
            line-height: 1.5;
        }

        .bulk-summary {
            border: 1px solid var(--border-color);
            background: var(--bg-color);
            border-radius: 1rem;
            padding: 1rem 1.125rem;
            color: var(--text-main);
            line-height: 1.6;
        }

        .bulk-summary strong {
            display: block;
            margin-bottom: 0.35rem;
        }

        .bulk-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding: 0 1.5rem 1.5rem;
        }

        /* Checkbox Style */
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        input[type="checkbox"] {
            width: 1.125rem;
            height: 1.125rem;
            border-radius: 0.25rem;
            border: 1px solid var(--border-color);
            cursor: pointer;
        }

        @media (max-width: 720px) {
            .bulk-action-bar {
                width: calc(100% - 2rem);
                border-radius: 1.25rem;
                padding: 1rem;
                left: 1rem;
                right: 1rem;
                transform: translateY(100px);
            }

            .bulk-action-bar.visible {
                transform: translateY(0);
            }

            .bulk-scope-grid {
                grid-template-columns: 1fr;
            }

            .bulk-modal-actions {
                flex-direction: column-reverse;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $canManageRequests = auth()->check() && in_array(auth()->user()->role, ['admin', 'editor'], true);
        $currentFilters = [
            'status' => request('status', 'All'),
            'search' => request('search'),
            'university' => request('university'),
            'date_from' => request('date_from'),
            'date_to' => request('date_to'),
        ];
        $hasActiveFilters = ($currentFilters['status'] ?? 'All') !== 'All'
            || filled($currentFilters['search'] ?? null)
            || filled($currentFilters['university'] ?? null)
            || filled($currentFilters['date_from'] ?? null)
            || filled($currentFilters['date_to'] ?? null);
        $bulkModalOpen = old('operation') === 'status' || $errors->has('status') || $errors->has('admin_message') || $errors->has('selection_scope') || $errors->has('operation');
    @endphp

    <!-- Filters Bar -->
    <div class="filters-bar">
        <div class="search-box">
            <i data-feather="search"></i>
            <form method="GET" action="{{ route('admin.requests') }}" id="searchForm">
                <input type="text" name="search" placeholder="Search by name, email, or tracking ID..."
                    value="{{ request('search') }}" onchange="document.getElementById('searchForm').submit()">
                <input type="hidden" name="status" value="{{ request('status', 'All') }}">
                <input type="hidden" name="university" value="{{ request('university') }}">
                <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                <input type="hidden" name="date_to" value="{{ request('date_to') }}">
            </form>
        </div>

        <div class="status-filters">
            @foreach(['All', 'Submitted', 'Under Review', 'Needs Revision', 'Approved', 'Rejected', 'Archived'] as $status)
                <a href="{{ route('admin.requests', ['status' => $status, 'search' => request('search'), 'university' => request('university'), 'date_from' => request('date_from'), 'date_to' => request('date_to')]) }}"
                    class="status-btn {{ request('status', 'All') === $status ? 'active' : '' }}">
                    {{ $status }}
                </a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('admin.requests') }}" class="advanced-filters" id="advancedFilters">
            <input type="hidden" name="status" value="{{ request('status', 'All') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input class="filter-input" type="text" name="university" placeholder="University"
                value="{{ request('university') }}">
            <input class="filter-input" type="date" name="date_from" value="{{ request('date_from') }}">
            <input class="filter-input" type="date" name="date_to" value="{{ request('date_to') }}">
            <button class="btn-reset" type="submit">Apply</button>
            <a class="btn-reset" href="{{ route('admin.requests') }}">Reset</a>
        </form>
    </div>

    @if(session('success') || session('error') || $errors->any())
        <div class="alert-stack">
            @if(session('success'))
                <div class="alert-box success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert-box error">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert-box error">
                    <strong>Could not complete the bulk action.</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <!-- Actions Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <div class="results-info" style="margin-bottom: 0;">
            Showing {{ $requests->firstItem() ?? 0 }} to {{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }}
            requests
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            @if($canManageRequests)
                <button type="button" class="btn btn-secondary" id="openExportDriveModal" style="gap: 0.5rem;">
                    <i data-feather="hard-drive" style="width: 16px; height: 16px;"></i>
                    Export to Google Drive
                </button>
                <button type="button" class="btn btn-secondary" id="openExportLettersModal" style="gap: 0.5rem;">
                    <i data-feather="file-down" style="width: 16px; height: 16px;"></i>
                    Export Letters PDF
                </button>
            @endif
            <a href="{{ route('admin.requests.export', request()->query()) }}" class="btn btn-primary" style="gap: 0.5rem;">
                <i data-feather="download" style="width: 16px; height: 16px;"></i>
                Export to Excel
            </a>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            @if($requests->isEmpty())
                <div class="empty-state">
                    <i data-feather="inbox"></i>
                    <h3 style="font-weight: 600; margin-bottom: 0.5rem;">No requests found</h3>
                    <p>Try adjusting your search or filters.</p>
                </div>
            @else
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                @if($canManageRequests)
                                    <th>
                                        <div class="checkbox-wrapper">
                                            <input type="checkbox" id="selectAll">
                                        </div>
                                    </th>
                                @endif
                                <th>Tracking ID</th>
                                <th>Student</th>
                                <th>Purpose</th>
                                <th>Status</th>
                                <th>Deadline</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                                <tr>
                                    @if($canManageRequests)
                                        <td>
                                            <div class="checkbox-wrapper">
                                                <input type="checkbox" name="ids[]" value="{{ $request->id }}" class="row-checkbox">
                                            </div>
                                        </td>
                                    @endif
                                    <td>
                                        <span class="tracking-id">{{ $request->tracking_id }}</span>
                                        <button class="action-btn" onclick="copyTracking('{{ $request->tracking_id }}')"
                                            title="Copy ID">
                                            <i data-feather="copy" style="width: 16px; height: 16px;"></i>
                                        </button>
                                        @if($request->drive_backup_status === 'synced')
                                            <div style="margin-top: 0.35rem; font-size: 0.78rem; color: #86efac;">
                                                Drive backup ready
                                                @if($request->drive_backup_synced_at)
                                                    • {{ $request->drive_backup_synced_at->format('M d, Y') }}
                                                @endif
                                            </div>
                                        @elseif($request->drive_backup_status === 'failed')
                                            <div style="margin-top: 0.35rem; font-size: 0.78rem; color: #fca5a5;">
                                                Drive backup failed
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="student-info">
                                            <div class="name">{{ $request->student_name }}</div>
                                            <div class="email">{{ $request->student_email }}</div>
                                            @if($request->university)
                                                <div class="university">{{ $request->university }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $request->purpose ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{ strtolower(str_replace(' ', '-', $request->status)) }}">
                                            {{ $request->status }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $request->deadline ? \Carbon\Carbon::parse($request->deadline)->format('M d, Y') : '-' }}
                                    </td>
                                    <td>
                                        <div class="actions-cell">
                                            <a href="{{ route('admin.requests.show', $request->id) }}" class="action-btn view"
                                                title="View Details">
                                                <i data-feather="eye" style="width: 18px; height: 18px;"></i>
                                            </a>
                                            @if($canManageRequests && $request->status === 'Approved')
                                                <a href="{{ route('admin.requests.letter-pdf', $request->id) }}" class="action-btn view"
                                                    title="Download PDF">
                                                    <i data-feather="file-down" style="width: 18px; height: 18px;"></i>
                                                </a>
                                                @if($request->drive_backup_url)
                                                    <a href="{{ $request->drive_backup_url }}" target="_blank" rel="noopener"
                                                        class="action-btn view" title="Open in Google Drive">
                                                        <i data-feather="hard-drive" style="width: 18px; height: 18px;"></i>
                                                    </a>
                                                @endif
                                            @endif
                                            @if($canManageRequests && ($request->status === 'Submitted' || $request->status === 'Under Review'))
                                                <form method="POST" action="{{ route('admin.requests.update-status', $request->id) }}"
                                                    style="display: inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="Approved">
                                                    <button type="submit" class="action-btn approve" title="Approve"
                                                        onclick="return confirm('Approve this request?')">
                                                        <i data-feather="check" style="width: 18px; height: 18px;"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.requests.update-status', $request->id) }}"
                                                    style="display: inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="Rejected">
                                                    <button type="submit" class="action-btn reject" title="Reject"
                                                        onclick="return confirm('Reject this request?')">
                                                        <i data-feather="x" style="width: 18px; height: 18px;"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($requests->hasPages())
                    <div class="pagination">
                        <div class="pagination-info">
                            Page {{ $requests->currentPage() }} of {{ $requests->lastPage() }}
                        </div>
                        <div class="pagination-buttons">
                            @if($requests->onFirstPage())
                                <span style="opacity: 0.5;"><i data-feather="chevron-left"
                                        style="width: 16px; height: 16px;"></i></span>
                            @else
                                <a href="{{ $requests->previousPageUrl() }}">
                                    <i data-feather="chevron-left" style="width: 16px; height: 16px;"></i>
                                </a>
                            @endif

                            @if($requests->hasMorePages())
                                <a href="{{ $requests->nextPageUrl() }}">
                                    <i data-feather="chevron-right" style="width: 16px; height: 16px;"></i>
                                </a>
                            @else
                                <span style="opacity: 0.5;"><i data-feather="chevron-right"
                                        style="width: 16px; height: 16px;"></i></span>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Bulk Action Bar -->
    @if($canManageRequests)
        <div id="bulkActionBar" class="bulk-action-bar">
            <span class="count-badge"><span id="selectedCount">0</span> selected</span>

            <div class="bulk-selection-meta">
                <strong style="font-size: 0.95rem;">Bulk actions</strong>
                <small id="bulkSelectionHint">Update the checked requests, or switch to all filtered results in the next step.</small>
            </div>

            <div style="height: 24px; width: 1px; background: rgba(255,255,255,0.2);"></div>

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <button type="button" class="bulk-btn" id="openBulkStatusBtn">
                    <i data-feather="shuffle"></i> Change Status
                </button>
                <button type="button" class="bulk-btn" id="openExportDriveModalFromBar">
                    <i data-feather="hard-drive"></i> Export to Drive
                </button>
                <button type="button" class="bulk-btn" id="openExportLettersModalFromBar">
                    <i data-feather="file-down"></i> Export PDFs
                </button>
                <button type="button" class="bulk-btn danger" id="bulkDeleteBtn">
                    <i data-feather="trash-2"></i> Delete Selected
                </button>
            </div>
        </div>
    @endif

    @if($canManageRequests)
    <div id="bulkStatusModal" class="bulk-modal {{ $bulkModalOpen ? 'visible' : '' }}" aria-hidden="{{ $bulkModalOpen ? 'false' : 'true' }}">
        <div class="bulk-modal-card" role="dialog" aria-modal="true" aria-labelledby="bulkStatusTitle">
            <div class="bulk-modal-header">
                <div>
                    <h3 id="bulkStatusTitle">Bulk status update</h3>
                    <p>Choose which requests to update, set the new status, and add one shared note for students when needed.</p>
                </div>
                <button type="button" class="bulk-modal-close" id="closeBulkStatusModal" aria-label="Close bulk status dialog">
                    <i data-feather="x"></i>
                </button>
            </div>

            <form id="bulkStatusForm" method="POST" action="{{ route('admin.requests.bulk') }}">
                @csrf
                <input type="hidden" name="operation" value="status">
                <input type="hidden" name="selection_scope" id="bulkSelectionScope" value="{{ old('selection_scope', 'selected') }}">
                <input type="hidden" name="ids" id="bulkIds">
                <input type="hidden" name="filters" id="bulkFilters">

                <div class="bulk-modal-body">
                    <div class="bulk-scope-grid">
                        <button type="button" class="bulk-scope-option" data-scope="selected">
                            <span class="scope-label">Selected requests</span>
                            <span class="scope-count" id="modalSelectedCount">0</span>
                            <span class="scope-help">Only the rows you checked on this page will be updated.</span>
                        </button>

                        <button type="button" class="bulk-scope-option" data-scope="filtered">
                            <span class="scope-label">{{ $hasActiveFilters ? 'All matching current filters' : 'All requests currently in the list' }}</span>
                            <span class="scope-count" id="modalFilteredCount">{{ $requests->total() }}</span>
                            <span class="scope-help">
                                {{ $hasActiveFilters ? 'Uses the current status, search, university, and date filters across every page.' : 'Updates every request in the current admin list, not just this page.' }}
                            </span>
                        </button>
                    </div>

                    <div class="bulk-field">
                        <label for="bulkStatusSelect">New status</label>
                        <select id="bulkStatusSelect" name="status">
                            <option value="">Choose a status</option>
                            @foreach(['Submitted', 'Under Review', 'Needs Revision', 'Approved', 'Rejected', 'Archived'] as $status)
                                <option value="{{ $status }}" {{ old('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="bulk-field">
                        <label for="bulkAdminMessage" id="bulkAdminMessageLabel">Shared student message <span id="bulkAdminMessageRequirement">(Optional)</span></label>
                        <textarea id="bulkAdminMessage" name="admin_message"
                            placeholder="{{ old('status') === 'Rejected' ? 'Reason for rejection for all selected students...' : 'Optional note shown to all affected students...' }}">{{ old('admin_message') }}</textarea>
                        <div class="field-help" id="bulkAdminMessageHelp">
                            Add one shared note for every affected student. This becomes required for <strong>Needs Revision</strong> and <strong>Rejected</strong>.
                        </div>
                    </div>

                    <div class="bulk-summary" id="bulkSummary">
                        <strong>Review before sending</strong>
                        Select at least one request, or switch to all filtered results, then choose the new status.
                    </div>
                </div>

                <div class="bulk-modal-actions">
                    <button type="button" class="btn btn-ghost" id="cancelBulkStatusBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Bulk Update</button>
                </div>
            </form>
        </div>
    </div>

    <div id="exportLettersModal" class="bulk-modal" aria-hidden="true">
        <div class="bulk-modal-card" role="dialog" aria-modal="true" aria-labelledby="exportLettersTitle">
            <div class="bulk-modal-header">
                <div>
                    <h3 id="exportLettersTitle">Export approved letters as PDF</h3>
                    <p>Use the same browser print path students already trust. Only approved requests will be included in the ZIP file.</p>
                </div>
                <button type="button" class="bulk-modal-close" id="closeExportLettersModal" aria-label="Close export letters dialog">
                    <i data-feather="x"></i>
                </button>
            </div>

            <form id="exportLettersForm" method="POST" action="{{ route('admin.requests.letters.export-pdf') }}">
                @csrf
                <input type="hidden" name="selection_scope" id="exportSelectionScope" value="selected">
                <input type="hidden" name="ids" id="exportIds">
                <input type="hidden" name="filters" id="exportFilters">

                <div class="bulk-modal-body">
                    <div class="bulk-scope-grid">
                        <button type="button" class="bulk-scope-option active" data-export-scope="selected">
                            <span class="scope-label">Selected requests</span>
                            <span class="scope-count" id="exportSelectedCount">0</span>
                            <span class="scope-help">Exports the approved letters for the rows you checked on this page.</span>
                        </button>

                        <button type="button" class="bulk-scope-option" data-export-scope="filtered">
                            <span class="scope-label">{{ $hasActiveFilters ? 'All approved letters matching current filters' : 'All approved letters in the current list' }}</span>
                            <span class="scope-count" id="exportFilteredCount">{{ $requests->total() }}</span>
                            <span class="scope-help">
                                {{ $hasActiveFilters ? 'Uses your current status, search, university, and date filters across every page.' : 'Exports every approved letter visible in the current admin list scope.' }}
                            </span>
                        </button>
                    </div>

                    <div class="bulk-summary" id="exportLettersSummary">
                        <strong>Choose what to export</strong>
                        Select requests on this page, or switch to all requests matching the current filters. The ZIP will only contain approved letters.
                    </div>
                </div>

                <div class="bulk-modal-actions">
                    <button type="button" class="btn btn-ghost" id="cancelExportLettersBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Download ZIP</button>
                </div>
            </form>
        </div>
    </div>

    <div id="exportDriveModal" class="bulk-modal" aria-hidden="true">
        <div class="bulk-modal-card" role="dialog" aria-modal="true" aria-labelledby="exportDriveTitle">
            <div class="bulk-modal-header">
                <div>
                    <h3 id="exportDriveTitle">Back up approved letters to Google Drive</h3>
                    <p>Send the same approved-letter PDF into your configured Google Drive folder. Files stay private unless you share them from Drive later.</p>
                </div>
                <button type="button" class="bulk-modal-close" id="closeExportDriveModal" aria-label="Close Google Drive export dialog">
                    <i data-feather="x"></i>
                </button>
            </div>

            <form id="exportDriveForm" method="POST" action="{{ route('admin.requests.letters.export-drive') }}">
                @csrf
                <input type="hidden" name="selection_scope" id="exportDriveSelectionScope" value="selected">
                <input type="hidden" name="ids" id="exportDriveIds">
                <input type="hidden" name="filters" id="exportDriveFilters">

                <div class="bulk-modal-body">
                    <div class="bulk-scope-grid">
                        <button type="button" class="bulk-scope-option active" data-drive-scope="selected">
                            <span class="scope-label">Selected requests</span>
                            <span class="scope-count" id="exportDriveSelectedCount">0</span>
                            <span class="scope-help">Backs up the approved letters for the rows you checked on this page.</span>
                        </button>

                        <button type="button" class="bulk-scope-option" data-drive-scope="filtered">
                            <span class="scope-label">{{ $hasActiveFilters ? 'All approved letters matching current filters' : 'All approved letters in the current list' }}</span>
                            <span class="scope-count" id="exportDriveFilteredCount">{{ $requests->total() }}</span>
                            <span class="scope-help">
                                {{ $hasActiveFilters ? 'Uses your current status, search, university, and date filters across every page.' : 'Backs up every approved letter visible in the current admin list scope.' }}
                            </span>
                        </button>
                    </div>

                    <div class="bulk-summary" id="exportDriveSummary">
                        <strong>Choose what to back up</strong>
                        Select requests on this page, or switch to all requests matching the current filters. Only approved letters will be sent to Google Drive.
                    </div>
                </div>

                <div class="bulk-modal-actions">
                    <button type="button" class="btn btn-ghost" id="cancelExportDriveBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Back Up to Drive</button>
                </div>
            </form>
        </div>
    </div>

    <form id="bulkDeleteForm" method="POST" action="{{ route('admin.requests.bulk') }}" style="display: none;">
            @csrf
            <input type="hidden" name="operation" value="delete">
            <input type="hidden" name="selection_scope" value="selected">
            <input type="hidden" name="ids" id="bulkDeleteIds">
        </form>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('selectAll');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');
            const bar = document.getElementById('bulkActionBar');
            const countSpan = document.getElementById('selectedCount');
            const bulkSelectionHint = document.getElementById('bulkSelectionHint');
            const bulkStatusModal = document.getElementById('bulkStatusModal');
            const bulkStatusForm = document.getElementById('bulkStatusForm');
            const bulkDeleteForm = document.getElementById('bulkDeleteForm');
            const exportLettersModal = document.getElementById('exportLettersModal');
            const exportLettersForm = document.getElementById('exportLettersForm');
            const exportDriveModal = document.getElementById('exportDriveModal');
            const exportDriveForm = document.getElementById('exportDriveForm');
            const bulkIdsInput = document.getElementById('bulkIds');
            const bulkFiltersInput = document.getElementById('bulkFilters');
            const bulkSelectionScopeInput = document.getElementById('bulkSelectionScope');
            const exportIdsInput = document.getElementById('exportIds');
            const exportFiltersInput = document.getElementById('exportFilters');
            const exportSelectionScopeInput = document.getElementById('exportSelectionScope');
            const exportDriveIdsInput = document.getElementById('exportDriveIds');
            const exportDriveFiltersInput = document.getElementById('exportDriveFilters');
            const exportDriveSelectionScopeInput = document.getElementById('exportDriveSelectionScope');
            const bulkStatusSelect = document.getElementById('bulkStatusSelect');
            const bulkAdminMessage = document.getElementById('bulkAdminMessage');
            const bulkAdminMessageRequirement = document.getElementById('bulkAdminMessageRequirement');
            const bulkAdminMessageHelp = document.getElementById('bulkAdminMessageHelp');
            const bulkSummary = document.getElementById('bulkSummary');
            const modalSelectedCount = document.getElementById('modalSelectedCount');
            const modalFilteredCount = document.getElementById('modalFilteredCount');
            const openBulkStatusBtn = document.getElementById('openBulkStatusBtn');
            const openExportDriveModal = document.getElementById('openExportDriveModal');
            const openExportDriveModalFromBar = document.getElementById('openExportDriveModalFromBar');
            const openExportLettersModal = document.getElementById('openExportLettersModal');
            const openExportLettersModalFromBar = document.getElementById('openExportLettersModalFromBar');
            const closeBulkStatusBtn = document.getElementById('closeBulkStatusModal');
            const cancelBulkStatusBtn = document.getElementById('cancelBulkStatusBtn');
            const closeExportDriveModal = document.getElementById('closeExportDriveModal');
            const cancelExportDriveBtn = document.getElementById('cancelExportDriveBtn');
            const closeExportLettersModal = document.getElementById('closeExportLettersModal');
            const cancelExportLettersBtn = document.getElementById('cancelExportLettersBtn');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const scopeButtons = document.querySelectorAll('[data-scope]');
            const exportScopeButtons = document.querySelectorAll('[data-export-scope]');
            const driveScopeButtons = document.querySelectorAll('[data-drive-scope]');
            const exportSelectedCount = document.getElementById('exportSelectedCount');
            const exportFilteredCount = document.getElementById('exportFilteredCount');
            const exportLettersSummary = document.getElementById('exportLettersSummary');
            const exportDriveSelectedCount = document.getElementById('exportDriveSelectedCount');
            const exportDriveFilteredCount = document.getElementById('exportDriveFilteredCount');
            const exportDriveSummary = document.getElementById('exportDriveSummary');
            const totalFilteredCount = {{ $requests->total() }};
            const currentFilters = @json($currentFilters);
            const hasActiveFilters = @json($hasActiveFilters);
            const canManageRequests = @json($canManageRequests);
            let selectedScope = bulkSelectionScopeInput ? (bulkSelectionScopeInput.value || 'selected') : 'selected';
            let exportScope = exportSelectionScopeInput ? (exportSelectionScopeInput.value || 'selected') : 'selected';
            let driveExportScope = exportDriveSelectionScopeInput ? (exportDriveSelectionScopeInput.value || 'selected') : 'selected';

            function getSelectedIds() {
                return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
            }

            function updateBar() {
                const checked = document.querySelectorAll('.row-checkbox:checked');
                const count = checked.length;
                if (countSpan) countSpan.textContent = count;
                if (modalSelectedCount) modalSelectedCount.textContent = count;
                if (modalFilteredCount) modalFilteredCount.textContent = totalFilteredCount;
                if (exportSelectedCount) exportSelectedCount.textContent = count;
                if (exportFilteredCount) exportFilteredCount.textContent = totalFilteredCount;
                if (exportDriveSelectedCount) exportDriveSelectedCount.textContent = count;
                if (exportDriveFilteredCount) exportDriveFilteredCount.textContent = totalFilteredCount;

                if (bulkSelectionHint && count > 0) {
                    bulkSelectionHint.textContent = totalFilteredCount > count
                        ? `You can update the ${count} checked requests or switch to all ${totalFilteredCount} matching requests in the next step.`
                        : `You are ready to update the ${count} checked request${count === 1 ? '' : 's'}.`;
                } else if (bulkSelectionHint) {
                    bulkSelectionHint.textContent = 'Select requests first, then choose whether to update only those rows or everything matching the current filters.';
                }

                if (bar && count > 0) {
                    bar.classList.add('visible');
                } else if (bar) {
                    bar.classList.remove('visible');
                }

                if (canManageRequests) {
                    updateBulkSummary();
                    updateExportSummary();
                    updateDriveExportSummary();
                }
            }

            function setScope(scope) {
                if (!canManageRequests) {
                    return;
                }

                selectedScope = scope;
                bulkSelectionScopeInput.value = scope;

                scopeButtons.forEach(button => {
                    button.classList.toggle('active', button.dataset.scope === scope);
                });

                updateBulkSummary();
            }

            function updateBulkSummary() {
                if (!canManageRequests || !bulkStatusSelect || !bulkSummary || !bulkAdminMessageRequirement || !bulkAdminMessageHelp || !bulkAdminMessage) {
                    return;
                }

                const selectedCount = getSelectedIds().length;
                const status = bulkStatusSelect.value;
                const scopeCount = selectedScope === 'filtered' ? totalFilteredCount : selectedCount;
                const scopeLabel = selectedScope === 'filtered'
                    ? (hasActiveFilters ? 'all requests matching your current filters' : 'all requests in the current list')
                    : `${selectedCount} selected request${selectedCount === 1 ? '' : 's'}`;

                const requiresMessage = status === 'Needs Revision' || status === 'Rejected';
                bulkAdminMessageRequirement.textContent = requiresMessage ? '(Required)' : '(Optional)';
                bulkAdminMessageHelp.innerHTML = requiresMessage
                    ? `This message will be sent to every affected student and is required for <strong>${status}</strong>.`
                    : 'Add one shared note for every affected student. Leave it empty if no extra note is needed.';
                bulkAdminMessage.required = requiresMessage;
                bulkAdminMessage.placeholder = status === 'Rejected'
                    ? 'Reason for rejection for all affected students...'
                    : 'Optional note shown to all affected students...';

                if (!status) {
                    bulkSummary.innerHTML = '<strong>Review before sending</strong>Select the new status you want to apply, then confirm which requests should receive it.';
                    return;
                }

                if (selectedScope === 'selected' && selectedCount === 0) {
                    bulkSummary.innerHTML = '<strong>No selected requests yet</strong>Check at least one request on this page, or switch the scope to all filtered results.';
                    return;
                }

                bulkSummary.innerHTML = `
                    <strong>Ready to update ${scopeCount} request${scopeCount === 1 ? '' : 's'}</strong>
                    This will change <strong>${scopeLabel}</strong> to <strong>${status}</strong>${requiresMessage ? ' and send the shared student message below.' : '.'}
                `;
            }

            function openBulkStatusModal() {
                if (!canManageRequests || !bulkStatusModal) {
                    return;
                }

                if (getSelectedIds().length === 0 && selectedScope === 'selected') {
                    setScope(totalFilteredCount > 0 ? 'filtered' : 'selected');
                }

                bulkStatusModal.classList.add('visible');
                bulkStatusModal.setAttribute('aria-hidden', 'false');
                updateBulkSummary();
            }

            function closeBulkStatusModal() {
                if (!bulkStatusModal) {
                    return;
                }

                bulkStatusModal.classList.remove('visible');
                bulkStatusModal.setAttribute('aria-hidden', 'true');
            }

            function setExportScope(scope) {
                if (!canManageRequests || !exportSelectionScopeInput) {
                    return;
                }

                exportScope = scope;
                exportSelectionScopeInput.value = scope;

                exportScopeButtons.forEach(button => {
                    button.classList.toggle('active', button.dataset.exportScope === scope);
                });

                updateExportSummary();
            }

            function updateExportSummary() {
                if (!canManageRequests || !exportLettersSummary) {
                    return;
                }

                const selectedCount = getSelectedIds().length;
                const scopeCount = exportScope === 'filtered' ? totalFilteredCount : selectedCount;
                const scopeLabel = exportScope === 'filtered'
                    ? (hasActiveFilters ? `all ${scopeCount} requests matching your current filters` : `all ${scopeCount} requests in the current list`)
                    : `${selectedCount} selected request${selectedCount === 1 ? '' : 's'}`;

                if (exportScope === 'selected' && selectedCount === 0) {
                    exportLettersSummary.innerHTML = '<strong>No selected requests yet</strong>Check the requests you want first, or switch to all requests matching the current filters.';
                    return;
                }

                exportLettersSummary.innerHTML = `
                    <strong>Ready to export ${scopeCount} request${scopeCount === 1 ? '' : 's'}</strong>
                    The ZIP file will use the browser-faithful print layout and include <strong>approved letters only</strong> from ${scopeLabel}. Any request that is not approved will be skipped automatically.
                `;
            }

            function setDriveExportScope(scope) {
                if (!canManageRequests || !exportDriveSelectionScopeInput) {
                    return;
                }

                driveExportScope = scope;
                exportDriveSelectionScopeInput.value = scope;

                driveScopeButtons.forEach(button => {
                    button.classList.toggle('active', button.dataset.driveScope === scope);
                });

                updateDriveExportSummary();
            }

            function updateDriveExportSummary() {
                if (!canManageRequests || !exportDriveSummary) {
                    return;
                }

                const selectedCount = getSelectedIds().length;
                const scopeCount = driveExportScope === 'filtered' ? totalFilteredCount : selectedCount;
                const scopeLabel = driveExportScope === 'filtered'
                    ? (hasActiveFilters ? `all ${scopeCount} requests matching your current filters` : `all ${scopeCount} requests in the current list`)
                    : `${selectedCount} selected request${selectedCount === 1 ? '' : 's'}`;

                if (driveExportScope === 'selected' && selectedCount === 0) {
                    exportDriveSummary.innerHTML = '<strong>No selected requests yet</strong>Check the requests you want first, or switch to all requests matching the current filters.';
                    return;
                }

                exportDriveSummary.innerHTML = `
                    <strong>Ready to back up ${scopeCount} request${scopeCount === 1 ? '' : 's'}</strong>
                    Google Drive backup will use the trusted browser-faithful PDF export and send <strong>approved letters only</strong> from ${scopeLabel}. Requests that are not approved will be skipped automatically.
                `;
            }

            function openExportModal(preferredScope = null) {
                if (!canManageRequests || !exportLettersModal) {
                    return;
                }

                if (preferredScope) {
                    setExportScope(preferredScope);
                } else if (getSelectedIds().length === 0) {
                    setExportScope('filtered');
                } else {
                    setExportScope(exportScope);
                }

                exportLettersModal.classList.add('visible');
                exportLettersModal.setAttribute('aria-hidden', 'false');
                updateExportSummary();
            }

            function closeExportModal() {
                if (!exportLettersModal) {
                    return;
                }

                exportLettersModal.classList.remove('visible');
                exportLettersModal.setAttribute('aria-hidden', 'true');
            }

            function openDriveExportModal(preferredScope = null) {
                if (!canManageRequests || !exportDriveModal) {
                    return;
                }

                if (preferredScope) {
                    setDriveExportScope(preferredScope);
                } else if (getSelectedIds().length === 0) {
                    setDriveExportScope('filtered');
                } else {
                    setDriveExportScope(driveExportScope);
                }

                exportDriveModal.classList.add('visible');
                exportDriveModal.setAttribute('aria-hidden', 'false');
                updateDriveExportSummary();
            }

            function closeDriveExportModal() {
                if (!exportDriveModal) {
                    return;
                }

                exportDriveModal.classList.remove('visible');
                exportDriveModal.setAttribute('aria-hidden', 'true');
            }

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    rowCheckboxes.forEach(cb => {
                        cb.checked = this.checked;
                    });
                    updateBar();
                });
            }

            rowCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateBar);
            });

            scopeButtons.forEach(button => {
                button.addEventListener('click', function () {
                    setScope(this.dataset.scope);
                });
            });

            exportScopeButtons.forEach(button => {
                button.addEventListener('click', function () {
                    setExportScope(this.dataset.exportScope);
                });
            });

            driveScopeButtons.forEach(button => {
                button.addEventListener('click', function () {
                    setDriveExportScope(this.dataset.driveScope);
                });
            });

            if (bulkStatusSelect) {
                bulkStatusSelect.addEventListener('change', updateBulkSummary);
            }

            if (bulkAdminMessage) {
                bulkAdminMessage.addEventListener('input', updateBulkSummary);
            }

            if (openBulkStatusBtn) {
                openBulkStatusBtn.addEventListener('click', openBulkStatusModal);
            }

            if (openExportDriveModal) {
                openExportDriveModal.addEventListener('click', function () {
                    openDriveExportModal();
                });
            }

            if (openExportDriveModalFromBar) {
                openExportDriveModalFromBar.addEventListener('click', function () {
                    openDriveExportModal('selected');
                });
            }

            if (openExportLettersModal) {
                openExportLettersModal.addEventListener('click', function () {
                    openExportModal();
                });
            }

            if (openExportLettersModalFromBar) {
                openExportLettersModalFromBar.addEventListener('click', function () {
                    openExportModal('selected');
                });
            }

            if (closeBulkStatusBtn) {
                closeBulkStatusBtn.addEventListener('click', closeBulkStatusModal);
            }

            if (cancelBulkStatusBtn) {
                cancelBulkStatusBtn.addEventListener('click', closeBulkStatusModal);
            }

            if (closeExportDriveModal) {
                closeExportDriveModal.addEventListener('click', closeDriveExportModal);
            }

            if (cancelExportDriveBtn) {
                cancelExportDriveBtn.addEventListener('click', closeDriveExportModal);
            }

            if (closeExportLettersModal) {
                closeExportLettersModal.addEventListener('click', closeExportModal);
            }

            if (cancelExportLettersBtn) {
                cancelExportLettersBtn.addEventListener('click', closeExportModal);
            }

            if (bulkStatusModal) {
                bulkStatusModal.addEventListener('click', function (event) {
                    if (event.target === bulkStatusModal) {
                        closeBulkStatusModal();
                    }
                });
            }

            if (exportLettersModal) {
                exportLettersModal.addEventListener('click', function (event) {
                    if (event.target === exportLettersModal) {
                        closeExportModal();
                    }
                });
            }

            if (exportDriveModal) {
                exportDriveModal.addEventListener('click', function (event) {
                    if (event.target === exportDriveModal) {
                        closeDriveExportModal();
                    }
                });
            }

            if (bulkDeleteBtn) {
                bulkDeleteBtn.addEventListener('click', function () {
                    const ids = getSelectedIds();
                    if (ids.length === 0) {
                        alert('Select at least one request before deleting.');
                        return;
                    }

                    if (!confirm(`Delete ${ids.length} selected request${ids.length === 1 ? '' : 's'}? This cannot be undone.`)) {
                        return;
                    }

                    bulkDeleteForm.querySelector('input[name="ids"]').value = JSON.stringify(ids);
                    bulkDeleteForm.submit();
                });
            }

            if (bulkStatusForm) {
                bulkStatusForm.addEventListener('submit', function (event) {
                    const ids = getSelectedIds();

                    bulkIdsInput.value = JSON.stringify(ids);
                    bulkFiltersInput.value = JSON.stringify(currentFilters);
                    bulkSelectionScopeInput.value = selectedScope;

                    if (selectedScope === 'selected' && ids.length === 0) {
                        event.preventDefault();
                        alert('Select at least one request before applying a bulk status update.');
                        return;
                    }

                    if (!bulkStatusSelect.value) {
                        event.preventDefault();
                        alert('Choose the new status before applying a bulk update.');
                        return;
                    }

                    if ((bulkStatusSelect.value === 'Needs Revision' || bulkStatusSelect.value === 'Rejected') && bulkAdminMessage.value.trim() === '') {
                        event.preventDefault();
                        alert('Add the shared student message before applying this bulk update.');
                        return;
                    }

                    const scopeCount = selectedScope === 'filtered' ? totalFilteredCount : ids.length;
                    const scopeLabel = selectedScope === 'filtered'
                        ? (hasActiveFilters ? `all ${scopeCount} requests matching the current filters` : `all ${scopeCount} requests in the current list`)
                        : `${scopeCount} selected request${scopeCount === 1 ? '' : 's'}`;
                    const confirmation = `Update ${scopeLabel} to "${bulkStatusSelect.value}"? Students will receive the normal status notification${bulkAdminMessage.value.trim() ? ' plus the shared message shown in the form.' : '.'}`;

                    if (!confirm(confirmation)) {
                        event.preventDefault();
                    }
                });
            }

            if (exportLettersForm) {
                exportLettersForm.addEventListener('submit', function (event) {
                    const ids = getSelectedIds();

                    exportIdsInput.value = JSON.stringify(ids);
                    exportFiltersInput.value = JSON.stringify(currentFilters);
                    exportSelectionScopeInput.value = exportScope;

                    if (exportScope === 'selected' && ids.length === 0) {
                        event.preventDefault();
                        alert('Select at least one request before exporting PDF letters.');
                        return;
                    }

                    const scopeCount = exportScope === 'filtered' ? totalFilteredCount : ids.length;
                    const scopeLabel = exportScope === 'filtered'
                        ? (hasActiveFilters ? `all ${scopeCount} requests matching the current filters` : `all ${scopeCount} requests in the current list`)
                        : `${scopeCount} selected request${scopeCount === 1 ? '' : 's'}`;

                    if (!confirm(`Export approved letters from ${scopeLabel} as a ZIP file? Requests that are not approved will be skipped.`)) {
                        event.preventDefault();
                    }
                });
            }

            if (exportDriveForm) {
                exportDriveForm.addEventListener('submit', function (event) {
                    const ids = getSelectedIds();

                    exportDriveIdsInput.value = JSON.stringify(ids);
                    exportDriveFiltersInput.value = JSON.stringify(currentFilters);
                    exportDriveSelectionScopeInput.value = driveExportScope;

                    if (driveExportScope === 'selected' && ids.length === 0) {
                        event.preventDefault();
                        alert('Select at least one request before exporting letters to Google Drive.');
                        return;
                    }

                    const scopeCount = driveExportScope === 'filtered' ? totalFilteredCount : ids.length;
                    const scopeLabel = driveExportScope === 'filtered'
                        ? (hasActiveFilters ? `all ${scopeCount} requests matching the current filters` : `all ${scopeCount} requests in the current list`)
                        : `${scopeCount} selected request${scopeCount === 1 ? '' : 's'}`;

                    if (!confirm(`Back up approved letters from ${scopeLabel} to Google Drive? Requests that are not approved will be skipped.`)) {
                        event.preventDefault();
                    }
                });
            }

            document.addEventListener('keydown', function (event) {
                if (bulkStatusModal && event.key === 'Escape' && bulkStatusModal.classList.contains('visible')) {
                    closeBulkStatusModal();
                }

                if (exportLettersModal && event.key === 'Escape' && exportLettersModal.classList.contains('visible')) {
                    closeExportModal();
                }

                if (exportDriveModal && event.key === 'Escape' && exportDriveModal.classList.contains('visible')) {
                    closeDriveExportModal();
                }
            });

            if (canManageRequests) {
                setScope(selectedScope);
                setExportScope(exportScope);
                setDriveExportScope(driveExportScope);
            }
            updateBar();
        });
    </script>
@endsection
