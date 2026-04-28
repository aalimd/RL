@include('errors.layout', [
    'code' => 405,
    'badge' => 'Method blocked',
    'title' => 'This action is not available',
    'message' => 'The page received a request method it cannot accept. Please return to the page and try the action again.',
    'primaryLabel' => 'Go home',
    'primaryUrl' => \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/'),
    'secondaryLabel' => 'Track request',
    'secondaryUrl' => \Illuminate\Support\Facades\Route::has('public.tracking') ? route('public.tracking') : url('/'),
])
