@include('errors.layout', [
    'code' => 401,
    'badge' => 'Authentication required',
    'title' => 'Sign in required',
    'message' => 'Please sign in before opening this page.',
    'primaryLabel' => 'Go to login',
    'primaryUrl' => \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login'),
    'secondaryLabel' => 'Go home',
    'secondaryUrl' => \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/'),
])
