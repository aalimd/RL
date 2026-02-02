<?php

namespace App\Services;

use App\Models\Request;
use App\Models\Template;
use Illuminate\Support\Facades\Log;
use Mews\Purifier\Facades\Purifier;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class LetterService
{
    /**
     * Sanitize HTML content
     * Only admins can edit templates, but we still sanitize to prevent stored XSS
     */
    public function sanitizeHtml($html)
    {
        if (!$html)
            return null;

        // Use Purifier if available, with custom config to allow common styling
        try {
            return Purifier::clean($html, [
                'HTML.Allowed' => 'p,br,strong,b,em,i,u,ul,ol,li,a[href],span[style],div[style],h1,h2,h3,h4,h5,h6,img[src|alt|style],table,tr,td,th,thead,tbody',
                'CSS.AllowedProperties' => 'font-size,font-family,font-weight,text-align,color,background-color,margin,padding,border,border-radius,width,height,display,text-decoration,line-height',
                'AutoFormat.RemoveEmpty' => true,
            ]);
        } catch (\Exception $e) {
            // Fallback: strip dangerous tags but keep basic HTML
            Log::warning('Purifier failed, using basic sanitization: ' . $e->getMessage());
            return strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><span><div><h1><h2><h3><h4><h5><h6><table><tr><td><th><img>');
        }
    }

    /**
     * Generate all letter content sections with variables replaced
     */
    public function generateLetterContent(Request $request, ?Template $template = null): array
    {
        // Resolve Template
        if (!$template) {
            $templateId = null;
            try {
                $formData = $request->form_data ?? [];
                $templateId = $formData['template_id'] ?? null;
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                Log::error('Decryption failed for request ' . $request->id . ': ' . $e->getMessage());
                $formData = [];
            }

            if ($templateId) {
                $template = Template::find($templateId);
            }

            // Fallback to template_id column
            if (!$template && $request->template_id) {
                $template = Template::find($request->template_id);
            }

            // Fallback to active template
            if (!$template) {
                $template = Template::where('is_active', true)->first();
            }
        }

        if (!$template) {
            return []; // Or throw exception specific to business logic
        }

        // Variable Substitution
        $variables = $this->getVariables($request);

        // Helper to replace signature placeholder
        if ($template->signature_image && $request->status === 'Approved') {
            $sigImg = '<img src="' . $template->signature_image . '" style="max-height: 80px; display: block; margin-bottom: 5px;" alt="Signature">';
            $stampImg = '';
            if ($template->stamp_image) {
                // Stamp usually goes over or next to signature. We'll float it or absolute pos ideally, 
                // but for simple HTML print, we might just put it next to it.
                // Or better, just stack them.
                $stampImg = '<img src="' . $template->stamp_image . '" style="max-height: 100px; display: block; margin-top: -40px; margin-left: 100px; opacity: 0.8;" alt="Stamp">';
            }
            $signatureHtml = '<div class="official-signature">' . $sigImg . $stampImg . '</div>';

            // Replace the placeholder we set in getVariables
            $variables['{{signature}}'] = $signatureHtml;
        } else {
            $variables['{{signature}}'] = '';
        }

        // Re-bind closure to use updated variables
        $replaceVars = function ($text) use ($variables) {
            if (!$text)
                return '';
            return str_replace(array_keys($variables), array_values($variables), $text);
        };

        $headerContent = $replaceVars($template->header_content);
        $bodyContent = $replaceVars($template->body_content ?? $template->content);
        $footerContent = $replaceVars($template->footer_content);

        // Auto-apply gender pronouns based on student's gender
        $gender = !empty($formData['gender']) ? $formData['gender'] : 'male';
        $headerContent = $this->applyGenderPronouns($headerContent, $gender);
        $bodyContent = $this->applyGenderPronouns($bodyContent, $gender);
        $footerContent = $this->applyGenderPronouns($footerContent, $gender);



        // Update signature array for view (this is separate from the {{signature}} content variable)
        $signature = [
            'name' => $replaceVars($template->signature_name),
            'title' => $replaceVars($template->signature_title),
            'image' => $template->signature_image,
            'stamp' => $template->stamp_image,
            'institution' => $template->signature_institution,
            'department' => $template->signature_department,
            'email' => $template->signature_email,
            'phone' => $template->signature_phone
        ];

        // Layout Settings
        $layoutSettings = [];
        if (isset($template->layout_settings)) {
            $rawSettings = $template->layout_settings;
            if (is_string($rawSettings)) {
                try {
                    $layoutSettings = json_decode($rawSettings, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    $layoutSettings = [];
                }
            } else {
                $layoutSettings = (array) $rawSettings;
            }
        }

        // Layout defaults
        $layoutSettings['margins'] = $layoutSettings['margins'] ?? ['top' => 25, 'bottom' => 25, 'left' => 25, 'right' => 25];
        $layoutSettings['fontSize'] = $layoutSettings['fontSize'] ?? 12;
        $layoutSettings['fontFamily'] = $layoutSettings['fontFamily'] ?? 'Times New Roman';
        $layoutSettings['direction'] = $layoutSettings['direction'] ?? 'ltr';

        return [
            'template' => $template,
            'header' => $headerContent,
            'body' => $bodyContent,
            'footer' => $footerContent,
            'signature' => $signature,
            'layout' => $layoutSettings,
            'qrCode' => $this->generateQrCodeHtml($request),
        ];
    }

    /**
     * Build variables map for substitution
     */
    public function getVariables(Request $request): array
    {
        try {
            $formData = $request->form_data ?? [];
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            $formData = [];
        }

        $rotationMonthFormatted = '';
        if (isset($formData['rotationMonth'])) {
            try {
                $date = Carbon::parse($formData['rotationMonth']);
                $rotationMonthFormatted = $date->format('F Y');
            } catch (\Exception $e) {
                // Ignore parsing error
            }
        }

        // Format training period
        $trainingPeriodFormatted = '';
        $trainingPeriodRaw = $request->training_period ?? ($formData['training_period'] ?? null);
        if ($trainingPeriodRaw) {
            try {
                $date = Carbon::parse($trainingPeriodRaw . '-01');
                $trainingPeriodFormatted = $date->format('F, Y');
            } catch (\Exception $e) {
                $trainingPeriodFormatted = $trainingPeriodRaw;
            }
        }

        // Build full name
        $firstName = $request->student_name ?? '';
        $middleName = $request->middle_name ?? ($formData['middle_name'] ?? '');
        $lastName = $request->last_name ?? ($formData['last_name'] ?? '');
        $fullName = trim($firstName . ' ' . $middleName . ' ' . $lastName);

        // Gender-aware pronouns
        $gender = $formData['gender'] ?? 'male'; // Default to male for existing data
        $pronouns = match (strtolower($gender)) {
            'female' => [
                'subject' => 'she',
                'object' => 'her',
                'possessive' => 'her',
                'possessive_pronoun' => 'hers',
                'reflexive' => 'herself',
                'title' => 'Ms.',
            ],
            default => [
                'subject' => 'he',
                'object' => 'him',
                'possessive' => 'his',
                'possessive_pronoun' => 'his',
                'reflexive' => 'himself',
                'title' => 'Mr.',
            ],
        };

        // Also create capitalized versions for sentence starts
        $pronounsCapitalized = [
            'subject' => ucfirst($pronouns['subject']),
            'object' => ucfirst($pronouns['object']),
            'possessive' => ucfirst($pronouns['possessive']),
        ];

        return [
            '{{fullName}}' => $fullName,
            '{{studentName}}' => $firstName,
            '{{middleName}}' => $middleName,
            '{{lastName}}' => $lastName,
            '{{studentEmail}}' => $request->student_email,
            '{{university}}' => $request->university ?? '',
            '{{purpose}}' => $request->purpose ?? '',
            '{{trackingId}}' => $request->tracking_id,
            '{{date}}' => now()->format('F d, Y'),
            '{{rotationMonth}}' => $rotationMonthFormatted,
            '{{trainingPeriod}}' => $trainingPeriodFormatted,
            '{{status}}' => $request->status,
            '{{phone}}' => $request->phone ?? ($formData['phone'] ?? ''),
            '{{major}}' => $request->major ?? ($formData['major'] ?? ''),
            '{{notes}}' => $request->notes ?? ($formData['notes'] ?? ''),
            '{{qrCode}}' => $this->generateQrCodeHtml($request),
            '{{signature}}' => ($request->status === 'Approved') ? 'signature_placeholder' : '',
            // Gender-aware pronouns (lowercase)
            '{{he}}' => $pronouns['subject'],
            '{{him}}' => $pronouns['object'],
            '{{his}}' => $pronouns['possessive'],
            '{{himself}}' => $pronouns['reflexive'],
            // Gender-aware pronouns (capitalized for sentence starts)
            '{{He}}' => $pronounsCapitalized['subject'],
            '{{Him}}' => $pronounsCapitalized['object'],
            '{{His}}' => $pronounsCapitalized['possessive'],
            // Title
            '{{title}}' => $pronouns['title'],
            // Gender value itself
            '{{gender}}' => $gender,
        ];
    }

    /**
     * Apply gender-specific pronoun replacements
     * Uses word boundaries to avoid partial word replacements (e.g., "history")
     */
    private function applyGenderPronouns(?string $text, string $gender): string
    {
        if (!$text || strtolower($gender) !== 'female') {
            return $text ?? '';
        }

        // Pronoun mapping: male → female (using word boundaries for safety)
        $replacements = [
            // Lowercase
            '/\bhe\b/' => 'she',
            '/\bhim\b/' => 'her',
            '/\bhis\b/' => 'her',
            '/\bhimself\b/' => 'herself',
            // Capitalized (for sentence starts)
            '/\bHe\b/' => 'She',
            '/\bHim\b/' => 'Her',
            '/\bHis\b/' => 'Her',
            '/\bHimself\b/' => 'Herself',
            // Titles
            '/\bMr\./' => 'Ms.',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }

        return $text;
    }

    /**
     * Generate QR Code HTML
     */
    private function generateQrCodeHtml(Request $request)
    {
        if (!$request->verify_token) {
            return '';
        }

        $url = route('public.verify', $request->verify_token);

        // Generate SVG and Encode as Base64 to survive HTML Purifier
        $qrInfo = QrCode::format('svg')->size(70)->generate($url);

        // Clean XML header if strictly embedding
        $qrString = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $qrInfo);
        $base64Qr = base64_encode($qrString);

        // Return as IMG tag because Purifier accepts <img> but strips <svg>
        return '<div class="qr-code-container" style="margin-top: 10px;"><img src="data:image/svg+xml;base64,' . $base64Qr . '" alt="QR Code" style="width: 70px; height: 70px;"><div style="font-size: 10px; color: #555; margin-top: 2px;">Scan to Verify</div></div>';
    }
}
