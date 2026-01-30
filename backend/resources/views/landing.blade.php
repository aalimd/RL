@extends('layouts.app')

@section('title', $settings['siteName'] ?? 'AAMD Recommendations')

@section('styles')
    <style>
        /* ========================================
                       HERO SECTION
                       ======================================== */
        .hero-section {
            min-height: calc(100vh - 4.5rem);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 3rem 1rem;
        }

        /* ========================================
                       FLOATING PARTICLES
                       ======================================== */
        .particles {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            will-change: transform;
        }

        .particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: var(--primary);
            border-radius: 50%;
            opacity: 0.3;
            animation: float-particle 15s infinite;
            will-change: transform, opacity;
        }

        .particle:nth-child(1) {
            left: 10%;
            animation-delay: 0s;
        }

        .particle:nth-child(2) {
            left: 25%;
            animation-delay: 2s;
            background: var(--secondary);
        }

        .particle:nth-child(3) {
            left: 40%;
            animation-delay: 4s;
        }

        .particle:nth-child(4) {
            left: 55%;
            animation-delay: 6s;
            background: var(--accent);
        }

        .particle:nth-child(5) {
            left: 70%;
            animation-delay: 8s;
        }

        .particle:nth-child(6) {
            left: 85%;
            animation-delay: 10s;
            background: var(--secondary);
        }

        @keyframes float-particle {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }

            10% {
                opacity: 0.4;
            }

            90% {
                opacity: 0.4;
            }

            100% {
                transform: translateY(-100vh) scale(1);
                opacity: 0;
            }
        }

        /* ========================================
                       HERO CONTENT
                       ======================================== */
        .hero-content {
            max-width: 700px;
            text-align: center;
            position: relative;
            z-index: 10;
        }

        /* ========================================
                       ANIMATED BADGE
                       ======================================== */
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(139, 92, 246, 0.15));
            border: 1px solid rgba(99, 102, 241, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            color: var(--primary);
            animation: pulse-glow 2s ease-in-out infinite;
            will-change: box-shadow;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(99, 102, 241, 0.2);
            }

            50% {
                box-shadow: 0 0 30px rgba(99, 102, 241, 0.4);
            }
        }

        /* ========================================
                       HERO TITLE
                       ======================================== */
        .hero-title {
            font-size: 2.5rem;
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -0.03em;
        }

        .hero-title .line1 {
            display: block;
            color: var(--text-primary);
            animation: slide-up 0.6s ease-out;
        }

        .hero-title .line2 {
            display: block;
            background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: var(--primary);
            animation: slide-up 0.6s ease-out 0.1s backwards;
        }

        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ========================================
                       HERO DESCRIPTION
                       ======================================== */
        .hero-description {
            font-size: 1.125rem;
            color: var(--text-secondary);
            line-height: 1.8;
            margin-bottom: 2.5rem;
            max-width: 550px;
            margin-left: auto;
            margin-right: auto;
            animation: slide-up 0.6s ease-out 0.2s backwards;
        }

        /* ========================================
                       ACTION BUTTONS
                       ======================================== */
        .hero-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            animation: slide-up 0.6s ease-out 0.3s backwards;
        }

        .hero-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        /* Primary Button with Shimmer Effect */
        .hero-btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 10px 40px -10px rgba(99, 102, 241, 0.5);
            animation: subtle-pulse 3s ease-in-out infinite;
        }

        @keyframes subtle-pulse {

            0%,
            100% {
                box-shadow: 0 10px 40px -10px rgba(99, 102, 241, 0.5);
            }

            50% {
                box-shadow: 0 15px 50px -10px rgba(99, 102, 241, 0.7);
            }
        }

        .hero-btn-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 20px 50px -15px rgba(99, 102, 241, 0.6);
        }

        /* Shimmer Effect */
        .hero-btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                left: -100%;
            }

            50%,
            100% {
                left: 100%;
            }
        }

        .hero-btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 2px solid var(--border-color);
            position: relative;
        }

        .hero-btn-secondary::before {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 0.875rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            opacity: 0;
            z-index: -1;
            transition: opacity 0.3s ease;
        }

        .hero-btn-secondary:hover {
            transform: translateY(-3px);
            border-color: transparent;
            box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.3);
        }

        .hero-btn-secondary:hover::before {
            opacity: 0.15;
        }

        /* ========================================
                       QUICK STATS - Enhanced
                       ======================================== */
        .quick-stats {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 2rem;
            gap: clamp(1.5rem, 5vw, 4rem);
            margin-top: 4rem;
            animation: slide-up 0.6s ease-out 0.4s backwards;
            padding: 0 1rem;
        }

        .stat-item {
            text-align: center;
            flex: 0 0 auto;
            min-width: 100px;
            cursor: default;
            transition: transform 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            color: var(--primary);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-icon::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: 17px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            opacity: 0;
            z-index: -1;
            transition: opacity 0.3s ease;
        }

        .stat-item:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .stat-item:hover .stat-icon::before {
            opacity: 1;
        }

        .stat-value {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.25rem;
            opacity: 0.9;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* ========================================
                       SCROLL TO TOP BUTTON
                       ======================================== */
        .scroll-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.3s ease;
            z-index: 100;
        }

        .scroll-to-top.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .scroll-to-top:hover {
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 8px 30px rgba(99, 102, 241, 0.5);
        }

        /* ========================================
                       FOOTER
                       ======================================== */
        .landing-footer {
            padding: 1.5rem;
            text-align: center;
            border-top: 1px solid var(--border-light);
            background: var(--bg-secondary);
        }

        .landing-footer p {
            color: var(--text-muted);
            font-size: 0.8125rem;
            margin: 0;
        }

        /* ========================================
                       MOBILE RESPONSIVE
                       ======================================== */
        @media (max-width: 640px) {
            .hero-actions {
                flex-direction: column;
                padding: 0 1rem;
            }

            .hero-btn {
                width: 100%;
                justify-content: center;
            }

            .quick-stats {
                gap: 1rem;
            }

            .stat-icon {
                width: 48px;
                height: 48px;
            }

            .stat-value {
                font-size: 0.7rem;
            }

            .scroll-to-top {
                bottom: 1rem;
                right: 1rem;
                width: 42px;
                height: 42px;
            }
        }
    </style>
