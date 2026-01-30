<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel')</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary:
                {{ $settings['primaryColor'] ?? '#4F46E5' }}
            ;
            --secondary:
                {{ $settings['secondaryColor'] ?? '#9333EA' }}
            ;
            --sidebar-width: 260px;
            --bg-color: #f3f4f6;
            --card-bg: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
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
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #e5e7eb;
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
            color: #111827;
            letter-spacing: -0.025em;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: white;
            padding: 0.25rem 0.25rem 0.25rem 1rem;
            border-radius: 9999px;
            border: 1px solid #e5e7eb;
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
            overflow: hidden;
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
            /* SIGNIFICANTLY LARGER */
            font-weight: 600;
            /* Bolder */
            color: #111827;
            /* Darker text */
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
            /* Taller, more comfortable inputs */
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            /* More rounded */
            font-size: 1rem;
            /* Larger input text */
            background-color: #f9fafb;
            transition: all 0.2s ease-in-out;
            color: #111827;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            background-color: #ffffff;
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
    </style>
    @yield('styles')
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
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

            <div class="user-menu-wrapper">
                <button class="user-menu" onclick="toggleUserMenu()"
                    style="background: white; display: flex; align-items: center; gap: 0.75rem; padding: 0.25rem 0.5rem 0.25rem 0.25rem; border-radius: 9999px; border: 1px solid #e5e7eb; cursor: pointer;">
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

                    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                        @csrf
                        <button type="submit" class="dropdown-item text-red">
                            <i data-feather="log-out"></i>
                            <span>Logout</span>
                        </button>
                    </form>
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
        feather.replace();

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
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