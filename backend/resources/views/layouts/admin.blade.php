@php
    $locale = app()->getLocale();
    $dir = $locale === 'ar' || ($settings['siteLanguage'] ?? 'en') === 'ar' ? 'rtl' : 'ltr';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Anton&family=Bebas+Neue&family=Cinzel:wght@400;700&family=Dancing+Script:wght@400;700&family=DM+Serif+Display&family=Great+Vibes&family=Inter:wght@300;400;500;600;700&family=Josefin+Sans:wght@300;400;500;600;700&family=Libre+Baskerville:wght@400;700&family=Lobster&family=Manrope:wght@400;500;600;700&family=Merriweather:wght@300;400;700&family=Montserrat:wght@400;500;600;700&family=Outfit:wght@300;400;500;600;700&family=Pacifico&family=Playfair+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Prata&family=Quicksand:wght@300;400;500;600;700&family=Raleway:wght@400;500;600;700&family=Righteous&family=Satisfy&family=Space+Grotesk:wght@300;400;500;600;700&family=Urbanist:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* RTL Support */
        [dir="rtl"] .sidebar {
            left: auto;
            right: 0;
            border-right: none;
            border-left: 1px solid rgba(255, 255, 255, 0.05);
        }

        [dir="rtl"] .main-content {
            margin-left: 0;
            margin-right: var(--sidebar-width);
        }

        [dir="rtl"] .admin-header {
            left: auto;
            right: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
        }

        [dir="rtl"] .user-menu-wrapper {
            margin-left: 0;
            margin-right: auto;
            /* Push to left in RTL flex */
        }

        @media (max-width: 1024px) {
            [dir="rtl"] .sidebar {
                transform: translateX(100%);
            }

            [dir="rtl"] .sidebar.open {
                transform: translateX(0);
            }

            [dir="rtl"] .main-content {
                margin-right: 0;
            }

            [dir="rtl"] .admin-header {
                right: 0;
                width: 100%;
            }
        }

        [dir="rtl"] .nav-item {
            border-left: none;
            border-right: 3px solid transparent;
        }

        [dir="rtl"] .nav-item.active {
            border-right-color: var(--primary);
            background: linear-gradient(270deg, rgba(79, 70, 229, 0.1) 0%, transparent 100%);
        }

        :root {
            --primary:
                {{ $settings['primaryColor'] ?? '#4F46E5' }}
            ;
            --secondary:
                {{ $settings['secondaryColor'] ?? '#9333EA' }}
            ;
            --sidebar-width: 260px;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --input-bg: #ffffff;

            /* Status Colors - Light Mode */
            --success-bg: rgba(16, 185, 129, 0.1);
            --success-text: #059669;
            --success-border: rgba(16, 185, 129, 0.2);
            --error-bg: rgba(239, 68, 68, 0.1);
            --error-text: #dc2626;
            --error-border: rgba(239, 68, 68, 0.2);
            --warning-bg: rgba(245, 158, 11, 0.1);
            --warning-text: #d97706;
            --warning-border: rgba(245, 158, 11, 0.2);
        }

        /* Dark Mode */
        body.dark-mode {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --input-bg: #1e293b;

            /* Status Colors - Dark Mode */
            --success-bg: rgba(16, 185, 129, 0.2);
            --success-text: #34d399;
            --success-border: rgba(16, 185, 129, 0.3);
            --error-bg: rgba(239, 68, 68, 0.2);
            --error-text: #f87171;
            --error-border: rgba(239, 68, 68, 0.3);
            --warning-bg: rgba(245, 158, 11, 0.2);
            --warning-text: #fbbf24;
            --warning-border: rgba(245, 158, 11, 0.3);
        }

        body.dark-mode .admin-header {
            background: rgba(30, 41, 59, 0.9);
            border-bottom-color: var(--border-color);
        }

        body.dark-mode .admin-header h1 {
            color: var(--text-main);
        }

        body.dark-mode .user-menu,
        body.dark-mode .user-menu-wrapper button {
            background: var(--card-bg) !important;
            border-color: var(--border-color) !important;
        }

        .user-menu:hover {
            background: #f1f5f9 !important;
        }

        body.dark-mode .user-menu:hover {
            background: rgba(255, 255, 255, 0.05) !important;
        }

        body.dark-mode .user-menu-wrapper button span {
            color: var(--text-main) !important;
        }

        body.dark-mode .card,
        body.dark-mode .stat-card,
        body.dark-mode .chart-card,
        body.dark-mode .mini-stat {
            background: var(--card-bg);
            border-color: var(--border-color);
        }

        body.dark-mode .card-header {
            background: var(--card-bg);
            border-bottom-color: var(--border-color);
        }

        body.dark-mode .card-header h3 {
            color: var(--text-main);
        }

        body.dark-mode .table th {
            background: #1e293b;
            color: var(--text-muted);
            border-bottom-color: var(--border-color);
        }

        body.dark-mode .table td {
            color: var(--text-muted);
            border-bottom-color: var(--border-color);
        }

        body.dark-mode .table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        body.dark-mode .form-input,
        body.dark-mode .form-select,
        body.dark-mode .form-textarea {
            background: var(--input-bg);
            border-color: var(--border-color);
            color: var(--text-main);
        }

        body.dark-mode .btn-ghost {
            color: var(--text-muted);
        }

        body.dark-mode .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-main);
        }

        body.dark-mode .user-dropdown {
            background: var(--card-bg);
            border-color: var(--border-color);
        }

        body.dark-mode .dropdown-item {
            color: var(--text-main);
        }

        body.dark-mode .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        body.dark-mode .dropdown-header {
            border-bottom-color: var(--border-color);
        }

        body.dark-mode .dropdown-header strong {
            color: var(--text-main);
        }

        /* Dark mode for filters and inputs */
        body.dark-mode .search-box input,
        body.dark-mode .filter-input,
        body.dark-mode .date-filter input {
            background: var(--input-bg);
            border-color: var(--border-color);
            color: var(--text-main);
        }

        body.dark-mode .search-box input::placeholder {
            color: var(--text-muted);
        }

        body.dark-mode .status-btn {
            background: var(--card-bg);
            border-color: var(--border-color);
            color: var(--text-muted);
        }

        body.dark-mode .status-btn:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-main);
        }

        body.dark-mode .btn-reset {
            background: var(--card-bg);
            border-color: var(--border-color);
            color: var(--text-muted);
        }

        body.dark-mode .badge {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        body.dark-mode .badge-pending {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        body.dark-mode .badge-approved {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        body.dark-mode .badge-rejected {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        body.dark-mode .badge-revision {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }

        body.dark-mode .pagination {
            background: var(--card-bg);
            border-top-color: var(--border-color);
        }

        body.dark-mode .pagination-buttons a,
        body.dark-mode .pagination-buttons span {
            background: var(--card-bg);
            border-color: var(--border-color);
            color: var(--text-muted);
        }

        body.dark-mode .pagination-info,
        body.dark-mode .results-info {
            color: var(--text-muted);
        }

        body.dark-mode .tracking-id {
            background: rgba(79, 70, 229, 0.2);
            color: #a5b4fc;
        }

        body.dark-mode .data-table th {
            background: var(--card-bg);
            color: var(--text-muted);
            border-bottom-color: var(--border-color);
        }

        body.dark-mode .data-table td {
            color: var(--text-muted);
            border-bottom-color: var(--border-color);
        }

        body.dark-mode .data-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        body.dark-mode .student-info .name {
            color: var(--text-main);
        }

        body.dark-mode .empty-state,
        body.dark-mode .empty-message {
            color: var(--text-muted);
        }

        /* Modal Dark Mode Fixes */
        body.dark-mode .modal-content {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
        }

        body.dark-mode .modal-header {
            background: var(--card-bg);
            border-bottom-color: var(--border-color);
        }

        body.dark-mode .modal-header h3 {
            color: var(--text-main);
        }

        body.dark-mode .modal-body {
            color: var(--text-main);
        }

        body.dark-mode .modal-footer {
            background: rgba(0, 0, 0, 0.2);
            border-top-color: var(--border-color);
        }

        body.dark-mode .sidebar-nav {
            scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
        }

        /* Dark mode toggle button */
        .dark-mode-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            background: transparent;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            color: #6b7280;
            transition: all 0.2s;
            margin-right: 0.5rem;
        }

        .dark-mode-toggle:hover {
            background: #f3f4f6;
            color: #111827;
        }

        body.dark-mode .dark-mode-toggle {
            border-color: var(--border-color);
            color: var(--text-muted);
        }

        body.dark-mode .dark-mode-toggle:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-main);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--bg-color);
            min-height: 100vh;
            color: var(--text-main);
            transition: background-color 0.3s, color 0.3s;
        }

        /* Sidebar Polish */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: #111827;
            /* Darker, more premium */
            color: white;
            padding: 1.5rem 1rem;
            overflow-y: auto;
            z-index: 100;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-brand {
            padding: 0 0.5rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
        }

        .sidebar-brand h2 {
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            letter-spacing: -0.025em;
        }

        .sidebar-brand .logo-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .nav-section-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9ca3af;
            padding: 0 0.75rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            color: #d1d5db;
            text-decoration: none;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            transform: translateX(4px);
        }

        .nav-item.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            /* Subtle glow */
        }

        .nav-item svg {
            width: 20px;
            height: 20px;
            stroke-width: 2px;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Header Polish */
        .admin-header {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .admin-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.025em;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: var(--card-bg);
            padding: 0.25rem 0.25rem 0.25rem 1rem;
            border-radius: 9999px;
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .user-avatar {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* Page Content */
        .page-content {
            padding: 2rem;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(0, 0, 0, 0.02);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
        }

        .stat-card .stat-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .stat-card .stat-value {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--text-main);
            line-height: 1;
        }

        .stat-card .stat-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .stat-card .stat-icon.blue {
            background: #eff6ff;
            color: #3b82f6;
        }

        .stat-card .stat-icon.yellow {
            background: #fffbeb;
            color: #f59e0b;
        }

        .stat-card .stat-icon.green {
            background: #ecfdf5;
            color: #10b981;
        }

        .stat-card .stat-icon.red {
            background: #fef2f2;
            color: #ef4444;
        }

        /* Modern Card */
        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.02);
            /* overflow: hidden; Removed to allow dropdowns to spill over */
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
        }

        .card-header h3 {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-main);
        }

        /* Modern Table */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            white-space: nowrap;
        }

        .table th {
            padding: 1rem 1.5rem;
            text-align: left;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            font-weight: 600;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }

        .table td {
            padding: 1rem 1.5rem;
            color: #4b5563;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.875rem;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tr:hover td {
            background: #f9fafb;
        }

        /* Modern Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
        }

        .badge-pending {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }

        .badge-approved {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }

        .badge-rejected {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .badge-revision {
            background: #fff7ed;
            color: #ea580c;
            border: 1px solid #fed7aa;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-muted);
        }

        .btn-ghost:hover {
            background: #f3f4f6;
            color: var(--text-main);
        }

        /* Mobile Adjustments */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: flex !important;
                align-items: center;
                justify-content: center;
            }

            .admin-header {
                padding: 1rem;
            }

            .page-content {
                padding: 1rem;
            }
        }

        .mobile-menu-btn {
            display: none;
            background: white;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            color: #374151;
        }

        /* --- Modern Form System (Pixel-Perfect) --- */
        .form-grid {
            display: grid;
            gap: 2rem;
            /* Increased gap between major sections */
            max-width: 100%;
            /* Allow full width within container */
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            /* Increased horizontal gap */
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            /* More space between label and input */
        }

        .form-label {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: space-between;
            letter-spacing: -0.01em;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.875rem 1.25rem;
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            font-size: 1rem;
            background-color: var(--input-bg);
            transition: all 0.2s ease-in-out;
            color: var(--text-main);
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            background-color: var(--card-bg);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        /* Fixed height for color inputs */
        input[type="color"].form-input {
            height: 52px;
            /* Adjusted for larger inputs */
            padding: 4px;
            cursor: pointer;
        }

        /* --- Custom File Upload Button --- */
        .file-upload-container {
            position: relative;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .file-upload-input {
            opacity: 0;
            position: absolute;
            z-index: -1;
            width: 0.1px;
            height: 0.1px;
        }

        .file-upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            /* Match input height */
            background-color: white;
            border: 1px solid #d1d5db;
            border-radius: 0.75rem;
            color: #374151;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .file-upload-btn:hover {
            background-color: #f9fafb;
            border-color: #9ca3af;
        }

        .file-upload-btn:active {
            transform: translateY(1px);
        }

        .file-upload-name {
            font-size: 0.95rem;
            color: #4b5563;
            /* Darker than before */
        }


        /* --- Modern Toggle Switch --- */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            /* Slightly larger */
            height: 28px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e5e7eb;
            transition: .4s;
            border-radius: 34px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        input:checked+.toggle-slider {
            background-color: var(--primary);
        }

        input:checked+.toggle-slider:before {
            transform: translateX(22px);
        }

        /* --- Hybrid Image Upload --- */
        .image-upload-wrapper {
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            /* More internal breath */
            background: #ffffff;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        .upload-tabs {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 0.75rem;
        }

        .upload-tab {
            font-size: 0.95rem;
            /* Larger tab text */
            font-weight: 500;
            color: #6b7280;
            cursor: pointer;
            padding-bottom: 0.75rem;
            margin-bottom: -0.85rem;
            border-bottom: 2px solid transparent;
            transition: color 0.2s;
        }

        .upload-tab:hover {
            color: #374151;
        }

        .upload-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .upload-preview {
            margin-top: 1.5rem;
            width: 100%;
            height: 200px;
            /* Larger preview area */
            border: 2px dashed #e5e7eb;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #f9fafb;
            position: relative;
            transition: border-color 0.2s;
        }

        .upload-preview:hover {
            border-color: #d1d5db;
        }

        .upload-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .upload-preview.has-image {
            border-style: solid;
            background: #fff;
        }

        /* --- Global Modal Styles --- */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1000;
            display: none;
            /* JS toggles flex/block */
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            padding: 1rem;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(17, 24, 39, 0.6);
            /* Darker, more premium overlay */
            backdrop-filter: blur(4px);
            z-index: -1;
            transition: opacity 0.3s;
        }

        .modal-content {
            position: relative;
            background: white;
            border-radius: 1rem;
            width: 100%;
            max-width: 500px;
            /* Default width */
            margin: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            transform: translateY(0);
            animation: modalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes modalPop {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(10px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            background: #ffffff;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            /* Larger Header */
            font-weight: 700;
            color: #111827;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding: 1.25rem 1.5rem;
            background: #f9fafb;
            border-top: 1px solid #f3f4f6;
        }

        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            z-index: 200;
        }

        .toast {
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            color: #fff;
            font-size: 0.875rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .toast-success {
            background: #10b981;
        }

        .toast-error {
            background: #ef4444;
        }

        /* --- ADDED: Card Body Padding --- */
        .card-body {
            padding: 2.5rem;
            /* Large breathing room inside cards */
        }

        .card-header {
            padding: 1.5rem 2.5rem;
            /* Match body padding horizontally */
        }

        /* --- User Dropdown --- */
        .user-menu-wrapper {
            position: relative;
        }

        .user-menu {
            cursor: pointer;
            transition: all 0.2s;
        }

        .user-menu:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .user-dropdown {
            position: absolute;
            top: 120%;
            right: 0;
            width: 220px;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #f3f4f6;
            display: none;
            flex-direction: column;
            padding: 0.5rem;
            z-index: 50;
        }

        .user-dropdown.show {
            display: flex;
            animation: fadeIn 0.15s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-header {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f3f4f6;
            margin-bottom: 0.5rem;
        }

        .dropdown-header strong {
            display: block;
            color: #111827;
            font-size: 0.9rem;
        }

        .dropdown-header span {
            display: block;
            color: #6b7280;
            font-size: 0.75rem;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 1rem;
            color: #4b5563;
            text-decoration: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
        }

        .dropdown-item:hover {
            background: #f3f4f6;
            color: #111827;
        }

        .dropdown-item i {
            width: 16px;
            height: 16px;
        }

        .dropdown-item.text-red {
            color: #ef4444;
        }

        .dropdown-item.text-red:hover {
            background: #fef2f2;
            color: #dc2626;
        }

        /* Mobile Sidebar Overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 40;
            display: none;
            backdrop-filter: blur(4px);
            transition: opacity 0.3s;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Close Button */
        .sidebar-close {
            display: none;
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            padding: 0.5rem;
        }

        @media (max-width: 1024px) {
            .sidebar-close {
                display: block;
                position: absolute;
                right: 1rem;
                top: 1rem;
            }

            [dir="rtl"] .sidebar-close {
                right: auto;
                left: 1rem;
            }
        }
    </style>
    @yield('styles')
</head>

<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <button class="sidebar-close" onclick="toggleSidebar()">
            <i data-feather="x"></i>
        </button>
        <div class="sidebar-brand">
            <h2>
                <div class="logo-icon">R</div>
                <span>{{ $settings['siteName'] ?? 'Admin Panel' }}</span>
            </h2>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i data-feather="home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('home') }}" target="_blank" class="nav-item">
                    <i data-feather="external-link"></i>
                    <span>Visit Site</span>
                </a>
                <a href="{{ route('admin.requests') }}"
                    class="nav-item {{ request()->routeIs('admin.requests*') ? 'active' : '' }}">
                    <i data-feather="file-text"></i>
                    <span>Requests</span>
                </a>
                <a href="{{ route('admin.analytics') }}"
                    class="nav-item {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                    <i data-feather="bar-chart-2"></i>
                    <span>Analytics</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Management</div>
                <a href="{{ route('admin.users') }}"
                    class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                    <i data-feather="users"></i>
                    <span>Users</span>
                </a>
                <a href="{{ route('admin.templates') }}"
                    class="nav-item {{ request()->routeIs('admin.templates') ? 'active' : '' }}">
                    <i data-feather="layout"></i>
                    <span>Templates</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Settings</div>
                <a href="{{ route('admin.settings') }}"
                    class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <i data-feather="settings"></i>
                    <span>General</span>
                </a>
                <a href="{{ route('admin.appearance') }}"
                    class="nav-item {{ request()->routeIs('admin.appearance') ? 'active' : '' }}">
                    <i data-feather="pen-tool"></i>
                    <span>Appearance</span>
                </a>
                <a href="{{ route('admin.audit-logs') }}"
                    class="nav-item {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}">
                    <i data-feather="activity"></i>
                    <span>Audit Logs</span>
                </a>
                <a href="{{ route('admin.form-settings') }}"
                    class="nav-item {{ request()->routeIs('admin.form-settings') ? 'active' : '' }}">
                    <i data-feather="clipboard"></i>
                    <span>Form Settings</span>
                </a>
                <a href="{{ route('admin.settings.security') }}"
                    class="nav-item {{ request()->routeIs('admin.settings.security') ? 'active' : '' }}">
                    <i data-feather="shield"></i>
                    <span>Security</span>
                </a>
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.system-tools') }}"
                        class="nav-item {{ request()->routeIs('admin.system-tools') ? 'active' : '' }}">
                        <i data-feather="terminal"></i>
                        <span>System Tools</span>
                    </a>
                @endif
                <a href="{{ route('admin.email-templates.index') }}"
                    class="nav-item {{ request()->routeIs('admin.email-templates.*') ? 'active' : '' }}">
                    <i data-feather="mail"></i>
                    <span>Email Templates</span>
                </a>
            </div>

            <div class="nav-section"
                style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-item"
                        style="width: 100%; background: none; border: none; cursor: pointer; text-align: left;">
                        <i data-feather="log-out"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="admin-header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button class="mobile-menu-btn" onclick="toggleSidebar()">
                    <i data-feather="menu"></i>
                </button>
                <h1>@yield('page-title', 'Dashboard')</h1>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <!-- Dark Mode Toggle -->
                <button class="dark-mode-toggle" onclick="toggleDarkMode()" title="Toggle Dark Mode">
                    <i data-feather="moon" id="darkModeIcon"></i>
                </button>

                <div class="user-menu-wrapper">
                    <button class="user-menu" onclick="toggleUserMenu()"
                        style="display: flex; align-items: center; gap: 0.75rem; padding: 0.25rem 0.5rem 0.25rem 0.25rem; border-radius: 9999px; border: 1px solid var(--border-color); cursor: pointer;">
                        <div class="user-avatar"
                            style="width: 2rem; height: 2rem; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; color: white;">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <span
                            style="font-weight: 500; color: #374151; font-size: 0.875rem;">{{ auth()->user()->name ?? 'Admin' }}</span>
                        <i data-feather="chevron-down" style="width: 16px; height: 16px; color: #9ca3af;"></i>
                    </button>

                    <!-- Dropdown -->
                    <div id="userDropdown" class="user-dropdown">
                        <div class="dropdown-header">
                            <strong>{{ auth()->user()->name ?? 'Admin' }}</strong>
                            <span>{{ auth()->user()->email ?? '' }}</span>
                        </div>

                        <a href="{{ route('admin.settings') }}" class="dropdown-item">
                            <i data-feather="settings"></i>
                            <span>Settings</span>
                        </a>
                        <a href="{{ route('admin.settings.security') }}" class="dropdown-item">
                            <i data-feather="shield"></i>
                            <span>Security (2FA)</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="dropdown-item text-red">
                                <i data-feather="log-out"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content">
            @yield('content')
        </div>
    </main>

    @yield('modals')

    <div class="toast-container" id="toastContainer"></div>

    <script>
        // Initialize dark mode from localStorage
        (function () {
            if (localStorage.getItem('darkMode') === 'true') {
                document.body.classList.add('dark-mode');
            }
        })();

        feather.replace();

        // Dark Mode Toggle
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDark);

            // Update icon
            const icon = document.getElementById('darkModeIcon');
            if (icon) {
                icon.setAttribute('data-feather', isDark ? 'sun' : 'moon');
                feather.replace();
            }
        }

        // Set correct icon on load
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.body.classList.contains('dark-mode');
            const icon = document.getElementById('darkModeIcon');
            if (icon && isDark) {
                icon.setAttribute('data-feather', 'sun');
                feather.replace();
            }

            // Close sidebar when clicking nav items on mobile
            document.querySelectorAll('.sidebar-nav .nav-item').forEach(item => {
                item.addEventListener('click', () => {
                    if (window.innerWidth <= 1024) {
                        toggleSidebar();
                    }
                });
            });
        });

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }

        function toggleUserMenu() {
            document.getElementById('userDropdown').classList.toggle('show');
        }

        // Close dropdown when clicking outside
        window.onclick = function (event) {
            if (!event.target.closest('.user-menu-wrapper')) {
                var dropdowns = document.getElementsByClassName("user-dropdown");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
        function showToast(message, type) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type === 'error' ? 'toast-error' : 'toast-success'}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 1500);
        }
        function copyTracking(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Copied', 'success');
            }).catch(() => {
                showToast('Copy failed', 'error');
            });
        }
    </script>

    @yield('scripts')
</body>

</html>