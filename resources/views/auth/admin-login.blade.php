<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log In - NU Fairview SDAO</title>
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
        background: url('/public/images/login_bg.png') no-repeat center center/cover;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
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
            margin: 0 1rem;
            text-decoration: none;
            font-weight: 500;
        }

        .login-bg-wrapper {
    background: url('/images/login_bg.png') no-repeat center center;
    background-size: cover;
    min-height: calc(100vh - 70px); /* adjust based on header height */
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem 0;
}

.login-container {
    background-color: white; /* solid background */
    max-width: 400px;
    margin: 4rem auto;
    padding: 2rem;
    border: 2px solid #003DA5;
    border-radius: 25px;
    text-align: center;
    color: #003DA5; /* change text color for readability */
}

        .login-container h2 {
            color: #003DA5;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            font-weight: 800;
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

        .login-container p {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 1rem;
        }

        .login-container button {
            background-color: #003DA5;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
      <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
            Student Developments and Activities Office
        </div>
        <nav>
            <a href="/">ABOUT US</a>
            <a href="/#contact">CONTACT US</a>
        </nav>
    </header>
    <div class="login-bg-wrapper">

    <div class="login-container">
        <h2>Log In</h2>

        <form method="POST" action="{{ route('admin.login.store') }}">
            @csrf

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            @if ($errors->any())
                <div class="mb-2 text-sm text-red-500">
                    {{ $errors->first() }}
                </div>
            @endif

            <p><em>Forgot your password?</em><br>            </p>

            <button type="submit">Log In</button>
        </form>
    </div>
    </div>
</body>
</html>
