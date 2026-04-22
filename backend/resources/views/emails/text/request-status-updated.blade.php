@php
    $message = $request->status === 'Rejected'
        ? trim((string) $request->rejection_reason)
        : trim((string) $request->admin_message);
@endphp
@if(!empty($textBody))
{{ $textBody }}
@else
Hello {{ $request->student_name }},

Your recommendation request has a new status.

Status: {{ $request->status }}
Tracking ID: {{ $request->tracking_id }}
Request ID: #{{ $request->id }}
@if($message !== '')

Details:
{{ $message }}
@endif

Review your request:
{{ $trackingUrl }}
@endif

{{ $branding['site_name'] ?? config('app.name') }}
@if(!empty($branding['support_email']))
Support: {{ $branding['support_email'] }}
@endif
