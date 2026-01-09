<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Changed</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; background-color:#f4f6f8;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding: 30px 15px;">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background:#004aad; padding:20px; text-align:center; color:#ffffff;">
                            <h2 style="margin:0;">Security Notification</h2>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px;">
                            <p style="font-size:16px; color:#333;">
                                Hello <strong>{{ $name }}</strong>,
                            </p>

                            <p style="font-size:15px; color:#555; line-height:1.6;">
                                This email is to confirm that your account password was
                                <strong>successfully changed</strong>.
                            </p>

                            <p style="font-size:15px; color:#555; line-height:1.6;">
                                <strong>Date & Time:</strong> {{ $date }} <br>
                                <strong>Email:</strong> {{ $email }}
                            </p>

                            <p style="font-size:15px; color:#555; line-height:1.6;">
                                If you did not perform this action, please contact the system
                                administrator immediately.
                            </p>

                            <p style="font-size:15px; color:#555;">
                                Stay safe,<br>
                                <strong>IsKonnect System</strong><br>
                                <em>This is an automated email. Please do not reply.</em>                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f0f0f0; padding:15px; text-align:center; font-size:13px; color:#777;">
                            © IsKonnect. All rights reserved.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
