<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Volunteer Opportunity Request</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; background-color:#f4f6f8;">
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" style="padding:30px 15px;">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">

                <!-- Header -->
                <tr>
                    <td style="background:#004aad; padding:20px; text-align:center; color:#ffffff;">
                        <h2 style="margin:0;">New Volunteer Opportunity Request</h2>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:30px; color:#333;">
                        <p style="font-size:15px;">Good day,</p>

                        <p style="font-size:15px; line-height:1.6;">
                            This is to inform you that a new volunteer opportunity request has been
                            submitted through <strong>IsKonnect</strong> and is now available for your review.
                        </p>

                        <p style="font-size:15px; line-height:1.6;">
                            <strong>Request Details:</strong><br><br>
                            <strong>Requestor:</strong> {{ $name }}<br>
                            <strong>Office:</strong> {{ $office }}<br>
<p><strong>Type of Activity:</strong> {{ $type_of_activity }}</p>

                            <strong>Location:</strong> {{ $location }}
                            <strong>Date & Time:</strong> {{ $date_time }}<br>

                        </p>

                        <p style="font-size:15px; line-height:1.6;">
                            You may access the full details by logging in to your IsKonnect account.
                            Kindly review the submission at your earliest convenience.
                        </p>

                        <p style="font-size:14px;">
                            —<br>
                            <strong>IsKonnect System</strong><br>
                            <em>This is an automated email. Please do not reply.</em>
                        </p>
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
