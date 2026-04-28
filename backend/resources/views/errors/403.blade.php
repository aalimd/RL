@php
    $rawMessage = isset($exception) ? trim((string) $exception->getMessage()) : '';
    $isVerificationError = str_contains($rawMessage, 'verify your request') || str_contains($rawMessage, 'OTP');
    $trackingId = request()->route('tracking_id') ?: request()->route('id');
    $trackingId = is_string($trackingId) ? trim($trackingId) : '';
    $trackingUrl = route('public.tracking', $trackingId !== '' ? ['id' => $trackingId] : []);

    $title = $isVerificationError ? 'Verification required' : 'Access restricted';
    $message = $isVerificationError
        ? 'For your privacy, approved letters stay protected until the request is verified with the one-time code. Open tracking, complete verification, then download the PDF again.'
        : ($rawMessage !== '' ? $rawMessage : 'You do not have permission to view this page.');
@endphp

@include('errors.layout', [
    'code' => 403,
    'badge' => $isVerificationError ? 'Security check' : 'Access control',
    'title' => $title,
    'message' => $message,
    'contextLabel' => $trackingId !== '' ? 'Tracking ID' : null,
    'contextValue' => $trackingId !== '' ? $trackingId : null,
    'primaryLabel' => $isVerificationError ? 'Track and verify request' : 'Go home',
    'primaryUrl' => $isVerificationError ? $trackingUrl : (\Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/')),
    'secondaryLabel' => $isVerificationError ? 'Go home' : 'Track request',
    'secondaryUrl' => $isVerificationError ? (\Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/')) : $trackingUrl,
])
