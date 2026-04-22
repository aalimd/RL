@if(!empty($textBody))
{{ $textBody }}
@else
Hello {{ $request->student_name }},

We received your recommendation request and added it to the review queue.

Tracking ID: {{ $request->tracking_id }}
Request ID: #{{ $request->id }}
Submitted: {{ optional($request->created_at)->format('M d, Y h:i A') }}

Track your request:
{{ $trackingUrl }}
@endif

{{ $branding['site_name'] ?? config('app.name') }}
@if(!empty($branding['support_email']))
Support: {{ $branding['support_email'] }}
@endif
