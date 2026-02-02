@extends('layouts.admin')

@section('page-title', 'Request Form Settings')

@section('content')
    @if(session('success'))
        <div style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid rgba(16, 185, 129, 0.3);">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.form-settings.update') }}">
        @csrf
        @method('PUT')

        <!-- Template Selection Mode -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3>Template Selection Mode</h3>
                <span style="font-size: 0.875rem; color: #6b7280;">Control how students select or receive templates</span>
            </div>
            <div class="card-body">
                <div style="display: grid; gap: 1rem; max-width: 600px;">
                    <div class="form-group">
                        <label class="form-label">Selection Mode</label>
                        <select name="templateSelectionMode" class="form-input" onchange="toggleTemplateOptions(this.value)">
                            <option value="student_choice" {{ ($formSettings['templateSelectionMode'] ?? 'student_choice') === 'student_choice' ? 'selected' : '' }}>
                                Student Can Choose Template
                            </option>
                            <option value="admin_fixed" {{ ($formSettings['templateSelectionMode'] ?? '') === 'admin_fixed' ? 'selected' : '' }}>
                                Fixed Template (Admin Selected)
                            </option>
                            <option value="custom_only" {{ ($formSettings['templateSelectionMode'] ?? '') === 'custom_only' ? 'selected' : '' }}>
                                Custom Content Only
                            </option>
                        </select>
                    </div>

                    <div id="fixedTemplateSection" style="{{ ($formSettings['templateSelectionMode'] ?? '') === 'admin_fixed' ? '' : 'display: none;' }}">
                        <label class="form-label">Default Template</label>
                        <select name="defaultTemplateId" class="form-input">
                            <option value="">-- Select Template --</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ ($formSettings['defaultTemplateId'] ?? '') == $template->id ? 'selected' : '' }}>
                                    {{ $template->name }} ({{ strtoupper($template->language) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="allowCustomContent" id="allowCustomContent" 
                            {{ ($formSettings['allowCustomContent'] ?? 'true') === 'true' ? 'checked' : '' }}>
                        <label for="allowCustomContent">Allow students to write custom content</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Fields Configuration -->
        <div class="card">
            <div class="card-header">
                <h3>Form Fields Configuration</h3>
                <span style="font-size: 0.875rem; color: #6b7280;">Control visibility and requirement status of each field</span>
            </div>
            <div class="card-body">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--input-bg); border-bottom: 2px solid var(--border-color);">
                                <th style="text-align: left; padding: 0.75rem 1rem; font-weight: 600; color: var(--text-main);">Field Name</th>
                                <th style="text-align: center; padding: 0.75rem 1rem; font-weight: 600; color: var(--text-main);">Visible</th>
                                <th style="text-align: center; padding: 0.75rem 1rem; font-weight: 600; color: var(--text-main);">Required</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $fields = [
                                    'student_name' => 'First Name',
                                    'middle_name' => 'Middle Name',
                                    'last_name' => 'Last Name',
                                    'gender' => 'Gender',
                                    'student_email' => 'Email Address',
                                    'university' => 'University / Institution',
                                    'verification_token' => 'Student ID / National ID',
                                    'training_period' => 'Training Period',
                                    'phone' => 'Phone Number',
                                    'major' => 'Major / Field of Study',
                                    'purpose' => 'Purpose of Recommendation',
                                    'deadline' => 'Deadline Date',
                                    'notes' => 'Additional Notes',
                                ];
                                $fieldConfig = json_decode($formSettings['formFieldConfig'] ?? '{}', true) ?: [];
                            @endphp
                            @foreach($fields as $fieldKey => $fieldLabel)
                                @php
                                    $isVisible = $fieldConfig[$fieldKey]['visible'] ?? true;
                                    $isRequired = $fieldConfig[$fieldKey]['required'] ?? in_array($fieldKey, ['student_name', 'last_name', 'student_email', 'gender', 'verification_token', 'training_period', 'purpose', 'deadline']);
                                @endphp
                                <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-main);">
                                    <td style="padding: 0.75rem 1rem;">{{ $fieldLabel }}</td>
                                    <td style="text-align: center; padding: 0.75rem 1rem;">
                                        <input type="checkbox" name="fields[{{ $fieldKey }}][visible]" value="1" {{ $isVisible ? 'checked' : '' }}>
                                    </td>
                                    <td style="text-align: center; padding: 0.75rem 1rem;">
                                        <input type="checkbox" name="fields[{{ $fieldKey }}][required]" value="1" {{ $isRequired ? 'checked' : '' }}>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Save Form Settings</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('styles')
    <!-- Global Styles Used -->
@endsection

@section('scripts')
<script>
    function toggleTemplateOptions(mode) {
        const fixedSection = document.getElementById('fixedTemplateSection');
        if (mode === 'admin_fixed') {
            fixedSection.style.display = 'block';
        } else {
            fixedSection.style.display = 'none';
        }
    }
</script>
@endsection
