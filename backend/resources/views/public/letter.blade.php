@php
    $margins = $layout['margins'] ?? [];
    $marginTop = max(10, min((float) ($margins['top'] ?? 20), 35));
    $marginSide = max(10, min((((float) ($margins['left'] ?? 20)) + ((float) ($margins['right'] ?? 20))) / 2, 30));
    $marginBottom = max(10, min((float) ($margins['bottom'] ?? 20), 35));
    $fontSize = max(10.5, min((float) ($layout['fontSize'] ?? 12), 16));
    $fontFamily = $layout['fontFamily'] ?? "'Times New Roman', serif";
    $language = $layout['language'] ?? 'en';
    $direction = $layout['direction'] ?? ($language === 'ar' ? 'rtl' : 'ltr');
    $embedded = (bool) ($embedded ?? false);
    $watermarkConfig = $layout['watermark'] ?? [];
    $watermarkEnabled = (bool) ($watermarkConfig['enabled'] ?? false);
    $watermarkText = !empty($watermarkConfig['text']) ? $watermarkConfig['text'] : ($request->tracking_id ?? 'OFFICIAL COPY');
    $showDigitalFooter = (bool) ($layout['footer']['enabled'] ?? true);
    $frameConfig = is_array($layout['frame'] ?? null) ? $layout['frame'] : [];
    $officialFrameEnabled = ($frameConfig['style'] ?? '') === 'ngha_green';
    $safeFrameColor = static function ($color, string $fallback = '#2f8e55'): string {
        $color = trim((string) $color);
        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : $fallback;
    };
    $officialFrameColor = $safeFrameColor($frameConfig['color'] ?? ($layout['border']['color'] ?? '#2f8e55'));
    $officialFrameTopInset = max(6, min((float) ($frameConfig['topInset'] ?? 9), 14));
    $officialFrameSideInset = max(7, min((float) ($frameConfig['sideInset'] ?? 9), 14));
    $rawFrameBottomInset = (float) ($frameConfig['bottomInset'] ?? 8);
    $officialFrameBottomInset = $rawFrameBottomInset > 18 ? 8 : max(6, min($rawFrameBottomInset, 14));
    $verifyUrl = $request->verify_token ? route('public.verify', $request->verify_token) : null;
    $pageStyle = implode(' ', [
        '--official-frame-color: ' . $officialFrameColor . ';',
        '--page-pad-top: ' . $officialFrameTopInset . 'mm;',
        '--page-pad-side: ' . $officialFrameSideInset . 'mm;',
        '--page-pad-bottom: ' . $officialFrameBottomInset . 'mm;',
        '--frame-pad-top: ' . max(5, min($marginTop * 0.32, 9)) . 'mm;',
        '--frame-pad-bottom: ' . max(4, min($marginBottom * 0.24, 7)) . 'mm;',
        '--section-pad-side: ' . max(8, min($marginSide * 0.68, 14)) . 'mm;',
        '--header-pad-bottom: ' . max(2.5, min($marginTop * 0.18, 5.5)) . 'mm;',
        '--body-font-size: ' . $fontSize . 'pt;',
        '--body-line-height: ' . ($direction === 'rtl' ? '1.65' : '1.55') . ';',
        '--paragraph-gap: 8px;',
        '--closing-pad-top: 10px;',
        '--closing-pad-bottom: 4px;',
        '--signature-name-size: ' . max(10.5, min($fontSize + 0.3, 13)) . 'pt;',
        '--signature-detail-size: ' . max(8.2, min($fontSize - 2, 10)) . 'pt;',
        '--signature-image-height: 58px;',
        '--stamp-size: 90px;',
        '--qr-size: 70px;',
        '--digital-footer-font-size: ' . max(6.4, min($fontSize - 3.2, 7.6)) . 'pt;',
        '--footer-pad-top: 2.5mm;',
        '--footer-pad-bottom: ' . max(2.5, min($marginBottom * 0.18, 5)) . 'mm;',
        '--footer-font-size: ' . max(6.8, min($fontSize - 3, 8.2)) . 'pt;',
        '--footer-line-height: 1.2;',
        '--page-font-family: ' . $fontFamily . ';',
    ]);
