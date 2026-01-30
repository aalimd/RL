<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Request</title>
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
            background: linear-gradient(135deg, #059669, #10B981);
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

        .info-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .info-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #666;
            width: 120px;
        }

        .info-value {
            color: #111;
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
            <h1>📬 New Recommendation Request</h1>
        </div>
        <div class="content">
            <p>A new recommendation letter request has been submitted and requires your attention.</p>

            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Tracking ID:</span>
                    <span class="info-value"><strong>{{ $request->tracking_id }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Student Name:</span>
                    <span class="info-value">{{ $request->student_name }} {{ $request->middle_name }}
                        {{ $request->last_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $request->student_email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">University:</span>
                    <span class="info-value">{{ $request->university ?? 'Not specified' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Purpose:</span>
                    <span class="info-value">{{ $request->purpose ?? 'Not specified' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Submitted:</span>
                    <span class="info-value">{{ $request->created_at->format('M d, Y H:i') }}</span>
                </div>
            </div>

            <p style="text-align: center;">
                <a href="{{ $detailsUrl }}" class="btn">Review Request</a>
            </p>
        </div>
        <div class="footer">
            <p>This is an automated notification from the Recommendation System.</p>
        </div>
    </div>
</body>

</html>