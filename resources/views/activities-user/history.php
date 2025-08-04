<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>History - NU Fairview SDAO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/activities.css'])
</head>
<body>

    <header class="custom-header">
        <div class="header-left">
            <div class="logo">
                <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
                <span>Student Developments and Activities Office - Activities</span>
            </div>
        </div>
        <div class="header-right-group">
            <p>Welcome, {{ session('activities_user.first_name') }}!</p>
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
        <a href="{{ route('activities-user.home') }}">Home</a>
        <a href="{{ route('activities-user.activities-calendar') }}">Activities Calendar</a>
        <a href="{{ route('activities-user.history') }}">History</a>
        <a href="{{ route('activities-user.profile') }}">Profile</a>
    </nav>
    </header>

    
</body>
</html>
