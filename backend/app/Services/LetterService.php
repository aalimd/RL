<?php

namespace App\Services;

use App\Models\Request;
use App\Models\Template;
use ArPHP\I18N\Arabic as ArabicText;
use Carbon\Carbon;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LetterService
{
    private ?HTMLPurifier $purifier = null;
    private ?ArabicText $arabicText = null;

    /**
     * Sanitize HTML content
     * Only admins can edit templates, but we still sanitize to prevent stored XSS
     */
    public function sanitizeHtml($html)
    {
        if (!$html) {
            return null;
        }

        try {
            return $this->purifier()->purify((string) $html);
        } catch (\Throwable $e) {
            // Fallback: strip dangerous tags but keep basic HTML
            Log::warning('HTMLPurifier failed, using basic sanitization: ' . $e->getMessage());
            return strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><span><div><h1><h2><h3><h4><h5><h6><table><tr><td><th><img>');
        }
    }

    private function purifier(): HTMLPurifier
    {
        if ($this->purifier instanceof HTMLPurifier) {
            return $this->purifier;
        }

        $cachePath = (string) config('purifier.cachePath', storage_path('app/purifier'));
        $cacheMode = (int) config('purifier.cacheFileMode', 0755);

        if (!is_dir($cachePath)) {
            @mkdir($cachePath, $cacheMode, true);
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->loadArray((array) config('purifier.settings.default', []));
        $config->set('Core.Encoding', config('purifier.encoding', 'UTF-8'));
        $config->set('Cache.SerializerPath', $cachePath);
        $config->set('Cache.SerializerPermissions', $cacheMode);

        if (!config('purifier.finalize', true)) {
            $config->autoFinalize = false;
        }

        $config->set('HTML.Allowed', 'p[style|class|id],br,strong,b,em,i,u,ul,ol,li,a[href|target],span[style|class|id],div[style|class|id],h1[style|class|id],h2[style|class|id],h3[style|class|id],h4[style|class|id],h5[style|class|id],h6[style|class|id],img[src|alt|style|width|height|class],table[style|class|border|width|cellpadding|cellspacing],tr[style|class],td[style|class|colspan|rowspan|width|height|align|valign],th[style|class|colspan|rowspan|width|height|align|valign],thead,tbody,tfoot,hr[style|class],font[color|size|face],center,blockquote');
        $config->set('CSS.AllowedProperties', 'font-size,font-family,font-weight,font-style,text-align,text-decoration,line-height,color,background-color,background,border,border-radius,border-collapse,border-spacing,width,height,min-width,max-width,min-height,max-height,display,padding,margin,float,clear,overflow,position,top,bottom,left,right,z-index,vertical-align,white-space,list-style-type');
        $config->set('AutoFormat.RemoveEmpty', false);

        return $this->purifier = new HTMLPurifier($config);
    }

    /**
     * Generate all letter content sections with variables replaced
     */
    public function generateLetterContent(Request $request, ?Template $template = null): array
    {
        $formData = [];
        $formData = $request->form_data ?? [];
        $templateId = $formData['template_id'] ?? null;

        // Resolve Template
        if (!$template) {
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

    public function prepareHtmlForPdf(?string $html): ?string
    {
        if (!$html || !preg_match('/\p{Arabic}/u', $html)) {
            return $html;
        }

        $internalErrors = libxml_use_internal_errors(true);

        try {
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $wrapperId = 'pdf-arabic-root';
            $fragment = '<?xml encoding="utf-8" ?><div id="' . $wrapperId . '">' . $html . '</div>';
            $dom->loadHTML($fragment, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            $xpath = new \DOMXPath($dom);
            $root = $xpath->query('//*[@id="' . $wrapperId . '"]')->item(0);

            if (!$root instanceof \DOMElement) {
                return $html;
            }

            foreach ($xpath->query('.//text()[normalize-space(.) != ""]', $root) as $textNode) {
                if (!$textNode instanceof \DOMText) {
                    continue;
                }

                if (preg_match('/\p{Arabic}/u', $textNode->nodeValue ?? '')) {
                    $textNode->nodeValue = $this->shapeArabicStringForPdf($textNode->nodeValue);
                }
            }

            foreach ($xpath->query('.//*[@style or @dir]', $root) as $element) {
                if (!$element instanceof \DOMElement) {
                    continue;
                }

                $style = (string) $element->getAttribute('style');
                $dir = strtolower(trim((string) $element->getAttribute('dir')));
                $textContent = (string) $element->textContent;
                $hasArabic = preg_match('/\p{Arabic}/u', $textContent) === 1;
                $hadRtlDirection = $dir === 'rtl' || preg_match('/direction\s*:\s*rtl/i', $style);

                if (!$hasArabic && !$hadRtlDirection) {
                    continue;
                }

                if ($dir === 'rtl') {
                    $element->setAttribute('dir', 'ltr');
                }

                $style = preg_replace('/direction\s*:\s*rtl\s*;?/i', 'direction: ltr;', $style) ?? $style;
                $style = preg_replace('/unicode-bidi\s*:\s*[^;]+;?/i', '', $style) ?? $style;

                if ($hasArabic && !preg_match('/font-family\s*:/i', $style)) {
                    $style = rtrim($style, '; ') . '; font-family: DejaVu Sans, sans-serif;';
                }

                if ($hadRtlDirection && !preg_match('/text-align\s*:/i', $style)) {
                    $style = rtrim($style, '; ') . '; text-align: right;';
                }

                if ($hasArabic && !preg_match('/direction\s*:/i', $style)) {
                    $style = rtrim($style, '; ') . '; direction: ltr;';
                }

                $element->setAttribute('style', trim($style, " \t\n\r\0\x0B;") . ';');
            }

            $result = '';
            foreach ($root->childNodes as $child) {
                $result .= $dom->saveHTML($child);
            }

            return $result;
        } catch (\Throwable $e) {
            Log::warning('Arabic PDF HTML shaping failed, using original HTML: ' . $e->getMessage());
            return $html;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($internalErrors);
        }
    }

    public function prepareSignatureForPdf(array $signature): array
    {
        foreach (['name', 'title', 'institution', 'department', 'email', 'phone'] as $field) {
            if (!isset($signature[$field]) || !is_string($signature[$field])) {
                continue;
            }

            $signature[$field] = $this->shapeArabicStringForPdf($signature[$field]);
        }

        return $signature;
    }

    private function shapeArabicStringForPdf(?string $text): string
    {
        $text = (string) $text;

        if ($text === '' || !preg_match('/\p{Arabic}/u', $text)) {
            return $text;
        }

        try {
            return $this->arabicText()->utf8Glyphs($text);
        } catch (\Throwable $e) {
            Log::warning('Arabic glyph shaping failed, using original text: ' . $e->getMessage());
            return $text;
        }
    }

    private function arabicText(): ArabicText
    {
        return $this->arabicText ??= new ArabicText();
    }

    /**
     * Build variables map for substitution
     */
    public function getVariables(Request $request): array
    {
        $formData = $request->form_data ?? [];

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
     * Generate QR Code HTML
     */
    private function generateQrCodeHtml(Request $request)
    {
        // Auto-generate verify_token if missing (for older requests)
        if (!$request->verify_token) {
            try {
                $request->verify_token = \Illuminate\Support\Str::random(32);
                $request->saveQuietly();
            } catch (\Exception $e) {
                Log::warning('Failed to auto-generate verify_token for request ' . $request->id . ': ' . $e->getMessage());
                return '';
            }
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
