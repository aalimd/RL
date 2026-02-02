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
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
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
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
            opacity: 0.15;
            animation: float-particle 20s infinite linear;
        }

        .particle:nth-child(1) {
            left: 10%;
            top: 20%;
            animation-duration: 25s;
        }

        .particle:nth-child(2) {
            left: 85%;
            top: 15%;
            animation-duration: 30s;
            background: var(--secondary);
        }

        .particle:nth-child(3) {
            left: 50%;
            top: 50%;
            width: 4px;
            height: 4px;
            animation-duration: 15s;
        }

        .particle:nth-child(4) {
            left: 75%;
            top: 80%;
            width: 12px;
            height: 12px;
            animation-duration: 35s;
            background: var(--accent);
        }

        .particle:nth-child(5) {
            left: 15%;
            top: 75%;
            width: 6px;
            height: 6px;
            animation-duration: 22s;
        }

        @keyframes float-particle {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 0;
            }

            10% {
                opacity: 0.3;
            }

            90% {
                opacity: 0.3;
            }

            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        /* ========================================
                               CARD DESIGN
                               ======================================== */
        .landing-card {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 1.5rem;
            padding: 2.5rem 2rem;
            max-width: 800px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 10;
            animation: fadeInUp 0.8s ease-out;
        }

        /* ========================================
                               ANIMATED BADGE
                               ======================================== */
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
            border: 1px solid rgba(99, 102, 241, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--primary);
            animation: pulse-glow 3s ease-in-out infinite;
        }

        /* ========================================
                               HERO TITLE
                               ======================================== */
        .hero-title {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .hero-title .highlight {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-description {
            font-size: 1.125rem;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* ========================================
                               ACTION BUTTONS
                               ======================================== */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .action-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
            border-radius: 1rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: var(--text-primary);
            position: relative;
            overflow: hidden;
        }

        .action-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 10px 40px -10px rgba(99, 102, 241, 0.2);
        }

        .action-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .action-card:hover .action-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .action-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .action-desc {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        /* New Request Special Style */
        .action-card.primary {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, rgba(99, 102, 241, 0.05) 100%);
        }

        .action-card.primary .action-icon {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 8px 16px -4px rgba(99, 102, 241, 0.4);
        }

        /* ========================================
                               RESPONSIVE
                               ======================================== */
        @media (max-width: 640px) {
            .landing-card {
                padding: 2rem 1.5rem;
            }

            .hero-title {
                font-size: 2rem;
            }
        }

        /* Developer Credit */
        .developer-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            padding: 0.5rem 1.25rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        }

        .developer-link::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(16, 185, 129, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .developer-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.15);
            border-color: rgba(99, 102, 241, 0.2);
        }

        .developer-link:hover::before {
            opacity: 1;
        }

        .dev-label {
            font-family: 'Dancing Script', cursive;
            font-size: 1.1rem;
            color: var(--text-muted);
            margin-right: 0.25rem;
        }

        .dev-name {
            font-family: 'Fira Code', monospace;
            font-weight: 600;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 0.95rem;
        }

        html.dark .developer-link {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: none;
        }

        html.dark .developer-link:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        /* Scroll To Top */
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
            box-shadow: 0 8px 25px -5px rgba(99, 102, 241, 0.4);
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 100;
        }

        .scroll-to-top.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .scroll-to-top:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 12px 30px -8px rgba(99, 102, 241, 0.6);
        }
        /* ========================================
           RESPONSIVE HEADER & MOBILE MENU
           ======================================== */
        .nav-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 4.5rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .logo-img {
            height: 2.5rem;
            width: auto;
            border-radius: 0.5rem;
            margin-right: 0.75rem;
        }

        /* Desktop Nav Links */
        .nav-links {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
            padding: 0.5rem;
            z-index: 1001; /* Above menu overlay */
        }

        /* Mobile Menu Overlay */
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .mobile-menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Mobile Menu Container */
        .mobile-menu {
            position: fixed;
            top: 0;
            right: -100%; /* Hidden by default */
            width: 100%;
            max-width: 320px;
            height: 100vh;
            background: var(--bg-secondary);
            z-index: 1000;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
            transform: translateX(100%);
        }

        .mobile-menu.active {
            transform: translateX(0);
            right: 0;
        }

        /* Mobile Menu Header */
        .mobile-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        /* Mobile Menu Items */
        .mobile-nav-link {
            font-size: 1.125rem;
            font-weight: 500;
            color: var(--text-primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .mobile-nav-link:hover, .mobile-nav-link.active {
            background: var(--bg-primary);
            color: var(--primary);
            border-color: var(--border-light);
            transform: translateX(4px);
        }

        .mobile-nav-link i {
            width: 22px;
            height: 22px;
            color: var(--text-muted);
            transition: color 0.2s;
        }

        .mobile-nav-link:hover i {
            color: var(--primary);
        }

        /* Theme Toggle Row */
        .mobile-theme-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-radius: 0.75rem;
            background: var(--bg-primary);
            margin-top: auto; /* Push to bottom */
            cursor: pointer;
            border: 1px solid var(--border-color);
            transition: all 0.2s;
        }

        .mobile-theme-toggle:hover {
            border-color: var(--primary);
        }

        /* Staggered Animation for Items */
        .mobile-menu.active .mobile-nav-link, 
        .mobile-menu.active .mobile-theme-toggle {
            animation: slideInRight 0.4s ease forwards;
            opacity: 0;
        }

        .mobile-menu.active .mobile-nav-link:nth-child(1) { animation-delay: 0.1s; }
        .mobile-menu.active .mobile-nav-link:nth-child(2) { animation-delay: 0.15s; }
        .mobile-menu.active .mobile-nav-link:nth-child(3) { animation-delay: 0.2s; }
        .mobile-menu.active .mobile-theme-toggle { animation-delay: 0.25s; }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none; /* Hide desktop nav */
            }
            .mobile-menu-btn {
                display: block; /* Show hamburger */
            }
        }
    </style>
