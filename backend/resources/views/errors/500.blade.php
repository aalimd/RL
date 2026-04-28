@include('errors.layout', [
    'code' => 500,
    'badge' => 'Server error',
    'title' => 'Something went wrong',
    'message' => 'The application could not finish this request. Please try again in a moment.',
    'primaryLabel' => 'Try again',
    'primaryUrl' => request()->fullUrl(),
    'secondaryLabel' => 'Go home',
    'secondaryUrl' => \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/'),
])
