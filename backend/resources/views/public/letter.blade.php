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
            height: 297mm;
            position: relative;
            overflow: hidden;
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
                height: 297mm !important;
                margin: 0;
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

        <!-- Title Banner -->
        <div class="title-banner">
            RECOMMENDATION LETTER
        </div>

        <!-- Body -->
        <div class="letter-body">
            <!-- Body Content from Template -->
            <div class="content">
                {!! $body !!}
            </div>
        </div>

        <!-- Signature Block -->
        <div class="letter-signature">
            <div class="signature-name">{{ $signature['name'] }}</div>
            <div class="signature-details">
                @if(!empty($signature['title']))
                    <div>{{ $signature['title'] }}</div>
                @endif
                @if(!empty($signature['department']))
                    <div>{{ $signature['department'] }}</div>
                @endif
                @if(!empty($signature['institution']))
                    <div>{{ $signature['institution'] }}</div>
                @endif
                @if(!empty($signature['email']))
                    <div>Email: {{ $signature['email'] }}</div>
                @endif
            </div>

            @if(!empty($signature['image']))
                <div class="signature-image">
                    <img src="{{ $signature['image'] }}" alt="Signature">
                </div>
            @endif
        </div>

        {{-- QR Code Automatic Display --}}
        @if(!empty($qrCode))
            <div class="letter-qrcode" style="padding: 0 15mm;">
                {!! $qrCode !!}
            </div>
        @endif

        <!-- Footer -->
        <div class="letter-footer">
            {!! $footer !!}
        </div>
    </div>

</body>

</html>