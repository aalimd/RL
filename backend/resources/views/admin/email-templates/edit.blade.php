@extends('layouts.admin')

@section('page-title', 'Edit Template')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3>Edit Template: {{ ucwords(str_replace('_', ' ', $template->name)) }}</h3>
            <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.email-templates.update', $template->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label"
                        style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Subject</label>
                    <input type="text" name="subject" class="form-input" value="{{ old('subject', $template->subject) }}"
                        required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label"
                        style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Content</label>
                    <div style="margin-bottom: 0.5rem;">
                        <span style="font-size: 0.875rem; color: #6b7280;">Available Variables:</span>
                        @foreach($template->variables ?? [] as $var)
                            <code
                                style="background: #f3f4f6; padding: 2px 4px; border-radius: 4px; color: #4b5563; margin-right: 5px; cursor: pointer;"
                                onclick="insertVar('{!! '{' . $var . '}' !!}')">{!! '{' . $var . '}' !!}</code>
                        @endforeach
                    </div>
                    <textarea name="body" id="bodyEditor" class="form-textarea" rows="15" required
                        style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: monospace;">{{ old('body', $template->body) }}</textarea>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.tiny.cloud/1/djjpb8qqw9cskwcb9wz6j9y4qdx8fbkno7ccret2axmq61mw/tinymce/6/tinymce.min.js"
        referrerpolicy="origin"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
                    editor.on('change', function () {
                        editor.save();
                    });
                }
            });
        });

        function insertVar(text) {
            if (tinymce.get('bodyEditor')) {
                tinymce.get('bodyEditor').execCommand('mceInsertContent', false, text);
            } else {
                const textarea = document.getElementById('bodyEditor');
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const value = textarea.value;
                textarea.value = value.substring(0, start) + text + value.substring(end);
                textarea.selectionStart = textarea.selectionEnd = start + text.length;
                textarea.focus();
            }
        }
    </script>
@endsection