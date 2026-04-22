@if(!empty($textBody))
{{ $textBody }}
@else
Hello {{ $requestModel->student_name }},

Use this verification code to access request {{ $requestModel->tracking_id }}:

{{ $otp }}

This code expires in 5 minutes. If you did not request it, you can ignore this email.
@endif

{{ $branding['site_name'] ?? config('app.name') }}
