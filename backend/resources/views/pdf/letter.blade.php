<!DOCTYPE html>
<html lang="en" dir="{{ $layout['direction'] ?? 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Recommendation Letter - {{ $request->tracking_id }}</title>
    <style>
        @page {
            margin:
                {{ ($layout['margins']['top'] ?? 25) }}
                px
                {{ ($layout['margins']['right'] ?? 25) }}
                px
                {{ ($layout['margins']['bottom'] ?? 25) }}
                px
                {{ ($layout['margins']['left'] ?? 25) }}
                px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size:
                {{ ($layout['fontSize'] ?? 12) }}
                pt;
            line-height: 1.5;
            color: #000;
            direction:
                {{ $layout['direction'] ?? 'ltr' }}
            ;
        }

        .page-container {
            width: 100%;
            min-height: 100%;
            position: relative;
            @if(isset($layout['border']['enabled']) && $layout['border']['enabled'])
                border:
                    {{ $layout['border']['width'] ?? 2 }}
                    px solid
                    {{ $layout['border']['color'] ?? '#000' }}
                ;
                padding: 15px;
            @endif
        }

        .header {
            margin-bottom: 15px;
        }

        .body-content {
            margin-bottom: 20px;
        }

        .signature-section {
            margin-top: 30px;
        }

        .signature-image {
            max-width: 150px;
            max-height: 80px;
            margin-bottom: 10px;
        }

        .stamp-image {
            max-width: 100px;
            max-height: 100px;
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
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 9pt;
        }

        p {
            margin-bottom: 8px;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        img {
            max-width: 100%;
            height: auto;

            /* Watermark Styles */
            .watermark {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                font-size: 80pt;
                color: rgba(200, 200, 200, 0.15);
                font-weight: bold;
                z-index: -1;
                white-space: nowrap;
                pointer-events: none;
                user-select: none;
            }

            /* Digital Footer Styles */
            .digital-footer {
                margin-top: 30px;
                border-top: 2px solid #eee;
                padding-top: 10px;
                font-size: 8pt;
                color: #666;
                text-align: center;
                background-color: #f9f9f9;
                padding: 10px;
                border-radius: 4px;
            }
    </style>
</head>

<body>
    {{-- Watermark --}}
    @if($layout['watermark']['enabled'] ?? false)
        <div class="watermark">
            {{ $layout['watermark']['text'] ?: ($request->tracking_id ?? 'OFFICIAL COPY') }}
        </div>
    @endif

    <div class="page-container">
        {{-- Header Section --}}
        @if($header)
            <div class="header">
                {!! $header !!}
            </div>
        @endif

        {{-- Body Content --}}
        <div class="body-content">
            {!! $body !!}
        </div>

        {{-- Signature Section --}}
        @if(($signature['name'] ?? null) || ($signature['image'] ?? null))
            <div class="signature-section">
                <table style="width: 100%;">
                    <tr>
                        <td style="vertical-align: top; width: 65%;">
                            @if(!empty($signature['image']))
                                <img src="{{ $signature['image'] }}" alt="Signature" class="signature-image"><br>
                            @endif

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
                                <div class="signature-details" style="margin-top: 5px;">
                                    @if(!empty($signature['email']))
                                        Email: {{ $signature['email'] }}<br>
                                    @endif
                                    @if(!empty($signature['phone']))
                                        Phone: {{ $signature['phone'] }}
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td style="vertical-align: top; text-align: right; width: 35%;">
                            @if(!empty($signature['stamp']))
                                <img src="{{ $signature['stamp'] }}" alt="Official Stamp" class="stamp-image">
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        @endif

        {{-- QR Code Automatic Display --}}
        {{-- @if(!empty($qrCode))
        <div class="letter-qrcode" style="margin-top: 15px;">
            {!! $qrCode !!}
        </div>
        @endif --}}

        {{-- Digital Footer Verification Strip --}}
        @if($layout['footer']['enabled'] ?? true)
            <div class="digital-footer">
                <strong>DIGITALLY VERIFIED DOCUMENT</strong><br>
                Reference ID: {{ $request->tracking_id }} | Issued: {{ now()->format('Y-m-d H:i') }}<br>
                Verify this document at: <a href="{{ route('public.verify', $request->verify_token) }}"
                    style="color: #666;">{{ route('public.verify', $request->verify_token) }}</a>
            </div>
        @endif

        {{-- Original Footer (Custom Content) --}}
        @if($footer)
            <div class="footer">
                {!! $footer !!}
            </div>
        @endif
    </div>
</body>

</html>