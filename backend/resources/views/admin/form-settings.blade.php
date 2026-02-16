@extends('layouts.admin')

@section('page-title', 'Request Form Settings')

@section('content')
    @php
        $selectedMode = old('templateSelectionMode', $formSettings['templateSelectionMode'] ?? 'student_choice');
        $selectedDefaultTemplate = old('defaultTemplateId', $formSettings['defaultTemplateId'] ?? '');
        $allowCustomChecked = old('allowCustomContent', ($formSettings['allowCustomContent'] ?? 'true') === 'true' ? '1' : null);
        $forceCustom = $selectedMode === 'custom_only';
        $lockedRequiredFields = ['student_email', 'verification_token'];
        $fieldConfig = json_decode($formSettings['formFieldConfig'] ?? '{}', true) ?: [];
        $submittedFields = old('fields', []);
        if (!is_array($submittedFields)) {
            $submittedFields = [];
        }
    @endphp

    @if(session('success'))
        <div style="background: var(--success-bg); color: var(--success-text); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid var(--success-border);">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background: #fff7ed; color: #9a3412; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid #fed7aa;">
            <div style="font-weight: 600; margin-bottom: 0.5rem;">Please fix the following:</div>
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
                        <select id="templateSelectionMode" name="templateSelectionMode" class="form-input" onchange="toggleTemplateOptions(this.value)">
                            <option value="student_choice" {{ $selectedMode === 'student_choice' ? 'selected' : '' }}>
                                Student Can Choose Template
                            </option>
                            <option value="admin_fixed" {{ $selectedMode === 'admin_fixed' ? 'selected' : '' }}>
                                Fixed Template (Admin Selected)
                            </option>
                            <option value="custom_only" {{ $selectedMode === 'custom_only' ? 'selected' : '' }}>
                                Custom Content Only
                            </option>
                        </select>
                    </div>

                    <div id="fixedTemplateSection" style="{{ $selectedMode === 'admin_fixed' ? '' : 'display: none;' }}">
                        <label class="form-label">Default Template</label>
                        <select id="defaultTemplateId" name="defaultTemplateId" class="form-input" {{ $selectedMode === 'admin_fixed' ? 'required' : '' }}>
                            <option value="">-- Select Template --</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ (string) $selectedDefaultTemplate === (string) $template->id ? 'selected' : '' }}>
                                    {{ $template->name }} ({{ strtoupper($template->language) }})
                                </option>
                            @endforeach
                        </select>
                        <small style="display: block; margin-top: 0.4rem; color: #6b7280;">
                            Required when mode is <strong>Fixed Template</strong>.
                        </small>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="allowCustomContent" id="allowCustomContent" value="1"
                            {{ ($allowCustomChecked || $forceCustom) ? 'checked' : '' }} {{ $forceCustom ? 'disabled' : '' }}>
                        @if($forceCustom)
                            <input type="hidden" id="allowCustomContentForced" name="allowCustomContent" value="1">
                        @endif
                        <label for="allowCustomContent">Allow students to write custom content</label>
                    </div>
                    <small id="allowCustomContentHint" style="color: #6b7280;">
                        {{ $forceCustom ? 'Custom Content Only mode forces this option to stay enabled.' : '' }}
                    </small>
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
                            @endphp
                            @foreach($fields as $fieldKey => $fieldLabel)
                                @php
                                    $isLocked = in_array($fieldKey, $lockedRequiredFields, true);
                                    if (isset($submittedFields[$fieldKey])) {
                                        $isVisible = isset($submittedFields[$fieldKey]['visible']);
                                        $isRequired = isset($submittedFields[$fieldKey]['required']);
                                    } else {
                                        $isVisible = $fieldConfig[$fieldKey]['visible'] ?? true;
                                        $isRequired = $fieldConfig[$fieldKey]['required'] ?? in_array($fieldKey, ['student_name', 'last_name', 'student_email', 'gender', 'verification_token', 'training_period', 'purpose', 'deadline']);
                                    }

                                    if (!$isVisible) {
                                        $isRequired = false;
                                    }
                                    if ($isLocked) {
                                        $isVisible = true;
                                        $isRequired = true;
                                    }
                                @endphp
                                <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-main);">
                                    <td style="padding: 0.75rem 1rem;">
                                        {{ $fieldLabel }}
                                        @if($isLocked)
                                            <small style="display: block; color: #6b7280;">Locked for secure tracking</small>
                                        @endif
                                    </td>
                                    <td style="text-align: center; padding: 0.75rem 1rem;">
                                        <input type="checkbox" name="fields[{{ $fieldKey }}][visible]" value="1"
                                            {{ $isVisible ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                        @if($isLocked)
                                            <input type="hidden" name="fields[{{ $fieldKey }}][visible]" value="1">
                                        @endif
                                    </td>
                                    <td style="text-align: center; padding: 0.75rem 1rem;">
                                        <input type="checkbox" name="fields[{{ $fieldKey }}][required]" value="1"
                                            {{ $isRequired ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                        @if($isLocked)
                                            <input type="hidden" name="fields[{{ $fieldKey }}][required]" value="1">
                                        @endif
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
        const defaultTemplateId = document.getElementById('defaultTemplateId');
        const allowCustomContent = document.getElementById('allowCustomContent');
        const allowCustomContentHint = document.getElementById('allowCustomContentHint');

        if (fixedSection && defaultTemplateId) {
            fixedSection.style.display = mode === 'admin_fixed' ? 'block' : 'none';
            defaultTemplateId.required = mode === 'admin_fixed';
        }

        if (allowCustomContent) {
            if (mode === 'custom_only') {
                allowCustomContent.checked = true;
                allowCustomContent.disabled = true;
                if (allowCustomContentHint) {
                    allowCustomContentHint.textContent = 'Custom Content Only mode forces this option to stay enabled.';
                }
                if (!document.getElementById('allowCustomContentForced')) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'allowCustomContent';
                    hidden.value = '1';
                    hidden.id = 'allowCustomContentForced';
                    allowCustomContent.parentElement.appendChild(hidden);
                }
            } else {
                allowCustomContent.disabled = false;
                if (allowCustomContentHint) {
                    allowCustomContentHint.textContent = '';
                }
                const forced = document.getElementById('allowCustomContentForced');
                if (forced) {
                    forced.remove();
                }
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const modeSelect = document.getElementById('templateSelectionMode');
        if (modeSelect) {
            toggleTemplateOptions(modeSelect.value);
        }
    });
</script>
@endsection
