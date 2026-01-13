<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - NU Fairview SDAO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    @vite('resources/css/volunteers.css')

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            position: sticky;
            top: 0;
            z-index: 999;
            background-color: #003DA5;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
        }

        header .logo {
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        header nav a {
            color: white;
            margin-left: 1rem;
            text-decoration: none;
            font-weight: 500;
        }

        .login-bg-wrapper {
            background: url('/images/login_bg.png') no-repeat center center;
            background-size: cover;
            min-height: calc(100vh - 70px);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .login-container {
            background-color: white;
            width: 100%;
            max-width: 500px;
            padding: 2.3rem;
            border: 2px solid #003DA5;
            border-radius: 25px;
            text-align: center;
            color: #003DA5;
        }

        .login-container h2 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
        }

        .login-container label {
            display: block;
            text-align: left;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .login-container input {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #003DA5;
            border-radius: 5px;
        }

        .login-container button {
            background-color: #003DA5;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 0.5rem;
        }

        .error {
            color: red;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .back-link {
            display: inline-block;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #6c757d;
            text-decoration: underline;
        }
    </style>
</head>

<body>

<header>
    <div class="logo">
        <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
        NU Fairview - Student Development and Activities Office
    </div>

    <nav>
        <a href="{{ url('/') }}">HOME</a>
        <a href="{{ route('login') }}"
           style="background:#FFC300; color:#003DA5; padding:8px 18px; border-radius:20px; font-weight:700;">
            LOG IN
        </a>
    </nav>
</header>

<div class="login-bg-wrapper">
    <div class="login-container">
        <h2>Reset Password</h2>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PUT')

            <input type="hidden" name="token" value="{{ $token }}">

            <label>New Password</label>
            <input type="password" name="password" required placeholder="Enter new password">

            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" required placeholder="Confirm new password">

            <button type="submit">Reset Password</button>
        </form>

        <a href="{{ route('login') }}" class="back-link">
            ← Back to Login
        </a>
    </div>
</div>

</body>
</html>
