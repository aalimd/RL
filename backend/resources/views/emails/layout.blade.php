@php
    $siteName = $branding['site_name'] ?? config('app.name', 'Application');
    $fromName = $branding['from_name'] ?? $siteName;
    $supportEmail = $branding['support_email'] ?? null;
    $logoUrl = $branding['logo_url'] ?? null;
    $primaryColor = $branding['primary_color'] ?? '#1d4ed8';
    $primaryColorDark = $branding['primary_color_dark'] ?? '#163b96';
    $primaryColorSoft = $branding['primary_color_soft'] ?? '#e8f1fe';
    $appUrl = $branding['app_url'] ?? config('app.url');
    $appHost = $branding['app_host'] ?? parse_url((string) $appUrl, PHP_URL_HOST);
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? $siteName }}</title>
</head>

<body style="margin: 0; padding: 0; background-color: #eef3f8; color: #0f172a;">
    <span
        style="display: none !important; visibility: hidden; opacity: 0; color: transparent; height: 0; width: 0; overflow: hidden; mso-hide: all;">
        {{ $summary ?? $title ?? $siteName }}
    </span>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="width: 100%; background-color: #eef3f8; margin: 0; padding: 0;">
        <tr>
            <td align="center" style="padding: 28px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="width: 100%; max-width: 640px; background-color: #ffffff; border: 1px solid #dbe5f0; border-radius: 20px; overflow: hidden;">
                    <tr>
                        <td
                            style="padding: 0; border-bottom: 1px solid #e2e8f0; background-color: {{ $primaryColor }}; background-image: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $primaryColorDark }} 100%);">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width: 100%;">
                                <tr>
                                    <td style="padding: 28px 28px 18px 28px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width: 100%;">
                                            <tr>
                                                <td align="left" style="vertical-align: middle;">
                                                    @if($logoUrl)
                                                        <img src="{{ $logoUrl }}" alt="{{ $siteName }} logo"
                                                            style="display: block; max-width: 168px; max-height: 48px; height: auto; border: 0; filter: brightness(0) invert(1); opacity: 0.98;">
                                                    @else
                                                        <div
                                                            style="font-family: Arial, Helvetica, sans-serif; font-size: 18px; font-weight: 700; color: #ffffff;">
                                                            {{ $siteName }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td align="right" style="vertical-align: middle;">
                                                    @if($appHost)
                                                        <div
                                                            style="display: inline-block; padding: 7px 12px; border-radius: 999px; background-color: rgba(255,255,255,0.14); font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #dbeafe;">
                                                            {{ $appHost }}
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 0 28px 28px 28px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                            style="width: 100%; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.12); border-radius: 18px;">
                                            <tr>
                                                <td style="padding: 22px 22px 20px 22px;">
                                                    @if(!empty($badge))
                                                        <div
                                                            style="display: inline-block; margin-bottom: 14px; padding: 7px 12px; border-radius: 999px; background-color: rgba(255,255,255,0.18); color: #eff6ff; font-family: Arial, Helvetica, sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase;">
                                                            {{ $badge }}
                                                        </div>
                                                    @endif
                                                    <h1
                                                        style="margin: 0 0 10px 0; font-family: Arial, Helvetica, sans-serif; font-size: 30px; line-height: 1.2; font-weight: 700; color: #ffffff;">
                                                        {{ $title }}
                                                    </h1>
                                                    @if(!empty($summary))
                                                        <p
                                                            style="margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 1.7; color: #e0ecff;">
                                                            {{ $summary }}
                                                        </p>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 28px 0 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="width: 100%; margin-top: -18px; background-color: {{ $primaryColorSoft }}; border: 1px solid #dbe5f0; border-radius: 16px;">
                                <tr>
                                    <td style="padding: 12px 16px; font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #334155;">
                                        This is a transactional message related to your activity in {{ $siteName }}.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="padding: 24px 28px 0 28px; font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.7; color: #0f172a;">
                            {!! $contentHtml !!}
                        </td>
                    </tr>

                    @if(!empty($ctaUrl) && !empty($ctaLabel))
                        <tr>
                            <td style="padding: 22px 28px 0 28px;">
                                <table role="presentation" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td bgcolor="{{ $primaryColor }}" style="border-radius: 999px;">
                                            <a href="{{ $ctaUrl }}"
                                                style="display: inline-block; padding: 14px 22px; font-family: Arial, Helvetica, sans-serif; font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none;">
                                                {{ $ctaLabel }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    @if(!empty($closingNote))
                        <tr>
                            <td style="padding: 18px 28px 0 28px;">
                                <p
                                    style="margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.7; color: #64748b;">
                                    {{ $closingNote }}
                                </p>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding: 26px 28px 28px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="width: 100%; border-top: 1px solid #e2e8f0;">
                                <tr>
                                    <td style="padding-top: 18px;">
                                        <p
                                            style="margin: 0 0 8px 0; font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 1.6; color: #64748b;">
                                            Sent by {{ $fromName }} for {{ $siteName }}.
                                        </p>
                                        @if($supportEmail)
                                            <p
                                                style="margin: 0 0 8px 0; font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 1.6; color: #64748b;">
                                                Need help? Contact
                                                <a href="mailto:{{ $supportEmail }}"
                                                    style="color: {{ $primaryColor }}; text-decoration: none;">{{ $supportEmail }}</a>.
                                            </p>
                                        @endif
                                        @if($appUrl)
                                            <p
                                                style="margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 1.6; color: #64748b;">
                                                <a href="{{ $appUrl }}"
                                                    style="color: {{ $primaryColor }}; text-decoration: none;">{{ $appUrl }}</a>
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
