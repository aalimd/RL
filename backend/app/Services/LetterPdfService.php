<?php

namespace App\Services;

use App\Models\Request as RequestModel;
use App\Models\Template;
use Dompdf\Dompdf;
use Dompdf\Options;
use RuntimeException;

class LetterPdfService
{
    private const MAX_FIT_ATTEMPTS = 12;

    private const DEFAULT_PDF_FIT = [
        'lineHeight' => 1.45,
        'paragraphGap' => 8.0,
        'headerGap' => 14.0,
        'bodyGap' => 14.0,
        'signatureTop' => 14.0,
        'signatureNameSize' => 11.0,
        'signatureTitleSize' => 10.0,
        'signatureDetailSize' => 9.0,
        'signatureImageHeight' => 70.0,
        'stampSize' => 96.0,
        'qrSize' => 72.0,
        'digitalFooterTop' => 10.0,
        'digitalFooterPadY' => 8.0,
        'digitalFooterPadX' => 8.0,
        'digitalFooterFontSize' => 7.1,
        'footerTop' => 8.0,
        'footerFontSize' => 8.0,
        'footerLineHeight' => 1.15,
        'borderPadding' => 12.0,
    ];

    private const FITTING_RULES = [
        ['path' => 'fontSize', 'step' => 0.2, 'min' => 9.8, 'group' => 'body'],
        ['path' => 'margins.top', 'step' => 0.6, 'min' => 12.0, 'group' => 'margins'],
        ['path' => 'margins.right', 'step' => 0.6, 'min' => 12.0, 'group' => 'margins'],
        ['path' => 'margins.bottom', 'step' => 0.6, 'min' => 12.0, 'group' => 'margins'],
        ['path' => 'margins.left', 'step' => 0.6, 'min' => 12.0, 'group' => 'margins'],
        ['path' => 'pdfFit.lineHeight', 'step' => 0.03, 'min' => 1.22, 'group' => 'body'],
        ['path' => 'pdfFit.paragraphGap', 'step' => 0.8, 'min' => 3.0, 'group' => 'body'],
        ['path' => 'pdfFit.headerGap', 'step' => 1.0, 'min' => 6.0, 'group' => 'header'],
        ['path' => 'pdfFit.bodyGap', 'step' => 1.0, 'min' => 6.0, 'group' => 'body'],
        ['path' => 'pdfFit.signatureTop', 'step' => 1.0, 'min' => 6.0, 'group' => 'signature'],
        ['path' => 'pdfFit.signatureNameSize', 'step' => 0.2, 'min' => 9.3, 'group' => 'signature'],
        ['path' => 'pdfFit.signatureTitleSize', 'step' => 0.2, 'min' => 8.6, 'group' => 'signature'],
        ['path' => 'pdfFit.signatureDetailSize', 'step' => 0.2, 'min' => 7.2, 'group' => 'signature'],
        ['path' => 'pdfFit.signatureImageHeight', 'step' => 2.0, 'min' => 42.0, 'group' => 'signature'],
        ['path' => 'pdfFit.stampSize', 'step' => 3.0, 'min' => 68.0, 'group' => 'signature'],
        ['path' => 'pdfFit.qrSize', 'step' => 2.0, 'min' => 56.0, 'group' => 'signature'],
        ['path' => 'pdfFit.digitalFooterTop', 'step' => 1.0, 'min' => 6.0, 'group' => 'footer'],
        ['path' => 'pdfFit.digitalFooterPadY', 'step' => 0.8, 'min' => 5.0, 'group' => 'footer'],
        ['path' => 'pdfFit.digitalFooterPadX', 'step' => 0.8, 'min' => 6.0, 'group' => 'footer'],
        ['path' => 'pdfFit.digitalFooterFontSize', 'step' => 0.15, 'min' => 6.0, 'group' => 'footer'],
        ['path' => 'pdfFit.footerTop', 'step' => 0.8, 'min' => 5.0, 'group' => 'footer'],
        ['path' => 'pdfFit.footerFontSize', 'step' => 0.15, 'min' => 6.3, 'group' => 'footer'],
        ['path' => 'pdfFit.footerLineHeight', 'step' => 0.03, 'min' => 1.0, 'group' => 'footer'],
        ['path' => 'pdfFit.borderPadding', 'step' => 0.8, 'min' => 8.0, 'group' => 'decorative'],
    ];

    public function __construct(private LetterService $letterService)
    {
    }

