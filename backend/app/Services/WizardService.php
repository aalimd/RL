<?php

namespace App\Services;

use App\Models\Settings;
use App\Models\Template;
use Illuminate\Http\Request;

class WizardService
{
    /**
     * Get Wizard Configuration
     */
    public function getFormConfig(): array
    {
        $allSettings = Settings::whereIn('key', ['templateSelectionMode', 'defaultTemplateId', 'allowCustomContent', 'formFieldConfig'])
            ->pluck('value', 'key')
            ->toArray();

        $templateMode = $allSettings['templateSelectionMode'] ?? 'student_choice';
        if (!in_array($templateMode, ['student_choice', 'admin_fixed', 'custom_only'], true)) {
            $templateMode = 'student_choice';
        }

        $defaultTemplateId = !empty($allSettings['defaultTemplateId']) ? (int) $allSettings['defaultTemplateId'] : null;
        $allowCustomContent = ($allSettings['allowCustomContent'] ?? 'true') === 'true';
        if ($templateMode === 'custom_only') {
            // In custom-only mode, custom content must stay enabled.
            $allowCustomContent = true;
        }

        $fields = json_decode($allSettings['formFieldConfig'] ?? '{}', true) ?: [];

        // Critical fields for tracking/OTP should never be hidden or optional.
        foreach (['student_email', 'verification_token'] as $lockedField) {
            $fields[$lockedField] = [
                'visible' => true,
                'required' => true,
            ];
        }

        return [
            'templateMode' => $templateMode,
            'defaultTemplateId' => $defaultTemplateId,
            'allowCustomContent' => $allowCustomContent,
            'fields' => $fields,
        ];
    }

    /**
     * Get Available Templates based on Config
     */
    public function getTemplates(array $formConfig)
    {
        if (($formConfig['templateMode'] ?? 'student_choice') === 'admin_fixed') {
            if (empty($formConfig['defaultTemplateId'])) {
                return Template::whereRaw('1 = 0')->get();
            }

            return Template::where('id', $formConfig['defaultTemplateId'])
                ->where('is_active', true)
                ->get();
        }

        return Template::where('is_active', true)->orderBy('name')->get();
    }

    /**
     * Resolve content selection safely based on admin form settings.
     */
    public function resolveContentData(array $formData, array $formConfig): array
    {
        $templateMode = $formConfig['templateMode'] ?? 'student_choice';
        $allowCustom = (bool) ($formConfig['allowCustomContent'] ?? true);
        $defaultTemplateId = !empty($formConfig['defaultTemplateId']) ? (int) $formConfig['defaultTemplateId'] : null;

        $contentOption = $formData['content_option'] ?? 'template';
        if ($templateMode === 'admin_fixed') {
            $contentOption = 'template';
        } elseif ($templateMode === 'custom_only') {
            $contentOption = 'custom';
        } elseif (!$allowCustom && $contentOption === 'custom') {
            $contentOption = 'template';
        }

        $templateId = null;
        $customContent = null;

        if ($contentOption === 'template') {
            $templateId = $templateMode === 'admin_fixed'
                ? $defaultTemplateId
                : (!empty($formData['template_id']) ? (int) $formData['template_id'] : null);
        } else {
            $customContent = trim((string) ($formData['custom_content'] ?? ''));
            $customContent = $customContent === '' ? null : $customContent;
        }

        return [
            'content_option' => $contentOption,
            'template_id' => $templateId,
            'custom_content' => $customContent,
        ];
    }

