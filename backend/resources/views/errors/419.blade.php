@include('errors.layout', [
    'code' => 419,
    'badge' => 'Session protection',
    'title' => 'Page expired',
    'message' => 'For security, this page expired before the request was completed. Please reload the page, then try again.',
    'primaryLabel' => 'Reload page',
    'primaryUrl' => request()->fullUrl(),
    'secondaryLabel' => 'Track request',
    'secondaryUrl' => \Illuminate\Support\Facades\Route::has('public.tracking') ? route('public.tracking') : url('/'),
])
