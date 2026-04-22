@php
    $contentHtml = isset($body) && $body
        ? $body
        : view('emails.partials.tracking-verification-body', [
            'requestModel' => $requestModel,
            'otp' => $otp,
        ])->render();
@endphp

@include('emails.layout', [
    'branding' => $branding ?? [],
    'badge' => 'Security code',
    'title' => 'Verify access to your request',
    'summary' => 'Enter this short-lived code on the tracking page to continue securely.',
    'contentHtml' => $contentHtml,
    'closingNote' => 'For security, do not share this code with anyone.',
])
