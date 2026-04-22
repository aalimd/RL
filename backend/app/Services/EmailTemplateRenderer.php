<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Arr;

class EmailTemplateRenderer
{
    public function __construct(private EmailBrandingService $emailBrandingService)
    {
    }

    public function render(array|string $names, array $variables, string $defaultSubject): array
    {
        $template = $this->findTemplate(Arr::wrap($names));
        $branding = $this->emailBrandingService->getBranding();
        $templateVariables = $this->emailBrandingService->mergeVariables($variables, $branding);

        $subject = $template?->subject ?: $defaultSubject;
        $bodyHtml = $template?->body;

        $subject = str_replace(array_keys($templateVariables), array_values($templateVariables), $subject);
        $bodyHtml = $bodyHtml !== null
            ? str_replace(array_keys($templateVariables), array_values($templateVariables), $bodyHtml)
            : null;

        return [
            'template' => $template,
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $this->htmlToText($bodyHtml),
            'branding' => $branding,
            'variables' => $templateVariables,
        ];
    }

    public function htmlToText(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $text = preg_replace('/<(br|\/p|\/div|\/li|\/h[1-6])[^>]*>/i', "\n", $html);
        $text = preg_replace('/<li[^>]*>/i', '- ', $text ?? '');
        $text = strip_tags((string) $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/\r\n|\r/", "\n", $text);
        $text = preg_replace("/[ \t]+\n/", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim((string) $text);
    }

    private function findTemplate(array $names): ?EmailTemplate
    {
        try {
            $templates = EmailTemplate::whereIn('name', $names)->get()->keyBy('name');
        } catch (\Throwable) {
            return null;
        }

        foreach ($names as $name) {
            if ($templates->has($name)) {
                return $templates->get($name);
            }
        }

        return null;
    }
}
