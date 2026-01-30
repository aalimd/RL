@extends('layouts.admin')

@section('page-title', 'Appearance')

@section('content')
    @if(session('success'))
        <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Colors & Branding -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3>Colors & Branding</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.appearance.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="branding">

                <div class="form-grid">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Primary Color</label>
                            <input type="color" name="primaryColor" value="{{ $settings['primaryColor'] ?? '#4F46E5' }}"
                                class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Secondary Color</label>
                            <input type="color" name="secondaryColor" value="{{ $settings['secondaryColor'] ?? '#9333EA' }}"
                                class="form-input">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Font Family</label>
                        <select name="fontFamily" class="form-select">
                            <option value="Inter" {{ ($settings['fontFamily'] ?? 'Inter') === 'Inter' ? 'selected' : '' }}>
                                Inter (Default)</option>
                            <option value="Plus Jakarta Sans" {{ ($settings['fontFamily'] ?? '') === 'Plus Jakarta Sans' ? 'selected' : '' }}>Plus Jakarta Sans</option>
                            <option value="Outfit" {{ ($settings['fontFamily'] ?? '') === 'Outfit' ? 'selected' : '' }}>Outfit
                            </option>
                            <option value="Poppins" {{ ($settings['fontFamily'] ?? '') === 'Poppins' ? 'selected' : '' }}>
                                Poppins</option>
                        </select>
                        <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem;">Choose the font used across your
                            public pages</p>
                    </div>

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

                    <!-- Hybrid Upload: Login Background -->
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
                                        onchange="previewImage(this, 'bgPreview'); document.getElementById('bgFileName').textContent = this.files[0]?.name || 'No file chosen'">
                                    <label for="loginBg_file" class="file-upload-btn">
                                        <i data-feather="upload" style="width: 16px; height: 16px;"></i>
                                        Choose File
                                    </label>
                                    <span class="file-upload-name" id="bgFileName">No file chosen</span>
                                </div>
                                <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem;">Recommended size:
                                    1920x1080px. Max: 4MB.</p>
                            </div>

                            <div class="upload-content" data-type="url" style="display: none;">
                                <input type="text" name="loginBackgroundImage" class="form-input"
                                    value="{{ $settings['loginBackgroundImage'] ?? '' }}"
                                    placeholder="https://example.com/bg.jpg"
                                    oninput="updatePreview(this.value, 'bgPreview')">
                            </div>

                            <div class="upload-preview {{ !empty($settings['loginBackgroundImage']) ? 'has-image' : '' }}"
                                id="bgPreviewBox">
                                <img id="bgPreview" src="{{ $settings['loginBackgroundImage'] ?? '' }}"
                                    style="{{ empty($settings['loginBackgroundImage']) ? 'display: none;' : '' }}"
                                    onerror="this.style.display='none'; this.parentElement.classList.remove('has-image');">
                                <span class="placeholder-text"
                                    style="{{ !empty($settings['loginBackgroundImage']) ? 'display: none;' : 'color: #9ca3af;' }}">
                                    No image selected
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <label class="form-label" style="margin-bottom: 0;">Show Logo on Login Page</label>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="showBranding" id="showBranding" {{ ($settings['showBranding'] ?? 'false') === 'true' ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary">Save Branding Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Landing Page Content -->
    <div class="card">
        <div class="card-header">
            <h3>Landing Page Content</h3>
            <span style="font-size: 0.875rem; color: #6b7280;">Control the content displayed on your homepage</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.appearance.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="landing">

                <div class="form-grid">
                    <!-- Hero Section -->
                    <div style="border-bottom: 1px solid #e5e7eb; padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
                        <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">
                            <i data-feather="layout"
                                style="width: 16px; height: 16px; display: inline; vertical-align: middle;"></i>
                            Hero Section
                        </h4>

                        <div class="form-group">
                            <label class="form-label">Hero Title Line 1</label>
                            <input type="text" name="heroTitle1" class="form-input"
                                value="{{ $settings['heroTitle1'] ?? 'Secure Your Academic' }}"
                                placeholder="Secure Your Academic">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Hero Title Line 2 (Gradient)</label>
                            <input type="text" name="heroTitle2" class="form-input"
                                value="{{ $settings['heroTitle2'] ?? 'Future Today' }}" placeholder="Future Today">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Hero Description</label>
                            <textarea name="heroDescription" class="form-textarea" rows="3"
                                placeholder="Description text...">{{ $settings['heroDescription'] ?? $settings['welcomeText'] ?? 'Streamline your academic recommendation process.' }}</textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Primary Button Text</label>
                                <input type="text" name="heroPrimaryBtn" class="form-input"
                                    value="{{ $settings['heroPrimaryBtn'] ?? 'Request Recommendation' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Secondary Button Text</label>
                                <input type="text" name="heroSecondaryBtn" class="form-input"
                                    value="{{ $settings['heroSecondaryBtn'] ?? 'Track Existing Request' }}">
                            </div>
                        </div>
                    </div>

                    <!-- Feature 1 -->
                    <div style="border-bottom: 1px solid #e5e7eb; padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
                        <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">
                            <i data-feather="box"
                                style="width: 16px; height: 16px; display: inline; vertical-align: middle;"></i>
                            Feature Card 1
                        </h4>
                        <div class="form-grid" style="gap: 1rem;">
                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem;">
                                <div class="form-group">
                                    <label class="form-label">Icon (Feather)</label>
                                    <input type="text" name="feature1Icon" class="form-input"
                                        value="{{ $settings['feature1Icon'] ?? 'file-text' }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="feature1Title" class="form-input"
                                        value="{{ $settings['feature1Title'] ?? 'Professional Templates' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="feature1Text" class="form-textarea"
                                    rows="2">{{ $settings['feature1Text'] ?? 'Choose from verified academic templates tailored for Masters, PhD, and Job applications.' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div style="border-bottom: 1px solid #e5e7eb; padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
                        <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">
                            <i data-feather="box"
                                style="width: 16px; height: 16px; display: inline; vertical-align: middle;"></i>
                            Feature Card 2
                        </h4>
                        <div class="form-grid" style="gap: 1rem;">
                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem;">
                                <div class="form-group">
                                    <label class="form-label">Icon (Feather)</label>
                                    <input type="text" name="feature2Icon" class="form-input"
                                        value="{{ $settings['feature2Icon'] ?? 'shield' }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="feature2Title" class="form-input"
                                        value="{{ $settings['feature2Title'] ?? 'Secure Verification' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="feature2Text" class="form-textarea"
                                    rows="2">{{ $settings['feature2Text'] ?? 'Every request is tracked and verified. Use your unique ID to check real-time progress.' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div style="border-bottom: 1px solid #e5e7eb; padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
                        <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">
                            <i data-feather="box"
                                style="width: 16px; height: 16px; display: inline; vertical-align: middle;"></i>
                            Feature Card 3
                        </h4>
                        <div class="form-grid" style="gap: 1rem;">
                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem;">
                                <div class="form-group">
                                    <label class="form-label">Icon (Feather)</label>
                                    <input type="text" name="feature3Icon" class="form-input"
                                        value="{{ $settings['feature3Icon'] ?? 'edit-3' }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="feature3Title" class="form-input"
                                        value="{{ $settings['feature3Title'] ?? 'Custom Inputs' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="feature3Text" class="form-textarea"
                                    rows="2">{{ $settings['feature3Text'] ?? 'Provide your own draft or specific points you want highlighted in your recommendation.' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="form-group">
                        <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">
                            <i data-feather="align-center"
                                style="width: 16px; height: 16px; display: inline; vertical-align: middle;"></i>
                            Footer
                        </h4>
                        <label class="form-label">Footer Text (use {year} for current year, {siteName} for site
                            name)</label>
                        <input type="text" name="footerText" class="form-input"
                            value="{{ $settings['footerText'] ?? '© {year} {siteName}. All rights reserved.' }}">
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary">Save Landing Page Content</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tracking Page Messages -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3>Tracking Page Messages</h3>
            <span style="font-size: 0.875rem; color: #6b7280;">Customize messages shown on the request tracking page</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.appearance.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="tracking">

                <div class="form-grid">
                    <!-- Fixed Message -->
                    <div style="border-bottom: 1px solid #e5e7eb; padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
                        <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">
                            <i data-feather="message-circle"
                                style="width: 16px; height: 16px; display: inline; vertical-align: middle;"></i>
                            Fixed Message
                        </h4>
                        <div class="form-group">
                            <label class="form-label">Message shown at the bottom of tracking results</label>
                            <textarea name="trackingFixedMessage" class="form-textarea" rows="2"
                                placeholder="If you need to submit additional documents...">{{ $settings['trackingFixedMessage'] ?? 'If you need to submit additional documents, please wait for the "Needs Revision" status.' }}</textarea>
                        </div>
                    </div>

                    <!-- Status-Based Messages -->
                    <div>
                        <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">
                            <i data-feather="flag"
                                style="width: 16px; height: 16px; display: inline; vertical-align: middle;"></i>
                            Status-Based Messages
                        </h4>

                        <div class="form-grid" style="gap: 1rem;">
                            <div style="display: grid; grid-template-columns: 120px 1fr; gap: 1rem; align-items: start;">
                                <span class="badge badge-pending" style="margin-top: 0.5rem;">Submitted</span>
                                <textarea name="trackingPendingMessage" class="form-textarea" rows="2"
                                    placeholder="Your request has been received...">{{ $settings['trackingPendingMessage'] ?? '' }}</textarea>
                            </div>

                            <div style="display: grid; grid-template-columns: 120px 1fr; gap: 1rem; align-items: start;">
                                <span class="badge" style="background: #fef3c7; color: #d97706; margin-top: 0.5rem;">Under
                                    Review</span>
                                <textarea name="trackingReviewMessage" class="form-textarea" rows="2"
                                    placeholder="Your request is currently being reviewed...">{{ $settings['trackingReviewMessage'] ?? '' }}</textarea>
                            </div>

                            <div style="display: grid; grid-template-columns: 120px 1fr; gap: 1rem; align-items: start;">
                                <span class="badge badge-approved" style="margin-top: 0.5rem;">Approved</span>
                                <textarea name="trackingApprovedMessage" class="form-textarea" rows="2"
                                    placeholder="Congratulations...">{{ $settings['trackingApprovedMessage'] ?? '' }}</textarea>
                            </div>

                            <div style="display: grid; grid-template-columns: 120px 1fr; gap: 1rem; align-items: start;">
                                <span class="badge badge-rejected" style="margin-top: 0.5rem;">Rejected</span>
                                <textarea name="trackingRejectedMessage" class="form-textarea" rows="2"
                                    placeholder="Unfortunately...">{{ $settings['trackingRejectedMessage'] ?? '' }}</textarea>
                            </div>

                            <div style="display: grid; grid-template-columns: 120px 1fr; gap: 1rem; align-items: start;">
                                <span class="badge badge-revision" style="margin-top: 0.5rem;">Needs Revision</span>
                                <textarea name="trackingRevisionMessage" class="form-textarea" rows="2"
                                    placeholder="Please review...">{{ $settings['trackingRevisionMessage'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary">Save Tracking Messages</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tab switching for uploads
            const uploadWrappers = document.querySelectorAll('.image-upload-wrapper');
            uploadWrappers.forEach(wrapper => {
                const tabs = wrapper.querySelectorAll('.upload-tab');
                const contents = wrapper.querySelectorAll('.upload-content');

                tabs.forEach(tab => {
                    tab.addEventListener('click', () => {
                        tabs.forEach(t => t.classList.remove('active'));
                        tab.classList.add('active');

                        const target = tab.dataset.target;
                        contents.forEach(c => {
                            if (c.dataset.type === target) c.style.display = 'block';
                            else c.style.display = 'none';
                        });
                    });
                });
            });
        });

        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const wrapper = preview.parentElement;
            const placeholder = wrapper.querySelector('.placeholder-text');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    wrapper.classList.add('has-image');
                    if (placeholder) placeholder.style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function updatePreview(url, previewId) {
            const preview = document.getElementById(previewId);
            const wrapper = preview.parentElement;
            const placeholder = wrapper.querySelector('.placeholder-text');

            if (url) {
                preview.src = url;
                preview.style.display = 'block';
                wrapper.classList.add('has-image');
                if (placeholder) placeholder.style.display = 'none';
            } else {
                preview.style.display = 'none';
                wrapper.classList.remove('has-image');
                if (placeholder) placeholder.style.display = 'block';
            }
        }
    </script>
@endsection