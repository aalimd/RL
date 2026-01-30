@extends('layouts.admin')

@section('page-title', 'Analytics')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i data-feather="file-text"></i>
        </div>
        <div class="stat-title">Total Requests</div>
        <div class="stat-value">{{ $analytics['total'] ?? 0 }}</div>
    </div>
    
    @foreach($analytics['byStatus'] ?? [] as $status => $count)
        <div class="stat-card">
            <div class="stat-icon 
                @if($status === 'Approved') green
                @elseif($status === 'Rejected') red
                @elseif($status === 'Pending') yellow
                @else blue @endif">
                <i data-feather="@if($status === 'Approved')check-circle @elseif($status === 'Rejected')x-circle @else clock @endif"></i>
            </div>
            <div class="stat-title">{{ $status }}</div>
            <div class="stat-value">{{ $count }}</div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header">
        <h3>Requests by Month</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            @foreach($analytics['byMonth'] ?? [] as $month => $count)
                <div style="background: #f3f4f6; padding: 1rem; border-radius: 0.5rem; text-align: center; min-width: 80px;">
                    <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.25rem;">
                        {{ \Carbon\Carbon::create()->month($month)->format('M') }}
                    </div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #111827;">{{ $count }}</div>
                </div>
            @endforeach
            
            @if(empty($analytics['byMonth']))
                <p style="color: #6b7280;">No data available yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
