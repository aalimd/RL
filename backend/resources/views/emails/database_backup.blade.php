<!DOCTYPE html>
<html>

<head>
    <title>Database Backup</title>
</head>

<body style="font-family: sans-serif; line-height: 1.5; color: #333;">
    <div style="background-color: #f3f4f6; padding: 20px;">
        <div style="background-color: #ffffff; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #4f46e5; margin-top: 0;">🛡️ Weekly Database Backup</h2>
            <p>Hello Admin,</p>
            <p>Here is your automated database backup generated on <strong>{{ $backupDate }}</strong>.</p>
            <p>Please keep this file safe. It contains a full dump of your application's data.</p>
            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">
            <p style="font-size: 12px; color: #6b7280;">
                This email was sent automatically by your application's scheduler.
            </p>
        </div>
    </div>
</body>

</html>