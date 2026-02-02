@extends('layouts.admin')

@section('page-title', 'Audit Logs')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3>Activity Logs</h3>
        </div>
        <div class="card-body" style="padding: 0; overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $details = $log->details;
                            $decoded = json_decode($details, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                // Simplify known patterns
                                if (isset($decoded['template_id'])) {
                                    $actionName = isset($decoded['name']) ? "Template: {$decoded['name']}" : "Template #{$decoded['template_id']}";
                                    $details = "Modified " . $actionName;
                                } elseif (isset($decoded['tracking_id'])) {
                                    $details = "Request " . $decoded['tracking_id'];
                                } elseif (isset($decoded['ids'])) {
                                    $details = "Bulk Action on " . count($decoded['ids']) . " items";
                                }
                            }
                        @endphp
                        <tr>
                            <td style="font-size: 0.875rem; color: var(--text-muted);">
                                {{ $log->created_at->format('M d, Y H:i') }}</td>
                            <td>{{ $log->user->name ?? 'System' }}</td>
                            <td>
                                <span class="badge badge-pending">{{ str_replace('_', ' ', $log->action) }}</span>
                            </td>
                            <td style="max-width: 400px; overflow: hidden; text-overflow: ellipsis;"
                                title="{{ is_string($log->details) ? $log->details : '' }}">
                                {{ $details }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                <i data-feather="activity"
                                    style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p>No activity logs yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="card-body" style="border-top: 1px solid var(--border-color);">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection