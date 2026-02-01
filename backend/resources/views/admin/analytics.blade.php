@extends('layouts.admin')

@section('page-title', 'Analytics')

@section('styles')
    <style>
        .analytics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .date-filter {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .date-filter input {
            padding: 0.5rem 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }

        .date-filter button {
            padding: 0.5rem 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 500;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .mini-stat {
            background: white;
            padding: 1.25rem;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #f3f4f6;
        }

        .mini-stat .label {
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .mini-stat .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
        }

        .mini-stat .value.green {
            color: #10b981;
        }

        .mini-stat .value.red {
            color: #ef4444;
        }

        .mini-stat .value.yellow {
            color: #f59e0b;
        }

        .mini-stat .value.blue {
            color: #3b82f6;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #f3f4f6;
            overflow: hidden;
        }

        .chart-card .card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            font-weight: 600;
            color: #111827;
        }

        .chart-card .card-body {
            padding: 1.5rem;
        }

        .chart-container {
            height: 300px;
            position: relative;
        }

        .top-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .top-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .top-list li:last-child {
            border-bottom: none;
        }

        .top-list .name {
            font-size: 0.875rem;
            color: #374151;
            font-weight: 500;
        }

        .top-list .count {
            background: #f3f4f6;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #4b5563;
        }

        .empty-message {
            text-align: center;
            padding: 2rem;
            color: #9ca3af;
        }

        /* Dark Mode for Analytics */
        body.dark-mode .analytics-header p {
            color: var(--text-muted);
        }

        body.dark-mode .date-filter span {
            color: var(--text-muted);
        }

        body.dark-mode .mini-stat {
            background: var(--card-bg);
            border-color: var(--border-color);
        }

        body.dark-mode .mini-stat .label {
            color: var(--text-muted);
        }

        body.dark-mode .mini-stat .value {
            color: var(--text-main);
        }

        body.dark-mode .chart-card {
            background: var(--card-bg);
            border-color: var(--border-color);
        }

        body.dark-mode .chart-card .card-header {
            color: var(--text-main);
            border-bottom-color: var(--border-color);
        }

        body.dark-mode .top-list li {
            border-bottom-color: var(--border-color);
        }

        body.dark-mode .top-list .name {
            color: var(--text-main);
        }

        body.dark-mode .top-list .count {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-muted);
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Header with Date Filter -->
    <div class="analytics-header">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: #111827; margin-bottom: 0.25rem;">Analytics Dashboard
            </h2>
            <p style="font-size: 0.875rem; color: #6b7280;">
                {{ \Carbon\Carbon::parse($analytics['dateFrom'])->format('M d, Y') }} -
                {{ \Carbon\Carbon::parse($analytics['dateTo'])->format('M d, Y') }}
            </p>
        </div>
        <form method="GET" class="date-filter">
            <input type="date" name="date_from" value="{{ $analytics['dateFrom'] }}">
            <span style="color: #9ca3af;">to</span>
            <input type="date" name="date_to" value="{{ $analytics['dateTo'] }}">
            <button type="submit">
                <i data-feather="filter" style="width: 14px; height: 14px; margin-right: 0.25rem;"></i>
                Filter
            </button>
        </form>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="mini-stat">
            <div class="label">Total Requests</div>
            <div class="value blue">{{ $analytics['total'] }}</div>
        </div>
        <div class="mini-stat">
            <div class="label">Approved</div>
            <div class="value green">{{ $analytics['byStatus']['Approved'] ?? 0 }}</div>
        </div>
        <div class="mini-stat">
            <div class="label">Rejected</div>
            <div class="value red">{{ $analytics['byStatus']['Rejected'] ?? 0 }}</div>
        </div>
        <div class="mini-stat">
            <div class="label">Under Review</div>
            <div class="value yellow">{{ $analytics['byStatus']['Under Review'] ?? 0 }}</div>
        </div>
        <div class="mini-stat">
            <div class="label">Avg Processing</div>
            <div class="value">{{ $analytics['avgProcessingDays'] }} days</div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-grid">
        <!-- Trend Chart -->
        <div class="chart-card">
            <div class="card-header">
                <i data-feather="trending-up" style="width: 18px; height: 18px; margin-right: 0.5rem;"></i>
                Daily Requests Trend
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Status Pie -->
        <div class="chart-card">
            <div class="card-header">
                <i data-feather="pie-chart" style="width: 18px; height: 18px; margin-right: 0.5rem;"></i>
                Status Breakdown
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Lists Row -->
    <div class="charts-grid">
        <!-- Top Universities -->
        <div class="chart-card">
            <div class="card-header">
                <i data-feather="award" style="width: 18px; height: 18px; margin-right: 0.5rem;"></i>
                Top Universities
            </div>
            <div class="card-body" style="padding: 1rem 1.5rem;">
                @if(count($analytics['topUniversities']) > 0)
                    <ul class="top-list">
                        @foreach($analytics['topUniversities'] as $name => $count)
                            <li>
                                <span class="name">{{ $name }}</span>
                                <span class="count">{{ $count }} requests</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="empty-message">No university data available</div>
                @endif
            </div>
        </div>

        <!-- Top Purposes -->
        <div class="chart-card">
            <div class="card-header">
                <i data-feather="target" style="width: 18px; height: 18px; margin-right: 0.5rem;"></i>
                Top Purposes
            </div>
            <div class="card-body" style="padding: 1rem 1.5rem;">
                @if(count($analytics['topPurposes']) > 0)
                    <ul class="top-list">
                        @foreach($analytics['topPurposes'] as $name => $count)
                            <li>
                                <span class="name">{{ $name }}</span>
                                <span class="count">{{ $count }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="empty-message">No purpose data available</div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Trend Line Chart
            const trendCanvas = document.getElementById('trendChart');
            if (trendCanvas) {
                const trendData = @json($analytics['dailyTrend']);
                const labels = Object.keys(trendData);
                const values = Object.values(trendData);

                new Chart(trendCanvas, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Requests',
                            data: values,
                            borderColor: '#4F46E5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 3,
                            pointHoverRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f3f4f6' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { maxTicksLimit: 10 }
                            }
                        }
                    }
                });
            }

            // Status Doughnut Chart
            const statusCanvas = document.getElementById('statusChart');
            if (statusCanvas) {
                const statusData = @json($analytics['byStatus']);

                new Chart(statusCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(statusData),
                        datasets: [{
                            data: Object.values(statusData),
                            backgroundColor: ['#10b981', '#ef4444', '#f59e0b', '#6b7280', '#3b82f6'],
                            borderWidth: 0
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
                                    padding: 15,
                                    font: { size: 11 }
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            }
        });
    </script>
@endsection