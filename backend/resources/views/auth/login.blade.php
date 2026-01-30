@extends('layouts.app')

@section('title', 'Admin Login - ' . ($settings['siteName'] ?? 'AAMD Recommendations'))

@section('styles')
<style>
    .login-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        position: relative;
        overflow: hidden;
    }
    
    /* Floating Particles */
    .particles {
        position: absolute;
        inset: 0;
        overflow: hidden;
        pointer-events: none;
    }
    
    .particle {
        position: absolute;
        width: 6px;
        height: 6px;
        background: var(--primary);
        border-radius: 50%;
        opacity: 0.3;
        animation: float-particle 15s infinite;
    }
    
    .particle:nth-child(1) { left: 10%; animation-delay: 0s; }
    .particle:nth-child(2) { left: 30%; animation-delay: 3s; background: var(--secondary); }
    .particle:nth-child(3) { left: 50%; animation-delay: 6s; }
    .particle:nth-child(4) { left: 70%; animation-delay: 9s; background: var(--accent); }
    .particle:nth-child(5) { left: 90%; animation-delay: 12s; background: var(--secondary); }
    
    @keyframes float-particle {
        0% { transform: translateY(100vh) scale(0); opacity: 0; }
        10% { opacity: 0.4; }
        90% { opacity: 0.4; }
        100% { transform: translateY(-100vh) scale(1); opacity: 0; }
    }
    
    /* Login Card */
    .login-wrapper {
        width: 100%;
        max-width: 420px;
        position: relative;
        z-index: 10;
    }
    
    /* Animated Icon */
    .login-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        border-radius: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        box-shadow: 0 20px 40px -15px rgba(99, 102, 241, 0.5);
        animation: float 3s ease-in-out infinite;
    }
    
    .login-icon svg {
        width: 40px;
        height: 40px;
        color: white;
    }
    
    .login-header {
        text-align: center;
        margin-bottom: 2rem;
        animation: slide-up 0.5s ease-out;
    }
    
    .login-header h1 {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    
    .login-header p {
        color: var(--text-muted);
        font-size: 0.95rem;
    }
    
    .login-card {
        background: var(--bg-secondary);
        border-radius: 1.5rem;
        box-shadow: 0 20px 60px -20px var(--shadow-color);
        padding: 2.5rem;
        border: 1px solid var(--border-light);
        animation: slide-up 0.5s ease-out 0.1s backwards;
    }
    
    @keyframes slide-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Form Styles */
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    
    .input-wrapper {
        position: relative;
    }
    
    .input-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
        transition: color 0.2s;
    }
    
    .form-input {
        width: 100%;
        padding: 0.875rem 1rem 0.875rem 2.75rem;
        border: 2px solid var(--border-color);
        border-radius: 0.75rem;
        font-size: 1rem;
        transition: all 0.2s;
        background: var(--input-bg);
        color: var(--text-primary);
    }
    
    .form-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }
    
    .form-input:focus + .input-icon,
    .form-input:not(:placeholder-shown) + .input-icon {
        color: var(--primary);
    }
    
    .form-input::placeholder {
        color: #9ca3af;
    }
    
    /* Password Toggle */
    .password-toggle {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0.25rem;
        transition: color 0.2s;
    }
    
    .password-toggle:hover {
        color: var(--primary);
    }
    
    /* Login Button */
    .login-btn {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        border: none;
        border-radius: 0.75rem;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        position: relative;
        overflow: hidden;
    }
    
    .login-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px -10px rgba(99, 102, 241, 0.6);
    }
    
    .login-btn::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, transparent, rgba(255,255,255,0.2), transparent);
        transform: translateX(-100%);
        transition: transform 0.5s;
    }
    
    .login-btn:hover::before {
        transform: translateX(100%);
    }
    
    .login-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }
    
    /* Error Box */
    .error-box {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        padding: 1rem;
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
        font-size: 0.875rem;
        text-align: center;
        border: 1px solid rgba(239, 68, 68, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        animation: shake 0.5s ease-in-out;
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-5px); }
        40%, 80% { transform: translateX(5px); }
    }
    
    /* Security Badge */
    .security-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1.5rem;
        padding: 0.75rem;
        background: rgba(16, 185, 129, 0.1);
        border-radius: 0.5rem;
        color: var(--accent);
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    /* Footer */
    .login-footer {
        text-align: center;
        margin-top: 2rem;
        animation: slide-up 0.5s ease-out 0.2s backwards;
    }
    
    .login-footer p {
        color: var(--text-muted);
        font-size: 0.8rem;
    }
    
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 0.875rem;
        margin-top: 1rem;
        transition: color 0.2s;
    }
    
    .back-link:hover {
        color: var(--primary);
    }