    /**
     * Validate Step 1
     */
    public function validateStep1(Request $request, array $formConfig)
    {
        $fieldConfig = $formConfig['fields'] ?? [];
        $rules = [];
        $messages = [];

        $fieldMeta = [
            'student_name' => ['display' => 'First name', 'rule' => 'string|max:255'],
            'middle_name' => ['display' => 'Middle name', 'rule' => 'string|max:255'],
            'last_name' => ['display' => 'Last name', 'rule' => 'string|max:255'],
            'gender' => ['display' => 'Gender', 'rule' => 'in:male,female'],
            'student_email' => ['display' => 'Email', 'rule' => 'email|max:255'],
            'university' => ['display' => 'University', 'rule' => 'string|max:255'],
            'verification_token' => ['display' => 'ID number', 'rule' => 'string|max:100'],
            'training_period' => ['display' => 'Training period', 'rule' => 'string'],
            'phone' => ['display' => 'Phone number', 'rule' => 'regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:20'],
            'major' => ['display' => 'Major', 'rule' => 'string|max:255'],
        ];

        // Default required fields
        $defaultRequired = ['student_name', 'last_name', 'student_email', 'gender', 'verification_token', 'training_period'];
        $lockedRequiredFields = ['student_email', 'verification_token'];

        foreach ($fieldMeta as $key => $meta) {
            $isVisible = $fieldConfig[$key]['visible'] ?? true;
            $isRequired = $fieldConfig[$key]['required'] ?? in_array($key, $defaultRequired);

            if (in_array($key, $lockedRequiredFields, true)) {
                $isVisible = true;
                $isRequired = true;
            }

            if ($isVisible && $isRequired) {
                $rules["data.$key"] = 'required|' . $meta['rule'];
                $messages["data.$key.required"] = $meta['display'] . ' is required';
            } elseif ($isVisible) {
                $rules["data.$key"] = 'nullable|' . $meta['rule'];
            }
        }

        return $request->validate($rules, $messages);
    }

    /**
     * Validate Step 2
     */
    public function validateStep2(array $formData, array $formConfig = [])
    {
        $resolved = $this->resolveContentData($formData, $formConfig);
        $contentOption = $resolved['content_option'];
        $templateMode = $formConfig['templateMode'] ?? 'student_choice';
        $errors = [];

        if ($contentOption === 'template' && empty($resolved['template_id'])) {
            $errors['template'] = $templateMode === 'admin_fixed'
                ? 'No fixed template is configured. Please contact the administrator.'
                : 'Please select a template';
        }
        if ($contentOption === 'template' && !empty($resolved['template_id'])) {
            $templateAvailable = Template::where('id', $resolved['template_id'])
                ->where('is_active', true)
                ->exists();

            if (!$templateAvailable) {
                $errors['template'] = $templateMode === 'admin_fixed'
                    ? 'Configured fixed template is unavailable. Please contact the administrator.'
                    : 'Selected template is unavailable. Please choose another template.';
            }
        }
        if ($contentOption === 'custom' && empty($resolved['custom_content'])) {
            $errors['custom_content'] = 'Please enter custom content';
        }

        return $errors;
    }

    /**
     * Validate Step 3
     */
    public function validateStep3(Request $request, array $formConfig)
    {
        $fieldConfig = $formConfig['fields'] ?? [];
        $rules = [];
        $messages = [];

        // Fields relevant to Step 3
        $step3Fields = ['purpose', 'deadline', 'notes'];

        foreach ($step3Fields as $key) {
            $isVisible = $fieldConfig[$key]['visible'] ?? true;
            // Default required logic if not explicitly set in config
            $isDefaultRequired = in_array($key, ['purpose', 'deadline']);
            $isRequired = $fieldConfig[$key]['required'] ?? $isDefaultRequired;

            if ($isVisible && $isRequired) {
                // Determine rules based on field type
                $rule = 'string';
                if ($key === 'deadline') {
                    $rule = 'date|after:today';
                } elseif ($key === 'purpose') {
                    $rule = 'string|max:100';
                }

                $rules["data.$key"] = 'required|' . $rule;
                $messages["data.$key.required"] = ucfirst($key) . ' is required';
            } elseif ($isVisible) {
                $rule = 'string';
                if ($key === 'deadline') {
                    $rule = 'date|after:today';
                }
                $rules["data.$key"] = 'nullable|' . $rule;
            }
        }

        return $request->validate($rules, $messages);
    }
}
