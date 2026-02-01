@extends('layouts.admin')

@section('page-title', 'Email Templates')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3>Email Templates</h3>
            <span style="font-size: 0.875rem; color: #6b7280;">Manage the automated emails sent by the system</span>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                    {{ session('success') }}
                </div>
            @endif

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Template Name</th>
                        <th>Subject</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $template)
                        <tr>
                            <td>
                                <span
                                    style="font-weight: 600; color: #1f2937;">{{ ucwords(str_replace('_', ' ', $template->name)) }}</span>
                                <br>
                                <code style="font-size: 0.75rem; color: #6b7280;">{{ $template->name }}</code>
                            </td>
                            <td>{{ $template->subject }}</td>
                            <td>
                                <a href="{{ route('admin.email-templates.edit', $template->id) }}"
                                    class="btn btn-sm btn-primary">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection