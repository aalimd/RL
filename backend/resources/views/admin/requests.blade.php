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
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }

        .search-box svg {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
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
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            min-width: 160px;
            background: #fff;
            color: #374151;
        }

        .btn-reset {
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            background: white;
            color: #374151;
            cursor: pointer;
        }

        .status-btn {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            border: 1px solid #e5e7eb;
            background: white;
            color: #374151;
            cursor: pointer;
            transition: all 0.2s;
        }

        .status-btn:hover {
            background: #f9fafb;
        }

        .status-btn.active {
            background: var(--primary, #4f46e5);
            color: white;
            border-color: var(--primary, #4f46e5);
        }

        .results-info {
            font-size: 0.875rem;
            color: #6b7280;
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
            background: #f9fafb;
            padding: 0.75rem 1rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e5e7eb;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.875rem;
        }

        .data-table tbody tr:hover {
            background: #f9fafb;
        }

        .tracking-id {
            font-family: monospace;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary, #4f46e5);
            background: #eef2ff;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        .student-info .name {
            font-weight: 500;
            color: #111827;
        }

        .student-info .email {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .student-info .university {
            font-size: 0.75rem;
            color: #9ca3af;
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
            background: #f3f4f6;
        }

        .action-btn.view {
            color: var(--primary, #4f46e5);
        }

        .action-btn.approve {
            color: #10b981;
        }

        .action-btn.reject {
            color: #ef4444;
        }

        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .pagination-info {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .pagination-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .pagination-buttons a,
        .pagination-buttons span {
            padding: 0.5rem 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            text-decoration: none;
            color: #374151;
            background: white;
        }

        .pagination-buttons a:hover {
            background: #f3f4f6;
        }

        .pagination-buttons .current {
            background: var(--primary, #4f46e5);
            color: white;
            border-color: var(--primary, #4f46e5);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
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
            border: 1px solid #d1d5db;
            cursor: pointer;
        }
    </style>
@endsection

@section('content')
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

    <!-- Actions Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <div class="results-info" style="margin-bottom: 0;">
            Showing {{ $requests->firstItem() ?? 0 }} to {{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }}
            requests
        </div>
        <a href="{{ route('admin.requests.export', request()->query()) }}" class="btn btn-primary" style="gap: 0.5rem;">
            <i data-feather="download" style="width: 16px; height: 16px;"></i>
            Export to Excel
        </a>
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
                                <th>
                                    <div class="checkbox-wrapper">
                                        <input type="checkbox" id="selectAll">
                                    </div>
                                </th>
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
                                    <td>
                                        <div class="checkbox-wrapper">
                                            <input type="checkbox" name="ids[]" value="{{ $request->id }}" class="row-checkbox">
                                        </div>
                                    </td>
                                    <td>
                                        <span class="tracking-id">{{ $request->tracking_id }}</span>
                                        <button class="action-btn" onclick="copyTracking('{{ $request->tracking_id }}')"
                                            title="Copy ID">
                                            <i data-feather="copy" style="width: 16px; height: 16px;"></i>
                                        </button>
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
                                            @if($request->status === 'Submitted' || $request->status === 'Under Review')
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
    <div id="bulkActionBar" class="bulk-action-bar">
        <span class="count-badge"><span id="selectedCount">0</span> selected</span>

        <div style="height: 24px; width: 1px; background: rgba(255,255,255,0.2);"></div>

        <form id="bulkForm" method="POST" action="{{ route('admin.requests.bulk') }}"
            style="display: flex; gap: 0.75rem; margin:0;">
            @csrf
            <input type="hidden" name="ids" id="bulkIds">
            <input type="hidden" name="action" id="bulkAction">

            <button type="button" class="bulk-btn" onclick="submitBulk('approve')">
                <i data-feather="check"></i> Approve
            </button>
            <button type="button" class="bulk-btn" onclick="submitBulk('reject')">
                <i data-feather="x"></i> Reject
            </button>
            <button type="button" class="bulk-btn danger" onclick="submitBulk('delete')">
                <i data-feather="trash-2"></i> Delete
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('selectAll');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');
            const bar = document.getElementById('bulkActionBar');
            const countSpan = document.getElementById('selectedCount');

            function updateBar() {
                const checked = document.querySelectorAll('.row-checkbox:checked');
                const count = checked.length;
                countSpan.textContent = count;

                if (count > 0) {
                    bar.classList.add('visible');
                } else {
                    bar.classList.remove('visible');
                }
            }

            selectAll.addEventListener('change', function () {
                rowCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
                updateBar();
            });

            rowCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateBar);
            });

            window.submitBulk = function (action) {
                if (!confirm('Are you sure you want to ' + action + ' the selected requests?')) return;

                const checked = document.querySelectorAll('.row-checkbox:checked');
                const ids = Array.from(checked).map(cb => cb.value);

                document.getElementById('bulkIds').value = JSON.stringify(ids);
                document.getElementById('bulkAction').value = action;
                document.getElementById('bulkForm').submit();
            }
        });
    </script>
@endsection
