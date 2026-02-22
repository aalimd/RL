<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Received</title>
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
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
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
            background: #f0f9ff;
            border: 2px solid #4F46E5;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }

        .tracking-id {
            font-size: 28px;
            font-weight: bold;
            color: #4F46E5;
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
            <h1>✓ Request Received</h1>
        </div>
        <div class="content">
            @if(isset($body) && $body)
                {!! $body !!}
            @else
                <p>Dear <strong>{{ $request->student_name }}</strong>,</p>

                <p>Thank you for submitting your recommendation letter request. Your request has been successfully received
                    and is now pending review.</p>

                <div class="tracking-box">
                    <p style="margin: 0 0 10px 0; color: #666;">Your Tracking ID:</p>
                    <div class="tracking-id">{{ $request->tracking_id }}</div>
                    <p style="margin: 15px 0 0 0; font-size: 14px; color: #666;">Keep this ID safe to track your request
                        status.</p>
                </div>

                <p>You can track the status of your request at any time using the button below:</p>

                <p style="text-align: center;">
                    <a href="{{ $trackingUrl }}" class="btn">Track Your Request</a>
                </p>

                <p style="margin-top: 30px;">If you have any questions, please don't hesitate to contact us.</p>

                <p>Best regards,<br>{{ config('mail.from.name', 'Dr. Alzahrani EM') }}</p>
            @endif
        </div>
        <div class="footer">
            <p>This is an automated message. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>

</html>