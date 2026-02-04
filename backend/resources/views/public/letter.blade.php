<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .letter-page {
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            width: 210mm;
            min-height: 297mm;
            height: auto;
            position: relative;
            /* overflow: hidden; Removed to prevent text cutting */
            display: flex;
            flex-direction: column;
        }

        /* Header Section */
        .letter-header {
            padding: 12mm 15mm 8mm 15mm;
            flex-shrink: 0;
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
            padding: 0 15mm;
            line-height: 1.6;
            text-align: justify;
            flex: 1;
            font-size: 11pt;
            overflow: hidden;
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
            margin-bottom: 10px;
            text-align: justify;
        }

        /* Signature Section */
        .letter-signature {
            padding: 15px 15mm;
            flex-shrink: 0;
        }

        .signature-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 3px;
        }

        .signature-details {
            font-size: 9pt;
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
            height: 40px;
            max-width: 120px;
        }

        /* Footer Section */
        .letter-footer {
            padding: 8mm 15mm;
            border-top: 1px solid #e5e7eb;
            font-size: 8pt;
            flex-shrink: 0;
            background: #fafafa;
        }

        /* Toolbar */
        .toolbar {
            position: fixed;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
            z-index: 100;
        }

        .btn {
            padding: 0.75rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
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
                width: 210mm !important;
                min-height: 297mm !important;
                height: auto !important;
                margin: 0;
                overflow: visible !important;
                page-break-after: always;
            }

            .no-print {
                display: none !important;
            }

            .title-banner {
                background: #b91c1c !important;
                -webkit-print-color-adjust: exact !important;
            }

            .letter-footer {
                background: #fafafa !important;
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
                flex-direction: column-reverse;
                /* Stack buttons on mobile */
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
        <a href="{{ url('/track/' . $request->tracking_id) }}" class="btn btn-secondary">
            &larr; Back
        </a>
        {{--
        <a href="{{ route('public.letter.pdf', $request->id) }}" class="btn btn-primary"
            style="background: linear-gradient(135deg, #059669, #10b981);">
            📥 Download PDF
        </a>
        --}}
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Save/Print
        </button>
    </div>

    @php
        $borderStyle = isset($layout['border']['enabled']) && $layout['border']['enabled']
            ? "border: {$layout['border']['width']}px {$layout['border']['style']} {$layout['border']['color']};"
            : "";
    @endphp

    <div class="letter-page" style="{{ $borderStyle }}">

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

        <!-- Signature Block -->
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

                    <!-- Right Column: Stamp & QR Code -->
                    <td style="vertical-align: top; text-align: right; width: 35%;">
                        @if(!empty($signature['stamp']))
                            <div style="margin-bottom: 15px;">
                                <img src="{{ $signature['stamp'] }}" alt="Official Stamp"
                                    style="max-width: 100px; max-height: 100px; height: auto;">
                            </div>
                        @endif

                        @if(!empty($qrCode))
                            <div style="margin-top: 10px;">
                                {!! $qrCode !!}
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        {{-- QR Code Automatic Display --}}
        {{-- @if(!empty($qrCode))
        <div class="letter-qrcode" style="padding: 0 15mm;">
            {!! $qrCode !!}
        </div>
        @endif --}}

        <!-- Footer -->
        <div class="letter-footer">
            {!! $footer !!}
        </div>
    </div>

</body>

</html>