    /**
     * Compile a letter into a canonical one-page-aware PDF document plus diagnostics.
     *
     * @throws RuntimeException
     */
    public function compile(RequestModel $request, ?Template $template = null): array
    {
        $content = $this->letterService->generateLetterContent($request, $template);

        if ($content === [] || empty($content['template'])) {
            throw new RuntimeException('No active template found for this request.');
        }

        $sanitized = [
            'header' => $this->letterService->sanitizeHtml($content['header'] ?? ''),
            'body' => $this->letterService->sanitizeHtml($content['body'] ?? ''),
            'footer' => $this->letterService->sanitizeHtml($content['footer'] ?? ''),
        ];

        $layout = $this->normalizeLayout($content['layout'] ?? []);
        $fitGroups = [];
        $attempts = 0;
        $rendered = $this->renderAttempt($request, $sanitized, $content, $layout);

        while (
            $rendered['page_count'] > 1
            && $attempts < self::MAX_FIT_ATTEMPTS
        ) {
            $tightenResult = $this->tightenLayout($layout);

            if (!$tightenResult['changed']) {
                break;
            }

            $layout = $tightenResult['layout'];
            $fitGroups = array_values(array_unique(array_merge($fitGroups, $tightenResult['groups'])));
            $attempts++;
            $rendered = $this->renderAttempt($request, $sanitized, $content, $layout);
        }

        $fit = $this->buildFitSummary(
            $rendered['page_count'],
            $attempts,
            $fitGroups,
            $layout,
            $content,
        );

        return [
            'request' => $request,
            'template' => $content['template'],
            'layout' => $layout,
            'header' => $sanitized['header'],
            'body' => $sanitized['body'],
            'footer' => $sanitized['footer'],
            'signature' => $content['signature'] ?? [],
            'qrCode' => $content['qrCode'] ?? '',
            'fit' => $fit,
            'pdf_binary' => $rendered['binary'],
            'pdf_html' => $rendered['html'],
            'pdf_page_count' => $rendered['page_count'],
        ];
    }

    private function renderAttempt(
        RequestModel $request,
        array $sanitized,
        array $content,
        array $layout,
    ): array {
        $pdfData = [
            'request' => $request,
            'layout' => $layout,
            'header' => $this->letterService->prepareHtmlForPdf($sanitized['header']),
            'body' => $this->letterService->prepareHtmlForPdf($sanitized['body']),
            'footer' => $this->letterService->prepareHtmlForPdf($sanitized['footer']),
            'signature' => $this->letterService->prepareSignatureForPdf($content['signature'] ?? []),
            'qrCode' => $content['qrCode'] ?? '',
        ];

        $html = view('pdf.letter', $pdfData)->render();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', $this->defaultFontForLayout($layout));

        $pdf = new Dompdf($options);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('a4', 'portrait');
        $pdf->render();

        return [
            'binary' => $pdf->output(),
            'html' => $html,
            'page_count' => $pdf->getCanvas()->get_page_count(),
        ];
    }

    private function normalizeLayout(array $layout): array
    {
        $direction = $layout['direction'] ?? (($layout['language'] ?? 'en') === 'ar' ? 'rtl' : 'ltr');
        $layout['direction'] = $direction;
        $layout['fontFamily'] = $layout['fontFamily'] ?? "'Times New Roman', serif";
        $layout['fontSize'] = $this->clamp((float) ($layout['fontSize'] ?? 12), 9.8, 12.0);

        $margins = (array) ($layout['margins'] ?? []);
        $layout['margins'] = [
            'top' => $this->clamp((float) ($margins['top'] ?? 20), 12.0, 24.0),
            'right' => $this->clamp((float) ($margins['right'] ?? 20), 12.0, 24.0),
            'bottom' => $this->clamp((float) ($margins['bottom'] ?? 20), 12.0, 24.0),
            'left' => $this->clamp((float) ($margins['left'] ?? 20), 12.0, 24.0),
        ];

        $pdfFit = is_array($layout['pdfFit'] ?? null) ? $layout['pdfFit'] : [];
        $defaults = self::DEFAULT_PDF_FIT;
        if (!array_key_exists('lineHeight', $pdfFit) && $direction === 'rtl') {
            $defaults['lineHeight'] = 1.52;
        }

        $layout['pdfFit'] = array_merge($defaults, $pdfFit);

        foreach (self::FITTING_RULES as $rule) {
            $current = (float) data_get($layout, $rule['path'], data_get($defaults, $rule['path']));
            data_set($layout, $rule['path'], $this->clamp($current, $rule['min'], $this->maxForPath($rule['path'])));
        }

        return $layout;
    }