@endsection

@section('content')
    <div style="min-height: 100vh; display: flex; flex-direction: column;">
        <!-- Navigation -->
        <nav class="nav" style="position: sticky; top: 0; z-index: 999; background: var(--bg-nav); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border-color);">
            <div class="nav-inner">
                <a href="{{ url('/') }}" class="logo">
                    @if(!empty($settings['logoUrl']))
                        <img src="{{ $settings['logoUrl'] }}" alt="Logo" class="logo-img">
                    @else
                        <div class="logo-icon">R</div>
                    @endif
                    <span class="logo-text">{{ $settings['siteName'] ?? 'AAMD' }}</span>
                </a>

                <!-- Desktop Links -->
                <div class="nav-links">
                    <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme">
                        <i data-lucide="moon" class="moon-icon"></i>
                        <i data-lucide="sun" class="sun-icon"></i>
                    </button>
                    <a href="/RL/login" class="btn btn-secondary" style="padding: 0.5rem 1rem;">
                        <i data-lucide="shield" style="width: 16px; height: 16px;"></i>
                        Admin
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()" aria-label="Toggle menu">
                    <i data-lucide="menu" class="menu-icon" style="width: 24px; height: 24px;"></i>
                    <i data-lucide="x" class="close-icon" style="width: 24px; height: 24px; display: none;"></i>
                </button>
            </div>
        </nav>

        <!-- Mobile Menu Overlay -->
        <div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="toggleMobileMenu()"></div>

        <!-- Mobile Menu Config -->
        <div class="mobile-menu" id="mobileMenu">
            <div class="mobile-menu-header">
                <div>
                    <span style="font-weight: 800; font-size: 1.25rem; display: block; color: var(--text-primary);">Navigation</span>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">Welcome to AAMD</span>
                </div>
                <!-- Circular Close Button -->
                <button onclick="toggleMobileMenu()" 
                    style="background: var(--bg-primary); border: 1px solid var(--border-color); cursor: pointer; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-primary); transition: all 0.2s;">
                    <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                </button>
            </div>

            <!-- Links Group -->
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="{{ url('/request') }}" class="mobile-nav-link">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(99, 102, 241, 0.1); display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="file-plus" style="color: var(--primary); width: 18px; height: 18px;"></i>
                    </div>
                    <span>New Request</span>
                </a>
                
                <a href="{{ url('/track') }}" class="mobile-nav-link">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(139, 92, 246, 0.1); display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="search" style="color: var(--secondary); width: 18px; height: 18px;"></i>
                    </div>
                    <span>Track Request</span>
                </a>

                <a href="/RL/login" class="mobile-nav-link">
                     <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center;">
                         <i data-lucide="shield" style="color: var(--accent); width: 18px; height: 18px;"></i>
                     </div>
                    <span>Admin Panel</span>
                </a>
            </div>
            
            <!-- Theme Toggle Footer -->
            <button class="mobile-theme-toggle" onclick="toggleTheme()">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span class="theme-text-light" style="display: flex; gap: 0.75rem; align-items: center; font-weight: 500; font-size: 0.95rem;">
                        <i data-lucide="sun"></i> Light Mode
                    </span>
                    <span class="theme-text-dark" style="display: none; gap: 0.75rem; align-items: center; font-weight: 500; font-size: 0.95rem;">
                         <i data-lucide="moon"></i> Dark Mode
                    </span>
                </div>
                
                <!-- Toggle Switch CSS Visualization -->
                <div style="width: 36px; height: 20px; background: var(--border-color); border-radius: 10px; position: relative;">
                    <div style="width: 16px; height: 16px; background: white; border-radius: 50%; position: absolute; top: 2px; left: 2px; transition: transform 0.3s;"></div>
                </div>
            </button>
            
            <p style="text-align: center; color: var(--text-muted); font-size: 0.75rem; margin-bottom: 0;">
                v{{ date('Ymd') }}
            </p>
        </div>

        <!-- Hero Section -->
        <section class="hero-section">
            <!-- Floating Particles -->
            <div class="particles">
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
                <div class="particle"></div>
            </div>

            <div class="landing-card">
                <!-- Badge -->
                <div class="hero-badge">
                    <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                    <span>Official Portal</span>
                </div>

                <!-- Title -->
                <h1 class="hero-title">
                    {{ $settings['welcomeTitle'] ?? 'Welcome to' }}
                    <br>
                    <span class="highlight">{{ $settings['siteName'] ?? 'Academic Recommendations' }}</span>
                </h1>

                <!-- Description -->
                <p class="hero-description">
                    {{ $settings['welcomeText'] ?? 'Easily request, track, and manage your academic recommendation letters in one secure place.' }}
                </p>

                <!-- Action Grid -->
                <div class="action-grid">
                    <!-- New Request -->
                    <a href="{{ url('/request') }}" class="action-card primary">
                        <div class="action-icon">
                            <i data-lucide="file-plus" style="width: 32px; height: 32px;"></i>
                        </div>
                        <h3 class="action-title">New Request</h3>
                        <p class="action-desc">Submit a new recommendation letter request.</p>
                    </a>

                    <!-- Track Request -->
                    <a href="{{ url('/track') }}" class="action-card">
                        <div class="action-icon">
                            <i data-lucide="search" style="width: 32px; height: 32px;"></i>
                        </div>
                        <h3 class="action-title">Track Request</h3>
                        <p class="action-desc">Check the status of an existing request.</p>
                    </a>
                </div>

                <footer class="landing-footer"
                    style="border-top: 1px solid var(--border-light); padding-top: 2.5rem; margin-top: 2.5rem;">
                    @php
                        $footerText = $settings['footerText'] ?? '© {year} {siteName}';
                        $footerText = str_replace('{year}', date('Y'), $footerText);
                        $footerText = str_replace('{siteName}', $settings['siteName'] ?? 'AAMD Recommendations', $footerText);
                    @endphp

                    <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                        <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">{{ $footerText }}</p>

                        <div
                            style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: var(--text-muted);">
                            <span>Made By</span>
                            <link
                                href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&family=Fira+Code:wght@500;600&family=Dancing+Script:wght@600;700&display=swap"
                                rel="stylesheet">

                            <!-- Lucide Icons (Modern Fork of Feather) -->
                            <a href="https://x.com/I_am_Doctor" target="_blank" rel="noopener noreferrer"
                                class="developer-link">
                                <span class="dev-name">AAliMD</span> <span class="dev-label"
                                    style="font-style: normal; margin-left: 0.25rem;">🇸🇦</span>
                            </a>
                        </div>
                    </div>
                </footer>
            </div>
        </section>
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
                if (window.scrollY > 300) {
                    scrollToTopBtn.classList.add('visible');
                } else {
                    scrollToTopBtn.classList.remove('visible');
                }
            });

            // Scroll to top functionality
            scrollToTopBtn.addEventListener('click', function () {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });

        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileMenuOverlay');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            const menuIcon = menuBtn.querySelector('.menu-icon');
            const closeIcon = menuBtn.querySelector('.close-icon');

            menu.classList.toggle('active');
            overlay.classList.toggle('active');

            // Handle Icon Toggle
            const isActive = menu.classList.contains('active');
            menuIcon.style.display = isActive ? 'none' : 'block';
            closeIcon.style.display = isActive ? 'block' : 'none';
            
            // Re-render icons for new elements
            lucide.createIcons();
            
            // Handle Theme Text Toggle
            const isDark = document.documentElement.classList.contains('dark');
            document.querySelector('.theme-text-light').style.display = isDark ? 'none' : 'flex';
            document.querySelector('.theme-text-dark').style.display = isDark ? 'flex' : 'none';
        }
    </script>
@endsection