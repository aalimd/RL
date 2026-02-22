<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .code-box {
            background-color: #ffffff;
            border: 2px dashed #007bff;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #007bff;
            margin: 20px 0;
            border-radius: 5px;
        }

        .footer {
            font-size: 12px;
            color: #777;
            text-align: center;
            margin-top: 30px;
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