@extends('layouts.app')

@section('title', 'Verify Access')

@section('styles')
    <style>
        .verify-page {
            min-height: 100vh;
            background: var(--bg-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .verify-card {
            background: var(--bg-secondary);
            border-radius: 1.5rem;
            box-shadow: 0 4px 6px -1px var(--shadow-color);
            padding: 2.5rem;
            width: 100%;
            max-width: 450px;
            text-align: center;
            border: 1px solid var(--border-color);
        }

        .verify-icon {
            width: 64px;
            height: 64px;
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .verify-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .verify-text {
            color: var(--text-secondary);
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .otp-input {
            letter-spacing: 0.5em;
            font-size: 1.5rem;
            text-align: center;
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
    <div class="verify-page">
        <div class="verify-card">
            <div class="verify-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>

            <h1 class="verify-title">Two-Factor Authentication</h1>
            <p class="verify-text">We sent a 6-digit verification code to your linked Telegram account. Please enter it
                below.</p>

            @if(session('error'))
                <div
                    style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('public.tracking.verify.post') }}">
                @csrf
                <div class="form-group">
                    <input type="text" name="otp" class="form-input otp-input" placeholder="000000" maxlength="6" required
                        autofocus pattern="\d*">
                </div>

                <button type="submit" class="btn-submit"
                    style="width: 100%; padding: 1rem; border-radius: 0.75rem; font-weight: 600; margin-top: 1rem;">
                    Verify Code
                </button>
            </form>

            <div style="margin-top: 1.5rem;">
                <a href="{{ route('public.tracking') }}"
                    style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;">Back to Tracking</a>
            </div>
        </div>
    </div>
@endsection