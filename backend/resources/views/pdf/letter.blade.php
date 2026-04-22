@php
    $margins = $layout['margins'] ?? [];
    $pageMargins = [
        'top' => max(14, min((int) ($margins['top'] ?? 20), 24)),
        'right' => max(14, min((int) ($margins['right'] ?? 20), 24)),
        'bottom' => max(14, min((int) ($margins['bottom'] ?? 20), 24)),
        'left' => max(14, min((int) ($margins['left'] ?? 20), 24)),
    ];
    $fontSize = max(10.5, min((float) ($layout['fontSize'] ?? 12), 12));
    $fontFamily = $layout['fontFamily'] ?? 'DejaVu Sans, sans-serif';
    $direction = $layout['direction'] ?? 'ltr';
    $language = $layout['language'] ?? ($direction === 'rtl' ? 'ar' : 'en');
    $watermarkConfig = $layout['watermark'] ?? [];
    $watermarkEnabled = (bool) ($watermarkConfig['enabled'] ?? false);
    $watermarkText = !empty($watermarkConfig['text']) ? $watermarkConfig['text'] : ($request->tracking_id ?? 'OFFICIAL COPY');
    $showDigitalFooter = $layout['footer']['enabled'] ?? true;
@endphp
<!DOCTYPE html>
<html lang="{{ $language }}" dir="{{ $direction }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Recommendation Letter - {{ $request->tracking_id }}</title>
    <style>
        @page {
            size: A4;
            margin: {{ $pageMargins['top'] }}px {{ $pageMargins['right'] }}px {{ $pageMargins['bottom'] }}px {{ $pageMargins['left'] }}px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: {{ $fontFamily }};
            font-size: {{ $fontSize }}pt;
            line-height: 1.45;
            color: #000;
            direction: {{ $direction }};
            margin: 0;
            padding: 0;
        }

        .page-container {
            width: 100%;
            position: static;
            @if(isset($layout['border']['enabled']) && $layout['border']['enabled'])
                border: {{ max(1, min((int) ($layout['border']['width'] ?? 2), 3)) }}px {{ $layout['border']['style'] ?? 'solid' }} {{ $layout['border']['color'] ?? '#057f3a' }};
                padding: 12px;
            @endif
        }

        .header {
            margin-bottom: 14px;
        }

        .header img {
            max-height: 80px;
            width: auto;
        }

        .header table,
        .body-content table,
        .signature-table,
        .footer table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .body-content {
            margin-bottom: 14px;
        }

        .body-content p {
            margin-bottom: 8px;
        }

        .body-content h1,
        .body-content h2,
        .body-content h3,
        .body-content h4,
        .body-content h5,
        .body-content h6 {
            margin-bottom: 8px;
        }

        .body-content img,
        .signature-image img,
        .stamp-image img {
            max-width: 100%;
            height: auto;
        }

        .signature-section {
            margin-top: 14px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .signature-left {
            width: 68%;
            vertical-align: top;
        }

        .signature-right {
            width: 32%;
            vertical-align: top;
            text-align: right;
        }

        .signature-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 2px;
        }

        .signature-title {
            font-size: 10pt;
            color: #333;
        }

        .signature-details {
            font-size: 9pt;
            color: #555;
            margin-top: 5px;
            line-height: 1.3;
        }

        .signature-image {
            margin-top: 10px;
        }

        .signature-image img {
            max-width: 150px;
            max-height: 70px;
        }

        .stamp-image img {
            max-width: 96px;
            max-height: 96px;
        }

        .qr-block {
            margin-top: 8px;
            text-align: right;
        }

        .qr-block svg,
        .qr-block img {
            width: 72px !important;
            height: 72px !important;
            max-width: 72px !important;
            max-height: 72px !important;
        }

        .qr-block p,
        .qr-block span,
        .qr-block div {
            font-size: 6.8pt !important;
            line-height: 1.1 !important;
            margin-top: 1px !important;
        }

        .digital-footer {
            margin-top: 10px;
            border-top: 1px solid #e5e7eb;
            padding: 8px;
            font-size: 7.1pt;
            color: #666;
            text-align: center;
            background-color: #f9fafb;
            border-radius: 4px;
            line-height: 1.2;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .digital-footer a {
            color: #666;
            text-decoration: none;
        }

        .footer {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            line-height: 1.15;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .footer * {
            font-size: inherit !important;
            line-height: inherit !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72pt;
            color: rgba(200, 200, 200, 0.12);
            font-weight: bold;
            z-index: -1;
            white-space: nowrap;
            pointer-events: none;
            user-select: none;
        }
    </style>
</head>

<body>
    @if($watermarkEnabled)
        <div class="watermark">
            {{ $watermarkText }}
        </div>
    @endif

    <div class="page-container">
        @if($header)
            <div class="header">
                {!! $header !!}
            </div>
        @endif

        <div class="body-content">
            {!! $body !!}
        </div>

        @if(($signature['name'] ?? null) || ($signature['image'] ?? null) || !empty($qrCode))
            <div class="signature-section">
                <table class="signature-table">
                    <tr>
                        <td class="signature-left">
                            @if(!empty($signature['name']))
                                <div class="signature-name">{{ $signature['name'] }}</div>
                            @endif

                            @if(!empty($signature['title']))
                                <div class="signature-title">{{ $signature['title'] }}</div>
                            @endif

                            @if((!empty($signature['institution'])) || (!empty($signature['department'])))
                                <div class="signature-details">
                                    @if(!empty($signature['department']))
                                        {{ $signature['department'] }}<br>
                                    @endif
                                    @if(!empty($signature['institution']))
                                        {{ $signature['institution'] }}
                                    @endif
                                </div>
                            @endif

                            @if((!empty($signature['email'])) || (!empty($signature['phone'])))
                                <div class="signature-details">
                                    @if(!empty($signature['email']))
                                        Email: {{ $signature['email'] }}<br>
                                    @endif
                                    @if(!empty($signature['phone']))
                                        Phone: {{ $signature['phone'] }}
                                    @endif
                                </div>
                            @endif

                            @if(!empty($signature['image']))
                                <div class="signature-image">
                                    <img src="{{ $signature['image'] }}" alt="Signature">
                                </div>
                            @endif
                        </td>
                        <td class="signature-right">
                            @if(!empty($signature['stamp']))
                                <div class="stamp-image">
                                    <img src="{{ $signature['stamp'] }}" alt="Official Stamp">
                                </div>
                            @endif

                            @if(!empty($qrCode))
                                <div class="qr-block">
                                    {!! $qrCode !!}
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        @endif

        @if($showDigitalFooter)
            <div class="digital-footer">
                <strong>DIGITALLY VERIFIED DOCUMENT</strong><br>
                Reference ID: {{ $request->tracking_id }}<br>
                Verify this document at:
                <a href="{{ route('public.verify', $request->verify_token) }}">{{ route('public.verify', $request->verify_token) }}</a>
            </div>
        @endif

        @if($footer)
            <div class="footer">
                {!! $footer !!}
            </div>
        @endif
    </div>
</body>

</html>
