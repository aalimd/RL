<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background: #f3f4f6;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 40px 32px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .header h2 {
            margin: 0;
            color: #1f2937;
            font-weight: 700;
        }

        .code-box {
            background-color: #f3f4f6;
            border: 1px dashed #9ca3af;
            padding: 24px;
            text-align: center;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 0.25em;
            color: #111827;
            margin: 32px 0;
            border-radius: 8px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }

        .footer {
            font-size: 13px;
            color: #6b7280;
            text-align: center;
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>

<body>
    <div class="container">
        @if(isset($body) && $body)
            {!! $body !!}
        @else
            <div class="header">
                <h2>🔐 Security Verification</h2>
            </div>

            <p>Hello <strong>{{ $requestModel->student_name }}</strong>,</p>

            <p>To access the details of your request <strong>#{{ $requestModel->tracking_id }}</strong>, please use the
                verification code below:</p>

            <div class="code-box">
                {{ $otp }}
            </div>

            <p>This code will expire in 5 minutes. If you did not request this code, please ignore this email.</p>
        @endif

        <div class="footer">
            <p>&copy; {{ date('Y') }} Recommendation Letter System. All rights reserved.</p>
        </div>
    </div>
</body>

</html>