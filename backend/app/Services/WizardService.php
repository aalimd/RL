<?php

namespace App\Services;

use App\Models\Settings;
use App\Models\Request as RequestModel;
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

        return [
            'templateMode' => $allSettings['templateSelectionMode'] ?? 'student_choice',
            'defaultTemplateId' => $allSettings['defaultTemplateId'] ?? null,
            'allowCustomContent' => ($allSettings['allowCustomContent'] ?? 'true') === 'true',
            'fields' => json_decode($allSettings['formFieldConfig'] ?? '{}', true) ?: [],
        ];
    }

    /**
     * Get Available Templates based on Config
     */
    public function getTemplates(array $formConfig)
    {
        if ($formConfig['templateMode'] === 'admin_fixed' && $formConfig['defaultTemplateId']) {
            return Template::where('id', $formConfig['defaultTemplateId'])->get();
        }

        return Template::where('is_active', true)->orderBy('name')->get();
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
            'phone' => ['display' => 'Phone number', 'rule' => 'string|max:50'],
            'major' => ['display' => 'Major', 'rule' => 'string|max:255'],
        ];

        // Default required fields
        $defaultRequired = ['student_name', 'last_name', 'student_email', 'gender', 'verification_token', 'training_period'];

        foreach ($fieldMeta as $key => $meta) {
            $isVisible = $fieldConfig[$key]['visible'] ?? true;
            $isRequired = $fieldConfig[$key]['required'] ?? in_array($key, $defaultRequired);

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
    public function validateStep2(array $formData)
    {
        $contentOption = $formData['content_option'] ?? 'template';
        $errors = [];

        if ($contentOption === 'template' && empty($formData['template_id'])) {
            $errors['template'] = 'Please select a template';
        }
        if ($contentOption === 'custom' && empty($formData['custom_content'])) {
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
