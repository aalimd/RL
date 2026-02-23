<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Updated</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            margin: 0;
            padding: 0;
            background: #f3f4f6;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .header {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: #ffffff;
            padding: 32px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .content {
            padding: 40px 32px;
        }

        .tracking-box {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            margin: 24px 0;
        }

        .tracking-id {
            font-size: 28px;
            font-weight: 800;
            color: #d97706;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            letter-spacing: 0.1em;
            margin-top: 8px;
        }

        .btn {
            display: inline-block;
            background: #4f46e5;
            color: #ffffff;
            padding: 12px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 24px;
            text-align: center;
        }

        .footer {
            background: #f9fafb;
            padding: 24px;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🔔 Request Status Updated</h1>
        </div>
        <div class="content">
            @if(isset($body) && $body)
                {!! $body !!}
            @else
                <p>Dear <strong>{{ $request->student_name }}</strong>,</p>

                <p>We wanted to let you know that there has been an update to your recommendation letter request.</p>

                <div class="tracking-box">
                    <p style="margin: 0 0 10px 0; color: #666;">Your Tracking ID:</p>
                    <div class="tracking-id">{{ $request->tracking_id }}</div>
                </div>

                <p>To view the current status of your request and any updates, please use the button below:</p>

                <p style="text-align: center;">
                    <a href="{{ $trackingUrl }}" class="btn">Check Your Request Status</a>
                </p>

                <p style="margin-top: 30px;">Thank you for your patience.</p>

                <p>Best regards,<br>{{ config('mail.from.name', 'Dr. Alzahrani EM') }}</p>
            @endif
        </div>
        <div class="footer">
            <p>This is an automated message. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>

</html>