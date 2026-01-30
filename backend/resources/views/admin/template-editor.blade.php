@extends('layouts.admin')

@section('page-title', isset($template) ? 'Edit Template' : 'Create Template')

@section('styles')
<style>
    /* ========== PROFESSIONAL TEMPLATE EDITOR ========== */
    
    /* Reset for this page */
    .template-editor-page {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 60px);
        background: #f8fafc;
        overflow: hidden;
    }
    
    /* Header Bar */
    .te-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1.5rem;
        background: white;
        border-bottom: 1px solid #e2e8f0;
        flex-shrink: 0;
    }
    
    .te-header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .te-back-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #64748b;
        text-decoration: none;
        transition: all 0.2s;
    }
    .te-back-btn:hover {
        background: #e2e8f0;
        color: #334155;
    }
    
    .te-title-input {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1e293b;
        border: 1px solid transparent;
        background: transparent;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        min-width: 280px;
        transition: all 0.2s;
    }
    .te-title-input:hover,
    .te-title-input:focus {
        border-color: #e2e8f0;
        background: white;
        outline: none;
    }
    
    .te-header-right {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .te-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: #64748b;
    }
    
    .te-toggle {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
    }
    .te-toggle input {
        accent-color: #10b981;
    }
    
    .te-save-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .te-save-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }
    
    /* Tabs Bar */
    .te-tabs {
        display: flex;
        align-items: center;
        padding: 0 1.5rem;
        background: white;
        border-bottom: 1px solid #e2e8f0;
        flex-shrink: 0;
    }
    
    .te-tab {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1.25rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
        background: transparent;
        border: none;
        cursor: pointer;
        position: relative;
        transition: color 0.2s;
    }
    .te-tab:hover {
        color: #334155;
    }
    .te-tab.active {
        color: #4f46e5;
    }
    .te-tab.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: #4f46e5;
    }
    .te-tab svg {
        width: 16px;
        height: 16px;
    }
    
    /* Main Content Area */
    .te-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    /* Editor Panel */
    .te-editor-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: white;
        overflow: hidden;
    }
    
    .te-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .te-panel-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .te-editor-content {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
    }
    
    /* Tab Panes */
    .te-pane {
        display: none;
    }
    .te-pane.active {
        display: block;
    }
    
    /* Form Elements */
    .te-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    
    .te-input {
        width: 100%;
        padding: 0.625rem 0.875rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 0.875rem;
        color: #1e293b;
        transition: all 0.2s;
    }
    .te-input:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    
    .te-textarea {
        width: 100%;
        min-height: 300px;
        padding: 0.875rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.875rem;
        font-family: 'Monaco', 'Consolas', monospace;
        line-height: 1.6;
        resize: vertical;
        transition: all 0.2s;
    }
    .te-textarea:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    
    .te-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .te-field {
        margin-bottom: 1rem;
    }
    
    /* Header Builder */
    .te-builder-card {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 1px solid #86efac;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    
    .te-builder-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 700;
        color: #166534;
        margin-bottom: 1rem;
    }
    
    .te-upload-zone {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        border: 2px dashed #86efac;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
        margin-bottom: 1rem;
    }
    .te-upload-zone:hover {
        border-color: #22c55e;
        background: #f0fdf4;
    }
    .te-upload-zone svg {
        width: 32px;
        height: 32px;
        color: #22c55e;
        margin-bottom: 0.5rem;
    }
    .te-upload-zone p {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        color: #166534;
    }
    
    .te-builder-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .te-builder-input {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #86efac;
        border-radius: 6px;
        font-size: 0.8rem;
        background: white;
    }
    .te-builder-input:focus {
        outline: none;
        border-color: #22c55e;
    }
    
    .te-generate-btn {
        width: 100%;
        padding: 0.75rem;
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }
    .te-generate-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
    }
    
    /* Preview Panel */
    .te-preview-panel {
        display: flex;
        flex-direction: column;
        background: #f1f5f9;
        overflow: hidden;
    }
    
    .te-preview-container {
        flex: 1;
        overflow-y: auto;
        padding: 2rem;
        display: flex;
        justify-content: center;
        background-image: url("data:image/svg+xml,%3Csvg width='20' height='20' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='dots' patternUnits='userSpaceOnUse' width='20' height='20'%3E%3Ccircle cx='2' cy='2' r='1' fill='%23cbd5e1'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='20' height='20' fill='url(%23dots)'/%3E%3C/svg%3E");
    }
    
    .te-paper {
        width: 210mm;
        min-height: 297mm;
        background: white;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        position: relative;
    }
    
    .te-paper-content {
        padding: 20mm;
    }
    
    /* Preview Button */
    .te-preview-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .te-preview-btn:hover {
        background: #e2e8f0;
    }
    .te-preview-btn svg {
        width: 16px;
        height: 16px;
    }
    
    /* Preview Modal */
    .te-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s;
    }
    .te-modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    .te-modal {
        background: white;
        border-radius: 12px;
        width: 95%;
        max-width: 900px;
        max-height: 90vh;
        overflow: hidden;
        transform: scale(0.9);
        transition: transform 0.3s;
        display: flex;
        flex-direction: column;
    }
    .te-modal-overlay.active .te-modal {
        transform: scale(1);
    }
    
    .te-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .te-modal-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
    }
    
    .te-modal-close {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e2e8f0;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        color: #64748b;
        transition: all 0.2s;
    }
    .te-modal-close:hover {
        background: #cbd5e1;
        color: #334155;
    }
    
    .te-modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 2rem;
        display: flex;
        justify-content: center;
        background-image: url("data:image/svg+xml,%3Csvg width='20' height='20' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='dots' patternUnits='userSpaceOnUse' width='20' height='20'%3E%3Ccircle cx='2' cy='2' r='1' fill='%23cbd5e1'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='20' height='20' fill='url(%23dots)'/%3E%3C/svg%3E");
    }
    
    /* Variable Bar */
    .te-variable-bar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.5rem;
        background: white;
        border-top: 1px solid #e2e8f0;
        flex-shrink: 0;
    }
    
    .te-variable-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
    }
    
    .te-variable-chips {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .te-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.375rem 0.75rem;
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        color: #4338ca;
        border: 1px solid #c7d2fe;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .te-chip:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.2);
    }
    .te-chip svg {
        width: 12px;
        height: 12px;
    }
    
    /* TinyMCE Overrides */
    .tox-tinymce {
        border-radius: 8px !important;
        border-color: #e2e8f0 !important;
    }
    .tox .tox-toolbar__primary {
        background: #f8fafc !important;
    }
    
    /* Error Alert */
    .te-error-alert {
        position: fixed;
        top: 80px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        max-width: 400px;
    }
    .te-error-alert ul {
        margin: 0.5rem 0 0 0;
        padding-left: 1.25rem;
        font-size: 0.875rem;
    }
    

