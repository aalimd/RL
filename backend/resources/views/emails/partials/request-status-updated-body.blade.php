@php
    $statusColors = match ($request->status) {
        'Approved' => ['background' => '#ecfdf3', 'border' => '#b7efc6', 'text' => '#0f7a41'],
        'Rejected' => ['background' => '#fef2f2', 'border' => '#fecaca', 'text' => '#b91c1c'],
        'Needs Revision' => ['background' => '#fff7ed', 'border' => '#fed7aa', 'text' => '#c2410c'],
        default => ['background' => '#eff6ff', 'border' => '#bfdbfe', 'text' => '#1d4ed8'],
    };

    $message = $request->status === 'Rejected'
        ? trim((string) $request->rejection_reason)
        : trim((string) $request->admin_message);
@endphp

<p style="margin: 0 0 16px 0;">Hello <strong>{{ $request->student_name }}</strong>,</p>

<p style="margin: 0 0 16px 0;">
    Your recommendation request has a new status. Review the update below for the latest action or outcome.
</p>

<div
    style="margin: 22px 0; padding: 20px; border-radius: 16px; border: 1px solid {{ $statusColors['border'] }}; background-color: {{ $statusColors['background'] }};">
    <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">
        Current status
    </div>
    <div style="font-size: 24px; font-weight: 700; color: {{ $statusColors['text'] }};">
        {{ $request->status }}
    </div>
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0"
    style="width: 100%; margin: 0 0 22px 0; border: 1px solid #dbe5f0; border-radius: 16px; background-color: #f8fbff;">
    <tr>
        <td style="padding: 18px 20px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="padding: 0 0 12px 0; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">
                        Tracking ID
                    </td>
                    <td align="right"
                        style="padding: 0 0 12px 0; font-size: 16px; font-weight: 700; color: #0f172a; font-family: 'Courier New', Courier, monospace;">
                        {{ $request->tracking_id }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0 0 0; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-top: 1px solid #dbe5f0;">
                        Request ID
                    </td>
                    <td align="right" style="padding: 12px 0 0 0; font-size: 15px; color: #0f172a; border-top: 1px solid #dbe5f0;">
                        #{{ $request->id }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@if($message !== '')
    <div
        style="margin: 0 0 22px 0; padding: 18px 20px; border-radius: 16px; background-color: #f8fafc; border: 1px solid #dbe5f0;">
        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">
            {{ $request->status === 'Rejected' ? 'Reason provided' : 'Update details' }}
        </div>
        <div style="font-size: 15px; color: #0f172a; line-height: 1.7;">
            {{ $message }}
        </div>
    </div>
@endif

<p style="margin: 0;">
    Open your tracking page to review the request, respond to revision feedback, or access approved documents.
</p>
