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
            padding: 3rem;
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
            margin-bottom: 2rem;
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
            line-height: 1.2;
            margin-bottom: 1.5rem;
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
            line-height: 1.7;
            margin-bottom: 3rem;
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
            gap: 1.5rem;
            margin-bottom: 3rem;
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
                    <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme">
                        <i data-lucide="moon" class="moon-icon"></i>
                        <i data-lucide="sun" class="sun-icon"></i>
                    </button>
                    <a href="/RL/login" class="btn btn-secondary" style="padding: 0.5rem 1rem;">
                        <i data-lucide="shield" style="width: 16px; height: 16px;"></i>
                        Admin
                    </a>
                </div>
            </div>
        </nav>

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

                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 1rem;">
                    &copy; {{ date('Y') }} {{ $settings['siteName'] ?? 'AAMD' }}. All rights reserved.
                </p>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();
        });
    </script>
@endsection