@endphp
<!DOCTYPE html>
<html lang="{{ $language }}" dir="{{ $direction }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="{{ rtrim(url('/'), '/') }}/">
    <title>Recommendation Letter</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background:
                radial-gradient(circle at top left, rgba(15, 76, 129, 0.10), transparent 32rem),
                linear-gradient(180deg, #f4f6f9 0%, #e7ebf0 100%);
            font-family: 'Times New Roman', Times, serif;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 5rem 1rem 3rem;
            color: #111827;
        }

        body.embedded-letter-preview {
            --embedded-scale: 1;
            min-height: auto;
            display: block;
            padding: clamp(10px, 3vw, 18px);
            background: #f1f5f9;
            overflow-x: hidden;
        }

        body.embedded-letter-preview .letter-page {
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.12);
            left: 50%;
            margin: 0;
            transform: translateX(-50%) scale(var(--embedded-scale));
            transform-origin: top center;
            transition: transform 160ms ease;
        }

        .letter-page {
            --page-width: 210mm;
            --page-height: 297mm;
            --page-pad-top: 9mm;
            --page-pad-side: 9mm;
            --page-pad-bottom: 8mm;
            --frame-pad-top: 6mm;
            --frame-pad-bottom: 5mm;
            --section-pad-side: 12mm;
            --header-pad-bottom: 5mm;
            --body-font-size: 11pt;
            --body-line-height: 1.55;
            --paragraph-gap: 8px;
            --closing-pad-top: 10px;
            --closing-pad-bottom: 4px;
            --signature-name-size: 11pt;
            --signature-detail-size: 8.8pt;
            --signature-image-height: 58px;
            --stamp-size: 90px;
            --qr-size: 70px;
            --digital-footer-font-size: 7.2pt;
            --footer-pad-top: 3.5mm;
            --footer-pad-bottom: 5mm;
            --footer-font-size: 7.1pt;
            --footer-line-height: 1.2;
            background: white;
            box-shadow: 0 18px 40px rgba(17, 24, 39, 0.18);
            width: var(--page-width);
            height: var(--page-height);
            min-height: var(--page-height);
            max-height: var(--page-height);
            position: relative;
            overflow: hidden;
            padding: var(--page-pad-top) var(--page-pad-side) var(--page-pad-bottom);
            font-family: var(--page-font-family), 'Times New Roman', Times, serif;
            direction: inherit;
        }

        .letter-frame {
            position: relative;
            z-index: 1;
            height: 100%;
            min-height: 0;
            overflow: hidden;
            display: grid;
            grid-template-rows: auto minmax(0, 1fr) auto auto auto;
            padding: var(--frame-pad-top) 0 var(--frame-pad-bottom);
            background: #fff;
        }

        .letter-frame.official-green-frame {
            border: 2.4px solid var(--official-frame-color);
            box-shadow:
                0 0 0 1px rgba(47, 142, 85, 0.22),
                inset 0 0 0 2px #fff,
                inset 0 0 0 4px var(--official-frame-color);
        }

        .letter-frame.custom-border-frame {
            border: var(--custom-frame-border, none);
        }

        .letter-frame.official-green-frame::after {
            content: "";
            position: absolute;
            pointer-events: none;
            z-index: 0;
            top: 3mm;
            right: 3mm;
            bottom: 3mm;
            left: 3mm;
            border: 1px solid var(--official-frame-color);
            opacity: 0.78;
        }

        .letter-page[data-fit-status="overflow"] {
            height: auto;
            max-height: none;
            overflow: visible;
        }

        .letter-page[data-fit-status="overflow"] .letter-frame {
            height: auto;
            min-height: calc(var(--page-height) - var(--page-pad-top) - var(--page-pad-bottom));
            overflow: visible;
        }

        .letter-page[data-fit-status="overflow"] .letter-body {
            overflow: visible;
        }

        /* Header Section */
        .letter-header {
            padding: 0 var(--section-pad-side) var(--header-pad-bottom);
            flex-shrink: 0;
        }

        .letter-header img {
            max-height: 74px;
            width: auto;
        }

        .letter-header table {
            table-layout: fixed;
        }

        .letter-header p,
        .letter-header div,
        .letter-header span,
        .letter-header td,
        .letter-header th {
            line-height: 1.18 !important;
        }

        /* Title Banner */
        .title-banner {
            background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
            color: white;
            text-align: center;
            padding: 6px 15mm;
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0 15mm 8mm 15mm;
            flex-shrink: 0;
        }

        /* Body Section */
        .letter-body {
            padding: 0 var(--section-pad-side);
            line-height: var(--body-line-height);
            text-align: justify;
            min-height: 0;
            font-size: var(--body-font-size);
            overflow: hidden;
            color: #111827;
        }

        .letter-body .content {
            min-height: 0;
        }

        .letter-body .recipient-name {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 12px;
        }

        .letter-body .greeting {
            margin-bottom: 12px;
            font-weight: bold;
        }

        .letter-body .content p {
            margin-bottom: var(--paragraph-gap);
            text-align: justify;
        }

        .letter-body .content div,
        .letter-body .content li,
        .letter-body .content td,
        .letter-body .content th,
        .letter-body .content span {
            line-height: var(--body-line-height);
            font-size: inherit;
        }

        .letter-body .content table {
            width: 100%;
            table-layout: fixed;
        }

        .letter-body .content h1,
        .letter-body .content h2,
        .letter-body .content h3,
        .letter-body .content h4,
        .letter-body .content h5,
        .letter-body .content h6 {
            margin-bottom: 8px;
            line-height: 1.25;
        }

        .letter-body .content img {
            max-width: 100%;
            height: auto;
        }

        .letter-body .content>*:last-child {
            margin-bottom: 0 !important;
        }

        /* Signature Section */
        .letter-closing {
            padding: var(--closing-pad-top) var(--section-pad-side) var(--closing-pad-bottom);
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .letter-signature {
            flex-shrink: 0;
        }

        .signature-name {
            font-weight: bold;
            font-size: var(--signature-name-size);
            margin-bottom: 3px;
        }

        .signature-details {
            font-size: var(--signature-detail-size);
            color: #333;
            line-height: 1.4;
        }

        .signature-details div {
            margin-bottom: 1px;
        }

        .signature-image {
            margin-top: 8px;
        }

        .signature-image img {
            height: var(--signature-image-height);
            max-width: 120px;
        }

        .letter-side-column {
            text-align: right;
            vertical-align: top;
            width: 35%;
        }

        .stamp-image img {
            max-width: var(--stamp-size);
            max-height: var(--stamp-size);
            height: auto;
        }

        .letter-qr {
            margin-top: 10px;
            text-align: right;
        }

        .letter-qr svg,
        .letter-qr img {
            width: var(--qr-size) !important;
            height: var(--qr-size) !important;
            max-width: var(--qr-size) !important;
            max-height: var(--qr-size) !important;
        }

        .letter-qr p,
        .letter-qr span,
        .letter-qr div {
            font-size: 6.8pt !important;
            line-height: 1.15 !important;
            margin: 1px 0 0 !important;
        }

        /* Footer Section */
        .letter-footer {
            margin: 0 var(--section-pad-side);
            padding: var(--footer-pad-top) 0 var(--footer-pad-bottom);
            border-top: 1px solid #d1d5db;
            font-size: var(--footer-font-size);
            flex-shrink: 0;
            background: transparent;
            color: #374151;
            line-height: var(--footer-line-height);
            overflow-wrap: anywhere;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .letter-footer * {
            font-size: inherit !important;
            line-height: inherit !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            overflow-wrap: anywhere !important;
        }

        .letter-footer table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .letter-digital-footer {
            margin: 0 var(--section-pad-side);
            padding: 6px 10px;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            background: #f8fafc;
            color: #4b5563;
            font-size: var(--digital-footer-font-size);
            line-height: 1.35;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .letter-digital-footer strong {
            color: #111827;
        }

        .letter-digital-footer a {
            color: #4f46e5;
            text-decoration: none;
            word-break: break-all;
        }

        .letter-watermark {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 72pt;
            color: rgba(203, 213, 225, 0.18);
            font-weight: 700;
            letter-spacing: 3px;
            transform: rotate(-35deg);
            pointer-events: none;
            user-select: none;
            z-index: 0;
            text-transform: uppercase;
            text-align: center;
        }

        .letter-header,
        .letter-body,
        .letter-closing,
        .letter-digital-footer,
        .letter-footer {
            position: relative;
            z-index: 1;
        }

        /* Toolbar */
        .toolbar {
            position: fixed;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
            align-items: flex-end;
            flex-direction: column;
            z-index: 100;
        }

        .toolbar-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn {
            padding: 0.625rem 1.125rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.8125rem;
            text-decoration: none;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .btn-primary {
            background: linear-gradient(135deg, rgba(30, 88, 164, 0.96), rgba(54, 96, 217, 0.96));
            color: white;
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.9);
            color: #374151;
            border: 1px solid rgba(209, 213, 219, 0.8);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Print Styles */
        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body {
                background: white !important;
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .letter-page {
                box-shadow: none;
                width: var(--page-width) !important;
                height: var(--page-height) !important;
                min-height: var(--page-height) !important;
                max-height: var(--page-height) !important;
                margin: 0;
                overflow: hidden !important;
                page-break-after: auto;
                break-after: auto;
            }

            .letter-frame {
                height: 100% !important;
                overflow: hidden !important;
            }

            .letter-page[data-fit-status="overflow"] {
                height: auto !important;
                max-height: none !important;
                overflow: visible !important;
            }

            .letter-page[data-fit-status="overflow"] .letter-frame {
                height: auto !important;
                overflow: visible !important;
            }

            .no-print {
                display: none !important;
            }

            .title-banner {
                background: #b91c1c !important;
                -webkit-print-color-adjust: exact !important;
            }

            .letter-footer,
            .letter-closing,
            .letter-header,
            .letter-frame {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

        /* Scale for browser display */
        @media screen {
            .letter-page {
                transform-origin: top center;
            }
        }

        /* Mobile/Tablet Responsiveness */
        @media screen and (max-width: 220mm) {
            body {
                padding: 10px;
                justify-content: flex-start;
                overflow-x: auto;
                /* Allow horizontal scroll if really needed, but try to scale first */
            }

            .letter-page {
                /* Scale down the entire A4 page to fit screen width */
                width: 210mm;
                min-width: 210mm;
                margin-left: auto;
                margin-right: auto;
                transform: scale(0.9);
                /* Default slight scale down for laptops */
                margin-top: 20px;
            }

            .toolbar {
                position: fixed;
                top: auto;
                bottom: 20px;
                right: 20px;
                left: 20px;
                align-items: stretch;
            }

            .toolbar-actions {
                flex-direction: column-reverse;
                width: 100%;
            }

            .btn {
                justify-content: center;
            }
        }

        @media screen and (max-width: 800px) {
            .letter-page {
                transform: scale(0.6);
                /* Scale more for tablets */
                margin-top: 0;
            }
        }

        @media screen and (max-width: 500px) {
            .letter-page {
                transform: scale(0.40);
                /* Scale more for mobile */
                margin-top: -100px;
                /* Counteract empty space from scaling */
                margin-bottom: -100px;
            }

            body {
                padding: 0;
                overflow-x: hidden;
            }

            .toolbar {
                right: 10px;
                left: 10px;
                bottom: 10px;
            }
        }
    </style>
</head>

<body class="{{ $embedded ? 'embedded-letter-preview' : '' }}">

    @unless($embedded)
        <div class="toolbar no-print">
            <div class="toolbar-actions">
                <a href="{{ url('/track/' . $request->tracking_id) }}" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    Back
                </a>
                <button onclick="printOfficialLetter()" class="btn btn-secondary" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9" />
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <rect x="6" y="14" width="12" height="8" />
                    </svg>
                    Print Preview
                </button>
                <a href="{{ route('public.letter.pdf', ['tracking_id' => $request->tracking_id, 'download' => 1]) }}"
                    class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" x2="12" y1="15" y2="3" />
                    </svg>
                    Download Official PDF
                </a>
            </div>
        </div>
    @endunless

    @php
        $customFrameStyle = '';
        if (!$officialFrameEnabled && isset($layout['border']['enabled']) && $layout['border']['enabled']) {
            $allowedBorderStyles = ['solid', 'double', 'dashed', 'dotted'];
            $borderWidth = max(1, min((int) ($layout['border']['width'] ?? 2), 4));
            $borderLineStyle = in_array(($layout['border']['style'] ?? 'solid'), $allowedBorderStyles, true)
                ? $layout['border']['style']
                : 'solid';
            $borderColor = $safeFrameColor($layout['border']['color'] ?? '#2f8e55');
            $customFrameStyle = "--custom-frame-border: {$borderWidth}px {$borderLineStyle} {$borderColor};";
        }

        $pageClass = 'letter-page';
        $frameClass = 'letter-frame'
            . ($officialFrameEnabled ? ' official-green-frame' : '')
            . ($customFrameStyle !== '' ? ' custom-border-frame' : '');
    @endphp

    <div class="{{ $pageClass }}" style="{{ $pageStyle }}">
        <div class="{{ $frameClass }}" style="{{ $customFrameStyle }}">
            @if($watermarkEnabled)
                <div class="letter-watermark">{{ $watermarkText }}</div>
            @endif

            <!-- Header -->
            <div class="letter-header">
                {!! $header !!}
            </div>

            <!-- Title Banner Removed (User-Controlled via Template) -->
            {{-- <div class="title-banner">
                RECOMMENDATION LETTER
            </div> --}}

            <!-- Body -->
            <div class="letter-body">
                <!-- Body Content from Template -->
                <div class="content">
                    {!! $body !!}
                </div>
            </div>

            <!-- Signature, Stamp, QR -->
            <div class="letter-closing">
                <div class="letter-signature">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="vertical-align: top; width: 65%;">
                                <!-- Name & Title -->
                                <div class="signature-name">{{ $signature['name'] }}</div>

                                @if(!empty($signature['title']))
                                    <div class="signature-details" style="color: #333; font-weight: 500;">
                                        {{ $signature['title'] }}
                                    </div>
                                @endif

                                <!-- Dept & Institution -->
                                @if((!empty($signature['institution'])) || (!empty($signature['department'])))
                                    <div class="signature-details" style="margin-top: 5px;">
                                        @if(!empty($signature['department']))
                                            {{ $signature['department'] }}<br>
                                        @endif
                                        @if(!empty($signature['institution']))
                                            {{ $signature['institution'] }}
                                        @endif
                                    </div>
                                @endif

                                <!-- Contact Info -->
                                @if((!empty($signature['email'])) || (!empty($signature['phone'])))
                                    <div class="signature-details" style="margin-top: 8px;">
                                        @if(!empty($signature['email']))
                                            <div>Email: {{ $signature['email'] }}</div>
                                        @endif
                                        @if(!empty($signature['phone']))
                                            <div>Phone: {{ $signature['phone'] }}</div>
                                        @endif
                                    </div>
                                @endif

                                <!-- Signature Image (Now Below) -->
                                @if(!empty($signature['image']))
                                    <div class="signature-image" style="margin-top: 15px;">
                                        <img src="{{ $signature['image'] }}" alt="Signature"
                                            style="height: auto; max-height: 60px; max-width: 150px;">
                                    </div>
                                @endif
                            </td>

                            <!-- Right Column: Stamp -->
                            <td class="letter-side-column">
                                @if(!empty($signature['stamp']))
                                    <div class="stamp-image" style="margin-bottom: 12px;">
                                        <img src="{{ $signature['stamp'] }}" alt="Official Stamp"
                                            style="height: auto;">
                                    </div>
                                @endif

                                @if(!empty($qrCode))
                                    <div class="letter-qr">
                                        {!! $qrCode !!}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($showDigitalFooter && $verifyUrl)
                <div class="letter-digital-footer">
                    <strong>Digitally verified document</strong>
                    <span>Reference ID: {{ $request->tracking_id }}</span>
                    <span>Verify this document:
                        <a href="{{ $verifyUrl }}">{{ $verifyUrl }}</a>
                    </span>
                </div>
            @endif

            <!-- Footer -->
            <div class="letter-footer">
                {!! $footer !!}
            </div>
        </div>
    </div>

    <script>
        function adjustCssVariable(element, variableName, amount, unit, minimum) {
            const current = parseFloat(getComputedStyle(element).getPropertyValue(variableName));
            if (Number.isNaN(current)) {
                return;
            }

            const next = Math.max(minimum, current - amount);
            element.style.setProperty(variableName, `${next}${unit}`);
        }

        function adjustUnitlessVariable(element, variableName, amount, minimum) {
            const current = parseFloat(getComputedStyle(element).getPropertyValue(variableName));
            if (Number.isNaN(current)) {
                return;
            }

            const next = Math.max(minimum, current - amount);
            element.style.setProperty(variableName, `${next}`);
        }

        let letterFitDefaults = null;

        function captureLetterDefaults(page) {
            const style = getComputedStyle(page);
            letterFitDefaults = {
                '--page-pad-top': style.getPropertyValue('--page-pad-top').trim(),
                '--page-pad-side': style.getPropertyValue('--page-pad-side').trim(),
                '--page-pad-bottom': style.getPropertyValue('--page-pad-bottom').trim(),
                '--frame-pad-top': style.getPropertyValue('--frame-pad-top').trim(),
                '--frame-pad-bottom': style.getPropertyValue('--frame-pad-bottom').trim(),
                '--section-pad-side': style.getPropertyValue('--section-pad-side').trim(),
                '--header-pad-bottom': style.getPropertyValue('--header-pad-bottom').trim(),
                '--body-font-size': style.getPropertyValue('--body-font-size').trim(),
                '--body-line-height': style.getPropertyValue('--body-line-height').trim(),
                '--paragraph-gap': style.getPropertyValue('--paragraph-gap').trim(),
                '--closing-pad-top': style.getPropertyValue('--closing-pad-top').trim(),
                '--closing-pad-bottom': style.getPropertyValue('--closing-pad-bottom').trim(),
                '--signature-name-size': style.getPropertyValue('--signature-name-size').trim(),
                '--signature-detail-size': style.getPropertyValue('--signature-detail-size').trim(),
                '--signature-image-height': style.getPropertyValue('--signature-image-height').trim(),
                '--stamp-size': style.getPropertyValue('--stamp-size').trim(),
                '--qr-size': style.getPropertyValue('--qr-size').trim(),
                '--digital-footer-font-size': style.getPropertyValue('--digital-footer-font-size').trim(),
                '--footer-pad-top': style.getPropertyValue('--footer-pad-top').trim(),
                '--footer-pad-bottom': style.getPropertyValue('--footer-pad-bottom').trim(),
                '--footer-font-size': style.getPropertyValue('--footer-font-size').trim(),
                '--footer-line-height': style.getPropertyValue('--footer-line-height').trim(),
            };
        }

        function isLetterOverflowing(page) {
            const frame = page.querySelector('.letter-frame');
            const body = page.querySelector('.letter-body');
            const content = page.querySelector('.letter-body .content');
            const pageOverflow = page.scrollHeight > page.clientHeight + 2;
            const frameOverflow = frame ? frame.scrollHeight > frame.clientHeight + 2 : false;
            const bodyOverflow = body && content ? content.scrollHeight > body.clientHeight + 2 : false;

            return pageOverflow || frameOverflow || bodyOverflow;
        }

        function fitLetterToSinglePage() {
            const page = document.querySelector('.letter-page');
            const frame = page?.querySelector('.letter-frame');
            if (!page) {
                return;
            }

            if (!letterFitDefaults) {
                captureLetterDefaults(page);
            }

            Object.entries(letterFitDefaults).forEach(([key, value]) => page.style.setProperty(key, value));
            delete page.dataset.fitStatus;
            if (frame) {
                delete frame.dataset.fitStatus;
            }

            let guard = 0;
            while (isLetterOverflowing(page) && guard < 30) {
                adjustCssVariable(page, '--body-font-size', 0.18, 'pt', 9);
                adjustUnitlessVariable(page, '--body-line-height', 0.025, 1.25);
                adjustCssVariable(page, '--paragraph-gap', 0.7, 'px', 3);
                adjustCssVariable(page, '--page-pad-top', 0.12, 'mm', 6);
                adjustCssVariable(page, '--page-pad-side', 0.12, 'mm', 7);
                adjustCssVariable(page, '--page-pad-bottom', 0.12, 'mm', 6);
                adjustCssVariable(page, '--frame-pad-top', 0.18, 'mm', 4);
                adjustCssVariable(page, '--frame-pad-bottom', 0.15, 'mm', 3);
                adjustCssVariable(page, '--section-pad-side', 0.2, 'mm', 7);
                adjustCssVariable(page, '--header-pad-bottom', 0.2, 'mm', 2);
                adjustCssVariable(page, '--closing-pad-top', 1, 'px', 4);
                adjustCssVariable(page, '--closing-pad-bottom', 1, 'px', 1);
                adjustCssVariable(page, '--signature-name-size', 0.12, 'pt', 9.2);
                adjustCssVariable(page, '--signature-detail-size', 0.12, 'pt', 6.9);
                adjustCssVariable(page, '--signature-image-height', 1.5, 'px', 38);
                adjustCssVariable(page, '--stamp-size', 2.4, 'px', 58);
                adjustCssVariable(page, '--qr-size', 1.8, 'px', 52);
                adjustCssVariable(page, '--digital-footer-font-size', 0.12, 'pt', 5.8);
                adjustCssVariable(page, '--footer-pad-top', 0.15, 'mm', 1.4);
                adjustCssVariable(page, '--footer-pad-bottom', 0.15, 'mm', 2);
                adjustCssVariable(page, '--footer-font-size', 0.12, 'pt', 5.8);
                adjustUnitlessVariable(page, '--footer-line-height', 0.02, 1);
                guard += 1;
            }

            const isOverflowing = isLetterOverflowing(page);
            page.dataset.fitStatus = isOverflowing ? 'overflow' : 'fits';
            if (frame) {
                frame.dataset.fitStatus = page.dataset.fitStatus;
            }
            document.dispatchEvent(new CustomEvent('letter-fit-ready', {
                detail: {
                    status: page.dataset.fitStatus,
                    attempts: guard
                }
            }));
        }

        function printOfficialLetter() {
            fitLetterToSinglePage();
            requestAnimationFrame(() => window.print());
        }

        function fitEmbeddedPreviewToViewport() {
            if (!document.body.classList.contains('embedded-letter-preview')) {
                return;
            }

            const page = document.querySelector('.letter-page');
            if (!page) {
                return;
            }

            const bodyStyle = getComputedStyle(document.body);
            const horizontalPadding = (parseFloat(bodyStyle.paddingLeft) || 0) + (parseFloat(bodyStyle.paddingRight) || 0);
            const verticalPadding = (parseFloat(bodyStyle.paddingTop) || 0) + (parseFloat(bodyStyle.paddingBottom) || 0);
            const availableWidth = Math.max(280, document.documentElement.clientWidth - horizontalPadding);
            const pageWidth = page.offsetWidth || page.getBoundingClientRect().width || 1;
            const pageHeight = page.offsetHeight || page.getBoundingClientRect().height || 1;
            const scale = Math.min(1, Math.max(0.34, availableWidth / pageWidth));

            document.body.style.setProperty('--embedded-scale', scale.toFixed(4));
            page.style.marginBottom = `${Math.round(pageHeight * (scale - 1))}px`;
            document.documentElement.style.minHeight = `${Math.ceil((pageHeight * scale) + verticalPadding)}px`;
            document.body.style.minHeight = `${Math.ceil((pageHeight * scale) + verticalPadding)}px`;
            document.dispatchEvent(new CustomEvent('letter-preview-scale-ready', {
                detail: {
                    scale,
                    pageWidth,
                    availableWidth
                }
            }));
        }

        window.addEventListener('load', function() {
            const page = document.querySelector('.letter-page');
            if (page) {
                captureLetterDefaults(page);
            }

            fitLetterToSinglePage();
            fitEmbeddedPreviewToViewport();
        });
        window.addEventListener('beforeprint', fitLetterToSinglePage);
        window.addEventListener('resize', function() {
            window.requestAnimationFrame(fitEmbeddedPreviewToViewport);
        });
    </script>
</body>

</html>