</style>
@endsection

@section('content')
    <div class="login-page">
        <div class="hero-bg"></div>
        
        <!-- Floating Particles -->
        <div class="particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>
        
        <!-- Theme Toggle -->
        <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme"
            style="position: fixed; top: 1rem; right: 1rem; z-index: 100;">
            <i data-lucide="moon" class="moon-icon"></i>
            <i data-lucide="sun" class="sun-icon"></i>
        </button>

        <div class="login-wrapper">
            <!-- Animated Icon -->
            <div class="login-icon">
                <i data-lucide="shield-check"></i>
            </div>
            
            <div class="login-header">
                <h1>{{ $settings['loginTitle'] ?? 'Admin Login' }}</h1>
                <p>{{ $settings['loginSubtitle'] ?? 'Sign in to access your dashboard' }}</p>
            </div>
            
            <div class="login-card">
                @if(session('error'))
                    <div class="error-box">
                        <i data-lucide="alert-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="error-box">
                        <i data-lucide="alert-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                        <span>
                            @foreach($errors->all() as $error)
                                {{ $error }}
                            @endforeach
                        </span>
                    </div>
                @endif

                <form method="POST" action="{{ url('/login') }}" id="loginForm">
                    @csrf

                    <div class="form-group">
                        <label for="loginIdentifier" class="form-label">Email or Username</label>
                        <div class="input-wrapper">
                            <input type="text" id="loginIdentifier" name="loginIdentifier" class="form-input"
                                placeholder="Enter your email or username" value="{{ old('loginIdentifier') }}" 
                                required autofocus autocomplete="username">
                            <i data-lucide="user" class="input-icon" style="width: 18px; height: 18px;"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" class="form-input"
                                placeholder="Enter your password" required autocomplete="current-password">
                            <i data-lucide="lock" class="input-icon" style="width: 18px; height: 18px;"></i>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i data-lucide="eye" id="eyeIcon" style="width: 18px; height: 18px;"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-btn" id="loginBtn">
                        <i data-lucide="log-in" style="width: 20px; height: 20px;"></i>
                        <span>Sign In Securely</span>
                    </button>
                    
                    <div class="security-badge">
                        <i data-lucide="lock" style="width: 14px; height: 14px;"></i>
                        <span>256-bit SSL Encrypted Connection</span>
                    </div>
                </form>
            </div>
            
            <div class="login-footer">
                <a href="{{ url('/') }}" class="back-link">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                    Back to Home
                </a>
                <p style="margin-top: 1rem;">&copy; {{ date('Y') }} {{ $settings['siteName'] ?? 'AAMD Recommendations' }}</p>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
    });
    
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.setAttribute('data-lucide', 'eye-off');
        } else {
            passwordInput.type = 'password';
            eyeIcon.setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    }
    
    // Prevent double-submit
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('loginBtn');
        if (btn.disabled) {
            e.preventDefault();
            return;
        }
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader-2" style="width: 20px; height: 20px; animation: spin 1s linear infinite;"></i><span>Signing in...</span>';
        lucide.createIcons();
    });
</script>
<style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endsection