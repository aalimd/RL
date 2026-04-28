@extends('layouts.admin')

@section('page-title', isset($template) ? 'Edit Template' : 'Create Template')

@section('styles')
<style>
    /* ========== PROFESSIONAL TEMPLATE EDITOR ========== */
    
    /* Reset for this page */
    .template-editor-page {
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 60px);
        background: #f8fafc;
        overflow: visible;
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
        flex: 1 1 auto;
        min-height: 26rem;
        display: flex;
        flex-direction: column;
        overflow: visible;
    }
    
    /* Editor Panel */
    .te-editor-panel {
        flex: 1 1 auto;
        min-height: 100%;
        display: flex;
        flex-direction: column;
        background: white;
        overflow: visible;
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
        flex: 1 1 auto;
        min-height: 24rem;
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
    .te-variable-toolbar {
        background: white;
        border-top: 1px solid #e2e8f0;
        flex-shrink: 0;
    }

    .te-variable-bar {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding: 0.9rem 1.5rem 0.8rem;
        background: white;
    }

    .te-variable-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.6rem 0.9rem;
    }

    .te-variable-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
    }

    .te-variable-target {
        display: inline-flex;
        align-items: center;
        padding: 0.28rem 0.65rem;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .te-variable-target.is-empty {
        background: #f1f5f9;
        color: #64748b;
    }

    .te-variable-target.is-error {
        background: #fef2f2;
        color: #b91c1c;
    }

    .te-variable-help {
        margin: 0;
        font-size: 0.74rem;
        color: #64748b;
        line-height: 1.45;
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
        appearance: none;
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

    .te-chip[data-advanced="true"] {
        color: white;
        border-color: transparent;
    }

    .te-variable-manager {
        padding: 0.85rem 1.5rem 1rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        max-height: 18rem;
        overflow-y: auto;
    }

    .te-variable-manager-title {
        margin: 0 0 0.3rem 0;
        font-size: 0.8rem;
        font-weight: 700;
        color: #1e293b;
    }

    .te-variable-manager-copy {
        margin: 0 0 0.9rem 0;
        font-size: 0.74rem;
        color: #64748b;
        line-height: 1.55;
    }

    .te-variable-manager-empty {
        font-size: 0.76rem;
        color: #64748b;
        line-height: 1.5;
    }

    .te-variable-source {
        padding: 0.85rem 0.95rem;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: white;
        margin-bottom: 0.75rem;
    }

    .te-variable-source:last-child {
        margin-bottom: 0;
    }

    .te-variable-source-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.75rem;
    }

    .te-variable-source-link {
        background: none;
        border: none;
        padding: 0;
        font-size: 0.78rem;
        font-weight: 700;
        color: #4338ca;
        cursor: pointer;
        text-align: left;
    }

    .te-variable-source-link:hover {
        text-decoration: underline;
    }

    .te-variable-source-count {
        font-size: 0.72rem;
        color: #64748b;
        white-space: nowrap;
    }

    .te-variable-token-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem;
        padding-top: 0.6rem;
        margin-top: 0.6rem;
        border-top: 1px solid #f1f5f9;
    }

    .te-variable-token-row:first-of-type {
        margin-top: 0;
        padding-top: 0;
        border-top: none;
    }

    .te-variable-token-label {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        font-size: 0.75rem;
        color: #0f172a;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        padding: 0.32rem 0.65rem;
    }

    .te-variable-token-count {
        font-family: inherit;
        color: #475569;
        font-weight: 700;
    }

    .te-variable-actions {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .te-mini-btn {
        appearance: none;
        border: 1px solid #cbd5e1;
        background: white;
        color: #334155;
        border-radius: 999px;
        padding: 0.3rem 0.7rem;
        font-size: 0.72rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.18s ease;
    }

    .te-mini-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }

    .te-mini-btn.warning {
        border-color: #fbbf24;
        color: #92400e;
        background: #fffbeb;
    }

    .te-mini-btn.danger {
        border-color: #fecaca;
        color: #b91c1c;
        background: #fef2f2;
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
    

    /* ========== DARK MODE OVERRIDES ========== */
    body.dark-mode .template-editor-page { background: var(--bg-color); }
    body.dark-mode .te-header { background: var(--card-bg); border-color: var(--border-color); }
    body.dark-mode .te-back-btn { background: var(--bg-color); color: var(--text-muted); }
    body.dark-mode .te-back-btn:hover { background: var(--border-color); color: var(--text-main); }
    body.dark-mode .te-title-input { color: var(--text-main); }
    body.dark-mode .te-title-input:hover, body.dark-mode .te-title-input:focus { border-color: var(--border-color); background: var(--card-bg); }
    body.dark-mode .te-status { color: var(--text-muted); }
    body.dark-mode .te-toggle { background: var(--bg-color); border-color: var(--border-color); color: var(--text-main); }
    body.dark-mode .te-tabs { background: var(--card-bg); border-color: var(--border-color); }
    body.dark-mode .te-tab { color: var(--text-muted); }
    body.dark-mode .te-tab:hover { color: var(--text-main); }
    body.dark-mode .te-tab.active { color: var(--primary); }
    body.dark-mode .te-tab.active::after { background: var(--primary); }
    body.dark-mode .te-editor-panel { background: var(--card-bg); border-color: var(--border-color); }
    body.dark-mode .te-panel-header { background: var(--bg-color); border-color: var(--border-color); }
    body.dark-mode .te-panel-title { color: var(--text-muted); }
    body.dark-mode .te-label { color: var(--text-main); }
    body.dark-mode .te-input, body.dark-mode .te-textarea { background: var(--bg-color); border-color: var(--border-color); color: var(--text-main); color-scheme: dark; }
    body.dark-mode .te-input:focus, body.dark-mode .te-textarea:focus { border-color: var(--primary); }
    body.dark-mode .te-builder-card { background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.3); }
    body.dark-mode .te-builder-title { color: var(--success-text); }
    body.dark-mode .te-upload-zone { background: var(--bg-color); border-color: rgba(34, 197, 94, 0.3); }
    body.dark-mode .te-upload-zone:hover { border-color: var(--success-text); background: rgba(34, 197, 94, 0.1); }
    body.dark-mode .te-upload-zone p { color: var(--success-text); }
    body.dark-mode .te-builder-input { background: var(--bg-color); border-color: rgba(34, 197, 94, 0.3); color: var(--text-main); }
    body.dark-mode .te-builder-input:focus { border-color: var(--success-text); }
    body.dark-mode .te-preview-panel { background: var(--bg-color); }
    body.dark-mode .te-modal { background: var(--card-bg); }
    body.dark-mode .te-modal-header { background: var(--bg-color); border-color: var(--border-color); }
    body.dark-mode .te-modal-title { color: var(--text-main); }
    body.dark-mode .te-modal-close { background: var(--border-color); color: var(--text-muted); }
    body.dark-mode .te-modal-close:hover { background: var(--text-muted); color: var(--bg-color); }
    body.dark-mode .te-variable-toolbar { background: var(--card-bg); border-color: var(--border-color); }
    body.dark-mode .te-variable-bar { background: var(--card-bg); border-color: var(--border-color); }
    body.dark-mode .te-variable-label { color: var(--text-muted); }
    body.dark-mode .te-variable-target.is-empty { background: var(--bg-color); color: var(--text-muted); }
    body.dark-mode .te-variable-target.is-error { background: rgba(239, 68, 68, 0.18); color: #fecaca; }
    body.dark-mode .te-variable-help { color: var(--text-muted); }
    body.dark-mode .te-variable-manager { background: var(--bg-color); border-color: var(--border-color); }
    body.dark-mode .te-variable-manager-title { color: var(--text-main); }
    body.dark-mode .te-variable-manager-copy,
    body.dark-mode .te-variable-manager-empty,
    body.dark-mode .te-variable-source-count { color: var(--text-muted); }
    body.dark-mode .te-variable-source { background: var(--card-bg); border-color: var(--border-color); }
    body.dark-mode .te-variable-token-row { border-color: rgba(255, 255, 255, 0.06); }
    body.dark-mode .te-variable-token-label { background: var(--bg-color); border-color: var(--border-color); color: var(--text-main); }
    body.dark-mode .te-mini-btn { background: var(--card-bg); border-color: var(--border-color); color: var(--text-main); }
    body.dark-mode .te-mini-btn.warning { background: rgba(251, 191, 36, 0.12); color: #fcd34d; border-color: rgba(251, 191, 36, 0.35); }
    body.dark-mode .te-mini-btn.danger { background: rgba(239, 68, 68, 0.12); color: #fecaca; border-color: rgba(239, 68, 68, 0.35); }
    body.dark-mode .tox-tinymce { border-color: var(--border-color) !important; }
    body.dark-mode .tox .tox-toolbar__primary { background: var(--bg-color) !important; }
    body.dark-mode .te-error-alert { background: var(--error-bg); border-color: var(--error-text); color: var(--error-text); }
</style>
@endsection

@section('content')
@php
    $layoutSettings = [];
    if (isset($template) && $template->layout_settings) {
        $layoutSettings = is_array($template->layout_settings) ? $template->layout_settings : json_decode($template->layout_settings, true) ?? [];
    }
    $draftLoaded = $draftLoaded ?? false;
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
            <div class="te-status" id="autoSaveStatus">{{ $draftLoaded ? 'Unsaved draft restored' : '' }}</div>
            
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
            @if(auth()->user()?->role === 'admin')
            <button type="button" onclick="confirmSaveResetDefault()" style="margin-right: 0.5rem; background: #0f766e; color: white; border: none; padding: 0.625rem 1.25rem; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#115e59'" onmouseout="this.style.backgroundColor='#0f766e'">
                <i data-feather="bookmark"></i>
                Save as Default
            </button>
            @endif
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
                                <input type="hidden" name="layout_settings[border][enabled]" value="0">
                                <input type="checkbox" name="layout_settings[border][enabled]" id="borderEnabled"
                                    value="1"
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

                        <div class="te-field">
                            <label class="te-label">Official Frame</label>
                            <select name="layout_settings[frame][style]" id="frameStyle" class="te-input">
                                <option value="" {{ ($layoutSettings['frame']['style'] ?? '') === '' ? 'selected' : '' }}>None</option>
                                <option value="ngha_green" {{ ($layoutSettings['frame']['style'] ?? '') === 'ngha_green' ? 'selected' : '' }}>NGHA green frame</option>
                            </select>
                            <div style="margin-top: 0.75rem; display: grid; grid-template-columns: 1fr auto; gap: 0.75rem; align-items: end;">
                                <div>
                                    <label class="te-label" style="font-size: 0.7rem;">Frame Color</label>
                                    <input type="color" name="layout_settings[frame][color]" id="frameColor"
                                        class="te-input" style="height: 38px; padding: 2px;"
                                        value="{{ $layoutSettings['frame']['color'] ?? '#2f8e55' }}">
                                </div>
                                <div style="font-size: 0.72rem; color: #64748b; line-height: 1.35; padding-bottom: 0.15rem;">
                                    Matches official letterhead layouts.
                                </div>
                            </div>
                            <input type="hidden" name="layout_settings[frame][topInset]" value="{{ $layoutSettings['frame']['topInset'] ?? 10 }}">
                            <input type="hidden" name="layout_settings[frame][sideInset]" value="{{ $layoutSettings['frame']['sideInset'] ?? 10 }}">
                            <input type="hidden" name="layout_settings[frame][bottomInset]" value="{{ ($layoutSettings['frame']['bottomInset'] ?? 8) > 18 ? 8 : ($layoutSettings['frame']['bottomInset'] ?? 8) }}">
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
                                        <input type="hidden" name="layout_settings[watermark][enabled]" value="0">
                                        <input type="checkbox" name="layout_settings[watermark][enabled]" id="watermarkEnabled"
                                            value="1"
                                            {{ ($layoutSettings['watermark']['enabled'] ?? false) ? 'checked' : '' }}
                                            onchange="document.getElementById('watermarkTextGroup').style.display = this.checked ? 'block' : 'none'">
                                        <span style="font-weight: 600; font-size: 0.85rem;">Enable Digital Watermark</span>
                                    </label>
                                    <div id="watermarkTextGroup" style="display: {{ ($layoutSettings['watermark']['enabled'] ?? false) ? 'block' : 'none' }}; margin-left: 1.5rem;">
                                        <label class="te-label" style="font-size: 0.7rem;">Watermark Text (Default: Tracking ID)</label>
                                        <input type="text" name="layout_settings[watermark][text]" id="watermarkText"
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
    <div class="te-variable-toolbar">
        <div class="te-variable-bar">
            <div class="te-variable-meta">
                <span class="te-variable-label">Insert Variable:</span>
                <span class="te-variable-target is-empty" id="insertTargetStatus">No field selected</span>
                <p class="te-variable-help" id="insertTargetHelp">
                    Click inside Header, Body, Footer, or a signature field first. Inline `Signature` and `QR Code`
                    variables only work in Header, Body, or Footer content.
                </p>
            </div>
            <div class="te-variable-chips">
                <button type="button" class="te-chip" onclick="insertVar('fullName')">
                    <i data-feather="users"></i> Full Name
                </button>
                <button type="button" class="te-chip" onclick="insertVar('studentName')">
                    <i data-feather="user"></i> First Name
                </button>
                <button type="button" class="te-chip" onclick="insertVar('middleName')">
                    <i data-feather="user"></i> Middle Name
                </button>
                <button type="button" class="te-chip" onclick="insertVar('lastName')">
                    <i data-feather="user"></i> Last Name
                </button>
                <button type="button" class="te-chip" onclick="insertVar('university')">
                    <i data-feather="book"></i> University
                </button>
                <button type="button" class="te-chip" onclick="insertVar('trainingPeriod')">
                    <i data-feather="calendar"></i> Training Period
                </button>
                <button type="button" class="te-chip" onclick="insertVar('purpose')">
                    <i data-feather="target"></i> Purpose
                </button>
                <button type="button" class="te-chip" onclick="insertVar('date')">
                    <i data-feather="calendar"></i> Date
                </button>
                <button type="button" class="te-chip" onclick="insertVar('trackingId')">
                    <i data-feather="hash"></i> Tracking
                </button>
                <button type="button" class="te-chip" data-advanced="true" onclick="insertVar('qrCode')" style="background: #059669;">
                    <i data-feather="maximize"></i> QR Code
                </button>
                <button type="button" class="te-chip" data-advanced="true" onclick="insertVar('signature')" style="background: #7c3aed;">
                    <i data-feather="pen-tool"></i> Signature
                </button>
                <button type="button" class="te-chip" data-advanced="true" onclick="insertVar('he')" style="background: #0891b2;">
                    <i data-feather="user"></i> He/She
                </button>
                <button type="button" class="te-chip" data-advanced="true" onclick="insertVar('his')" style="background: #0891b2;">
                    <i data-feather="user"></i> His/Her
                </button>
            </div>
        </div>
        <div class="te-variable-manager" id="variableManager"></div>
    </div>
</form>

@if(isset($template))
<form id="resetForm" action="{{ route('admin.templates.reset', $template->id) }}" method="POST" style="display: none;">
    @csrf
</form>
<form id="resetDefaultForm" action="{{ route('admin.templates.reset-default', $template->id) }}" method="POST" style="display: none;">
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
    let currentTab = 'header';
    let currentInsertTarget = null;
    let hasChanges = false;
    let hasUnsyncedDraft = false;
    let autoSaveInFlight = false;
    const autoSaveIntervalMs = 30000;
    let editors = {};
    const autosaveUrl = @json(isset($template) ? route('admin.templates.autosave', $template->id) : null);
    const draftRestored = @json($draftLoaded);
    const singletonVariableNames = ['qrCode', 'signature'];
    const editorOnlyVariables = new Set(singletonVariableNames);
    const variableRegex = /\{\{[A-Za-z][A-Za-z0-9]*\}\}/g;
    const insertTargets = {
        headerEditor: { id: 'headerEditor', type: 'editor', tab: 'header', label: 'Header HTML', acceptsInsert: true },
        bodyEditor: { id: 'bodyEditor', type: 'editor', tab: 'body', label: 'Body Content', acceptsInsert: true },
        footerEditor: { id: 'footerEditor', type: 'editor', tab: 'footer', label: 'Footer Content', acceptsInsert: true },
        sigName: { id: 'sigName', type: 'input', tab: 'signature', label: 'Signature Full Name', acceptsInsert: true },
        sigTitle: { id: 'sigTitle', type: 'input', tab: 'signature', label: 'Signature Job Title', acceptsInsert: true },
        sigDept: { id: 'sigDept', type: 'input', tab: 'signature', label: 'Signature Department', acceptsInsert: true },
        sigInst: { id: 'sigInst', type: 'input', tab: 'signature', label: 'Signature Institution', acceptsInsert: true },
        sigEmail: { id: 'sigEmail', type: 'input', tab: 'signature', label: 'Signature Email', acceptsInsert: true },
        sigPhone: { id: 'sigPhone', type: 'input', tab: 'signature', label: 'Signature Phone', acceptsInsert: true },
        sigImage: { id: 'sigImage', type: 'input', tab: 'signature', label: 'Signature Image URL', acceptsInsert: false },
        stampImage: { id: 'stampImage', type: 'input', tab: 'signature', label: 'Stamp Image URL', acceptsInsert: false },
    };

    function setAutoSaveStatus(message, isError = false) {
        const statusEl = document.getElementById('autoSaveStatus');
        if (!statusEl) return;
        statusEl.textContent = message;
        statusEl.style.color = isError ? '#b91c1c' : '';
    }

    function markDirty() {
        hasChanges = true;
        hasUnsyncedDraft = true;
        setAutoSaveStatus('Unsaved changes');
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function escapeRegExp(value) {
        return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function buildVariableTag(name) {
        const openBrace = String.fromCharCode(123, 123);
        const closeBrace = String.fromCharCode(125, 125);
        return openBrace + name + closeBrace;
    }

    function getTargetConfig(targetId) {
        return insertTargets[targetId] || null;
    }

    function setVariableHelp(message, isError = false) {
        const helpEl = document.getElementById('insertTargetHelp');
        if (!helpEl) return;
        helpEl.textContent = message;
        helpEl.style.color = isError ? '#b91c1c' : '#64748b';
    }

    function setCurrentInsertTarget(targetId = null, options = {}) {
        const targetEl = document.getElementById('insertTargetStatus');
        const config = targetId ? getTargetConfig(targetId) : null;
        currentInsertTarget = config ? config.id : null;

        if (!targetEl) {
            return;
        }

        targetEl.classList.remove('is-empty', 'is-error');

        if (!config) {
            targetEl.textContent = 'No field selected';
            targetEl.classList.add(options.isError ? 'is-error' : 'is-empty');
            return;
        }

        targetEl.textContent = `Target: ${config.label}`;
        if (options.isError) {
            targetEl.classList.add('is-error');
        }
    }

    function setInsertTarget(targetId = null, options = {}) {
        const config = targetId ? getTargetConfig(targetId) : null;
        const helpMessage = options.helpMessage;
        const errorState = Boolean(options.isError);

        setCurrentInsertTarget(targetId, { isError: errorState });

        if (typeof helpMessage === 'string') {
            setVariableHelp(helpMessage, errorState);
            return config;
        }

        if (!config) {
            setVariableHelp('Click inside Header, Body, Footer, or a signature text field first. Inline Signature and QR Code variables only work inside rich content editors.');
            return null;
        }

        if (!config.acceptsInsert) {
            setVariableHelp(`${config.label} is a direct URL field. Variables cannot be inserted there.`, true);
            return config;
        }

        setVariableHelp(`Variables will insert into ${config.label}.`);
        return config;
    }

    function getFieldContent(targetId) {
        const config = getTargetConfig(targetId);
        if (!config) {
            return '';
        }

        if (config.type === 'editor') {
            if (editors[targetId]) {
                return editors[targetId].getContent() || '';
            }

            return document.getElementById(targetId)?.value || '';
        }

        return document.getElementById(targetId)?.value || '';
    }

    function setFieldContent(targetId, value) {
        const config = getTargetConfig(targetId);
        if (!config) {
            return;
        }

        if (config.type === 'editor') {
            if (editors[targetId]) {
                editors[targetId].setContent(value);
            } else {
                const textarea = document.getElementById(targetId);
                if (textarea) {
                    textarea.value = value;
                }
            }

            return;
        }

        const input = document.getElementById(targetId);
        if (input) {
            input.value = value;
        }
    }

    function insertIntoPlainField(field, text) {
        const start = field.selectionStart ?? field.value.length;
        const end = field.selectionEnd ?? field.value.length;
        const value = field.value || '';

        field.value = value.substring(0, start) + text + value.substring(end);
        field.selectionStart = field.selectionEnd = start + text.length;
        field.focus();
    }

    function activateTab(tabName) {
        currentTab = tabName;

        document.querySelectorAll('.te-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.tab === tabName);
        });

        document.querySelectorAll('.te-pane').forEach(pane => {
            pane.classList.toggle('active', pane.id === 'pane-' + tabName);
        });

        if (tabName === 'header') {
            currentEditor = 'headerEditor';
            setInsertTarget('headerEditor', {
                helpMessage: 'Header variables will insert into Header HTML unless you click a different field.',
            });
        } else if (tabName === 'body') {
            currentEditor = 'bodyEditor';
            setInsertTarget('bodyEditor', {
                helpMessage: 'Body variables will insert into Body Content unless you click a different field.',
            });
        } else if (tabName === 'footer') {
            currentEditor = 'footerEditor';
            setInsertTarget('footerEditor', {
                helpMessage: 'Footer variables will insert into Footer Content unless you click a different field.',
            });
        } else if (tabName === 'signature') {
            setInsertTarget(null, {
                helpMessage: 'Click inside a signature text field before inserting a variable. Image URL fields do not accept variables.',
            });
        } else {
            setInsertTarget(null, {
                helpMessage: 'Variable insertion is available in Header, Body, Footer, and signature text fields.',
            });
        }
    }

    function focusTemplateField(targetId) {
        const config = getTargetConfig(targetId);
        if (!config) {
            return;
        }

        activateTab(config.tab);

        if (config.type === 'editor') {
            const editor = editors[targetId];
            if (editor) {
                editor.focus();
                currentEditor = targetId;
                setInsertTarget(targetId);
            } else {
                const textarea = document.getElementById(targetId);
                if (textarea) {
                    textarea.focus();
                    currentEditor = targetId;
                    setInsertTarget(targetId);
                }
            }
            return;
        }

        const input = document.getElementById(targetId);
        if (input) {
            input.focus();
            input.select?.();
            setInsertTarget(targetId, { isError: !config.acceptsInsert });
        }
    }

    function getTemplateSources() {
        return Object.values(insertTargets);
    }

    function refreshVariableManager() {
        const container = document.getElementById('variableManager');
        if (!container) return;

        const sources = getTemplateSources().map((source) => {
            const matches = getFieldContent(source.id).match(variableRegex) || [];
            if (!matches.length) {
                return null;
            }

            const grouped = matches.reduce((carry, token) => {
                carry[token] = (carry[token] || 0) + 1;
                return carry;
            }, {});

            return {
                ...source,
                totalCount: matches.length,
                tokens: Object.entries(grouped).map(([token, count]) => ({
                    token,
                    count,
                    encoded: encodeURIComponent(token),
                })),
            };
        }).filter(Boolean);

        if (!sources.length) {
            container.innerHTML = `
                <div class="te-variable-manager-title">Variables currently used</div>
                <p class="te-variable-manager-empty">
                    No placeholders are stored right now. Click a field first, then use the chips above to insert a variable intentionally.
                </p>
            `;
            return;
        }

        container.innerHTML = `
            <div class="te-variable-manager-title">Variables currently used</div>
            <p class="te-variable-manager-copy">
                Use this panel to locate placeholders, keep only one copy of a duplicated variable, or remove it completely from any field.
            </p>
            ${sources.map((source) => `
                <div class="te-variable-source">
                    <div class="te-variable-source-header">
                        <button type="button" class="te-variable-source-link" onclick="focusTemplateField('${source.id}')">${escapeHtml(source.label)}</button>
                        <span class="te-variable-source-count">${source.totalCount} variable${source.totalCount === 1 ? '' : 's'}</span>
                    </div>
                    ${source.tokens.map((item) => `
                        <div class="te-variable-token-row">
                            <span class="te-variable-token-label">
                                ${escapeHtml(item.token)}
                                ${item.count > 1 ? `<span class="te-variable-token-count">x ${item.count}</span>` : ''}
                            </span>
                            <div class="te-variable-actions">
                                <button type="button" class="te-mini-btn" onclick="focusTemplateField('${source.id}')">Focus</button>
                                ${item.count > 1 ? `<button type="button" class="te-mini-btn warning" onclick="keepSingleVariableOccurrence('${source.id}', '${item.encoded}')">Keep 1</button>` : ''}
                                <button type="button" class="te-mini-btn danger" onclick="removeVariableOccurrences('${source.id}', '${item.encoded}')">Remove</button>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `).join('')}
        `;
    }

    function keepSingleVariableOccurrence(targetId, encodedToken) {
        const token = decodeURIComponent(encodedToken);
        const pattern = new RegExp(escapeRegExp(token), 'g');
        let seen = false;
        const original = getFieldContent(targetId);
        const updated = original.replace(pattern, () => {
            if (!seen) {
                seen = true;
                return token;
            }

            return '';
        });

        setFieldContent(targetId, updated);
        markDirty();
        setCurrentInsertTarget(targetId);
        setVariableHelp(`Kept one ${token} in ${getTargetConfig(targetId)?.label || 'the selected field'}.`);
        updatePreview();
    }

    function removeVariableOccurrences(targetId, encodedToken) {
        const token = decodeURIComponent(encodedToken);
        const pattern = new RegExp(escapeRegExp(token), 'g');
        const original = getFieldContent(targetId);
        const updated = original.replace(pattern, '');

        setFieldContent(targetId, updated);
        markDirty();
        setCurrentInsertTarget(targetId);
        setVariableHelp(`Removed ${token} from ${getTargetConfig(targetId)?.label || 'the selected field'}.`);
        updatePreview();
    }

    function normalizePreviewSingletons(blocks) {
        const seen = new Set();

        return blocks.map((block) => {
            let normalized = block || '';

            singletonVariableNames.forEach((name) => {
                const token = buildVariableTag(name);
                const pattern = new RegExp(escapeRegExp(token), 'g');

                normalized = normalized.replace(pattern, () => {
                    if (seen.has(name)) {
                        return '';
                    }

                    seen.add(name);
                    return token;
                });
            });

            return normalized;
        });
    }

    function getFieldValue(id) {
        return document.getElementById(id)?.value?.trim() || '';
    }

    function renderPreviewQrBlock() {
        return `
            <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 4px;">
                <div style="width: 70px; height: 70px; border: 2px solid #111827; background:
                    linear-gradient(90deg, #111827 8%, transparent 8%, transparent 16%, #111827 16%, #111827 24%, transparent 24%, transparent 32%, #111827 32%, #111827 40%, transparent 40%, transparent 48%, #111827 48%, #111827 56%, transparent 56%, transparent 64%, #111827 64%, #111827 72%, transparent 72%, transparent 80%, #111827 80%),
                    linear-gradient(#111827 8%, transparent 8%, transparent 16%, #111827 16%, #111827 24%, transparent 24%, transparent 32%, #111827 32%, #111827 40%, transparent 40%, transparent 48%, #111827 48%, #111827 56%, transparent 56%, transparent 64%, #111827 64%, #111827 72%, transparent 72%, transparent 80%, #111827 80%);
                    background-size: 100% 100%; background-color: #fff;"></div>
                <div style="font-size: 7pt; color: #6b7280;">Scan to Verify</div>
            </div>
        `;
    }

    function applyPreviewVariables(html, dynamicSamples) {
        let resolved = html || '';
        Object.entries(dynamicSamples).forEach(([name, value]) => {
            const placeholder = `${String.fromCharCode(123, 123)}${name}${String.fromCharCode(125, 125)}`;
            resolved = resolved.split(placeholder).join(value);
        });

        return resolved;
    }
    
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
                    markDirty();
                    updatePreview();
                });
                
                editor.on('focus', function() {
                    currentEditor = editor.id;
                    setInsertTarget(editor.id);
                });
            }
        });
    }
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();

        if (draftRestored) {
            setAutoSaveStatus('Unsaved draft restored');
        } else if (autosaveUrl) {
            setAutoSaveStatus('Auto-save enabled');
        } else {
            setAutoSaveStatus('Save template to enable auto-save');
        }
        
        // Initialize TinyMCE
        initTinyMCE();
        
        // Tab switching
        document.querySelectorAll('.te-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                activateTab(this.dataset.tab);
            });
        });

        Object.values(insertTargets).forEach((target) => {
            if (target.type !== 'input') {
                return;
            }

            const field = document.getElementById(target.id);
            if (!field) {
                return;
            }

            ['focus', 'click'].forEach((eventName) => {
                field.addEventListener(eventName, function() {
                    setInsertTarget(target.id, { isError: !target.acceptsInsert });
                });
            });
        });
        
        // Listen for non-TinyMCE input changes
        document.querySelectorAll('input:not(.tox-textfield), select, textarea').forEach(el => {
            ['input', 'change'].forEach(eventName => {
                el.addEventListener(eventName, function() {
                    markDirty();
                    updatePreview();
                });
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
                    markDirty();
                    updatePreview();
                };
                reader.readAsDataURL(file);
            }
        });
        
        activateTab(currentTab);
        refreshVariableManager();

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
            hasUnsyncedDraft = false;
        });

        if (autosaveUrl) {
            setInterval(function() {
                if (hasUnsyncedDraft && !autoSaveInFlight) {
                    saveDraft();
                }
            }, autoSaveIntervalMs);
        }
    });
    
    // Insert variable into current editor
    function insertVar(name) {
        const tag = buildVariableTag(name);
        const config = currentInsertTarget ? getTargetConfig(currentInsertTarget) : null;

        if (!config) {
            setInsertTarget(null, {
                isError: true,
                helpMessage: 'Select Header, Body, Footer, or a signature text field before inserting a variable.',
            });
            return;
        }

        if (!config.acceptsInsert) {
            setInsertTarget(config.id, {
                isError: true,
                helpMessage: `${config.label} is a direct URL field. Variables cannot be inserted there.`,
            });
            return;
        }

        if (editorOnlyVariables.has(name) && config.type !== 'editor') {
            setInsertTarget(config.id, {
                isError: true,
                helpMessage: `${tag} can only be inserted into Header, Body, or Footer content.`,
            });
            return;
        }

        if (config.type === 'editor') {
            const editor = editors[config.id];

            if (editor) {
                editor.focus();
                editor.execCommand('mceInsertContent', false, tag);
            } else {
                const textarea = document.getElementById(config.id);
                if (!textarea) {
                    return;
                }

                textarea.focus();
                insertIntoPlainField(textarea, tag);
            }

            currentEditor = config.id;
        } else {
            const field = document.getElementById(config.id);
            if (!field) {
                return;
            }

            insertIntoPlainField(field, tag);
        }

        markDirty();
        setInsertTarget(config.id, {
            helpMessage: `${tag} inserted into ${config.label}.`,
        });
        updatePreview();
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
        markDirty();
        updatePreview();
    }

    async function saveDraft() {
        if (!autosaveUrl) return;

        autoSaveInFlight = true;
        setAutoSaveStatus('Saving draft...');

        try {
            tinymce.triggerSave();
            const form = document.getElementById('templateForm');
            const formData = new FormData(form);
            formData.delete('_method');

            const response = await fetch(autosaveUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': formData.get('_token') || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
                credentials: 'same-origin'
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Auto-save failed');
            }

            hasUnsyncedDraft = false;
            const savedAt = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            setAutoSaveStatus(`Draft saved at ${savedAt}`);
        } catch (error) {
            setAutoSaveStatus('Auto-save failed', true);
        } finally {
            autoSaveInFlight = false;
        }
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

        [header, body, footer] = normalizePreviewSingletons([header, body, footer]);
        
        const sigName = document.getElementById('sigName')?.value || '';
        const sigTitle = document.getElementById('sigTitle')?.value || '';
        const sigDept = document.getElementById('sigDept')?.value || '';
        const sigInst = document.getElementById('sigInst')?.value || '';
        const sigEmail = document.getElementById('sigEmail')?.value || '';
        const sigPhone = document.getElementById('sigPhone')?.value || '';
        const sigImage = document.getElementById('sigImage')?.value || '';
        const stampImage = document.getElementById('stampImage')?.value || '';
        const qrEnabled = document.getElementById('qrCodeEnabled')?.checked ?? true;
        const footerStripEnabled = document.getElementById('footerEnabled')?.checked ?? true;
        const watermarkEnabled = document.getElementById('watermarkEnabled')?.checked ?? false;
        const watermarkText = getFieldValue('watermarkText');
        const fontFamily = getFieldValue('settingFont') || "'Times New Roman', serif";
        const fontSize = parseFloat(getFieldValue('settingSize') || '12');
        const language = getFieldValue('settingLang') || 'en';
        const direction = language === 'ar' ? 'rtl' : 'ltr';
        const frameStyle = getFieldValue('frameStyle');
        const frameColor = document.getElementById('frameColor')?.value || '#2f8e55';
        const marginTop = parseFloat(document.querySelector('[name="layout_settings[margins][top]"]')?.value || '20');
        const marginRight = parseFloat(document.querySelector('[name="layout_settings[margins][right]"]')?.value || '20');
        const marginBottom = parseFloat(document.querySelector('[name="layout_settings[margins][bottom]"]')?.value || '20');
        const marginLeft = parseFloat(document.querySelector('[name="layout_settings[margins][left]"]')?.value || '20');
        const previewQr = qrEnabled ? renderPreviewQrBlock() : '';
        const dynamicSamples = {
            fullName: 'Sarah Ahmed Alotaibi',
            studentName: 'Sarah',
            middleName: 'Ahmed',
            lastName: 'Alotaibi',
            studentEmail: 'sarah.alotaibi@example.com',
            university: 'King Saud University',
            purpose: 'Residency Application',
            trackingId: 'REC-2026-DEMO123',
            date: 'April 22, 2026',
            rotationMonth: 'April 2026',
            trainingPeriod: 'April, 2026',
            status: 'Approved',
            phone: '+966500000000',
            major: 'Medicine',
            notes: 'Excellent clinical performance',
            qrCode: previewQr,
            signature: sigImage ? `<img src="${sigImage}" alt="Signature" style="max-height: 60px; display: block;">` : '',
            he: 'she',
            him: 'her',
            his: 'her',
            himself: 'herself',
            He: 'She',
            Him: 'Her',
            His: 'Her',
            title: 'Ms.',
            gender: 'female',
        };

        header = sanitize(applyPreviewVariables(header, dynamicSamples));
        body = sanitize(applyPreviewVariables(body, dynamicSamples));
        footer = sanitize(applyPreviewVariables(footer, dynamicSamples));
        
        const preview = document.getElementById('previewContent');
        if (!preview) return;

        preview.style.padding = `${marginTop}mm ${marginRight}mm ${Math.max(marginBottom * 0.35, 8)}mm ${marginLeft}mm`;
        preview.style.fontFamily = fontFamily;
        preview.style.fontSize = `${fontSize}pt`;
        preview.style.direction = direction;
        preview.style.lineHeight = direction === 'rtl' ? '1.65' : '1.55';
        preview.style.position = 'relative';

        preview.innerHTML = `
            ${watermarkEnabled ? `
                <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; transform: rotate(-35deg); font-size: 64pt; color: rgba(203, 213, 225, 0.18); font-weight: 700; text-transform: uppercase; pointer-events: none; user-select: none;">
                    ${escapeHtml(watermarkText || 'OFFICIAL COPY')}
                </div>
            ` : ''}
            <div style="position: relative; z-index: 1;">
                <div style="margin-bottom: 16px;">${header}</div>
                <div style="min-height: 150mm;">${body}</div>
                <div style="margin-top: 28px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="vertical-align: top; width: 65%;">
                                ${sigName ? `<div style="font-weight: 700; font-size: ${Math.max(fontSize + 0.3, 11)}pt; margin-bottom: 3px;">${escapeHtml(sigName)}</div>` : ''}
                                ${sigTitle ? `<div style="margin-bottom: 4px; color: #374151;">${escapeHtml(sigTitle)}</div>` : ''}
                                ${(sigDept || sigInst) ? `
                                    <div style="color: #4b5563; font-size: ${Math.max(fontSize - 2, 9)}pt; line-height: 1.4;">
                                        ${sigDept ? `<div>${escapeHtml(sigDept)}</div>` : ''}
                                        ${sigInst ? `<div>${escapeHtml(sigInst)}</div>` : ''}
                                    </div>
                                ` : ''}
                                ${(sigEmail || sigPhone) ? `
                                    <div style="margin-top: 8px; color: #4b5563; font-size: ${Math.max(fontSize - 2, 9)}pt; line-height: 1.4;">
                                        ${sigEmail ? `<div>Email: ${escapeHtml(sigEmail)}</div>` : ''}
                                        ${sigPhone ? `<div>Phone: ${escapeHtml(sigPhone)}</div>` : ''}
                                    </div>
                                ` : ''}
                                ${sigImage ? `<div style="margin-top: 14px;"><img src="${sigImage}" alt="Signature" style="max-height: 58px; max-width: 150px;"></div>` : ''}
                            </td>
                            <td style="vertical-align: top; text-align: right; width: 35%;">
                                ${stampImage ? `<div style="margin-bottom: 10px;"><img src="${stampImage}" alt="Stamp" style="max-width: 90px; max-height: 90px;"></div>` : ''}
                                ${previewQr ? `<div style="margin-top: 8px;">${previewQr}</div>` : ''}
                            </td>
                        </tr>
                    </table>
                </div>
                ${footerStripEnabled ? `
                    <div style="margin-top: 14px; padding: 8px 12px; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #4b5563; font-size: 7.6pt; line-height: 1.35;">
                        <div style="font-weight: 700; color: #111827;">Digitally verified document</div>
                        <div>Reference ID: REC-2026-DEMO123</div>
                        <div>Verify this document at: https://apps.aamd.sa/RL/verify/demo-token</div>
                    </div>
                ` : ''}
                <div style="margin-top: 14px; padding-top: 8px; border-top: 1px solid #d1d5db; color: #374151; font-size: ${Math.max(fontSize - 3, 7)}pt; line-height: 1.25;">
                    ${footer}
                </div>
            </div>
        `;
        
        // Apply border to paper
        const paper = preview.closest('.te-paper');
        if (paper) {
            const borderEnabled = document.getElementById('borderEnabled')?.checked;
            if (frameStyle === 'ngha_green') {
                paper.style.border = `4px double ${frameColor}`;
            } else if (borderEnabled) {
                const width = document.getElementById('borderWidth')?.value || 2;
                const style = document.getElementById('borderStyle')?.value || 'solid';
                const color = document.getElementById('borderColor')?.value || '#000000';
                paper.style.border = `${width}px ${style} ${color}`;
            } else {
                paper.style.border = 'none';
            }
        }

        refreshVariableManager();
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
    ['borderEnabled', 'borderWidth', 'borderStyle', 'borderColor', 'frameStyle', 'frameColor'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function() {
                markDirty();
                updatePreview();
            });
        }
    });


    function confirmReset() {
        if (confirm('Are you sure you want to reset this template to its saved default?\n\nThis will overwrite the current template content with the default version saved for this template.')) {
            document.getElementById('resetForm').submit();
        }
    }

    function confirmSaveResetDefault() {
        if (confirm('Save the current saved template as the reset default?\n\nAfter this, Reset to Default will restore this current version. Save Template first if you have unsaved edits on this page.')) {
            document.getElementById('resetDefaultForm').submit();
        }
    }
</script>
@endsection
