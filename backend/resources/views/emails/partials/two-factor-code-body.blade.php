<p style="margin: 0 0 16px 0;">Hello{{ $recipientName !== 'there' ? ' ' . $recipientName : '' }},</p>

<p style="margin: 0 0 16px 0;">
    Use the verification code below to {{ $actionLabel }}.
</p>

<div
    style="margin: 24px 0; padding: 20px; border-radius: 18px; border: 1px solid #c7d7ea; background-color: #f8fbff; text-align: center;">
    <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 10px;">
        Verification code
    </div>
    <div
        style="font-size: 34px; font-weight: 800; letter-spacing: 0.26em; color: #0f172a; font-family: 'Courier New', Courier, monospace;">
        {{ $code }}
    </div>
</div>

<p style="margin: 0 0 12px 0;">This code expires in 10 minutes.</p>
<p style="margin: 0;">If you did not request this code, you can ignore this email and keep your account protected.</p>
