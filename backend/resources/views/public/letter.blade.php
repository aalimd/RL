@php
    $margins = $layout['margins'] ?? [];
    $marginTop = max(10, min((float) ($margins['top'] ?? 20), 35));
    $marginSide = max(10, min((((float) ($margins['left'] ?? 20)) + ((float) ($margins['right'] ?? 20))) / 2, 30));
    $marginBottom = max(10, min((float) ($margins['bottom'] ?? 20), 35));
    $fontSize = max(10.5, min((float) ($layout['fontSize'] ?? 12), 16));
    $fontFamily = $layout['fontFamily'] ?? "'Times New Roman', serif";
    $language = $layout['language'] ?? 'en';
    $direction = $layout['direction'] ?? ($language === 'ar' ? 'rtl' : 'ltr');
    $watermarkConfig = $layout['watermark'] ?? [];
    $watermarkEnabled = (bool) ($watermarkConfig['enabled'] ?? false);
    $watermarkText = !empty($watermarkConfig['text']) ? $watermarkConfig['text'] : ($request->tracking_id ?? 'OFFICIAL COPY');
    $showDigitalFooter = (bool) ($layout['footer']['enabled'] ?? true);
    $verifyUrl = $request->verify_token ? route('public.verify', $request->verify_token) : null;
    $pageStyle = implode(' ', [
        '--header-pad-top: ' . max(7, min($marginTop * 0.5, 16)) . 'mm;',
        '--header-pad-side: ' . $marginSide . 'mm;',
        '--header-pad-bottom: ' . max(3, min($marginTop * 0.25, 8)) . 'mm;',
        '--body-pad-side: ' . max(10, min($marginSide + 1.5, 30)) . 'mm;',
        '--body-font-size: ' . $fontSize . 'pt;',
        '--body-line-height: ' . ($direction === 'rtl' ? '1.65' : '1.55') . ';',
        '--paragraph-gap: 8px;',
        '--closing-pad-top: 10px;',
        '--closing-pad-side: ' . $marginSide . 'mm;',
        '--closing-pad-bottom: 4px;',
        '--signature-name-size: ' . max(10.5, min($fontSize + 0.3, 13)) . 'pt;',
        '--signature-detail-size: ' . max(8.2, min($fontSize - 2, 10)) . 'pt;',
        '--signature-image-height: 58px;',
        '--stamp-size: 90px;',
        '--footer-pad-top: 3mm;',
        '--footer-pad-side: ' . $marginSide . 'mm;',
        '--footer-pad-bottom: ' . max(3, min($marginBottom * 0.28, 8)) . 'mm;',
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
            background: #e5e7eb;
            font-family: 'Times New Roman', Times, serif;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 3rem 1rem;
            color: #111827;
        }

        .letter-page {
            --page-width: 210mm;
            --page-height: 295.5mm;
            --header-pad-top: 10mm;
            --header-pad-side: 12mm;
            --header-pad-bottom: 5mm;
            --body-pad-side: 14mm;
            --body-font-size: 11pt;
            --body-line-height: 1.55;
            --paragraph-gap: 8px;
            --closing-pad-top: 10px;
            --closing-pad-side: 14mm;
            --closing-pad-bottom: 4px;
            --signature-name-size: 11pt;
            --signature-detail-size: 8.8pt;
            --signature-image-height: 58px;
            --stamp-size: 90px;
            --footer-pad-top: 3.5mm;
            --footer-pad-side: 14mm;
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
            display: grid;
            grid-template-rows: auto minmax(0, 1fr) auto auto;
            font-family: var(--page-font-family), 'Times New Roman', Times, serif;
            direction: inherit;
        }

        /* Header Section */
        .letter-header {
            padding: var(--header-pad-top) var(--header-pad-side) var(--header-pad-bottom) var(--header-pad-side);
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
            padding: 0 var(--body-pad-side);
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
            padding: var(--closing-pad-top) var(--closing-pad-side) var(--closing-pad-bottom) var(--closing-pad-side);
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
            width: 70px !important;
            height: 70px !important;
            max-width: 70px !important;
            max-height: 70px !important;
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
            padding: var(--footer-pad-top) var(--footer-pad-side) var(--footer-pad-bottom) var(--footer-pad-side);
            border-top: 1px solid #d1d5db;
            font-size: var(--footer-font-size);
            flex-shrink: 0;
            background: #fff;
            color: #374151;
            line-height: var(--footer-line-height);
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .letter-footer * {
            font-size: inherit !important;
            line-height: inherit !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        .letter-footer table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .letter-digital-footer {
            margin: 0 var(--footer-pad-side);
            padding: 8px 12px;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            background: #f8fafc;
            color: #4b5563;
            font-size: 7.6pt;
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

        .toolbar-note {
            max-width: 270px;
            padding: 0.75rem 0.9rem;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.92);
            color: #374151;
            font-size: 0.8rem;
            line-height: 1.4;
            border: 1px solid rgba(209, 213, 219, 0.85);
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .toolbar-note strong {
            color: #111827;
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
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.9), rgba(99, 102, 241, 0.9));
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
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

            .no-print {
                display: none !important;
            }

            .title-banner {
                background: #b91c1c !important;
                -webkit-print-color-adjust: exact !important;
            }

            .letter-footer,
            .letter-closing,
            .letter-header {
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
                align-items: stretch;
            }

            .toolbar-actions {
                flex-direction: column-reverse;
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
                bottom: 10px;
            }
        }
    </style>
</head>

<body>

    <div class="toolbar no-print">
        <div class="toolbar-actions">
            <a href="{{ url('/track/' . $request->tracking_id) }}" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Back
            </a>
            <button onclick="printOfficialLetter()" class="btn btn-primary" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9" />
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                    <rect x="6" y="14" width="12" height="8" />
                </svg>
                Print / Save
            </button>
        </div>
        <div class="toolbar-note">
            <strong>Best result:</strong> use <em>Print / Save</em> to open your browser's print dialog, then choose
            your printer or <em>Save as PDF</em>.
        </div>
    </div>

    @php
        $borderStyle = isset($layout['border']['enabled']) && $layout['border']['enabled']
            ? "border: {$layout['border']['width']}px {$layout['border']['style']} {$layout['border']['color']};"
            : "";
    @endphp

    <div class="letter-page" style="{{ $borderStyle }} {{ $pageStyle }}">
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
                '--header-pad-top': style.getPropertyValue('--header-pad-top').trim(),
                '--header-pad-side': style.getPropertyValue('--header-pad-side').trim(),
                '--header-pad-bottom': style.getPropertyValue('--header-pad-bottom').trim(),
                '--body-pad-side': style.getPropertyValue('--body-pad-side').trim(),
                '--body-font-size': style.getPropertyValue('--body-font-size').trim(),
                '--body-line-height': style.getPropertyValue('--body-line-height').trim(),
                '--paragraph-gap': style.getPropertyValue('--paragraph-gap').trim(),
                '--closing-pad-top': style.getPropertyValue('--closing-pad-top').trim(),
                '--closing-pad-side': style.getPropertyValue('--closing-pad-side').trim(),
                '--closing-pad-bottom': style.getPropertyValue('--closing-pad-bottom').trim(),
                '--signature-name-size': style.getPropertyValue('--signature-name-size').trim(),
                '--signature-detail-size': style.getPropertyValue('--signature-detail-size').trim(),
                '--signature-image-height': style.getPropertyValue('--signature-image-height').trim(),
                '--stamp-size': style.getPropertyValue('--stamp-size').trim(),
                '--footer-pad-top': style.getPropertyValue('--footer-pad-top').trim(),
                '--footer-pad-side': style.getPropertyValue('--footer-pad-side').trim(),
                '--footer-pad-bottom': style.getPropertyValue('--footer-pad-bottom').trim(),
                '--footer-font-size': style.getPropertyValue('--footer-font-size').trim(),
                '--footer-line-height': style.getPropertyValue('--footer-line-height').trim(),
            };
        }

        function fitLetterToSinglePage() {
            const page = document.querySelector('.letter-page');
            if (!page) {
                return;
            }

            if (!letterFitDefaults) {
                captureLetterDefaults(page);
            }

            Object.entries(letterFitDefaults).forEach(([key, value]) => page.style.setProperty(key, value));

            let guard = 0;
            while (page.scrollHeight > page.clientHeight + 2 && guard < 18) {
                adjustCssVariable(page, '--body-font-size', 0.2, 'pt', 9.5);
                adjustUnitlessVariable(page, '--body-line-height', 0.03, 1.3);
                adjustCssVariable(page, '--paragraph-gap', 1, 'px', 4);
                adjustCssVariable(page, '--header-pad-top', 0.4, 'mm', 7.5);
                adjustCssVariable(page, '--header-pad-bottom', 0.3, 'mm', 2.5);
                adjustCssVariable(page, '--body-pad-side', 0.4, 'mm', 10);
                adjustCssVariable(page, '--closing-pad-top', 1, 'px', 4);
                adjustCssVariable(page, '--closing-pad-bottom', 1, 'px', 1);
                adjustCssVariable(page, '--signature-name-size', 0.15, 'pt', 9.5);
                adjustCssVariable(page, '--signature-detail-size', 0.15, 'pt', 7.2);
                adjustCssVariable(page, '--signature-image-height', 2, 'px', 42);
                adjustCssVariable(page, '--stamp-size', 3, 'px', 68);
                adjustCssVariable(page, '--footer-pad-top', 0.2, 'mm', 2);
                adjustCssVariable(page, '--footer-pad-bottom', 0.2, 'mm', 3);
                adjustCssVariable(page, '--footer-font-size', 0.15, 'pt', 6.1);
                adjustUnitlessVariable(page, '--footer-line-height', 0.03, 1.05);
                guard += 1;
            }
        }

        function printOfficialLetter() {
            fitLetterToSinglePage();
            requestAnimationFrame(() => window.print());
        }

        window.addEventListener('load', function() {
            const page = document.querySelector('.letter-page');
            if (page) {
                captureLetterDefaults(page);
            }

            fitLetterToSinglePage();
        });
        window.addEventListener('beforeprint', fitLetterToSinglePage);
    </script>
</body>

</html>