</style>
@endsection

@section('content')
@php
    $layoutSettings = [];
    if (isset($template) && $template->layout_settings) {
        $layoutSettings = is_array($template->layout_settings) ? $template->layout_settings : json_decode($template->layout_settings, true) ?? [];
    }
@endphp

<form method="POST" id="templateForm" class="template-editor-page"
    action="{{ isset($template) ? route('admin.templates.update', $template->id) : route('admin.templates.store') }}">
    @csrf
    @if(isset($template))
        @method('PUT')
    @endif
    
    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="te-error-alert">
            <strong>Please fix the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <!-- Header Bar -->
    <div class="te-header">
        <div class="te-header-left">
            <a href="{{ route('admin.templates') }}" class="te-back-btn">
                <i data-feather="arrow-left"></i>
            </a>
            <input type="text" name="name" class="te-title-input" 
                value="{{ old('name', $template->name ?? 'Untitled Template') }}"
                placeholder="Template Name" required>
        </div>
        
        <div class="te-header-right">
            <div class="te-status" id="autoSaveStatus"></div>
            
            <button type="button" class="te-preview-btn" onclick="openPreview()">
                <i data-feather="eye"></i>
                Preview
            </button>
            
            <label class="te-toggle">
                <input type="checkbox" name="is_active" {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
                <span>Active</span>
            </label>
            
            @if(isset($template))
            <button type="button" onclick="confirmReset()" style="margin-right: 0.5rem; background: #ef4444; color: white; border: none; padding: 0.625rem 1.25rem; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#dc2626'" onmouseout="this.style.backgroundColor='#ef4444'">
                <i data-feather="refresh-cw"></i>
                Reset to Default
            </button>
            @endif
            
            <button type="submit" class="te-save-btn">
                <i data-feather="save"></i>
                Save Template
            </button>
        </div>
    </div>
    
    <!-- Tabs Bar -->
    <div class="te-tabs">
        <button type="button" class="te-tab active" data-tab="header">
            <i data-feather="type"></i> Header
        </button>
        <button type="button" class="te-tab" data-tab="body">
            <i data-feather="file-text"></i> Body
        </button>
        <button type="button" class="te-tab" data-tab="footer">
            <i data-feather="align-justify"></i> Footer
        </button>
        <button type="button" class="te-tab" data-tab="signature">
            <i data-feather="pen-tool"></i> Signature
        </button>
        <button type="button" class="te-tab" data-tab="settings">
            <i data-feather="settings"></i> Settings
        </button>
    </div>
    
    <!-- Main Content -->
    <div class="te-main">
        <!-- Editor Panel -->
        <div class="te-editor-panel">
            <div class="te-panel-header">
                <span class="te-panel-title">Content Editor</span>
            </div>
            
            <div class="te-editor-content">
                <!-- Header Tab -->
                <div id="pane-header" class="te-pane active">
                    <!-- Quick Builder -->
                    <div class="te-builder-card">
                        <div class="te-builder-title">
                            <i data-feather="zap"></i>
                            Quick Header Builder
                        </div>
                        
                        <div class="te-upload-zone" onclick="document.getElementById('logoUpload').click()">
                            <i data-feather="upload-cloud"></i>
                            <p id="logoStatus">Click to upload logo</p>
                        </div>
                        <input type="file" id="logoUpload" accept="image/*" style="display: none;">
                        <input type="hidden" id="logoBase64">
                        
                        <div class="te-builder-grid">
                            <div>
                                <label class="te-label">Left Text (English)</label>
                                <textarea id="builderLeft" class="te-builder-input" rows="2" placeholder="Organization name..."></textarea>
                            </div>
                            <div>
                                <label class="te-label">Right Text (Arabic)</label>
                                <textarea id="builderRight" class="te-builder-input" rows="2" placeholder="اسم المؤسسة..." dir="rtl"></textarea>
                            </div>
                        </div>
                        
                        <button type="button" class="te-generate-btn" onclick="generateHeader()">
                            <i data-feather="zap"></i>
                            Generate Header
                        </button>
                    </div>
                    
                    <div class="te-field">
                        <label class="te-label">Header HTML</label>
                        <textarea name="header_content" id="headerEditor" class="te-textarea">{{ old('header_content', $template->header_content ?? '') }}</textarea>
                    </div>
                </div>
                
                <!-- Body Tab -->
                <div id="pane-body" class="te-pane">
                    <div class="te-field">
                        <label class="te-label">Body Content</label>
                        <textarea name="body_content" id="bodyEditor" class="te-textarea">{{ old('body_content', $template->body_content ?? '') }}</textarea>
                    </div>
                </div>
                
                <!-- Footer Tab -->
                <div id="pane-footer" class="te-pane">
                    <div class="te-field">
                        <label class="te-label">Footer Content</label>
                        <textarea name="footer_content" id="footerEditor" class="te-textarea">{{ old('footer_content', $template->footer_content ?? '') }}</textarea>
                    </div>
                </div>
                
                <!-- Signature Tab -->
                <div id="pane-signature" class="te-pane">
                    <div class="te-grid">
                        <div class="te-field">
                            <label class="te-label">Full Name</label>
                            <input type="text" name="signature_name" id="sigName" class="te-input" 
                                value="{{ old('signature_name', $template->signature_name ?? '') }}">
                        </div>
                        <div class="te-field">
                            <label class="te-label">Job Title</label>
                            <input type="text" name="signature_title" id="sigTitle" class="te-input" 
                                value="{{ old('signature_title', $template->signature_title ?? '') }}">
                        </div>
                        <div class="te-field">
                            <label class="te-label">Department</label>
                            <input type="text" name="signature_department" id="sigDept" class="te-input" 
                                value="{{ old('signature_department', $template->signature_department ?? '') }}">
                        </div>
                        <div class="te-field">
                            <label class="te-label">Institution</label>
                            <input type="text" name="signature_institution" id="sigInst" class="te-input" 
                                value="{{ old('signature_institution', $template->signature_institution ?? '') }}">
                        </div>
                        <div class="te-field">
                            <label class="te-label">Email Address</label>
                            <input type="email" name="signature_email" id="sigEmail" class="te-input" 
                                value="{{ old('signature_email', $template->signature_email ?? '') }}">
                        </div>
                        <div class="te-field">
                            <label class="te-label">Phone Number</label>
                            <input type="text" name="signature_phone" id="sigPhone" class="te-input" 
                                value="{{ old('signature_phone', $template->signature_phone ?? '') }}">
                        </div>
                    </div>
                    <div class="te-field">
                        <label class="te-label">Signature Image URL</label>
                        <input type="text" name="signature_image" id="sigImage" class="te-input" 
                            value="{{ old('signature_image', $template->signature_image ?? '') }}" 
                            placeholder="https://...">
                    </div>
                    <div class="te-field">
                        <label class="te-label">Official Stamp Image URL (Optional)</label>
                        <input type="text" name="stamp_image" id="stampImage" class="te-input" 
                            value="{{ old('stamp_image', $template->stamp_image ?? '') }}" 
                            placeholder="https://...">
                    </div>
                </div>
                
                <!-- Settings Tab -->
                <div id="pane-settings" class="te-pane">
                    <div class="te-grid">
                        <div class="te-field">
                            <label class="te-label">Font Family</label>
                            <select name="layout_settings[fontFamily]" id="settingFont" class="te-input">
                                <option value="Arial, sans-serif" {{ ($layoutSettings['fontFamily'] ?? '') === 'Arial, sans-serif' ? 'selected' : '' }}>Arial</option>
                                <option value="'Times New Roman', serif" {{ ($layoutSettings['fontFamily'] ?? '') === "'Times New Roman', serif" ? 'selected' : '' }}>Times New Roman</option>
                                <option value="'Courier New', monospace" {{ ($layoutSettings['fontFamily'] ?? '') === "'Courier New', monospace" ? 'selected' : '' }}>Courier New</option>
                            </select>
                        </div>
                        <div class="te-field">
                            <label class="te-label">Font Size (pt)</label>
                            <input type="number" name="layout_settings[fontSize]" id="settingSize" class="te-input" 
                                value="{{ $layoutSettings['fontSize'] ?? 12 }}">
                        </div>
                        <div class="te-field">
                            <label class="te-label">Language</label>
                            <select name="language" id="settingLang" class="te-input" required>
                                <option value="en" {{ old('language', $template->language ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                                <option value="ar" {{ old('language', $template->language ?? '') === 'ar' ? 'selected' : '' }}>العربية</option>
                            </select>
                        </div>
                        <div class="te-field">
                            <label class="te-label">Page Border</label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0; cursor: pointer;">
                                <input type="checkbox" name="layout_settings[border][enabled]" id="borderEnabled"
                                    {{ ($layoutSettings['border']['enabled'] ?? false) ? 'checked' : '' }}
                                    onchange="toggleBorderPanel()">
                                <span>Show border around page</span>
                            </label>
                            
                            <!-- Border Controls Panel -->
                            <div id="borderControlsPanel" style="display: {{ ($layoutSettings['border']['enabled'] ?? false) ? 'block' : 'none' }}; margin-top: 0.75rem; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <div class="te-grid" style="grid-template-columns: repeat(3, 1fr); gap: 0.75rem;">
                                    <div>
                                        <label class="te-label" style="font-size: 0.7rem;">Width (px)</label>
                                        <input type="number" name="layout_settings[border][width]" id="borderWidth" 
                                            class="te-input" value="{{ $layoutSettings['border']['width'] ?? 2 }}" min="1" max="10">
                                    </div>
                                    <div>
                                        <label class="te-label" style="font-size: 0.7rem;">Style</label>
                                        <select name="layout_settings[border][style]" id="borderStyle" class="te-input">
                                            <option value="solid" {{ ($layoutSettings['border']['style'] ?? '') === 'solid' ? 'selected' : '' }}>Solid</option>
                                            <option value="double" {{ ($layoutSettings['border']['style'] ?? '') === 'double' ? 'selected' : '' }}>Double</option>
                                            <option value="dashed" {{ ($layoutSettings['border']['style'] ?? '') === 'dashed' ? 'selected' : '' }}>Dashed</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="te-label" style="font-size: 0.7rem;">Color</label>
                                        <input type="color" name="layout_settings[border][color]" id="borderColor" 
                                            class="te-input" style="height: 38px; padding: 2px;"
                                            value="{{ $layoutSettings['border']['color'] ?? '#000000' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security Settings Section -->
                        <div class="te-field" style="grid-column: span 2; margin-top: 1.5rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                            <h4 style="font-size: 0.9rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem;">
                                <i data-feather="shield" style="width: 16px; height: 16px; vertical-align: text-bottom; margin-right: 5px;"></i>
                                Security Features
                            </h4>
                            
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                                <!-- Watermark -->
                                <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; cursor: pointer;">
                                        <input type="checkbox" name="layout_settings[watermark][enabled]" id="watermarkEnabled"
                                            {{ ($layoutSettings['watermark']['enabled'] ?? false) ? 'checked' : '' }}
                                            onchange="document.getElementById('watermarkTextGroup').style.display = this.checked ? 'block' : 'none'">
                                        <span style="font-weight: 600; font-size: 0.85rem;">Enable Digital Watermark</span>
                                    </label>
                                    <div id="watermarkTextGroup" style="display: {{ ($layoutSettings['watermark']['enabled'] ?? false) ? 'block' : 'none' }}; margin-left: 1.5rem;">
                                        <label class="te-label" style="font-size: 0.7rem;">Watermark Text (Default: Tracking ID)</label>
                                        <input type="text" name="layout_settings[watermark][text]" 
                                            class="te-input" placeholder="e.g. OFFICIAL COPY"
                                            value="{{ $layoutSettings['watermark']['text'] ?? '' }}">
                                        <p style="font-size: 0.7rem; color: #64748b; margin-top: 4px;">Leaves faint background text to prevent editing.</p>
                                    </div>
                                </div>

                                <!-- Verification Elements -->
                                <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; cursor: pointer;">
                                        <input type="hidden" name="layout_settings[qrCode][enabled]" value="0">
                                        <input type="checkbox" name="layout_settings[qrCode][enabled]" id="qrCodeEnabled" value="1"
                                            {{ ($layoutSettings['qrCode']['enabled'] ?? true) ? 'checked' : '' }}>
                                        <span style="font-weight: 600; font-size: 0.85rem;">Show Verification QR Code</span>
                                    </label>
                                    
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="hidden" name="layout_settings[footer][enabled]" value="0">
                                        <input type="checkbox" name="layout_settings[footer][enabled]" id="footerEnabled" value="1"
                                            {{ ($layoutSettings['footer']['enabled'] ?? true) ? 'checked' : '' }}>
                                        <span style="font-weight: 600; font-size: 0.85rem;">Show Digital Footer Strip</span>
                                    </label>
                                    <p style="font-size: 0.7rem; color: #64748b; margin-top: 8px; margin-left: 1.5rem;">Adds a secure footer with direct verification link.</p>
                                </div>
                            </div>
                        </div> 
                                            min="1" max="10">
                                    </div>
                                    <div>
                                        <label class="te-label" style="font-size: 0.7rem;">Style</label>
                                        <select name="layout_settings[border][style]" id="borderStyle" class="te-input">
                                            <option value="solid" {{ ($layoutSettings['border']['style'] ?? 'solid') === 'solid' ? 'selected' : '' }}>Solid</option>
                                            <option value="double" {{ ($layoutSettings['border']['style'] ?? '') === 'double' ? 'selected' : '' }}>Double</option>
                                            <option value="dashed" {{ ($layoutSettings['border']['style'] ?? '') === 'dashed' ? 'selected' : '' }}>Dashed</option>
                                            <option value="dotted" {{ ($layoutSettings['border']['style'] ?? '') === 'dotted' ? 'selected' : '' }}>Dotted</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="te-label" style="font-size: 0.7rem;">Color</label>
                                        <input type="color" name="layout_settings[border][color]" id="borderColor" 
                                            class="te-input" value="{{ $layoutSettings['border']['color'] ?? '#000000' }}" 
                                            style="height: 38px; padding: 2px; cursor: pointer;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="te-field">
                        <label class="te-label">Page Margins (mm)</label>
                        <div class="te-grid" style="grid-template-columns: repeat(4, 1fr);">
                            <input type="number" name="layout_settings[margins][top]" class="te-input" 
                                value="{{ $layoutSettings['margins']['top'] ?? 20 }}" placeholder="Top">
                            <input type="number" name="layout_settings[margins][right]" class="te-input" 
                                value="{{ $layoutSettings['margins']['right'] ?? 20 }}" placeholder="Right">
                            <input type="number" name="layout_settings[margins][bottom]" class="te-input" 
                                value="{{ $layoutSettings['margins']['bottom'] ?? 20 }}" placeholder="Bottom">
                            <input type="number" name="layout_settings[margins][left]" class="te-input" 
                                value="{{ $layoutSettings['margins']['left'] ?? 20 }}" placeholder="Left">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Variable Bar -->
    <div class="te-variable-bar">
        <span class="te-variable-label">Insert Variable:</span>
        <div class="te-variable-chips">
            <span class="te-chip" onclick="insertVar('fullName')">
                <i data-feather="users"></i> Full Name
            </span>
            <span class="te-chip" onclick="insertVar('studentName')">
                <i data-feather="user"></i> First Name
            </span>
            <span class="te-chip" onclick="insertVar('middleName')">
                <i data-feather="user"></i> Middle Name
            </span>
            <span class="te-chip" onclick="insertVar('lastName')">
                <i data-feather="user"></i> Last Name
            </span>
            <span class="te-chip" onclick="insertVar('university')">
                <i data-feather="book"></i> University
            </span>
            <span class="te-chip" onclick="insertVar('trainingPeriod')">
                <i data-feather="calendar"></i> Training Period
            </span>
            <span class="te-chip" onclick="insertVar('purpose')">
                <i data-feather="target"></i> Purpose
            </span>
            <span class="te-chip" onclick="insertVar('date')">
                <i data-feather="calendar"></i> Date
            </span>
            <span class="te-chip" onclick="insertVar('trackingId')">
                <i data-feather="hash"></i> Tracking
            </span>
            <span class="te-chip" onclick="insertVar('qrCode')" style="background: #059669;">
                <i data-feather="maximize"></i> QR Code
            </span>
            <span class="te-chip" onclick="insertVar('signature')" style="background: #7c3aed;">
                <i data-feather="pen-tool"></i> Signature
            </span>
            <span class="te-chip" onclick="insertVar('he')" style="background: #0891b2;">
                <i data-feather="user"></i> He/She
            </span>
            <span class="te-chip" onclick="insertVar('his')" style="background: #0891b2;">
                <i data-feather="user"></i> His/Her
            </span>
        </div>
    </div>
</form>

@if(isset($template))
<form id="resetForm" action="{{ route('admin.templates.reset', $template->id) }}" method="POST" style="display: none;">
    @csrf
</form>
@endif

<!-- Preview Modal -->
<div class="te-modal-overlay" id="previewModal" onclick="closePreview(event)">
    <div class="te-modal" onclick="event.stopPropagation()">
        <div class="te-modal-header">
            <span class="te-modal-title">Template Preview</span>
            <button type="button" class="te-modal-close" onclick="closePreview()">
                <i data-feather="x"></i>
            </button>
        </div>
        <div class="te-modal-body">
            <div class="te-paper">
                <div class="te-paper-content" id="previewContent">
                    <!-- Preview rendered here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/djjpb8qqw9cskwcb9wz6j9y4qdx8fbkno7ccret2axmq61mw/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
<script>
    // State
    let currentEditor = 'headerEditor';
    let hasChanges = false;
    let editors = {};
    
    // Sanitize HTML
    function sanitize(html) {
        return DOMPurify.sanitize(html || '', {
            ALLOWED_TAGS: ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'table', 'tr', 'td', 'th', 'thead', 'tbody', 'img', 'div', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'a', 'font', 'hr'],
            ALLOWED_ATTR: ['style', 'class', 'src', 'alt', 'href', 'title', 'target', 'dir', 'align', 'width', 'height', 'colspan', 'rowspan', 'border', 'cellpadding', 'cellspacing']
        });
    }
    
    // Initialize TinyMCE
    function initTinyMCE() {
        tinymce.init({
            selector: '#headerEditor, #bodyEditor, #footerEditor',
            height: 300,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount', 'directionality'
            ],
            toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | ltr rtl | bullist numlist outdent indent | table link image | code',
            content_style: 'body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.6; }',
            directionality: 'ltr',
            promotion: false,
            branding: false,
            // STOP REMOVING MY STYLES
            verify_html: false,
            valid_elements: '*[*]',
            valid_children: '+body[style]',
            extended_valid_elements: 'div[*],span[*],table[*],tr[*],td[*],th[*],img[*]',
            setup: function(editor) {
                editors[editor.id] = editor;
                
                editor.on('change keyup', function() {
                    editor.save();
                    hasChanges = true;
                    document.getElementById('autoSaveStatus').textContent = 'Unsaved changes';
                    updatePreview();
                });
                
                editor.on('focus', function() {
                    currentEditor = editor.id;
                });
            }
        });
    }
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();
        
        // Initialize TinyMCE
        initTinyMCE();
        
        // Tab switching
        document.querySelectorAll('.te-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabName = this.dataset.tab;
                
                // Update tabs
                document.querySelectorAll('.te-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Update panes
                document.querySelectorAll('.te-pane').forEach(p => p.classList.remove('active'));
                document.getElementById('pane-' + tabName).classList.add('active');
                
                // Track current editor
                if (tabName === 'header') currentEditor = 'headerEditor';
                else if (tabName === 'body') currentEditor = 'bodyEditor';
                else if (tabName === 'footer') currentEditor = 'footerEditor';
            });
        });
        
        // Listen for non-TinyMCE input changes
        document.querySelectorAll('input:not(.tox-textfield), select').forEach(el => {
            el.addEventListener('input', function() {
                hasChanges = true;
                document.getElementById('autoSaveStatus').textContent = 'Unsaved changes';
                updatePreview();
            });
        });
        
        // Logo upload
        document.getElementById('logoUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('logoBase64').value = e.target.result;
                    document.getElementById('logoStatus').textContent = '✓ Logo uploaded';
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Initial preview (delayed to wait for TinyMCE)
        setTimeout(updatePreview, 1000);
        
        // Warn before leaving
        window.addEventListener('beforeunload', function(e) {
            if (hasChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        
        // Form submit - sync TinyMCE content first
        document.getElementById('templateForm').addEventListener('submit', function() {
            tinymce.triggerSave();
            hasChanges = false;
        });
    });
    
    // Insert variable into current editor
    function insertVar(name) {
        // Build the tag using character codes to avoid Blade escaping issues
        const openBrace = String.fromCharCode(123, 123);
        const closeBrace = String.fromCharCode(125, 125);
        const tag = openBrace + name + closeBrace;
        const editor = editors[currentEditor];
        
        if (editor) {
            // Insert as plain text to avoid HTML encoding issues
            editor.execCommand('mceInsertContent', false, tag);
            hasChanges = true;
            document.getElementById('autoSaveStatus').textContent = 'Unsaved changes';
            updatePreview();
        } else {
            // Fallback for non-TinyMCE fields
            const textarea = document.getElementById(currentEditor);
            if (textarea) {
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const text = textarea.value;
                textarea.value = text.substring(0, start) + tag + text.substring(end);
                textarea.selectionStart = textarea.selectionEnd = start + tag.length;
                textarea.focus();
                hasChanges = true;
                updatePreview();
            }
        }
    }
    
    // Generate header
    function generateHeader() {
        const logo = document.getElementById('logoBase64').value;
        const left = document.getElementById('builderLeft').value.replace(/\n/g, '<br>');
        const right = document.getElementById('builderRight').value.replace(/\n/g, '<br>');
        
        let html = `<table style="width:100%; border:none; border-collapse:collapse;">
            <tr>
                <td style="width:33%; text-align:left; vertical-align:top; font-size:10pt;">${left}</td>
                <td style="width:34%; text-align:center; vertical-align:middle;">`;
        
        if (logo) {
            html += `<img src="${logo}" style="max-height:80px; max-width:120px;">`;
        }
        
        html += `</td>
                <td style="width:33%; text-align:right; vertical-align:top; font-size:10pt;" dir="rtl">${right}</td>
            </tr>
        </table>
        <hr style="border:none; border-top:2px solid #333; margin:10px 0;">`;
        
        // Set content in TinyMCE
        if (editors['headerEditor']) {
            editors['headerEditor'].setContent(html);
        } else {
            document.getElementById('headerEditor').value = html;
        }
        hasChanges = true;
        updatePreview();
    }
    
    // Update preview
    function updatePreview() {
        let header = '', body = '', footer = '';
        
        // Get content from TinyMCE or textarea
        if (editors['headerEditor']) {
            header = editors['headerEditor'].getContent();
        } else {
            header = document.getElementById('headerEditor')?.value || '';
        }
        
        if (editors['bodyEditor']) {
            body = editors['bodyEditor'].getContent();
        } else {
            body = document.getElementById('bodyEditor')?.value || '';
        }
        
        if (editors['footerEditor']) {
            footer = editors['footerEditor'].getContent();
        } else {
            footer = document.getElementById('footerEditor')?.value || '';
        }
        
        header = sanitize(header);
        body = sanitize(body);
        footer = sanitize(footer);
        
        const sigName = document.getElementById('sigName')?.value || '';
        const sigTitle = document.getElementById('sigTitle')?.value || '';
        const sigDept = document.getElementById('sigDept')?.value || '';
        const sigInst = document.getElementById('sigInst')?.value || '';
        const sigImage = document.getElementById('sigImage')?.value || '';
        
        const preview = document.getElementById('previewContent');
        if (!preview) return;
        
        preview.innerHTML = `
            <div style="margin-bottom: 20px;">${header}</div>
            <div style="min-height: 400px; line-height: 1.6;">${body}</div>
            <div style="margin-top: 40px;">
                <p style="margin-bottom: 5px;"><strong>${sigName}</strong></p>
                <p style="margin: 2px 0; color: #666;">${sigTitle}</p>
                <p style="margin: 2px 0; color: #666;">${sigDept}</p>
                <p style="margin: 2px 0; color: #666;">${sigInst}</p>
                ${sigImage ? `<img src="${sigImage}" style="height: 50px; margin-top: 10px;">` : ''}
            </div>
            <div style="position: absolute; bottom: 20mm; left: 20mm; right: 20mm; text-align: center; font-size: 9pt; color: #666;">${footer}</div>
        `;
        
        // Apply border to paper
        const paper = preview.closest('.te-paper');
        if (paper) {
            const borderEnabled = document.getElementById('borderEnabled')?.checked;
            if (borderEnabled) {
                const width = document.getElementById('borderWidth')?.value || 2;
                const style = document.getElementById('borderStyle')?.value || 'solid';
                const color = document.getElementById('borderColor')?.value || '#000000';
                paper.style.border = `${width}px ${style} ${color}`;
            } else {
                paper.style.border = 'none';
            }
        }
    }
    
    // Toggle border controls panel
    function toggleBorderPanel() {
        const panel = document.getElementById('borderControlsPanel');
        const enabled = document.getElementById('borderEnabled')?.checked;
        if (panel) {
            panel.style.display = enabled ? 'block' : 'none';
        }
    }
    
    // Open preview modal
    function openPreview() {
        updatePreview();
        document.getElementById('previewModal').classList.add('active');
        document.body.style.overflow = 'hidden';
        feather.replace();
    }
    
    // Close preview modal
    function closePreview(event) {
        if (event && event.target !== event.currentTarget) return;
        document.getElementById('previewModal').classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePreview();
        }
    });
    
    // Listen for border setting changes
    ['borderEnabled', 'borderWidth', 'borderStyle', 'borderColor'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function() {
                hasChanges = true;
                document.getElementById('autoSaveStatus').textContent = 'Unsaved changes';
            });
        }
    });


    function confirmReset() {
        if (confirm('Are you sure you want to reset this template to the stable default?\n\nThis will overwrite your current changes with the robust table-based layout.')) {
            document.getElementById('resetForm').submit();
        }
    }
</script>
@endsection