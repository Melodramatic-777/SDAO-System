<!DOCTYPE html>
<html>
<head>
    <title>Activities Admin</title>
</head>
<meta charset="UTF-8">
    <title>Home - NU Fairview SDAO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">

    @vite('resources/css/app.css') 
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
            margin: 0 0.75rem;  
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;  
        }


        .login-container {
            background-color: white;
            max-width: 400px;
            margin: 4rem auto;
            padding: 2rem;
            border: 2px solid #003DA5;
            border-radius: 25px;
            text-align: center;
        }

        .login-container h2 {
            color: #003DA5;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            font-weight: 800;
            font-style: italic;
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

        .custom-header {
            background-color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 2rem;
            border-bottom: 2px solid #003DA5;
        }

        .custom-header .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: bold;
            color: #003DA5;
        }

        .custom-header .logo img {
            height: 40px; /* ✅ Adjust logo height */
            width: auto;  /* ✅ Keeps aspect ratio */
        }

        .custom-header .header-right p {
            color: #003DA5;
            font-weight: 100;
            font-size: 1rem;
            margin: 0;
        }


            .side-button {
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
        }

        .side-button img {
            height: 30px;
            width: auto;
        }

        .header-right-group {
            display: flex;
            align-items: center;
            gap: 5.00rem; /* ✅ Controls spacing between welcome text & button */
        }

        .header-right-group p {
            color: #003DA5;
            font-weight: 100;
            font-size: 1rem;
            margin: 0;
        }

    </style>
<body>

    <header class="custom-header">
        <div class="header-left">
            <div class="logo">
                <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
                <span>Student Developments and Activities Office - Activities</span>
            </div>
        </div>
        <div class="header-right-group">
            <p>Welcome, {{ session('admin.first_name') }}!</p>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="side-button">
                    <img src="{{ asset('images/Side.png') }}" alt="Side" height="30">
                </button>
            </form>
        </div>
    </header>


    <header>
    <nav>
        <a href="{{ route('activities-admin.home') }}">Home</a>
        <a href="{{ route('activities-admin.activities-calendar') }}">Activities Calendar</a>
        <a href="{{ route('activities-admin.documents') }}">Documents</a>
        <a href="{{ route('activities-admin.profile') }}">Profile</a>
        <a href="{{ route('activities-admin.accounts') }}">Accounts</a>
        <a href="{{ route('activities-admin.inquiries') }}">Inquiries</a>
    </nav>
    </header>

    <h1>ACCOUNTS</h1>
    
</body>
</html>
