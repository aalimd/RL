@if(!empty($textBody))
{{ $textBody }}
@else
Hello{{ $recipientName !== 'there' ? ' ' . $recipientName : '' }},

Use this verification code to {{ $actionLabel }}:

{{ $code }}

This code expires in 10 minutes. If you did not request it, you can ignore this email.
@endif

{{ $branding['site_name'] ?? config('app.name') }}
