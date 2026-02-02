@extends('layouts.admin')

@section('page-title', 'Design & Appearance')

@section('styles')
    <style>
        .appearance-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.5rem;
        }

        .appearance-tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            border-radius: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            transition: all 0.3s ease;
        }

        .appearance-tab.active {
            background: var(--primary);
            color: white;
        }

        .appearance-tab:hover:not(.active) {
            background: var(--bg-hover);
            color: var(--text-main);
        }

        .section-content {
            display: none;
            animation: fadeIn 0.4s ease-out;
        }

        .section-content.active {
            display: block;
        }

        .aesthetic-preset {
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .aesthetic-preset:hover {
            border-color: var(--primary);
            background: var(--bg-hover);
        }

        .aesthetic-preset.active {
            border-color: var(--primary);
            background: rgba(var(--primary-rgb), 0.1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection

@section('content')
    @if(session('success'))
        <div
            style="background: var(--success-bg); color: var(--success-text); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid var(--success-border);">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div class="appearance-tabs" style="margin-bottom: 0; border: none;">
            <div class="appearance-tab active" onclick="switchTab('branding')">Branding & Styles</div>
            <div class="appearance-tab" onclick="switchTab('landing')">Landing Page</div>
            <div class="appearance-tab" onclick="switchTab('student-pages')">Student Pages</div>
            <div class="appearance-tab" onclick="switchTab('tracking-msgs')">Tracking Messages</div>
        </div>
        <form action="{{ route('admin.appearance.reset') }}" method="POST"
            onsubmit="return confirm('Are you sure you want to reset ALL design settings to factory defaults? This cannot be undone.');">
            @csrf
            <button type="submit" class="btn btn-danger"
                style="background: #ef4444; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 500; cursor: pointer;">
                <i data-lucide="rotate-ccw"
                    style="width: 18px; height: 18px; vertical-align: text-bottom; margin-right: 0.5rem;"></i>
                Reset to Defaults
            </button>
        </form>
    </div>

    <!-- 1. Branding & Global Styles -->
    <div id="branding" class="section-content active">
        <form method="POST" action="{{ route('admin.appearance.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="section" value="branding">

            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h3>Core Branding</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Primary Color</label>
                                <input type="color" name="primaryColor" value="{{ $settings['primaryColor'] ?? '#4F46E5' }}"
                                    class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Secondary Color</label>
                                <input type="color" name="secondaryColor"
                                    value="{{ $settings['secondaryColor'] ?? '#9333EA' }}" class="form-input">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Body Font (Main Text)</label>
                                <!-- Custom Font Picker for BODY (Visual Preview) -->
                                <input type="hidden" name="fontFamily" id="bodyFontInput"
                                    value="{{ $settings['fontFamily'] ?? 'Inter' }}">

                                <div class="custom-select-wrapper" style="position: relative;">
                                    <div id="bodyFontPickerTrigger" class="form-select"
                                        style="cursor: pointer; display: flex; align-items: center; justify-content: space-between;"
                                        onclick="toggleBodyFontPicker()">
                                        <span id="selectedBodyFontPreview"
                                            style="font-family: {{ $settings['fontFamily'] ?? 'Inter' }}; font-size: 1rem;">
                                            {{ $settings['fontFamily'] ?? 'Inter (Default)' }}
                                        </span>
                                        <i data-feather="chevron-down"></i>
                                    </div>

                                    <!-- Dropdown Menu -->
                                    <div id="bodyFontPickerDropdown" class="font-dropdown-menu"
                                        style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.5rem; max-height: 300px; overflow-y: auto; z-index: 9999; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">

                                        <!-- Modern Sans (Recommended) -->
                                        <div class="font-group-label"
                                            style="padding: 8px 12px; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); background: var(--bg-primary); text-transform: uppercase; letter-spacing: 0.05em;">
                                            Modern Sans (Recommended)</div>
                                        <div class="font-option" onclick="selectBodyFont('Inter')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Inter', sans-serif;">
                                            Inter (Default)</div>
                                        <div class="font-option" onclick="selectBodyFont('Plus Jakarta Sans')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif;">
                                            Plus Jakarta Sans</div>
                                        <div class="font-option" onclick="selectBodyFont('Outfit')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Outfit', sans-serif;">
                                            Outfit</div>
                                        <div class="font-option" onclick="selectBodyFont('Manrope')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Manrope', sans-serif;">
                                            Manrope</div>
                                        <div class="font-option" onclick="selectBodyFont('Urbanist')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Urbanist', sans-serif;">
                                            Urbanist</div>
                                        <div class="font-option" onclick="selectBodyFont('Quicksand')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Quicksand', sans-serif;">
                                            Quicksand</div>

                                        <!-- Classic & Professional -->
                                        <div class="font-group-label"
                                            style="padding: 8px 12px; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); background: var(--bg-primary); text-transform: uppercase; letter-spacing: 0.05em; border-top: 1px solid var(--border-color);">
                                            Classic & Professional</div>
                                        <div class="font-option" onclick="selectBodyFont('Poppins')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                                            Poppins</div>
                                        <div class="font-option" onclick="selectBodyFont('Montserrat')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Montserrat', sans-serif;">
                                            Montserrat</div>
                                        <div class="font-option" onclick="selectBodyFont('Raleway')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Raleway', sans-serif;">
                                            Raleway</div>
                                        <div class="font-option" onclick="selectBodyFont('Merriweather')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Merriweather', serif;">
                                            Merriweather (Serif)</div>
                                    </div>
                                </div>

                                <script>
                                    function toggleBodyFontPicker() {
                                        const dropdown = document.getElementById('bodyFontPickerDropdown');
                                        const otherDropdown = document.getElementById('fontPickerDropdown');
                                        if (otherDropdown) otherDropdown.style.display = 'none'; // Close other
                                        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
                                    }

                                    function selectBodyFont(fontName) {
                                        document.getElementById('bodyFontInput').value = fontName;
                                        const preview = document.getElementById('selectedBodyFontPreview');
                                        preview.textContent = fontName;
                                        preview.style.fontFamily = fontName;
                                        document.getElementById('bodyFontPickerDropdown').style.display = 'none';
                                    }

                                    // Close on click outside (merged logic later or separate is fine for now)
                                    document.addEventListener('click', function (event) {
                                        const wrapper = document.getElementById('bodyFontPickerTrigger');
                                        const dropdown = document.getElementById('bodyFontPickerDropdown');
                                        if (dropdown && dropdown.style.display === 'block' && !wrapper.contains(event.target) && !dropdown.contains(event.target)) {
                                            dropdown.style.display = 'none';
                                        }
                                    });
                                </script>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Heading Font (Titles)</label>
                                <!-- Custom Font Picker (Visual Preview) -->
                                <input type="hidden" name="headingFont" id="headingFontInput"
                                    value="{{ $settings['headingFont'] ?? 'Inter' }}">

                                <div class="custom-select-wrapper" style="position: relative;">
                                    <div id="fontPickerTrigger" class="form-select"
                                        style="cursor: pointer; display: flex; align-items: center; justify-content: space-between;"
                                        onclick="toggleFontPicker()">
                                        <span id="selectedFontPreview"
                                            style="font-family: {{ $settings['headingFont'] ?? 'Inter' }}; font-size: 1.1rem;">
                                            {{ $settings['headingFont'] ?? 'Select Heading Font' }}
                                        </span>
                                        <i data-feather="chevron-down"></i>
                                    </div>

                                    <!-- Dropdown Menu -->
                                    <div id="fontPickerDropdown" class="font-dropdown-menu"
                                        style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.5rem; max-height: 300px; overflow-y: auto; z-index: 9999; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">

                                        <!-- Modern Trendy -->
                                        <div class="font-group-label"
                                            style="padding: 8px 12px; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); background: var(--bg-primary); text-transform: uppercase; letter-spacing: 0.05em;">
                                            Trendy & Modern</div>
                                        <div class="font-option" onclick="selectFont('Space Grotesk')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Space Grotesk', sans-serif; font-size: 1.1rem;">
                                            Space Grotesk</div>
                                        <div class="font-option" onclick="selectFont('Urbanist')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Urbanist', sans-serif; font-size: 1.1rem;">
                                            Urbanist</div>
                                        <div class="font-option" onclick="selectFont('Outfit')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Outfit', sans-serif; font-size: 1.1rem;">
                                            Outfit</div>

                                        <!-- Script & Handwriting -->
                                        <div class="font-group-label"
                                            style="padding: 8px 12px; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); background: var(--bg-primary); text-transform: uppercase; letter-spacing: 0.05em; border-top: 1px solid var(--border-color);">
                                            Script & Handwriting</div>
                                        <div class="font-option" onclick="selectFont('Pacifico')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Pacifico', handwriting; font-size: 1.25rem;">
                                            Pacifico</div>
                                        <div class="font-option" onclick="selectFont('Satisfy')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Satisfy', handwriting; font-size: 1.25rem;">
                                            Satisfy</div>
                                        <div class="font-option" onclick="selectFont('Dancing Script')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Dancing Script', handwriting; font-size: 1.25rem;">
                                            Dancing Script</div>
                                        <div class="font-option" onclick="selectFont('Great Vibes')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Great Vibes', handwriting; font-size: 1.25rem;">
                                            Great Vibes</div>
                                        <div class="font-option" onclick="selectFont('Lobster')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Lobster', handwriting; font-size: 1.1rem;">
                                            Lobster</div>

                                        <!-- Elegant Serif -->
                                        <div class="font-group-label"
                                            style="padding: 8px 12px; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); background: var(--bg-primary); text-transform: uppercase; letter-spacing: 0.05em; border-top: 1px solid var(--border-color);">
                                            Elegant Serif</div>
                                        <div class="font-option" onclick="selectFont('Playfair Display')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Playfair Display', serif; font-size: 1.1rem;">
                                            Playfair Display</div>
                                        <div class="font-option" onclick="selectFont('Cinzel')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Cinzel', serif; font-size: 1.1rem;">
                                            Cinzel</div>
                                        <div class="font-option" onclick="selectFont('Prata')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Prata', serif; font-size: 1.1rem;">
                                            Prata</div>
                                        <div class="font-option" onclick="selectFont('DM Serif Display')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'DM Serif Display', serif; font-size: 1.1rem;">
                                            DM Serif Display</div>
                                        <div class="font-option" onclick="selectFont('Libre Baskerville')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Libre Baskerville', serif; font-size: 1.1rem;">
                                            Libre Baskerville</div>

                                        <!-- Bold Display -->
                                        <div class="font-group-label"
                                            style="padding: 8px 12px; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); background: var(--bg-primary); text-transform: uppercase; letter-spacing: 0.05em; border-top: 1px solid var(--border-color);">
                                            Bold Display</div>
                                        <div class="font-option" onclick="selectFont('Abril Fatface')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Abril Fatface', display; font-size: 1.2rem;">
                                            Abril Fatface</div>
                                        <div class="font-option" onclick="selectFont('Righteous')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Righteous', display; font-size: 1.1rem;">
                                            Righteous</div>
                                        <div class="font-option" onclick="selectFont('Bebas Neue')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Bebas Neue', display; font-size: 1.25rem;">
                                            Bebas Neue</div>
                                        <div class="font-option" onclick="selectFont('Anton')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Anton', display; font-size: 1.1rem;">
                                            Anton</div>

                                        <!-- Standard -->
                                        <div class="font-group-label"
                                            style="padding: 8px 12px; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); background: var(--bg-primary); text-transform: uppercase; letter-spacing: 0.05em; border-top: 1px solid var(--border-color);">
                                            Standard</div>
                                        <div class="font-option" onclick="selectFont('Inter')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Inter', sans-serif;">
                                            Inter</div>
                                        <div class="font-option" onclick="selectFont('Montserrat')"
                                            style="padding: 10px 15px; cursor: pointer; font-family: 'Montserrat', sans-serif;">
                                            Montserrat</div>
                                    </div>
                                </div>

                                <script>
                                    function toggleFontPicker() {
                                        const dropdown = document.getElementById('fontPickerDropdown');
                                        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
                                    }

                                    function selectFont(fontName) {
                                        // Update hidden input
                                        document.getElementById('headingFontInput').value = fontName;

                                        // Update visual preview
                                        const preview = document.getElementById('selectedFontPreview');
                                        preview.textContent = fontName;
                                        preview.style.fontFamily = fontName;

                                        // Close dropdown
                                        document.getElementById('fontPickerDropdown').style.display = 'none';
                                    }

                                    // Close on click outside
                                    document.addEventListener('click', function (event) {
                                        const trigger = document.getElementById('fontPickerTrigger');
                                        const dropdown = document.getElementById('fontPickerDropdown');
                                        if (dropdown && dropdown.style.display === 'block' && !trigger.contains(event.target) && !dropdown.contains(event.target)) {
                                            dropdown.style.display = 'none';
                                        }
                                    });

                                    // Add hover effect via JS since inline css is heavy
                                    document.querySelectorAll('.font-option').forEach(item => {
                                        item.addEventListener('mouseover', () => {
                                            item.style.background = 'var(--primary)';
                                            item.style.color = '#fff';
                                        });
                                        item.addEventListener('mouseout', () => {
                                            item.style.background = 'transparent';
                                            item.style.color = 'var(--text-main)';
                                        });
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h3>Global Aesthetics</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Border Radius (Smoothness)</label>
                            <select name="borderRadius" class="form-select">
                                <option value="0" {{ ($settings['borderRadius'] ?? '1') == '0' ? 'selected' : '' }}>Sharp
                                    Corners (0px)</option>
                                <option value="0.5" {{ ($settings['borderRadius'] ?? '1') == '0.5' ? 'selected' : '' }}>
                                    Slightly Rounded (8px)</option>
                                <option value="1" {{ ($settings['borderRadius'] ?? '1') == '1' ? 'selected' : '' }}>Modern
                                    Rounded (16px - Default)</option>
                                <option value="1.5" {{ ($settings['borderRadius'] ?? '1') == '1.5' ? 'selected' : '' }}>Extra
                                    Rounded (24px)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Shadow Intensity</label>
                            <select name="shadowIntensity" class="form-select">
                                <option value="0" {{ ($settings['shadowIntensity'] ?? '1') == '0' ? 'selected' : '' }}>No
                                    Shadows (Flat Design)</option>
                                <option value="0.5" {{ ($settings['shadowIntensity'] ?? '1') == '0.5' ? 'selected' : '' }}>
                                    Soft Subtle Shadows</option>
                                <option value="1" {{ ($settings['shadowIntensity'] ?? '1') == '1' ? 'selected' : '' }}>
                                    Standard Deep Shadows (Default)</option>
                                <option value="1.5" {{ ($settings['shadowIntensity'] ?? '1') == '1.5' ? 'selected' : '' }}>
                                    Extra Deep Premium Shadows</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Glassmorphism Effect Transparency</label>
                            <input type="range" name="glassEffect" min="0" max="1" step="0.1"
                                value="{{ $settings['glassEffect'] ?? '0.7' }}" class="form-input" style="padding:0">
                            <div
                                style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted);">
                                <span>Solid</span>
                                <span>Ideal (0.7)</span>
                                <span>Transparent</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Buttons Gradient Direction</label>
                            <select name="buttonGradient" class="form-select">
                                <option value="linear-gradient(135deg, var(--primary), var(--secondary))" {{ ($settings['buttonGradient'] ?? '') === 'linear-gradient(135deg, var(--primary), var(--secondary))' ? 'selected' : '' }}>Standard (Diagonal)</option>
                                <option value="linear-gradient(to right, var(--primary), var(--secondary))" {{ ($settings['buttonGradient'] ?? '') === 'linear-gradient(to right, var(--primary), var(--secondary))' ? 'selected' : '' }}>Horizontal (Left to Right)</option>
                                <option value="var(--primary)" {{ ($settings['buttonGradient'] ?? '') === 'var(--primary)' ? 'selected' : '' }}>Solid Primary Color</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h3>Login Page Appearance</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Login Title</label>
                                <input type="text" name="loginTitle" class="form-input"
                                    value="{{ $settings['loginTitle'] ?? 'Admin Login' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Login Subtitle</label>
                                <input type="text" name="loginSubtitle" class="form-input"
                                    value="{{ $settings['loginSubtitle'] ?? '' }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Login Background Image</label>
                            <div class="image-upload-wrapper" id="loginBgUpload">
                                <div class="upload-tabs">
                                    <div class="upload-tab active" data-target="file">Upload File</div>
                                    <div class="upload-tab" data-target="url">Image URL</div>
                                </div>
                                <div class="upload-content" data-type="file">
                                    <div class="file-upload-container">
                                        <input type="file" name="loginBackgroundImage_file" id="loginBg_file"
                                            class="file-upload-input" accept="image/*"
                                            onchange="previewImage(this, 'bgPreview')">
                                        <label for="loginBg_file" class="file-upload-btn"><i data-feather="upload"></i>
                                            Choose File</label>
                                    </div>
                                </div>
                                <div class="upload-content" data-type="url" style="display: none;">
                                    <input type="text" name="loginBackgroundImage" class="form-input"
                                        value="{{ $settings['loginBackgroundImage'] ?? '' }}"
                                        placeholder="https://example.com/bg.jpg">
                                </div>
                                <div
                                    class="upload-preview {{ !empty($settings['loginBackgroundImage']) ? 'has-image' : '' }}">
                                    <img id="bgPreview" src="{{ $settings['loginBackgroundImage'] ?? '' }}"
                                        style="{{ empty($settings['loginBackgroundImage']) ? 'display: none;' : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <label class="form-label" style="margin-bottom: 0;">Show Logo on Login Page</label>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="showBranding" id="showBranding" {{ ($settings['showBranding'] ?? 'false') === 'true' ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Save Branding & Global Styles</button>
            </div>
        </form>
    </div>

    <!-- 2. Landing Page Content -->
    <div id="landing" class="section-content">
        <form method="POST" action="{{ route('admin.appearance.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="section" value="landing">
            <div class="card">
                <div class="card-header">
                    <h3>Hero & Featured Section</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Hero Title Main</label>
                                <input type="text" name="heroTitle1" class="form-input"
                                    value="{{ $settings['heroTitle1'] ?? 'Secure Your Academic' }}">
                                <small style="color: var(--text-muted); font-size: 0.75rem;">Leave empty to hide this part.
                                    Spacing adjusts automatically.</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hero Title Gradient Part</label>
                                <input type="text" name="heroTitle2" class="form-input"
                                    value="{{ $settings['heroTitle2'] ?? 'Future Today' }}">
                                <small style="color: var(--text-muted); font-size: 0.75rem;">The highlighted/gradient part
                                    of the title.</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Hero Description</label>
                            <textarea name="heroDescription" class="form-textarea"
                                rows="3">{{ $settings['heroDescription'] ?? '' }}</textarea>
                            <small style="color: var(--text-muted); font-size: 0.75rem;">Optional. If empty, it will be
                                removed along with its spacing for a cleaner look.</small>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Request Button Label</label>
                                <input type="text" name="heroPrimaryBtn" class="form-input"
                                    value="{{ $settings['heroPrimaryBtn'] ?? 'Request Recommendation' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Track Button Label</label>
                                <input type="text" name="heroSecondaryBtn" class="form-input"
                                    value="{{ $settings['heroSecondaryBtn'] ?? 'Track Existing Request' }}">
                            </div>
                        </div>
                    </div>

                    <hr style="margin: 2rem 0; border: 0; border-top: 1px solid var(--border-color);">
                    <h4 style="margin-bottom: 1.5rem;">Feature Cards (Display 3 main advantages)</h4>

                    <div class="form-grid" style="grid-template-columns: repeat(3, 1fr);">
                        @for($i = 1; $i <= 3; $i++)
                            <div
                                style="padding: 1rem; border: 1px solid var(--border-color); border-radius: 1rem; background: var(--bg-hover);">
                                <div class="form-group">
                                    <label class="form-label">Icon (Feather Name)</label>
                                    <input type="text" name="feature{{$i}}Icon" class="form-input"
                                        value="{{ $settings['feature' . $i . 'Icon'] ?? 'box' }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Card Title {{$i}}</label>
                                    <input type="text" name="feature{{$i}}Title" class="form-input"
                                        value="{{ $settings['feature' . $i . 'Title'] ?? 'Feature ' . $i }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <textarea name="feature{{$i}}Text" class="form-textarea"
                                        rows="2">{{ $settings['feature' . $i . 'Text'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div class="form-group" style="margin-top: 1.5rem;">
                        <label class="form-label">Footer Copyright Text</label>
                        <input type="text" name="footerText" class="form-input"
                            value="{{ $settings['footerText'] ?? '© {year} {siteName}. All rights reserved.' }}">
                    </div>
                </div>
            </div>
            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">Save Landing Page Changes</button>
            </div>
        </form>
    </div>

    <!-- 3. Student Pages Content -->
    <div id="student-pages" class="section-content">
        <form method="POST" action="{{ route('admin.appearance.update') }}">
            @csrf
            @method('PUT')

            <div class="card" style="margin-bottom: 1.5rem;">
                <input type="hidden" name="section" value="request_page">
                <div class="card-header">
                    <h3>Request Form Page</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Page Title</label>
                            <input type="text" name="requestTitle" class="form-input"
                                value="{{ $settings['requestTitle'] ?? 'Request a Recommendation' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Page Subtitle</label>
                            <input type="text" name="requestSubtitle" class="form-input"
                                value="{{ $settings['requestSubtitle'] ?? 'Please fill out the form below carefully.' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Submit Button Label</label>
                            <input type="text" name="requestSubmitBtn" class="form-input"
                                value="{{ $settings['requestSubmitBtn'] ?? 'Submit Request' }}">
                        </div>
                    </div>
                    <div style="margin-top: 1rem;"><button type="submit" class="btn btn-primary">Save Request Page
                            Settings</button></div>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.appearance.update') }}">
            @csrf
            @method('PUT')
            <div class="card">
                <input type="hidden" name="section" value="tracking">
                <div class="card-header">
                    <h3>Tracking Results Page</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Search Title</label>
                            <input type="text" name="trackingTitle" class="form-input"
                                value="{{ $settings['trackingTitle'] ?? 'Track Your Request' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Search Subtitle</label>
                            <input type="text" name="trackingSubtitle" class="form-input"
                                value="{{ $settings['trackingSubtitle'] ?? 'Enter your ID Number to check your status.' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Search Button Label</label>
                            <input type="text" name="trackingSearchBtn" class="form-input"
                                value="{{ $settings['trackingSearchBtn'] ?? 'Track Request' }}">
                        </div>
                    </div>
                    <div style="margin-top: 1rem;"><button type="submit" class="btn btn-primary">Save Tracking Page
                            Settings</button></div>
                </div>
            </div>
        </form>
    </div>

    <!-- 4. Tracking Messages -->
    <div id="tracking-msgs" class="section-content">
        <form method="POST" action="{{ route('admin.appearance.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="section" value="tracking">
            <div class="card">
                <div class="card-header">
                    <h3>Status-Based Public Messages</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        @php
                            $statuses = [
                                'Pending' => ['pending', 'trackingPendingMessage'],
                                'Under Review' => ['review', 'trackingReviewMessage'],
                                'Approved' => ['approved', 'trackingApprovedMessage'],
                                'Rejected' => ['rejected', 'trackingRejectedMessage'],
                                'Needs Revision' => ['revision', 'trackingRevisionMessage'],
                            ];
                        @endphp
                        @foreach($statuses as $label => $data)
                            <div
                                style="display: grid; grid-template-columns: 140px 1fr; gap: 1rem; align-items: start; margin-bottom: 1rem;">
                                <span class="badge badge-{{ $data[0] }}"
                                    style="margin-top: 0.5rem; text-align:center">{{ $label }}</span>
                                <textarea name="{{ $data[1] }}" class="form-textarea" rows="2"
                                    placeholder="Message for {{ $label }}...">{{ $settings[$data[1]] ?? '' }}</textarea>
                            </div>
                        @endforeach

                        <hr style="margin: 1.5rem 0; border:0; border-top:1px solid var(--border-color)">
                        <div class="form-group">
                            <label class="form-label">Fixed footer message (always shown on results)</label>
                            <textarea name="trackingFixedMessage" class="form-textarea"
                                rows="2">{{ $settings['trackingFixedMessage'] ?? '' }}</textarea>
                        </div>
                    </div>
                    <div style="margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary">Save Status Messages</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.appearance-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.section-content').forEach(c => c.classList.remove('active'));

            event.currentTarget.classList.add('active');
            document.getElementById(tabId).classList.add('active');

            // Save last active tab to localStorage
            localStorage.setItem('appearance_active_tab', tabId);
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Restore last active tab
            const lastTab = localStorage.getItem('appearance_active_tab');
            if (lastTab && document.getElementById(lastTab)) {
                const tabBtn = Array.from(document.querySelectorAll('.appearance-tab')).find(b => b.onclick.toString().includes(lastTab));
                if (tabBtn) tabBtn.click();
            }

            // Image upload handling
            const uploadContainers = document.querySelectorAll('.image-upload-wrapper');
            uploadContainers.forEach(container => {
                const tabs = container.querySelectorAll('.upload-tab');
                const contents = container.querySelectorAll('.upload-content');
                tabs.forEach(tab => {
                    tab.onclick = () => {
                        tabs.forEach(t => t.classList.remove('active'));
                        tab.classList.add('active');
                        const target = tab.dataset.target;
                        contents.forEach(c => {
                            c.style.display = (c.dataset.type === target) ? 'block' : 'none';
                        });
                    };
                });
            });
        });

        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    preview.parentElement.classList.add('has-image');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection