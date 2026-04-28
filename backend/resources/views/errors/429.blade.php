@include('errors.layout', [
    'code' => 429,
    'badge' => 'Rate limit',
    'title' => 'Too many attempts',
    'message' => 'This action was paused because it was tried too many times in a short period. Please wait a moment, then try again.',
    'primaryLabel' => 'Try again',
    'primaryUrl' => request()->fullUrl(),
    'secondaryLabel' => 'Go home',
    'secondaryUrl' => \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/'),
])
