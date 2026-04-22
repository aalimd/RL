<p style="margin: 0 0 16px 0;">Hello,</p>

<p style="margin: 0 0 16px 0;">
    A database backup has been generated for <strong>{{ $branding['site_name'] ?? config('app.name') }}</strong>.
    The SQL export is attached to this email.
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0"
    style="width: 100%; margin: 22px 0; border: 1px solid #dbe5f0; border-radius: 16px; background-color: #f8fbff;">
    <tr>
        <td style="padding: 18px 20px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="padding: 0 0 12px 0; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">
                        Generated at
                    </td>
                    <td align="right" style="padding: 0 0 12px 0; font-size: 15px; color: #0f172a;">
                        {{ $backupDate }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0 0 0; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-top: 1px solid #dbe5f0;">
                        Attachment
                    </td>
                    <td align="right" style="padding: 12px 0 0 0; font-size: 15px; color: #0f172a; border-top: 1px solid #dbe5f0;">
                        SQL backup file
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin: 0;">Store this file securely and handle it according to your data-protection policies.</p>
