<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Research Access Request</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family:Arial, Helvetica, sans-serif;">

    <div style="max-width:600px; margin:40px auto; background:#ffffff; border-radius:10px; overflow:hidden;">

        {{-- Header --}}
        <div style="background:#072a89; padding:25px; text-align:center;">
            <h2 style="color:#ffffff; margin:0;">
                Research Repository System
            </h2>
        </div>

        {{-- Content --}}
        <div style="padding:30px;">

            <p style="font-size:16px;">
                Hello {{ $name }},
            </p>

            <p style="font-size:15px; color:#444;">
                Someone has requested access to your private research.
            </p>

            {{-- Research Information --}}
            <div style="background:#f8f9fa; border-radius:8px; padding:20px; margin:25px 0;">

                <p style="margin:0 0 12px 0;">
                    <strong>Research Title:</strong><br>
                    {{ $research_title }}
                </p>

                <p style="margin:0 0 12px 0;">
                    <strong>Requester Email:</strong><br>
                    {{ $requester_email }}
                </p>

                <p style="margin:0;">
                    <strong>Reason for Access:</strong><br>
                    {{ $reason_access }}
                </p>

            </div>


            {{-- Button --}}

            <table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0" style="margin:25px auto;">
                <tr>

                    {{-- Accept --}}
                    <td align="center"
                        style="border-radius:6px; background:#d9f5e5; border:1px solid #a8e6c3;">
                        <a href="{{ url('/access-requests/' . $access_request_id . '/accept') }}"
                        style="
                                display:inline-block;
                                padding:8px 16px;
                                font-family:Arial, Helvetica, sans-serif;
                                font-size:13px;
                                font-weight:600;
                                color:#146c43;
                                text-decoration:none;
                                border-radius:6px;
                        ">
                            ✓ Accept
                        </a>
                    </td>

                    <td width="8" style="font-size:0; line-height:0;">&nbsp;</td>

                    {{-- Decline --}}
                    <td align="center"
                        style="border-radius:6px; background:#fdecec; border:1px solid #f6c6c6;">
                        <a href="{{ url('/access-requests/' . $access_request_id . '/decline') }}"
                        style="
                                display:inline-block;
                                padding:8px 16px;
                                font-family:Arial, Helvetica, sans-serif;
                                font-size:13px;
                                font-weight:600;
                                color:#b42318;
                                text-decoration:none;
                                border-radius:6px;
                        ">
                            ✕ Decline
                        </a>
                    </td>

                </tr>
            </table>
        </div>

    </div>

</body>
</html>
