@include('errors.layout', [
    'code' => 400,
    'badge' => 'Request check',
    'title' => 'Bad request',
    'message' => 'The request could not be understood. Please refresh the page and try again.',
    'primaryLabel' => 'Go home',
    'primaryUrl' => \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/'),
    'secondaryLabel' => 'Track request',
    'secondaryUrl' => \Illuminate\Support\Facades\Route::has('public.tracking') ? route('public.tracking') : url('/'),
])
