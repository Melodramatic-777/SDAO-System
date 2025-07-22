<!DOCTYPE html>
<html>
<head>
    <title>Temporary Credentials</title>
</head>
<body>
    <p>Hello {{ $name }},</p>

    <p>Here are your temporary admin credentials:</p>

    <ul>
        <li>Email: {{ $email }}</li>
        <li>Password: {{ $password }}</li>
    </ul>

    <p>Please log in and change your password as soon as possible.</p>

    <p>Thank you!</p>
</body>
</html>
