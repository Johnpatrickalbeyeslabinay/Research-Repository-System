<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Request Update</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family:Arial, Helvetica, sans-serif;">

    <div style="max-width:600px; margin:40px auto; background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.06);">

        {{-- Header --}}
        <div style="background:#072a89; padding:25px; text-align:center;">
            <h2 style="color:#ffffff; margin:0;">Research Repository System</h2>
        </div>

        {{-- Content --}}
        <div style="padding:30px;">

            <p style="font-size:16px; margin:0 0 12px 0;">
                Hello {{ $accessRequest->name ?? $accessRequest->email }},
            </p>

            <p style="font-size:15px; color:#444; line-height:1.5; margin:0 0 20px 0;">
                @if ($accessRequest->status === 'approved')
                    Good news! Your request to access the research below has been approved.
                @else
                    We're sorry, your request to access the research below was not approved.
                @endif
            </p>

            {{-- Details --}}
            <div style="background:#f8f9fa; border-radius:8px; padding:20px; margin:0 0 25px 0;">

                <p style="margin:0 0 12px 0; font-size:14px;">
                    <strong>Research Title:</strong><br>
                    {{ $accessRequest->file->title ?? 'the requested research' }}
                </p>

                <p style="margin:0 0 12px 0; font-size:14px;">
                    <strong>Approved by:</strong><br>
                    {{ $ownerName }}
                </p>

                <p style="margin:0; font-size:14px;">
                    <strong>Status:</strong><br>
                    @if ($accessRequest->status === 'approved')
                        <span style="display:inline-block; margin-top:4px; padding:4px 12px; border-radius:20px; background:#d9f5e5; border:1px solid #a8e6c3; color:#146c43; font-size:13px; font-weight:600;">✓ Approved</span>
                    @else
                        <span style="display:inline-block; margin-top:4px; padding:4px 12px; border-radius:20px; background:#fdecec; border:1px solid #f6c6c6; color:#b42318; font-size:13px; font-weight:600;">✕ Rejected</span>
                    @endif
                </p>

            </div>

            @if ($accessRequest->status === 'approved')
                <table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 10px auto;">
                    <tr>
                        <td align="center" style="border-radius:6px; background:#072a89;">
                            <a href="{{ $approvedUrl }}"
                                style="display:inline-block; padding:10px 22px; font-size:14px; font-weight:600; color:#ffffff; text-decoration:none; border-radius:6px;">
                                View Research
                            </a>
                        </td>
                    </tr>
                </table>
            @else
                <p style="font-size:14px; color:#666; margin:0;">
                    You may contact the owner for more information.
                </p>
            @endif

        </div>

        {{-- Footer --}}
        <div style="background:#f8f9fa; padding:15px; text-align:center; font-size:12px; color:#888;">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>

    </div>

</body>
</html>