@endsection

@section('content')
    <div style="min-height: 100vh; display: flex; flex-direction: column;">
        <!-- Navigation -->
        <nav class="nav">
            <div class="nav-inner">
                <a href="{{ url('/') }}" class="logo">
                    @if(!empty($settings['logoUrl']))
                        <img src="{{ $settings['logoUrl'] }}" alt="Logo"
                            style="height: 2.5rem; margin-right: 0.75rem; border-radius: 0.5rem;">
                    @else
                        <div class="logo-icon">R</div>
                    @endif
                    <span class="logo-text">{{ $settings['siteName'] ?? 'AAMD' }}</span>
                </a>

                <div class="nav-links">
                    <a href="{{ url('/track') }}" class="btn btn-secondary" style="padding: 0.5rem 1rem;">
                        <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                        Track
                    </a>
                    <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme">
                        <i data-lucide="moon" class="moon-icon"></i>
                        <i data-lucide="sun" class="sun-icon"></i>
                    </button>
                    <a href="/RL/login" class="btn-icon" title="Admin Panel"
                        style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-secondary); transition: all 0.2s;">
                        <i data-lucide="shield" style="width: 18px; height: 18px;"></i>
                    </a>
                    <a href="{{ url('/request') }}" class="btn btn-primary" style="padding: 0.5rem 1rem;">
                        <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                        Request
                    </a>
                </div>

                <button class="mobile-menu-btn" onclick="toggleMobileMenu()" aria-label="Toggle Menu">
                    <i data-lucide="menu" class="menu-icon" style="width: 24px; height: 24px;"></i>
                    <i data-lucide="x" class="close-icon" style="width: 24px; height: 24px; display: none;"></i>
                </button>
            </div>

            <div id="mobileMenu" class="mobile-menu">
                <a href="{{ url('/track') }}">
                    <i data-lucide="search" style="width: 18px; height: 18px;"></i>
                    Track Request
                </a>
                <a href="/RL/login">
                    <i data-lucide="shield" style="width: 18px; height: 18px;"></i>
                    Admin Panel
                </a>
                <button class="theme-toggle" onclick="toggleTheme()"
                    style="margin: 0.5rem 0; width: 100%; justify-content: flex-start; gap: 0.75rem; padding: 0.875rem;">
                    <i data-lucide="moon" class="moon-icon" style="width: 18px; height: 18px;"></i>
                    <i data-lucide="sun" class="sun-icon" style="width: 18px; height: 18px;"></i>
                    <span>Toggle Theme</span>
                </button>
                <a href="{{ url('/request') }}" class="btn btn-primary"
                    style="margin-top: 0.5rem; justify-content: center;">
                    <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
                    New Request
                </a>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-bg"></div>

            <!-- Floating Particles -->
            <div class="particles">
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
            </div>

            <div class="hero-content">
                <!-- Badge -->
                <div class="hero-badge">
                    <i data-lucide="zap" style="width: 16px; height: 16px;"></i>
                    <span>Fast & Secure</span>
                </div>

                <!-- Title -->
                <h1 class="hero-title">
                    <span class="line1">{{ $settings['heroTitle1'] ?? 'Secure Your Academic' }}</span>
                    <span class="line2">{{ $settings['heroTitle2'] ?? 'Future Today' }}</span>
                </h1>

                <!-- Description -->
                <p class="hero-description">
                    {{ $settings['heroDescription'] ?? $settings['welcomeText'] ?? 'Professional recommendation letters for your academic and career journey. Simple, fast, and secure.' }}
                </p>

                <!-- Action Buttons -->
                <div class="hero-actions">
                    <a href="{{ url('/request') }}" class="hero-btn hero-btn-primary">
                        <i data-lucide="file-plus" style="width: 20px; height: 20px;"></i>
                        {{ $settings['heroPrimaryBtn'] ?? 'Request Recommendation' }}
                        <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                    </a>
                    <a href="{{ url('/track') }}" class="hero-btn hero-btn-secondary">
                        <i data-lucide="search" style="width: 20px; height: 20px;"></i>
                        {{ $settings['heroSecondaryBtn'] ?? 'Track Existing Request' }}
                    </a>
                </div>

                <!-- Quick Stats with Values -->
                <div class="quick-stats">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i data-lucide="shield-check" style="width: 24px; height: 24px;"></i>
                        </div>
                        <div class="stat-value">256-bit SSL</div>
                        <div class="stat-label">Secure</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i data-lucide="clock" style="width: 24px; height: 24px;"></i>
                        </div>
                        <div class="stat-value">24-48 hrs</div>
                        <div class="stat-label">Fast Response</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i data-lucide="award" style="width: 24px; height: 24px;"></i>
                        </div>
                        <div class="stat-value">100%</div>
                        <div class="stat-label">Professional</div>
                    </div>
                </div>
            </div>

        </section>

        <!-- Footer -->
        <footer class="landing-footer">
            @php
                $footerText = $settings['footerText'] ?? '© {year} {siteName}';
                $footerText = str_replace('{year}', date('Y'), $footerText);
                $footerText = str_replace('{siteName}', $settings['siteName'] ?? 'AAMD Recommendations', $footerText);
            @endphp
            <p>{{ $footerText }}</p>
        </footer>
    </div>

    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop" onclick="scrollToTop()" aria-label="Scroll to top">
        <i data-lucide="arrow-up" style="width: 20px; height: 20px;"></i>
    </button>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();

            const scrollToTopBtn = document.getElementById('scrollToTop');

            window.addEventListener('scroll', function () {
                const scrollY = window.scrollY;

                // Show scroll-to-top button
                if (scrollY > 300) {
                    scrollToTopBtn.classList.add('visible');
                } else {
                    scrollToTopBtn.classList.remove('visible');
                }
            });
        });

        // Scroll to top function
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Enhanced mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            const menuIcon = menuBtn.querySelector('.menu-icon');
            const closeIcon = menuBtn.querySelector('.close-icon');

            menu.classList.toggle('active');

            // Toggle icons
            if (menu.classList.contains('active')) {
                menuIcon.style.display = 'none';
                closeIcon.style.display = 'block';
            } else {
                menuIcon.style.display = 'block';
                closeIcon.style.display = 'none';
            }
        }
    </script>
@endsection