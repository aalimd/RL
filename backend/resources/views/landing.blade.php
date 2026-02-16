@extends('layouts.app')

@section('title', $settings['siteName'] ?? 'AAMD Recommendations')

@section('styles')
    <style>
        /* ========================================
                                                                                       HERO SECTION
                                                                                       ======================================== */
        .hero-section {
            height: calc(100vh - 4.5rem);
            min-height: auto;
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
            border-radius: var(--border-radius);
            padding: 2rem 2.5rem;
            max-width: 900px;
            width: 100%;
            text-align: center;
            box-shadow: var(--shadow-lg);
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
            margin-bottom: 1rem;
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
            margin-bottom: 0.75rem;
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
            margin-bottom: 1.5rem;
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
            border-radius: var(--radius-md);
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: var(--text-primary);
            box-shadow: var(--shadow-sm);
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
            background: var(--btn-gradient);
            color: white;
            box-shadow: 0 8px 16px -4px rgba(var(--primary-rgb, 99, 102, 241), 0.4);
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
            z-index: 1001;
            /* Above menu overlay */
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
            right: -100%;
            width: 100%;
            max-width: 320px;
            /* Limit width for cleaner look */
            height: 100vh;
            background: var(--bg-secondary);
            z-index: 1000;
            padding: 0;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(100%);
            overflow-y: auto;
            box-shadow: -10px 0 40px rgba(0, 0, 0, 0.2);
            border-top-left-radius: 24px;
            border-bottom-left-radius: 24px;
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
            padding: 1.5rem 1.5rem 1rem;
            /* Compact header */
        }

        .mobile-menu-title {
            font-size: 1.25rem;
            /* Smaller title */
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.01em;
        }

        /* Mobile Menu Links Section */
        .mobile-nav-group {
            padding: 0 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            /* Tighter gap */
            margin-top: 0.5rem;
        }

        .mobile-nav-card {
            display: flex;
            align-items: center;
            padding: 0.875rem 1rem;
            /* Compact padding */
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            /* Thinner border */
            border-radius: 1rem;
            /* Softer rounded corners */
            text-decoration: none;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        /* Icon Container */
        .mobile-nav-icon {
            width: 40px;
            /* Smaller icons */
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        /* Text Content */
        .mobile-nav-content {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .mobile-nav-title {
            font-size: 1rem;
            /* Compact title */
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0;
            line-height: 1.3;
        }

        .mobile-nav-desc {
            font-size: 0.75rem;
            /* Smaller description */
            color: var(--text-muted);
            font-weight: 500;
        }

        /* Hover & Active States */
        .mobile-nav-card:hover {
            border-color: var(--primary);
            background: var(--bg-primary);
        }

        /* Specific Colors for Items (Slightly softer opacity) */
        .mobile-nav-card.item-request .mobile-nav-icon {
            background: rgba(99, 102, 241, 0.08);
            color: var(--primary);
        }

        .mobile-nav-card.item-track .mobile-nav-icon {
            background: rgba(139, 92, 246, 0.08);
            color: var(--secondary);
        }

        .mobile-nav-card.item-admin .mobile-nav-icon {
            background: rgba(16, 185, 129, 0.08);
            color: var(--accent);
        }

        /* Theme Toggle specific */
        .mobile-theme-card {
            margin: auto 1.5rem 2rem;
            padding: 1rem;
            border-radius: 1rem;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
        }

        /* Entrance Animations */
        .mobile-menu.active .mobile-nav-card {
            animation: slideUpFade 0.4s ease forwards;
            opacity: 0;
            transform: translateY(15px);
        }

        .mobile-menu.active .mobile-nav-card:nth-child(1) {
            animation-delay: 0.05s;
        }

        .mobile-menu.active .mobile-nav-card:nth-child(2) {
            animation-delay: 0.1s;
        }

        .mobile-menu.active .mobile-nav-card:nth-child(3) {
            animation-delay: 0.15s;
        }

        @keyframes slideUpFade {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
                /* Hide desktop nav */
            }

            .mobile-menu-btn {
                display: block;
                /* Show hamburger */
            }
        }
    </style>
    <style>
        /* Mobile Optimization */
        @media (max-width: 640px) {
            .hero-section {
                padding: 1rem;
                height: auto;
                min-height: calc(100vh - 4.5rem);
                align-items: flex-start;
                /* Align top on mobile preventing center cut-off */
                padding-top: 2rem;
            }

            .landing-card {
                padding: 1.5rem 1rem;
                border-radius: 1.5rem;
            }

            .hero-title {
                font-size: 2.25rem !important;
                /* Force readable size */
                line-height: 1.25;
                margin-bottom: 1rem;
            }

            .hero-description {
                font-size: 0.95rem;
                line-height: 1.6;
                padding: 0 0.5rem;
            }

            .action-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .action-card {
                padding: 1.25rem;
                align-items: center;
                /* Center align content */
            }

            .action-icon {
                width: 48px;
                height: 48px;
                margin-bottom: 0.75rem;
            }

            .action-title {
                font-size: 1.15rem;
            }
        }

        /* ========================================
                       RAMADAN MODAL STYLES
                       ======================================== */
        .ramadan-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s ease;
        }

        .ramadan-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .ramadan-card {
            background: #0f172a;
            /* Dark Slate 900 */
            border: 1px solid rgba(245, 158, 11, 0.3);
            /* Amber border */
            border-radius: 24px;
            padding: 3rem 2rem;
            max-width: 450px;
            width: 90%;
            text-align: center;
            position: relative;
            transform: scale(0.9) translateY(20px);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 30px rgba(245, 158, 11, 0.15);
            overflow: hidden;
        }

        .ramadan-modal-overlay.active .ramadan-card {
            transform: scale(1) translateY(0);
        }

        /* Golden Glow Effect */
        .ramadan-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at center, rgba(245, 158, 11, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .ramadan-icon-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(180, 83, 9, 0.1));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            border: 1px solid rgba(245, 158, 11, 0.2);
            position: relative;
        }

        .ramadan-moon-icon {
            color: #f59e0b;
            /* Amber 500 */
            width: 40px;
            height: 40px;
            filter: drop-shadow(0 0 10px rgba(245, 158, 11, 0.4));
        }

        .ramadan-star {
            position: absolute;
            color: #fcd34d;
            /* Amber 300 */
            animation: twinkle 3s infinite ease-in-out;
        }

        @keyframes twinkle {

            0%,
            100% {
                opacity: 0.5;
                transform: scale(1);
            }

            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

        .ramadan-title {
            font-family: 'Times New Roman', serif;
            /* Or a nicer serif font if available */
            font-size: 2rem;
            font-weight: 700;
            color: #f3f4f6;
            margin-bottom: 0.5rem;
            background: linear-gradient(to right, #fcd34d, #f59e0b, #fff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 0.05em;
        }

        .ramadan-text {
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        .ramadan-btn {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        .ramadan-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
            filter: brightness(1.1);
        }
    </style>
@endsection

@section('content')
    @php
        $siteName = $settings['siteName'] ?? 'AAMD Recommendations';
        $currentYear = date('Y');

        $heroTitle1 = $settings['heroTitle1'] ?? '';
        $heroTitle2 = $settings['heroTitle2'] ?? '';
        $heroDescription = $settings['heroDescription'] ?? '';

        // Replace variables
        $heroTitle1 = str_replace(['{siteName}', '{year}'], [$siteName, $currentYear], $heroTitle1);
        $heroTitle2 = str_replace(['{siteName}', '{year}'], [$siteName, $currentYear], $heroTitle2);
        $heroDescription = str_replace(['{siteName}', '{year}'], [$siteName, $currentYear], $heroDescription);
    @endphp
    <div style="min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden;">
        <!-- Navigation -->
        <nav class="nav"
            style="position: sticky; top: 0; z-index: 999; background: var(--bg-nav); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border-color);">
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
            <!-- Header -->
            <div class="mobile-menu-header">
                <div>
                    <span
                        style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px;">Navigation</span>
                    <div class="mobile-menu-title">{{ $settings['siteName'] ?? 'AAMD' }}</div>
                </div>
                <!-- Close Button -->
                <button onclick="toggleMobileMenu()"
                    style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid var(--border-color); background: var(--bg-primary); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                    <i data-lucide="x" style="width: 18px; height: 18px; color: var(--text-primary);"></i>
                </button>
            </div>

            <!-- Links -->
            <div class="mobile-nav-group">
                <!-- New Request -->
                <a href="{{ url('/request') }}" class="mobile-nav-card item-request">
                    <div class="mobile-nav-icon">
                        <i data-lucide="file-plus" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div class="mobile-nav-content">
                        <span class="mobile-nav-title">New Request</span>
                        <span class="mobile-nav-desc">Start application</span>
                    </div>
                    <i data-lucide="chevron-right" style="width: 16px; color: var(--text-muted); opacity: 0.5;"></i>
                </a>

                <!-- Track Request -->
                <a href="{{ url('/track') }}" class="mobile-nav-card item-track">
                    <div class="mobile-nav-icon">
                        <i data-lucide="search" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div class="mobile-nav-content">
                        <span class="mobile-nav-title">Track Request</span>
                        <span class="mobile-nav-desc">Check status</span>
                    </div>
                    <i data-lucide="chevron-right" style="width: 16px; color: var(--text-muted); opacity: 0.5;"></i>
                </a>

                <!-- Admin -->
                <a href="/RL/login" class="mobile-nav-card item-admin">
                    <div class="mobile-nav-icon">
                        <i data-lucide="shield" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div class="mobile-nav-content">
                        <span class="mobile-nav-title">Admin Panel</span>
                        <span class="mobile-nav-desc">Restricted access</span>
                    </div>
                    <i data-lucide="chevron-right" style="width: 16px; color: var(--text-muted); opacity: 0.5;"></i>
                </a>
            </div>

            <!-- Bottom Actions -->
            <div class="mobile-theme-card" onclick="toggleTheme()">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div
                        style="width: 32px; height: 32px; border-radius: 8px; background: var(--bg-secondary); display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="sun" class="sun-icon"
                            style="width: 16px; height: 16px; color: var(--text-primary);"></i>
                        <i data-lucide="moon" class="moon-icon"
                            style="width: 16px; height: 16px; color: var(--text-primary); display: none;"></i>
                    </div>
                    <span class="mobile-nav-title" style="font-size: 0.95rem;">Dark Mode</span>
                </div>

                <!-- Toggle Switch -->
                <div class="theme-switch"
                    style="width: 44px; height: 24px; background: var(--border-color); border-radius: 99px; position: relative; transition: background 0.3s;">
                    <div class="switch-ball"
                        style="width: 20px; height: 20px; background: white; border-radius: 50%; position: absolute; top: 2px; left: 2px; transition: transform 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    </div>
                </div>
            </div>
            <!-- Deploy Trigger: {{ date('Y-m-d H:i:s') }} .. -->
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
                @if(!empty($heroTitle1) || !empty($heroTitle2))
                    <h1 class="hero-title">
                        @if(!empty($heroTitle1))
                            {{ $heroTitle1 }}
                        @endif
                        @if(!empty($heroTitle1) && !empty($heroTitle2))
                            <br>
                        @endif
                        @if(!empty($heroTitle2))
                            <span class="highlight">{{ $heroTitle2 }}</span>
                        @endif
                    </h1>
                @endif

                <!-- Description -->
                @if(!empty($heroDescription))
                    <p class="hero-description">
                        {{ $heroDescription }}
                    </p>
                @endif

                <!-- Action Grid -->
                <div class="action-grid">
                    <!-- New Request -->
                    <a href="{{ url('/request') }}" class="action-card primary">
                        <div class="action-icon">
                            <i data-lucide="{{ $settings['feature1Icon'] ?? 'file-plus' }}"
                                style="width: 32px; height: 32px;"></i>
                        </div>
                        <h3 class="action-title">{{ $settings['heroPrimaryBtn'] ?? 'Request Recommendation' }}</h3>
                        <p class="action-desc">
                            {{ $settings['feature1Title'] ?? 'Submit a new recommendation letter request.' }}
                        </p>
                    </a>

                    <!-- Track Request -->
                    <a href="{{ url('/track') }}" class="action-card">
                        <div class="action-icon">
                            <i data-lucide="{{ $settings['feature2Icon'] ?? 'search' }}"
                                style="width: 32px; height: 32px;"></i>
                        </div>
                        <h3 class="action-title">{{ $settings['heroSecondaryBtn'] ?? 'Track Existing Request' }}</h3>
                        <p class="action-desc">
                            {{ $settings['feature2Title'] ?? 'Check the status of an existing request.' }}
                        </p>
                    </a>
                </div>

                <footer class="landing-footer"
                    style="border-top: 1px solid var(--border-light); padding-top: 1.5rem; margin-top: 1.5rem;">
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
                                <span class="dev-name">Dr. AbdulRahman Alzahrani</span> <span class="dev-label"
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

    <!-- RAMADAN MODAL -->
    <div class="ramadan-modal-overlay" id="ramadanModal">
        <div class="ramadan-card">
            <div class="ramadan-icon-wrapper">
                <i data-lucide="moon" class="ramadan-moon-icon"></i>
                <i data-lucide="star" class="ramadan-star"
                    style="width: 12px; top: 15px; right: 20px; animation-delay: 0s;"></i>
                <i data-lucide="star" class="ramadan-star"
                    style="width: 10px; bottom: 20px; left: 15px; animation-delay: 1s;"></i>
            </div>

            <h2 class="ramadan-title">Ramadan Kareem</h2>

            <p class="ramadan-text">
                Wishing you a blessed month filled with peace, prosperity, and spiritual growth. May your days be
                illuminated with light and grace.
            </p>

            <p style="color: #fcd34d; font-family: 'Dancing Script', cursive; font-size: 1.25rem; margin-top: -1rem; margin-bottom: 1.5rem; opacity: 0.9;">
                Dr. AbdulRahman Alzahrani
            </p>

            <button class="ramadan-btn" onclick="closeRamadanModal()">
                Ramadan Mubarak
            </button>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();

            // Check Ramadan Modal
            checkRamadanModal();

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

        // Ramadan Modal Logic
        function checkRamadanModal() {
            const seen = localStorage.getItem('ramadan_modal_seen_v1'); // v1 allows us to reset it next year by changing key
            const now = new Date();

            // Logic: Show if never seen OR if seen more than 24 hours ago (optional, currently just once forever/session)
            // For now, let's make it show once per browser session (removed from storage on close? No, persistent)
            // Let's stick to "Show once" logic as per plan.

            if (!seen) {
                // Delay slightly for effect
                setTimeout(() => {
                    document.getElementById('ramadanModal').classList.add('active');
                }, 1000);
            }
        }

        function closeRamadanModal() {
            const modal = document.getElementById('ramadanModal');
            modal.classList.remove('active');

            // Set flag in localStorage
            localStorage.setItem('ramadan_modal_seen_v1', 'true');
        }

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
            if (menuIcon) menuIcon.style.display = isActive ? 'none' : 'block';
            if (closeIcon) closeIcon.style.display = isActive ? 'block' : 'none';

            // Re-render icons for new elements
            lucide.createIcons();

            // Handle Theme Text Toggle
            const isDark = document.documentElement.classList.contains('dark');
            const lightText = document.querySelector('.theme-text-light');
            const darkText = document.querySelector('.theme-text-dark');

            if (lightText) lightText.style.display = isDark ? 'none' : 'flex';
            if (darkText) darkText.style.display = isDark ? 'flex' : 'none';
        }
    </script>
@endsection