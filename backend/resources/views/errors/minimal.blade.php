@php
    $statusCode = isset($exception) && method_exists($exception, 'getStatusCode')
        ? (int) $exception->getStatusCode()
        : 500;
@endphp

@include('errors.layout', [
    'code' => $statusCode,
    'badge' => 'Application notice',
    'title' => 'Request interrupted',
    'message' => 'We could not complete this request. Please try again or return to a safe page.',
    'primaryLabel' => 'Go home',
    'primaryUrl' => \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/'),
    'secondaryLabel' => 'Track request',
    'secondaryUrl' => \Illuminate\Support\Facades\Route::has('public.tracking') ? route('public.tracking') : url('/'),
])
