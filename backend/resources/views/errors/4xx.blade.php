@php
    $statusCode = isset($exception) && method_exists($exception, 'getStatusCode')
        ? (int) $exception->getStatusCode()
        : 400;
@endphp

@include('errors.layout', [
    'code' => $statusCode,
    'badge' => 'Request notice',
    'title' => 'Request interrupted',
    'message' => 'The request could not be completed with the current link, session, or permissions. Please return to a safe page and try again.',
    'primaryLabel' => 'Go home',
    'primaryUrl' => \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/'),
    'secondaryLabel' => 'Track request',
    'secondaryUrl' => \Illuminate\Support\Facades\Route::has('public.tracking') ? route('public.tracking') : url('/'),
])
