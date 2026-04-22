@php
    $margins = $layout['margins'] ?? [];
    $pageMargins = [
        'top' => max(14, min((int) ($margins['top'] ?? 18), 22)),
        'right' => max(14, min((int) ($margins['right'] ?? 18), 22)),
        'bottom' => max(14, min((int) ($margins['bottom'] ?? 18), 22)),
        'left' => max(14, min((int) ($margins['left'] ?? 18), 22)),
    ];
    $fontSize = max(10.5, min((float) ($layout['fontSize'] ?? 11), 12));
    $direction = $layout['direction'] ?? 'ltr';
    $borderEnabled = (bool) ($layout['border']['enabled'] ?? false);
    $borderWidth = max(1, min((int) ($layout['border']['width'] ?? 2), 3));
    $borderColor = $layout['border']['color'] ?? '#1f2937';
    $watermarkConfig = $layout['watermark'] ?? [];
    $watermarkEnabled = (bool) ($watermarkConfig['enabled'] ?? false);
    $watermarkText = !empty($watermarkConfig['text']) ? $watermarkConfig['text'] : ($request->tracking_id ?? 'OFFICIAL COPY');
    $showDigitalFooter = $layout['footer']['enabled'] ?? true;
@endphp
<!DOCTYPE html>
<html lang="en" dir="{{ $direction }}">

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
            font-family: 'DejaVu Sans', sans-serif;
            font-size: {{ $fontSize }}pt;
            line-height: 1.45;
            color: #111827;
            direction: {{ $direction }};
            margin: 0;
            padding: 0;
        }

        .page-container {
            width: 100%;
            position: static;
            page-break-after: avoid;
            @if($borderEnabled)
                border: {{ $borderWidth }}px solid {{ $borderColor }};
                padding: 10px 12px 8px;
            @endif
        }

        .header {
            margin-bottom: 10px;
        }

        .header img {
            max-height: 74px;
            width: auto;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .header p,
        .header div,
        .header span,
        .header td,
        .header th {
            line-height: 1.18;
        }

        .body-content {
            color: #111827;
            line-height: 1.45;
        }

        .body-content p {
            margin-bottom: 7px;
            text-align: justify;
        }

        .body-content h1,
        .body-content h2,
        .body-content h3,
        .body-content h4,
        .body-content h5,
        .body-content h6 {
            margin-bottom: 8px;
            line-height: 1.25;
        }

        .body-content div,
        .body-content li,
        .body-content td,
        .body-content th,
        .body-content span {
            font-size: inherit;
            line-height: inherit;
        }

        .body-content table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .body-content img {
            max-width: 100%;
            height: auto;
        }

        .closing-section {
            margin-top: 8px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .signature-left {
            width: 65%;
            vertical-align: top;
        }

        .signature-right {
            width: 35%;
            vertical-align: top;
            text-align: right;
        }

        .signature-name {
            font-weight: 700;
            font-size: 11pt;
            margin-bottom: 2px;
        }

        .signature-title {
            font-size: 9.2pt;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .signature-details {
            font-size: 8.2pt;
            color: #374151;
            line-height: 1.32;
            margin-top: 4px;
        }

        .signature-image {
            margin-top: 8px;
        }

        .signature-image img {
            max-width: 140px;
            max-height: 58px;
            height: auto;
        }

        .stamp-image img {
            max-width: 86px;
            max-height: 86px;
            height: auto;
        }

        .qr-block {
            margin-top: 6px;
            text-align: right;
        }

        .qr-block svg,
        .qr-block img {
            width: 70px !important;
            height: 70px !important;
            max-width: 70px !important;
            max-height: 70px !important;
        }

        .qr-block p,
        .qr-block span,
        .qr-block div {
            margin-top: 1px !important;
            font-size: 6.8pt !important;
            line-height: 1.1 !important;
        }

        .footer-block {
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #d1d5db;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .verification-strip {
            padding: 6px 8px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            font-size: 7.4pt;
            line-height: 1.2;
            color: #374151;
            text-align: center;
        }

        .verification-strip strong {
            color: #111827;
        }

        .verification-strip a {
            color: #374151;
            text-decoration: none;
        }

        .custom-footer {
            margin-top: 4px;
            font-size: 7.5pt;
            line-height: 1.18;
            color: #374151;
        }

        .custom-footer * {
            font-size: inherit !important;
            line-height: inherit !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        .custom-footer table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72pt;
            color: rgba(148, 163, 184, 0.14);
            font-weight: 700;
            z-index: -1;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    @if($watermarkEnabled)
        <div class="watermark">{{ $watermarkText }}</div>
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
            <div class="closing-section">
                <table class="signature-table">
                    <tr>
                        <td class="signature-left">
                            @if(!empty($signature['name']))
                                <div class="signature-name">{{ $signature['name'] }}</div>
                            @endif

                            @if(!empty($signature['title']))
                                <div class="signature-title">{{ $signature['title'] }}</div>
                            @endif

                            @if((!empty($signature['department'])) || (!empty($signature['institution'])))
                                <div class="signature-details">
                                    @if(!empty($signature['department']))
                                        <div>{{ $signature['department'] }}</div>
                                    @endif
                                    @if(!empty($signature['institution']))
                                        <div>{{ $signature['institution'] }}</div>
                                    @endif
                                </div>
                            @endif

                            @if((!empty($signature['email'])) || (!empty($signature['phone'])))
                                <div class="signature-details">
                                    @if(!empty($signature['email']))
                                        <div>Email: {{ $signature['email'] }}</div>
                                    @endif
                                    @if(!empty($signature['phone']))
                                        <div>Phone: {{ $signature['phone'] }}</div>
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

        @if($showDigitalFooter || $footer)
            <div class="footer-block">
                @if($showDigitalFooter)
                    <div class="verification-strip">
                        <strong>Digitally verified document</strong>
                        | Reference ID: {{ $request->tracking_id }}
                        | Verify: <a
                            href="{{ route('public.verify', $request->verify_token) }}">{{ route('public.verify', $request->verify_token) }}</a>
                    </div>
                @endif

                @if($footer)
                    <div class="custom-footer">
                        {!! $footer !!}
                    </div>
                @endif
            </div>
        @endif
    </div>
</body>

</html>
