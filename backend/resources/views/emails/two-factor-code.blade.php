@php
    $contentHtml = isset($body) && $body
        ? $body
        : view('emails.partials.two-factor-code-body', [
            'code' => $code,
            'recipientName' => $recipientName,
            'actionLabel' => $actionLabel,
        ])->render();
@endphp

@include('emails.layout', [
    'branding' => $branding ?? [],
    'badge' => 'Account security',
    'title' => 'Your verification code',
    'summary' => 'Use this temporary code to continue your secure sign-in flow.',
    'contentHtml' => $contentHtml,
    'closingNote' => 'If this request was not made by you, review your account security settings.',
])
