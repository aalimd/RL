<p style="margin: 0 0 16px 0;">Hello <strong>{{ $request->student_name }}</strong>,</p>

<p style="margin: 0 0 16px 0;">
    We received your recommendation request and added it to the review queue.
    Keep your tracking ID for future updates and document access.
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0"
    style="width: 100%; margin: 22px 0; border: 1px solid #dbe5f0; border-radius: 16px; background-color: #f8fbff;">
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
                    <td style="padding: 12px 0; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-top: 1px solid #dbe5f0;">
                        Request ID
                    </td>
                    <td align="right"
                        style="padding: 12px 0; font-size: 15px; color: #0f172a; border-top: 1px solid #dbe5f0;">
                        #{{ $request->id }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-top: 1px solid #dbe5f0;">
                        Submitted
                    </td>
                    <td align="right"
                        style="padding: 12px 0; font-size: 15px; color: #0f172a; border-top: 1px solid #dbe5f0;">
                        {{ optional($request->created_at)->format('M d, Y h:i A') }}
                    </td>
                </tr>
                @if(!empty($request->purpose))
                    <tr>
                        <td style="padding: 12px 0; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-top: 1px solid #dbe5f0;">
                            Purpose
                        </td>
                        <td align="right"
                            style="padding: 12px 0; font-size: 15px; color: #0f172a; border-top: 1px solid #dbe5f0;">
                            {{ $request->purpose }}
                        </td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

<p style="margin: 0;">Use your tracking page for the latest status, revision requests, and approved document access.</p>
