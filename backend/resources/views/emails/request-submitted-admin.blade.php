@php
    $contentHtml = isset($body) && $body
        ? $body
        : view('emails.partials.request-submitted-admin-body', [
            'request' => $request,
        ])->render();
@endphp

@include('emails.layout', [
    'branding' => $branding ?? [],
    'badge' => 'Admin notification',
    'title' => 'A new request needs review',
    'summary' => 'A student submission is ready for review inside the admin panel.',
    'contentHtml' => $contentHtml,
    'ctaUrl' => $detailsUrl,
    'ctaLabel' => 'Review request',
    'closingNote' => 'Open the request to review the submission, manage status, and continue the workflow.',
])
