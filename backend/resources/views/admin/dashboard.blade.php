@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i data-feather="file-text"></i>
            </div>
            <div class="stat-title">Total Requests</div>
            <div class="stat-value">{{ $stats['totalRequests'] ?? 0 }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon yellow">
                <i data-feather="clock"></i>
            </div>
            <div class="stat-title">Pending Review</div>
            <div class="stat-value">{{ $stats['pendingRequests'] ?? 0 }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i data-feather="check-circle"></i>
            </div>
            <div class="stat-title">Approved</div>
            <div class="stat-value">{{ $stats['approvedRequests'] ?? 0 }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon red">
                <i data-feather="x-circle"></i>
            </div>
            <div class="stat-title">Rejected</div>
            <div class="stat-value">{{ $stats['rejectedRequests'] ?? 0 }}</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <!-- Recent Requests -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h3>Recent Requests</h3>
                <a href="{{ route('admin.requests') }}" class="btn btn-ghost btn-sm">
                    View All <i data-feather="arrow-right" style="width: 14px; height: 14px;"></i>
                </a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tracking ID</th>
                                <th>Candidate</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentRequests as $request)
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span
                                                style="font-family: monospace; font-weight: 600; color: var(--text-main);">{{ $request->tracking_id }}</span>
                                            <button onclick="copyTracking('{{ $request->tracking_id }}')" class="btn-ghost"
                                                style="padding: 2px; height: auto;" title="Copy ID">
                                                <i data-feather="copy" style="width: 12px; height: 12px;"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 500; color: var(--text-main);">
                                            {{ $request->student_name ?? 'N/A' }}
                                        </div>
                                        <div style="font-size: 0.75rem; color: #6b7280;">{{ $request->purpose ?? 'General' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge 
                                                                    @if($request->status === 'Approved') badge-approved
                                                                    @elseif($request->status === 'Rejected') badge-rejected
                                                                    @elseif($request->status === 'Under Review') badge-revision
                                                                    @else badge-pending @endif">
                                            {{ $request->status }}
                                        </span>
                                    </td>
                                    <td>{{ $request->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.requests.show', $request->id) }}" class="btn btn-ghost btn-sm"
                                            title="View Details">
                                            <i data-feather="eye" style="width: 16px; height: 16px;"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 3rem; color: #6b7280;">
                                        <i data-feather="inbox"
                                            style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                                        <p>No recent requests found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Status Chart -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h3>Status Overview</h3>
            </div>
            <div class="card-body"
                style="display: flex; align-items: center; justify-content: center; position: relative; height: 300px;">
                <canvas id="statusChart" data-pending="{{ $stats['pendingRequests'] ?? 0 }}"
                    data-under-review="{{ $stats['underReviewRequests'] ?? 0 }}"
                    data-approved="{{ $stats['approvedRequests'] ?? 0 }}"
                    data-rejected="{{ $stats['rejectedRequests'] ?? 0 }}"></canvas>
            </div>
        </div>
    </div>

    <!-- Second Row: Trends & Activity -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">

        <!-- Trend Chart -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h3>Requests Trend (Last 30 Days)</h3>
            </div>
            <div class="card-body" style="height: 350px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity Feed -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h3>System Activity</h3>
            </div>
            <div class="card-body" style="padding: 0; overflow-y: auto; max-height: 350px;">
                @forelse($recentActivities as $log)
                    @php
                        $details = $log->details;
                        $isJson = false;
                        $decoded = json_decode($details, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $isJson = true;
                            // Simplify known patterns
                            if (isset($decoded['template_id'])) {
                                $actionName = isset($decoded['name']) ? "Template: {$decoded['name']}" : "Template #{$decoded['template_id']}";
                                $details = "Modified " . $actionName;
                            } elseif (isset($decoded['tracking_id'])) {
                                $details = "Request " . $decoded['tracking_id'];
                            }
                        }
                    @endphp
                    <div
                        style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; gap: 1rem; align-items: flex-start;">
                        <div
                            style="background: var(--input-bg); padding: 0.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color);">
                            <i data-feather="activity" style="width: 16px; height: 16px; color: #6b7280;"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.875rem; color: #111827; font-weight: 500;">
                                {{ $log->user->name ?? 'System' }}
                                <span style="font-weight: normal; color: #6b7280; font-size: 0.8rem;">
                                    {{ str_replace(['_', 'api'], [' ', ''], $log->action) }}
                                </span>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">
                                {{ $isJson ? $details : Str::limit($details, 60) }}
                            </div>
                            <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.25rem;">
                                {{ $log->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="padding: 2rem; text-align: center; color: #9ca3af;">
                        No recent activity
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            try {
                const isDarkMode = document.body.classList.contains('dark-mode');

                // Status Doughnut Chart
                const statusCanvas = document.getElementById('statusChart');
                if (statusCanvas) {
                    const ctx = statusCanvas.getContext('2d');
                    const pending = Number(statusCanvas.dataset.pending || 0);
                    const underReview = Number(statusCanvas.dataset.underReview || 0);
                    const approved = Number(statusCanvas.dataset.approved || 0);
                    const rejected = Number(statusCanvas.dataset.rejected || 0);

                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Submitted', 'Under Review', 'Approved', 'Rejected'],
                            datasets: [{
                                data: [pending, underReview, approved, rejected],
                                backgroundColor: ['#6b7280', '#f59e0b', '#10b981', '#ef4444'],
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20,
                                        font: { family: "'Inter', sans-serif", size: 11 },
                                        color: isDarkMode ? '#f1f5f9' : '#111827'
                                    }
                                }
                            },
                            cutout: '75%'
                        }
                    });
                }

                // Trend Line Chart
                const trendCanvas = document.getElementById('trendChart');
                if (trendCanvas) {
                    const trendCtx = trendCanvas.getContext('2d');
                    // Data injected from Controller
                    const labels = {!! json_encode($chartLabels) !!};
                    const data = {!! json_encode($chartValues) !!};

                    new Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Requests',
                                data: data,
                                borderColor: '#4F46E5', // Primary color
                                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                borderWidth: 2,
                                tension: 0.4, // Smooth curves
                                fill: true,
                                pointRadius: 0,
                                pointHoverRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: { mode: 'index', intersect: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        borderDash: [2, 4],
                                        color: isDarkMode ? '#334155' : '#f3f4f6'
                                    },
                                    ticks: {
                                        color: isDarkMode ? '#94a3b8' : '#64748b'
                                    }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: {
                                        color: isDarkMode ? '#94a3b8' : '#64748b'
                                    }
                                }
                            }
                        }
                    });
                }

            } catch (e) {
                console.error("Chart init failed (Chart.js likely not loaded):", e);
            }
        });
    </script>
@endsection