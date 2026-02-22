<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Updated</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #F59E0B, #D97706);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 30px;
        }

        .tracking-box {
            background: #fffbeb;
            border: 2px solid #F59E0B;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }

        .tracking-id {
            font-size: 24px;
            font-weight: bold;
            color: #D97706;
            font-family: monospace;
            letter-spacing: 2px;
        }

        .btn {
            display: inline-block;
            background: #4F46E5;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 15px;
        }

        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
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