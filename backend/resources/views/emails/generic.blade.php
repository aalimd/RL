<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $subject ?? 'Notification' }}</title>
</head>

<body
    style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8fafc; padding: 20px; border-radius: 8px;">
        {!! $body !!}
    </div>

    <div style="margin-top: 20px; font-size: 12px; color: #999; text-align: center;">
        <p>This is an automated message. Please do not reply directly to this email.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
    </div>
</body>

</html>