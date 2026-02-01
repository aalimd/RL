@extends('layouts.app')

@section('title', 'Two-Factor Authentication')

@section('styles')
<style>
    .auth-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        position: relative;
        overflow: hidden;
        /* Use main background variable */
        background: var(--bg-primary);
    }

    /* Ambient Background Effect (Matches Landing) */
    .ambient-bg {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        overflow: hidden;
        z-index: 0;
    }
    .ambient-bg::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 120%;
        height: 120%;
        background: radial-gradient(ellipse at center, var(--primary) 0%, var(--secondary) 30%, transparent 70%);
        opacity: 0.08;
    }
    /* Dark mode adjustment for ambient bg */
    html.dark .ambient-bg::before {
        opacity: 0.15;
    }

    /* Particles */
    .particles {
        position: absolute;
        inset: 0;
        pointer-events: none;
        overflow: hidden;
        z-index: 1;
    }
    .particle {
        position: absolute;
        width: 6px;
        height: 6px;
        background: var(--primary);
        border-radius: 50%;
        opacity: 0.4;
        animation: float-particle 20s infinite linear;
    }
    /* Use theme variables for particles */
    .particle:nth-child(2) { background: var(--secondary); animation-duration: 25s; }
    .particle:nth-child(4) { background: var(--accent); animation-duration: 22s; }

    @keyframes float-particle {
        0% { transform: translateY(100vh) scale(0); opacity: 0; }
        50% { opacity: 0.5; }
        100% { transform: translateY(-100vh) scale(1); opacity: 0; }
    }

    /* Glass Card - Using Theme Variables */
    .auth-card {
        max-width: 420px;
        width: 100%;
        position: relative;
        z-index: 10;
        border-radius: 1.5rem;
        
        /* Strict usage of theme variables for Glassmorphism */
        background: var(--glass-bg); 
        border: 1px solid var(--glass-border);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        
        box-shadow: 0 25px 50px -12px var(--shadow-color);
        padding: 2.5rem 2rem;
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .auth-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 35px 60px -15px var(--shadow-color);
    }

    /* Icon */
    .icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        margin: 0 auto 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        
        /* Dynamic gradient based on primary/secondary variables */
        background: linear-gradient(135deg, var(--bg-primary), var(--bg-secondary)); 
        border: 1px solid var(--border-color);
        box-shadow: 0 10px 20px var(--shadow-color);
        
        color: var(--primary);
        position: relative;
    }
    
    .icon-pulse {
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        border: 2px solid var(--primary);
        opacity: 0;
        animation: ring-pulse 2s infinite;
    }

    @keyframes ring-pulse {
        0% { transform: scale(0.9); opacity: 0.3; }
        100% { transform: scale(1.4); opacity: 0; }
    }

    /* Typography */
    .auth-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
        letter-spacing: -0.025em;
    }
    .auth-desc {
        color: var(--text-secondary);
        font-size: 1rem;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    /* Input - Using Input Theme Variables */
    .code-input {
        width: 100%;
        padding: 1rem;
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: 0.5rem;
        text-align: center;
        
        background: var(--input-bg);
        border: 2px solid var(--border-color);
        color: var(--text-primary);
        
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }
    .code-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15); /* Keep primary color shadow or derive? Hard to derive alpha in pure CSS without calc, keeping hardcoded alpha of a likely primary */
        transform: scale(1.02);
    }
    /* Correction: if primary matches --primary var, use that. But box-shadow with alpha needs RGB var. Layout has no primary-rgb. We stick to focus ring or use opacity tricks. */

    /* Button - Gradient using vars */
    .btn-verify {
        width: 100%;
        padding: 1rem;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        font-weight: 600;
        font-size: 1rem;
        border: none;
        cursor: pointer;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); 
        transition: all 0.3s ease;
    }
    .btn-verify:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.2);
        opacity: 0.95;
    }

    /* Links */
    .link-action {
        background: none;
        border: none;
        color: var(--text-muted);
        font-size: 0.9rem;
        cursor: pointer;
        margin-top: 1.25rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: color 0.2s;
    }
    .link-action:hover {
        color: var(--primary);
    }

    /* Alerts */
    .alert {
        padding: 1rem;
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-align: left;
    }
    .alert-error {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    .alert-success {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
    
    /* Theme Toggle */
    .floating-theme-toggle {
        position: fixed;
        top: 2rem;
        right: 2rem;
        z-index: 50;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 12px var(--shadow-color);
        transition: all 0.3s ease;
    }
    .floating-theme-toggle:hover {
        transform: scale(1.1);
        color: var(--primary);
        border-color: var(--primary);
    }
</style>
@endsection

@section('content')
<div class="auth-page">
    
    <!-- Ambient Background -->
    <div class="ambient-bg"></div>

    <!-- Particles -->
    <div class="particles">
        <div class="particle" style="left: 15%; animation-delay: 0s;"></div>
        <div class="particle" style="left: 35%; animation-delay: 2s;"></div>
        <div class="particle" style="left: 55%; animation-delay: 4s;"></div>
        <div class="particle" style="left: 75%; animation-delay: 6s;"></div>
        <div class="particle" style="left: 95%; animation-delay: 8s;"></div>
    </div>

    <!-- Theme Toggle -->
    <button class="floating-theme-toggle" onclick="toggleTheme()" title="Toggle Theme">
        <i data-lucide="moon" class="moon-icon"></i>
        <i data-lucide="sun" class="sun-icon"></i>
    </button>

    <!-- Glass Card -->
    <div class="auth-card">
        <div class="icon-wrapper">
            <i data-lucide="shield-check" style="width: 32px; height: 32px;"></i>
            <div class="icon-pulse"></div>
        </div>

        <h1 class="auth-title">Two-Factor Auth</h1>
        <p class="auth-desc">
            @if($method === 'app')
                Enter the 6-digit code from your authenticator app.
            @else
                Enter the verification code sent to your email.
            @endif
        </p>

        @if(session('error') || $errors->any())
            <div class="alert alert-error">
                <i data-lucide="alert-triangle" style="width: 20px; height: 20px;"></i>
                <span>{{ session('error') ?? $errors->first() }}</span>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                <i data-lucide="check-circle-2" style="width: 20px; height: 20px;"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('admin.2fa.verify.post') }}" method="POST">
            @csrf
            
            <input type="text" 
                   name="code" 
                   class="code-input" 
                   placeholder="000.000" 
                   required 
                   autofocus 
                   maxlength="6" 
                   pattern="[0-9]*"
                   inputmode="numeric"
                   autocomplete="one-time-code">
            
            <button type="submit" class="btn-verify">
                Verify Identity
            </button>
        </form>

        <div style="display: flex; flex-direction: column; align-items: center; gap: 0; margin-top: 1rem;">
            @if($method === 'email')
                <form action="{{ route('admin.2fa.resend') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="link-action">
                        <i data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
                        Resend Code
                    </button>
                </form>
            @endif

            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="link-action" style="color: var(--text-muted);">
                    <i data-lucide="log-out" style="width: 14px; height: 14px;"></i>
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>
@endsection