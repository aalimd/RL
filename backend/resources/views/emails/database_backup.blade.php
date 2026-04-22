@php
    $contentHtml = view('emails.partials.database-backup-body', [
        'backupDate' => $backupDate,
        'branding' => $branding ?? [],
    ])->render();
@endphp

@include('emails.layout', [
    'branding' => $branding ?? [],
    'badge' => 'System maintenance',
    'title' => 'Database backup generated',
    'summary' => 'The requested SQL backup is attached to this email.',
    'contentHtml' => $contentHtml,
    'closingNote' => 'Only share this backup with authorized administrators.',
])
