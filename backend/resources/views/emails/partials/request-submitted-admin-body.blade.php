@php
    $fullName = trim(implode(' ', array_filter([$request->student_name, $request->middle_name, $request->last_name])));
@endphp
<p style="margin: 0 0 16px 0;">
    A new recommendation request has been submitted and is ready for review.
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0"
    style="width: 100%; margin: 22px 0; border: 1px solid #dbe5f0; border-radius: 16px; background-color: #f8fbff;">
    <tr>
        <td style="padding: 18px 20px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width: 100%;">
                <tr>
                    <td style="padding: 0 0 12px 0; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">
                        Request ID
                    </td>
                    <td align="right" style="padding: 0 0 12px 0; font-size: 15px; color: #0f172a;">
                        #{{ $request->id }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-top: 1px solid #dbe5f0;">
                        Tracking ID
                    </td>
                    <td align="right"
                        style="padding: 12px 0; font-size: 16px; font-weight: 700; color: #0f172a; border-top: 1px solid #dbe5f0; font-family: 'Courier New', Courier, monospace;">
                        {{ $request->tracking_id }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-top: 1px solid #dbe5f0;">
                        Student
                    </td>
                    <td align="right"
                        style="padding: 12px 0; font-size: 15px; color: #0f172a; border-top: 1px solid #dbe5f0;">
                        {{ $fullName }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-top: 1px solid #dbe5f0;">
                        Email
                    </td>
                    <td align="right"
                        style="padding: 12px 0; font-size: 15px; color: #0f172a; border-top: 1px solid #dbe5f0;">
                        {{ $request->student_email }}
                    </td>
                </tr>
                @if(!empty($request->university))
                    <tr>
                        <td style="padding: 12px 0; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; border-top: 1px solid #dbe5f0;">
                            Destination
                        </td>
                        <td align="right"
                            style="padding: 12px 0; font-size: 15px; color: #0f172a; border-top: 1px solid #dbe5f0;">
                            {{ $request->university }}
                        </td>
                    </tr>
                @endif
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

<p style="margin: 0;">
    Review the request to confirm status, respond with revision notes if needed, and prepare the final document.
</p>