    private function tightenLayout(array $layout): array
    {
        $changed = false;
        $groups = [];

        foreach (self::FITTING_RULES as $rule) {
            $current = (float) data_get($layout, $rule['path']);
            $next = round(max($rule['min'], $current - $rule['step']), 2);

            if ($next >= $current) {
                continue;
            }

            data_set($layout, $rule['path'], $next);
            $changed = true;
            $groups[$rule['group']] = true;
        }

        return [
            'changed' => $changed,
            'groups' => array_keys($groups),
            'layout' => $layout,
        ];
    }

    private function buildFitSummary(
        int $pageCount,
        int $attempts,
        array $fitGroups,
        array $layout,
        array $content,
    ): array {
        if ($pageCount <= 1) {
            if ($attempts > 0) {
                return [
                    'status' => 'auto_fitted',
                    'label' => 'Auto-fitted to one A4 page',
                    'message' => 'Ready for official export. The system tightened spacing and typography within safe limits to keep this letter on a single A4 page.',
                    'page_count' => $pageCount,
                    'attempts' => $attempts,
                    'overflow_reason' => null,
                    'can_export' => true,
                ];
            }

            return [
                'status' => 'fits',
                'label' => 'Fits on one A4 page',
                'message' => 'Ready for official export. This letter already fits on a single A4 page.',
                'page_count' => $pageCount,
                'attempts' => 0,
                'overflow_reason' => null,
                'can_export' => true,
            ];
        }

        $overflowReason = $this->inferOverflowReason($fitGroups, $layout, $content);

        return [
            'status' => 'too_long',
            'label' => 'Too long for one A4 page',
            'message' => 'Still exceeds one A4 page after safe auto-fit. Shorten the ' . strtolower($overflowReason) . ' before approving or exporting the official PDF.',
            'page_count' => $pageCount,
            'attempts' => $attempts,
            'overflow_reason' => $overflowReason,
            'can_export' => false,
        ];
    }

    private function inferOverflowReason(array $fitGroups, array $layout, array $content): string
    {
        $bodyTextLength = mb_strlen(trim(strip_tags((string) ($content['body'] ?? ''))));
        $footerTextLength = mb_strlen(trim(strip_tags((string) ($content['footer'] ?? ''))));
        $hasSignatureContent = !empty(array_filter([
            $content['signature']['name'] ?? null,
            $content['signature']['title'] ?? null,
            $content['signature']['institution'] ?? null,
            $content['signature']['department'] ?? null,
            $content['signature']['email'] ?? null,
            $content['signature']['phone'] ?? null,
            $content['signature']['image'] ?? null,
            $content['signature']['stamp'] ?? null,
            $content['qrCode'] ?? null,
        ]));

        if (in_array('body', $fitGroups, true) || $bodyTextLength > 1600) {
            return 'body content';
        }

        if (in_array('footer', $fitGroups, true) && ($footerTextLength > 0 || ($layout['footer']['enabled'] ?? true))) {
            return 'footer area';
        }

        if (in_array('signature', $fitGroups, true) && $hasSignatureContent) {
            return 'signature section';
        }

        if (in_array('margins', $fitGroups, true)) {
            return 'page margins';
        }

        if (in_array('decorative', $fitGroups, true) && (($layout['border']['enabled'] ?? false) || ($layout['watermark']['enabled'] ?? false))) {
            return 'decorative options';
        }

        return 'letter content';
    }

    private function defaultFontForLayout(array $layout): string
    {
        $fontFamily = strtolower((string) ($layout['fontFamily'] ?? ''));

        if (($layout['direction'] ?? 'ltr') === 'rtl' || str_contains($fontFamily, 'dejavu sans')) {
            return 'DejaVu Sans';
        }

        if (str_contains($fontFamily, 'courier')) {
            return 'Courier';
        }

        if (str_contains($fontFamily, 'arial') || str_contains($fontFamily, 'sans')) {
            return 'Helvetica';
        }

        return 'Times-Roman';
    }

    private function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($value, $max));
    }

    private function maxForPath(string $path): float
    {
        foreach (self::FITTING_RULES as $rule) {
            if ($rule['path'] !== $path) {
                continue;
            }

            return (float) data_get(self::DEFAULT_PDF_FIT, str_replace('pdfFit.', '', $path), match ($path) {
                'fontSize' => 12.0,
                'margins.top', 'margins.right', 'margins.bottom', 'margins.left' => 24.0,
                default => 24.0,
            });
        }

        return 24.0;
    }
}
