@include('errors.layout', [
    'code' => 503,
    'badge' => 'Service unavailable',
    'title' => 'Temporarily unavailable',
    'message' => 'The application is being updated or is temporarily unavailable. Please try again shortly.',
    'primaryLabel' => 'Try again',
    'primaryUrl' => request()->fullUrl(),
    'secondaryLabel' => 'Go home',
    'secondaryUrl' => \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/'),
])
