@php
    $fullName = trim(implode(' ', array_filter([$request->student_name, $request->middle_name, $request->last_name])));
@endphp
@if(!empty($textBody))
{{ $textBody }}
@else
New request received and ready for review.

Request ID: #{{ $request->id }}
Tracking ID: {{ $request->tracking_id }}
Student: {{ $fullName }}
Email: {{ $request->student_email }}
@if(!empty($request->university))
Destination: {{ $request->university }}
@endif
@if(!empty($request->purpose))
Purpose: {{ $request->purpose }}
@endif

Review the request:
{{ $detailsUrl }}
@endif

{{ $branding['site_name'] ?? config('app.name') }}
