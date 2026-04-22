@extends('layouts.admin')

@php
    $previewTokens = array_merge($preview['samples'], [
        'site_name' => $preview['branding']['site_name'] ?? config('app.name'),
        'from_name' => $preview['branding']['from_name'] ?? config('app.name'),
        'support_email' => $preview['branding']['support_email'] ?? '',
        'app_url' => $preview['branding']['app_url'] ?? config('app.url'),
        'app_host' => $preview['branding']['app_host'] ?? parse_url((string) config('app.url'), PHP_URL_HOST),
        'year' => now()->year,
    ]);
    $previewPrimary = $preview['branding']['primary_color'] ?? '#1d4ed8';
    $previewPrimaryDark = $preview['branding']['primary_color_dark'] ?? '#163b96';
    $previewPrimarySoft = $preview['branding']['primary_color_soft'] ?? '#e8f1fe';
@endphp

@section('page-title', 'Edit Template')

@section('content')
    <style>
        .email-template-shell {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(340px, 0.85fr);
            gap: 1.5rem;
        }

        .email-template-card {
            border: 1px solid var(--border-color, #d1d5db);
            border-radius: 1rem;
            background: var(--card-bg, #ffffff);
            overflow: hidden;
        }

        .email-template-card .section-head {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color, #d1d5db);
            background: linear-gradient(180deg, rgba(59, 130, 246, 0.06), rgba(59, 130, 246, 0));
        }

        .email-template-card .section-body {
            padding: 1.5rem;
        }

        .template-guide {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .guide-tile {
            padding: 1rem 1.1rem;
            border-radius: 0.9rem;
            background: var(--bg-muted, #f8fafc);
            border: 1px solid var(--border-color, #dbe3ee);
        }

        .guide-tile .label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-muted, #64748b);
            margin-bottom: 0.35rem;
        }

        .token-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        .token-chip {
            padding: 0.45rem 0.7rem;
            border-radius: 999px;
            border: 1px solid var(--border-color, #cbd5e1);
            background: var(--bg-muted, #f8fafc);
            color: var(--text-main, #0f172a);
            font-size: 0.82rem;
            cursor: pointer;
            font-family: monospace;
        }

        .editor-note {
            margin-top: 0.75rem;
            color: var(--text-muted, #64748b);
            font-size: 0.82rem;
            line-height: 1.6;
        }

        .subject-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }

        .subject-count {
            font-size: 0.82rem;
            color: var(--text-muted, #64748b);
        }

        .subject-count.is-warning {
            color: #b45309;
            font-weight: 700;
        }

        .preview-panel {
            background: #eef3f8;
            border-radius: 1rem;
            border: 1px solid #d8e3ef;
            padding: 1rem;
        }

        .preview-envelope {
            background: #ffffff;
            border: 1px solid #dbe5f0;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        }

        .preview-envelope-head {
            padding: 1rem 1.1rem;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .preview-envelope-head .sender {
            font-size: 0.88rem;
            font-weight: 700;
            color: #0f172a;
        }

        .preview-envelope-head .meta {
            margin-top: 0.3rem;
            font-size: 0.78rem;
            color: #64748b;
        }

        .preview-envelope-body {
            padding: 1.1rem;
        }

        .preview-email-shell {
            background: #ffffff;
            border: 1px solid #dbe5f0;
            border-radius: 1.1rem;
            overflow: hidden;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        }

        .preview-hero {
            padding: 1.3rem;
            background: linear-gradient(135deg, {{ $previewPrimary }} 0%, {{ $previewPrimaryDark }} 100%);
        }

        .preview-host {
            display: inline-block;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            color: #dbeafe;
            font-size: 0.76rem;
            margin-top: 0.35rem;
        }

        .preview-badge {
            display: inline-block;
            margin-bottom: 0.85rem;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            color: #eff6ff;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .preview-title {
            margin: 0 0 0.55rem 0;
            color: #ffffff;
            font-size: 1.8rem;
            line-height: 1.2;
            font-weight: 700;
        }

        .preview-summary {
            margin: 0;
            color: #deebff;
            line-height: 1.7;
            font-size: 0.97rem;
        }

        .preview-transactional {
            margin: -0.9rem 1.1rem 0 1.1rem;
            padding: 0.85rem 1rem;
            border-radius: 0.9rem;
            background: {{ $previewPrimarySoft }};
            border: 1px solid #dbe5f0;
            color: #334155;
            font-size: 0.82rem;
        }

        .preview-content {
            padding: 1.15rem 1.1rem 0 1.1rem;
        }

        .preview-canvas {
            color: #0f172a;
            font-size: 0.95rem;
            line-height: 1.75;
        }

        .preview-canvas h1,
        .preview-canvas h2,
        .preview-canvas h3 {
            color: #0f172a;
        }

        .preview-canvas a {
            color: {{ $previewPrimary }};
        }

        .preview-canvas p:first-child {
            margin-top: 0;
        }

        .preview-cta {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.85rem 1.15rem;
            border-radius: 999px;
            background: {{ $previewPrimary }};
            color: #ffffff !important;
            text-decoration: none;
            font-weight: 700;
        }

        .preview-closing {
            margin-top: 1rem;
            color: #64748b;
            font-size: 0.88rem;
            line-height: 1.65;
        }

        .preview-footer {
            margin-top: 1.2rem;
            padding: 1rem 1.1rem 1.2rem 1.1rem;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 0.82rem;
            line-height: 1.7;
        }

        .preview-footer a {
            color: {{ $previewPrimary }};
            text-decoration: none;
        }

        .tips-list {
            display: grid;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .tip-row {
            padding: 0.9rem 1rem;
            border-radius: 0.9rem;
            background: var(--bg-muted, #f8fafc);
            border: 1px solid var(--border-color, #dbe3ee);
            color: var(--text-main, #0f172a);
            line-height: 1.6;
            font-size: 0.9rem;
        }

        @media (max-width: 1180px) {
            .email-template-shell {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="email-template-card" style="margin-bottom: 1.5rem;">
        <div class="section-head"
            style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div>
                <h3 style="margin: 0; color: var(--text-main);">Edit Email Template</h3>
                <p style="margin: 0.35rem 0 0 0; color: var(--text-muted);">
                    Make this message clear, professional, and easy to trust.
                </p>
            </div>
            <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
        </div>
        <div class="section-body">
            <div class="template-guide">
                <div class="guide-tile">
                    <div class="label">Template</div>
                    <div style="font-weight: 700; color: var(--text-main);">
                        {{ ucwords(str_replace('_', ' ', $template->name)) }}
                    </div>
                </div>
                <div class="guide-tile">
                    <div class="label">Recipient</div>
                    <div style="font-weight: 700; color: var(--text-main);">
                        {{ $preview['guide']['recipient'] }}
                    </div>
                </div>
                <div class="guide-tile" style="grid-column: 1 / -1;">
                    <div class="label">Goal</div>
                    <div style="color: var(--text-main); line-height: 1.65;">
                        {{ $preview['guide']['goal'] }}
                    </div>
                </div>
            </div>

            <div class="email-template-shell">
                <div class="email-template-card">
                    <div class="section-head">
                        <h4 style="margin: 0; color: var(--text-main);">Editor</h4>
                    </div>
                    <div class="section-body">
                        <form action="{{ route('admin.email-templates.update', $template->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <div class="subject-meta">
                                    <label class="form-label" style="margin: 0; font-weight: 600;">Subject line</label>
                                    <span id="subjectCount" class="subject-count">0 characters</span>
                                </div>
                                <input type="text" name="subject" id="subjectInput" class="form-input"
                                    value="{{ old('subject', $template->subject) }}" required
                                    style="width: 100%; padding: 0.85rem 1rem; border: 1px solid var(--border-color, #d1d5db); border-radius: 0.75rem; background: var(--bg-color, white); color: var(--text-main, black);">
                                <p class="editor-note">
                                    Keep the subject accurate and direct. Avoid decorative symbols, misleading urgency, or
                                    wording that feels promotional.
                                </p>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label class="form-label" style="display: block; margin-bottom: 0.35rem; font-weight: 600;">
                                    Body content
                                </label>
                                <p class="editor-note" style="margin-top: 0;">
                                    The application already adds the email shell, branding, footer, and support details.
                                    Edit only the message content that belongs inside the email body.
                                </p>

                                <div class="token-list">
                                    @foreach($template->variables ?? [] as $var)
                                        <button type="button" class="token-chip"
                                            onclick="insertVar('{!! '{' . $var . '}' !!}')">{!! '{' . $var . '}' !!}</button>
                                    @endforeach
                                    @foreach(['site_name', 'from_name', 'support_email', 'app_url'] as $var)
                                        @if(!in_array($var, $template->variables ?? [], true))
                                            <button type="button" class="token-chip"
                                                onclick="insertVar('{!! '{' . $var . '}' !!}')">{!! '{' . $var . '}' !!}</button>
                                        @endif
                                    @endforeach
                                </div>

                                <textarea name="body" id="bodyEditor" rows="15" required
                                    style="width: 100%; margin-top: 1rem; padding: 0.9rem; border: 1px solid var(--border-color, #d1d5db); border-radius: 0.75rem; font-family: monospace; background: var(--bg-color, white); color: var(--text-main, black);">{{ old('body', $template->body) }}</textarea>

                                <div class="editor-note">
                                    Keep links visible and easy to understand. Use one clear primary action and keep the
                                    message focused on the transaction the user expects.
                                </div>
                            </div>

                            <div style="display: flex; gap: 0.9rem; flex-wrap: wrap;">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div style="display: grid; gap: 1.5rem;">
                    <div class="email-template-card">
                        <div class="section-head">
                            <h4 style="margin: 0; color: var(--text-main);">Live Preview</h4>
                        </div>
                        <div class="section-body">
                            <div class="preview-panel">
                                <div class="preview-envelope">
                                    <div class="preview-envelope-head">
                                        <div class="sender" id="previewFrom">
                                            {{ $preview['branding']['from_name'] ?? config('app.name') }}
                                        </div>
                                        <div class="meta">
                                            to sample recipient •
                                            <span id="previewSubject">{{ $preview['subject'] }}</span>
                                        </div>
                                    </div>
                                    <div class="preview-envelope-body">
                                        <div class="preview-email-shell">
                                            <div class="preview-hero">
                                                <div
                                                    style="display:flex; align-items:flex-start; justify-content:space-between; gap:0.75rem; margin-bottom:1rem;">
                                                    <div style="color:#ffffff; font-weight:700; font-size:1.15rem;">
                                                        {{ $preview['branding']['site_name'] ?? config('app.name') }}
                                                    </div>
                                                    @if(!empty($preview['branding']['app_host']))
                                                        <div class="preview-host">{{ $preview['branding']['app_host'] }}</div>
                                                    @endif
                                                </div>
                                                @if(!empty($preview['frame']['badge']))
                                                    <div class="preview-badge">{{ $preview['frame']['badge'] }}</div>
                                                @endif
                                                <h2 class="preview-title">{{ $preview['frame']['title'] }}</h2>
                                                @if(!empty($preview['frame']['summary']))
                                                    <p class="preview-summary">{{ $preview['frame']['summary'] }}</p>
                                                @endif
                                            </div>
                                            <div class="preview-transactional">
                                                This is a transactional message related to activity in
                                                {{ $preview['branding']['site_name'] ?? config('app.name') }}.
                                            </div>
                                            <div class="preview-content">
                                                <div class="preview-canvas" id="previewBody">{!! $preview['body'] !!}</div>
                                                @if(!empty($preview['frame']['cta_label']) && !empty($preview['frame']['cta_url']))
                                                    <a id="previewCta" href="{{ $preview['frame']['cta_url'] }}"
                                                        class="preview-cta">{{ $preview['frame']['cta_label'] }}</a>
                                                @else
                                                    <a id="previewCta" href="#" class="preview-cta" style="display:none;">Action</a>
                                                @endif
                                                @if(!empty($preview['frame']['closing_note']))
                                                    <div class="preview-closing" id="previewClosing">
                                                        {{ $preview['frame']['closing_note'] }}
                                                    </div>
                                                @else
                                                    <div class="preview-closing" id="previewClosing" style="display:none;"></div>
                                                @endif
                                            </div>
                                            <div class="preview-footer">
                                                <div>
                                                    Sent by {{ $preview['branding']['from_name'] ?? config('app.name') }} for
                                                    {{ $preview['branding']['site_name'] ?? config('app.name') }}.
                                                </div>
                                                @if(!empty($preview['branding']['support_email']))
                                                    <div>
                                                        Need help? Contact
                                                        <a
                                                            href="mailto:{{ $preview['branding']['support_email'] }}">{{ $preview['branding']['support_email'] }}</a>.
                                                    </div>
                                                @endif
                                                @if(!empty($preview['branding']['app_url']))
                                                    <div>
                                                        <a href="{{ $preview['branding']['app_url'] }}">{{ $preview['branding']['app_url'] }}</a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="email-template-card">
                        <div class="section-head">
                            <h4 style="margin: 0; color: var(--text-main);">Editing Tips</h4>
                        </div>
                        <div class="section-body">
                            <div class="tips-list">
                                <div class="tip-row">
                                    Use clear sender identity and honest subject lines. Recipients should understand who sent
                                    the email and why they received it.
                                </div>
                                <div class="tip-row">
                                    Keep the content transactional and specific to the request, status, or security action.
                                    Avoid marketing-style urgency or decorative symbols.
                                </div>
                                <div class="tip-row">
                                    Use visible links and explicit actions. A reader should know exactly what happens after
                                    clicking.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="email-template-card">
                        <div class="section-head">
                            <h4 style="margin: 0; color: var(--text-main);">Sample Data</h4>
                        </div>
                        <div class="section-body">
                            <div class="token-list" style="margin-top: 0;">
                                @foreach($previewTokens as $token => $value)
                                    <div class="token-chip" style="cursor: default;">
                                        {{ '{' . $token . '}' }} = {{ $value }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.tiny.cloud/1/djjpb8qqw9cskwcb9wz6j9y4qdx8fbkno7ccret2axmq61mw/tinymce/6/tinymce.min.js"
        referrerpolicy="origin"></script>
    <script>
        const previewTokens = @json($previewTokens);

        document.addEventListener('DOMContentLoaded', function () {
            const subjectInput = document.getElementById('subjectInput');
            const bodyTextarea = document.getElementById('bodyEditor');
            const subjectCount = document.getElementById('subjectCount');
            const previewSubject = document.getElementById('previewSubject');
            const previewBody = document.getElementById('previewBody');

            function replaceTokens(content) {
                let rendered = content || '';

                for (const [key, value] of Object.entries(previewTokens)) {
                    rendered = rendered.split(`{${key}}`).join(value ?? '');
                }

                return rendered;
            }

            function updatePreview() {
                const subjectValue = replaceTokens(subjectInput.value);
                const rawBody = tinymce.get('bodyEditor')
                    ? tinymce.get('bodyEditor').getContent()
                    : bodyTextarea.value;
                const renderedBody = replaceTokens(rawBody);
                const count = subjectInput.value.trim().length;

                previewSubject.textContent = subjectValue || 'No subject';
                previewBody.innerHTML = renderedBody || '<p style="margin:0; color:#64748b;">Start writing to preview the email body.</p>';
                subjectCount.textContent = `${count} characters`;
                subjectCount.classList.toggle('is-warning', count > 78);
            }

            tinymce.init({
                selector: '#bodyEditor',
                height: 500,
                menubar: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount', 'directionality'
                ],
                toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | ltr rtl | bullist numlist outdent indent | table link image | code',
                content_style: 'body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.6; }',
                directionality: '{{ ($settings['siteLanguage'] ?? 'en') === 'ar' ? 'rtl' : 'ltr' }}',
                promotion: false,
                branding: false,
                verify_html: false,
                valid_elements: '*[*]',
                setup: function (editor) {
                    editor.on('change input keyup SetContent', function () {
                        editor.save();
                        updatePreview();
                    });
                }
            });

            subjectInput.addEventListener('input', updatePreview);
            updatePreview();
        });

        function insertVar(text) {
            if (tinymce.get('bodyEditor')) {
                tinymce.get('bodyEditor').execCommand('mceInsertContent', false, text);
                tinymce.get('bodyEditor').save();
            } else {
                const textarea = document.getElementById('bodyEditor');
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const value = textarea.value;
                textarea.value = value.substring(0, start) + text + value.substring(end);
                textarea.selectionStart = textarea.selectionEnd = start + text.length;
                textarea.focus();
            }

            const event = new Event('input', { bubbles: true });
            document.getElementById('subjectInput').dispatchEvent(event);
        }
    </script>
@endsection
