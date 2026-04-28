@include('errors.layout', [
    'code' => 404,
    'badge' => 'Page not found',
    'title' => 'This page is not available',
    'message' => 'The link may be incorrect, expired, or moved. You can return home or open tracking to find your request.',
    'primaryLabel' => 'Go home',
    'primaryUrl' => \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/'),
    'secondaryLabel' => 'Track request',
    'secondaryUrl' => \Illuminate\Support\Facades\Route::has('public.tracking') ? route('public.tracking') : url('/'),
])
