<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Volunteer Invitation</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; background-color:#f4f6f8;">
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td align="center" style="padding: 30px 15px;">
<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">

    <!-- Header -->
    <tr>
        <td style="background:#004aad; padding:20px; text-align:center; color:#ffffff;">
            <h2 style="margin:0;">Volunteer Opportunity Invitation</h2>
        </td>
    </tr>

    <!-- Body -->
    <tr>
        <td style="padding:30px;">
            <p style="font-size:16px; color:#333;">
                Hello <strong>{{ $name }}</strong>,
            </p>

            <p style="font-size:15px; color:#555; line-height:1.6;">
                You have been <strong> invited by the
                {{ $officeName }}</strong>
                to participate in a volunteer opportunity on
                <strong>IsKonnect</strong>.
            </p>

            <p style="font-size:15px; color:#555; line-height:1.6;">
                Please log in to the IsKonnect system to view the
                volunteer opportunity details and respond to the invitation.
            </p>

            <p style="font-size:15px; color:#555; line-height:1.6;">
                No action will be taken unless you accept the invitation.
            </p>

            <p style="font-size:15px; color:#555;">
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
