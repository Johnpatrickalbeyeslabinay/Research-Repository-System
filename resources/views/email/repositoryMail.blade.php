<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Research Repository System</title>
</head>

<body style="margin:0; padding:0; background-color:#eef1f6; font-family:'Segoe UI', Helvetica, Arial, sans-serif; color:#1f2937;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1f6; padding:32px 12px;">
        <tr>
            <td align="center">

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:520px; background-color:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e1e6ef;">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#1e3a8a; padding:26px 32px;">
                            <span style="font-size:18px; font-weight:700; color:#ffffff; letter-spacing:0.3px;">
                                Research Repository System
                            </span>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:32px;">

                            <p style="margin:0 0 6px; font-size:20px; font-weight:700; color:#111827;">
                                Hello {{ $name }},
                            </p>
                            <p style="margin:0 0 24px; font-size:15px; color:#4b5563;">
                                Your account has been created.
                            </p>

                            <!-- Credentials -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f6f8fc; border:1px solid #e1e6ef; border-radius:8px;">
                                <tr>
                                    <td style="padding:14px 18px; font-size:14px; color:#6b7280; width:110px; border-bottom:1px solid #e1e6ef;">
                                        User ID
                                    </td>
                                    <td style="padding:14px 18px; font-size:15px; font-weight:600; color:#111827; border-bottom:1px solid #e1e6ef;">
                                        {{ $user_id }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 18px; font-size:14px; color:#6b7280; border-bottom:1px solid #e1e6ef;">
                                        Password
                                    </td>
                                    <td style="padding:14px 18px; font-size:15px; font-weight:600; color:#111827; font-family:Consolas, 'Courier New', monospace; border-bottom:1px solid #e1e6ef;">
                                        {{ $password }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 18px; font-size:14px; color:#6b7280;">
                                        Role
                                    </td>
                                    <td style="padding:14px 18px; font-size:15px; font-weight:600; color:#111827;">
                                        {{ ucfirst($role) }}
                                    </td>
                                </tr>
                            </table>

                            <!-- Verify -->
                            <p style="margin:28px 0 16px; font-size:15px; color:#4b5563;">
                                Verify your email to activate your account.
                            </p>

                            <p style="margin:0 0 14px;">
                                <a
                                    href="{{ $verificationUrl }}"
                                    style="
                                        display: inline-block;
                                        padding: 12px 20px;
                                        background-color: #1e3a8a;
                                        color: #ffffff;
                                        text-decoration: none;
                                        border-radius: 6px;
                                        font-weight: bold;
                                        font-size: 15px;
                                    "
                                >
                                    Verify Email Address
                                </a>
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px 32px; background-color:#f6f8fc; border-top:1px solid #e1e6ef;">
                            <p style="margin:0 0 4px; font-size:12px; color:#6b7280;">
                                Didn't expect this account? You can ignore this email.
                            </p>
                            <p style="margin:0; font-size:12px; color:#9ca3af;">
                                Research Repository System
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
