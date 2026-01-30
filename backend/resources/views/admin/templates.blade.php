@extends('layouts.admin')

@section('page-title', 'Templates')

@section('content')
    @if(session('success'))
        <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3>Recommendation Templates</h3>
            <a href="{{ route('admin.templates.create') }}" class="btn btn-primary">
                <i data-feather="plus" style="width: 16px; height: 16px;"></i>
                Create Template
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            @if($templates->isEmpty())
                <div style="text-align: center; padding: 3rem; color: #6b7280;">
                    <i data-feather="file" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p style="margin-bottom: 1rem;">No templates yet</p>
                    <a href="{{ route('admin.templates.create') }}" class="btn btn-primary">Create Your First Template</a>
                </div>
            @else
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; padding: 1.5rem;">
                    @foreach($templates as $template)
                        <div style="background: #f9fafb; border-radius: 0.75rem; padding: 1.5rem; border: 1px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <h4 style="font-size: 1.125rem; font-weight: 600; color: #111827;">{{ $template->name }}</h4>
                                <span class="badge {{ $template->is_active ? 'badge-approved' : 'badge-rejected' }}">
                                    {{ $template->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <div
                                style="background: white; padding: 0.75rem; border-radius: 0.5rem; height: 80px; overflow: hidden; margin-bottom: 1rem; position: relative;">
                                <div style="font-size: 0.875rem; color: #6b7280; line-height: 1.4;">
                                    {{ \Str::limit(strip_tags($template->body_content ?? $template->content ?? ''), 150) }}
                                </div>
                                <div
                                    style="position: absolute; bottom: 0; left: 0; right: 0; height: 30px; background: linear-gradient(transparent, white);">
                                </div>
                            </div>

                            <div
                                style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid #e5e7eb;">
                                <span
                                    style="font-size: 0.75rem; background: #e5e7eb; padding: 0.25rem 0.5rem; border-radius: 0.25rem; text-transform: uppercase; font-weight: 600; color: #4b5563;">
                                    {{ strtoupper($template->language) }}
                                </span>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="{{ route('admin.templates.edit', $template->id) }}" class="btn btn-ghost"
                                        style="padding: 0.5rem;" title="Edit">
                                        <i data-feather="edit-2" style="width: 16px; height: 16px;"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.templates.destroy', $template->id) }}"
                                        style="display: inline;"
                                        onsubmit="return confirm('Are you sure you want to delete this template?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost" style="padding: 0.5rem; color: #dc2626;"
                                            title="Delete">
                                            <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection