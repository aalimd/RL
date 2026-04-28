@php
    $statusCode = isset($exception) && method_exists($exception, 'getStatusCode')
        ? (int) $exception->getStatusCode()
        : 500;
@endphp

@include('errors.layout', [
    'code' => $statusCode,
    'badge' => 'Service notice',
    'title' => 'Temporary service issue',
    'message' => 'The application could not complete this request right now. Please try again in a moment.',
    'primaryLabel' => 'Try again',
    'primaryUrl' => request()->fullUrl(),
    'secondaryLabel' => 'Go home',
    'secondaryUrl' => \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/'),
])
