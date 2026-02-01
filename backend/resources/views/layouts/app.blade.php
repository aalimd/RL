<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $settings['siteName'] ?? 'Academic Portal')</title>

    <!-- Google Fonts - Multiple Options -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Lucide Icons (Modern Fork of Feather) -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* ========================================
           MODERN DESIGN SYSTEM 2024
           ======================================== */

        /* RTL Overrides */
        [dir="rtl"] {
            text-align: right;
        }

        [dir="rtl"] .logo-icon {
            margin-right: 0;
            margin-left: 0.875rem;
        }

        [dir="rtl"] .btn {
            gap: 0.5rem;
        }

        [dir="rtl"] .mobile-menu a {
            text-align: right;
        }

        [dir="rtl"] .nav-links {
            /* gap is standard */
        }

        /* Light Mode (Default) */
        :root {
            /* Brand Colors - Customizable from Admin */
            --primary:
                {{ $settings['primaryColor'] ?? '#6366F1' }}
            ;
            --secondary:
                {{ $settings['secondaryColor'] ?? '#8B5CF6' }}
            ;
            --accent: #10B981;

            /* Font - Customizable from Admin */
            --font-family: '{{ $settings['fontFamily'] ?? 'Inter' }}', -apple-system, BlinkMacSystemFont, sans-serif;

            /* Theme Colors - Light */
            --bg-primary: #F8FAFC;
            --bg-secondary: #FFFFFF;
            --bg-card: rgba(255, 255, 255, 0.8);
            --bg-nav: rgba(255, 255, 255, 0.85);
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
            --border-light: #F1F5F9;
            --shadow-color: rgba(15, 23, 42, 0.08);
            --input-bg: #FFFFFF;
            --input-border: #CBD5E1;

            /* Glassmorphism */
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.5);

            /* RGB Values for Light Mode */
            --bg-secondary-rgb: 255, 255, 255;
        }

        /* Dark Mode */
        html.dark {
            --bg-primary: #0F172A;
            --bg-secondary: #1E293B;
            --bg-card: rgba(30, 41, 59, 0.8);
            --bg-nav: rgba(15, 23, 42, 0.9);
            --text-primary: #F8FAFC;
            --text-secondary: #CBD5E1;
            --text-muted: #94A3B8;
            --border-color: #334155;
            --border-light: #1E293B;
            --shadow-color: rgba(0, 0, 0, 0.4);
            --input-bg: #1E293B;
            --input-border: #475569;

            /* Glassmorphism Dark */
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(51, 65, 85, 0.5);

            /* RGB Values for Dark Mode */
            --bg-secondary-rgb: 30, 41, 59;
        }

        /* ========================================
           BASE STYLES
           ======================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            transition: background-color 0.4s ease, color 0.4s ease;
            overflow-x: hidden;
        }

        /* ========================================
           UTILITY CLASSES
           ======================================== */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .flex {
            display: flex;
        }

        .flex-col {
            flex-direction: column;
        }

        .items-center {
            align-items: center;
        }

        .justify-between {
            justify-content: space-between;
        }

        .justify-center {
            justify-content: center;
        }

        .text-center {
            text-align: center;
        }

        .hidden {
            display: none;
        }

        .w-full {
            width: 100%;
        }

        .relative {
            position: relative;
        }

        .absolute {
            position: absolute;
        }

        .gap-4 {
            gap: 1rem;
        }

        .gap-6 {
            gap: 1.5rem;
        }

        .gap-8 {
            gap: 2rem;
        }

        .gap-12 {
            gap: 3rem;
        }

        /* ========================================
           GLASSMORPHISM UTILITIES
           ======================================== */
        .glass {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
        }

        /* ========================================
           ANIMATIONS
           ======================================== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
            }

            50% {
                box-shadow: 0 0 40px rgba(99, 102, 241, 0.5);
            }
        }

        @keyframes gradient-shift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .animate-delay-100 {
            animation-delay: 0.1s;
        }

        .animate-delay-200 {
            animation-delay: 0.2s;
        }

        .animate-delay-300 {
            animation-delay: 0.3s;
        }

        .animate-delay-400 {
            animation-delay: 0.4s;
        }

        /* ========================================
           NAVIGATION
           ======================================== */
        .nav {
            background: var(--bg-nav);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--border-light);
            transition: all 0.3s ease;
        }

        .nav-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 4.5rem;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.875rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 1.25rem;
            margin-right: 0.875rem;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .logo:hover .logo-icon {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .logo-text {
            font-weight: 800;
            font-size: 1.375rem;
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* ========================================
           BUTTONS
           ======================================== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9375rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.45);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 8px var(--shadow-color);
        }

        .btn-secondary:hover {
            background: var(--bg-card);
            border-color: var(--primary);
            box-shadow: 0 4px 12px var(--shadow-color);
            transform: translateY(-2px);
        }

        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.0625rem;
            border-radius: 14px;
        }

        .btn-icon {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0.625rem;
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }

        .btn-icon:hover {
            background: var(--bg-secondary);
            color: var(--primary);
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        /* ========================================
           THEME TOGGLE
           ======================================== */
        .theme-toggle {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0.625rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            color: var(--text-secondary);
        }

        .theme-toggle:hover {
            background: var(--bg-secondary);
            color: var(--primary);
            border-color: var(--primary);
            transform: rotate(15deg);
        }

        .theme-toggle svg {
            width: 20px;
            height: 20px;
        }

        html:not(.dark) .theme-toggle .moon-icon {
            display: block;
        }

        html:not(.dark) .theme-toggle .sun-icon {
            display: none;
        }

        html.dark .theme-toggle .moon-icon {
            display: none;
        }

        html.dark .theme-toggle .sun-icon {
            display: block;
        }

        /* ========================================
           MOBILE MENU - Enhanced
           ======================================== */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
            color: var(--text-primary);
            transition: transform 0.3s ease;
        }

        .mobile-menu-btn:hover {
            transform: scale(1.1);
        }

        .mobile-menu {
            position: absolute;
            top: 4.5rem;
            left: 0;
            width: 100%;
            background: rgba(var(--bg-secondary-rgb, 255, 255, 255), 0.98);
            -webkit-backdrop-filter: blur(20px);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--border-color);
            box-shadow: 0 10px 40px var(--shadow-color);
            padding: 1.5rem;
            z-index: 99;
            /* Animation */
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            visibility: hidden;
        }

        .mobile-menu.active {
            max-height: 400px;
            opacity: 1;
            transform: translateY(0);
            visibility: visible;
        }

        .mobile-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            color: var(--text-primary);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .mobile-menu a:hover {
            background: var(--bg-card);
            transform: translateX(5px);
        }

        /* ========================================
           GRID SYSTEM
           ======================================== */
        .grid {
            display: grid;
            gap: 2rem;
        }

        .grid-cols-1 {
            grid-template-columns: repeat(1, 1fr);
        }

        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .grid-cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        /* ========================================
           FEATURE CARDS
           ======================================== */
        .feature-card {
            padding: 2rem;
            border-radius: 1.25rem;
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border-light);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px var(--shadow-color);
            border-color: var(--border-color);
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .feature-icon.indigo {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0.05));
            color: #6366F1;
        }

        .feature-icon.purple {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.15), rgba(139, 92, 246, 0.05));
            color: #8B5CF6;
        }

        .feature-icon.pink {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.15), rgba(236, 72, 153, 0.05));
            color: #EC4899;
        }

        .feature-icon.emerald {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
            color: #10B981;
        }

        /* ========================================
           GRADIENT TEXT
           ======================================== */
        .gradient-text {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ========================================
           HERO BACKGROUND
           ======================================== */
        .hero-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .hero-bg::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 120%;
            height: 120%;
            background: radial-gradient(ellipse at center, var(--primary) 0%, var(--secondary) 30%, transparent 70%);
            opacity: 0.06;
            animation: float 10s ease-in-out infinite;
        }

        .hero-bg::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            background: radial-gradient(ellipse at center, transparent 40%, var(--bg-primary) 100%);
            opacity: 1;
        }

        /* ========================================
           RESPONSIVE
           ======================================== */
        @media (max-width: 1024px) {
            .grid-cols-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }

            .grid-cols-3,
            .grid-cols-2 {
                grid-template-columns: repeat(1, 1fr);
            }

            .flex-col-mobile {
                flex-direction: column;
            }

            .hero-title {
                font-size: 2.5rem !important;
            }
        }

        /* ========================================
           SCROLLBAR (Modern)
           ======================================== */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-primary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }
    </style>
    @yield('styles')
</head>

<body>
    @yield('content')

    <script>
        // Dark Mode Toggle with Smooth Transition
        (function () {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark' || !savedTheme) {
                document.documentElement.classList.add('dark');
            }
        })();

        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }

        // Initialize Lucide Icons
        lucide.createIcons();

        // Mobile Menu Toggle
        function toggleMobileMenu() {
            document.getElementById('mobileMenu').classList.toggle('active');
        }
    </script>

    @yield('scripts')
    <!-- Deployment Version: {{ date('Y-m-d H:i:s') }} -->
    <div style="text-align: center; font-size: 0.75rem; color: var(--text-muted); padding: 1rem; opacity: 0.5;">
        v{{ date('Ymd.Hi') }}
    </div>
</body>

</html>