<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Leaderboard - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  @vite('resources/css/app.css')
<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
body { min-height: 100vh; display: flex; flex-direction: column; }
header { background-color: #003DA5; color: white; display: flex; justify-content: space-between; align-items: center; padding: 1rem 2rem; }
header .logo { font-weight: bold; display: flex; align-items: center; gap: 0.5rem; }
header nav a { color: white; margin: 0 0.75rem; text-decoration: none; font-weight: 500; font-size: 0.9rem; }
.login-container { background-color: white; max-width: 400px; margin: 4rem auto; padding: 2rem; border: 2px solid #003DA5; border-radius: 25px; text-align: center; }
.login-container h2 { color: #003DA5; font-size: 2rem; margin-bottom: 1.5rem; font-weight: 800; font-style: italic; }
.login-container label { display: block; text-align: left; margin-bottom: 0.25rem; font-size: 0.9rem; }
.login-container input { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #003DA5; border-radius: 5px; }
.login-container p { font-size: 0.85rem; color: #555; margin-bottom: 1rem; }
.login-container button { background-color: #003DA5; color: white; padding: 0.75rem 2rem; border: none; border-radius: 25px; font-weight: bold; cursor: pointer; }
.custom-header { background-color: white; display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 2rem; border-bottom: 2px solid #003DA5; }
.custom-header .logo { display: flex; align-items: center; gap: 0.75rem; font-weight: bold; color: #003DA5; }
.custom-header .logo img { height: 40px; width: auto; }
.custom-header .header-right p { color: #003DA5; font-weight: 100; font-size: 1rem; margin: 0; }
.side-button { background: none; border: none; padding: 0; cursor: pointer; }
.side-button img { height: 30px; width: auto; }
.header-right-group { display: flex; align-items: center; gap: 5.00rem; }
.header-right-group p { color: #003DA5; font-weight: 100; font-size: 1rem; margin: 0; }
.tabs { display: flex; border-bottom: 2px solid #003DA5; margin: 1rem 2rem; flex-wrap: wrap; }
.tabs button { background: none; border: none; padding: 1rem; cursor: pointer; font-weight: bold; color: #003DA5; }
.tabs button.active { border-bottom: 3px solid #003DA5; }
.tab-content { display: none; padding: 2rem; background: #fff; margin: 0 2rem; border: 1px solid #ccc; }
.tab-content.active { display: block; }
.subtab-content { display: none; }
.subtab-content.active { display: block; }
table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
table, th, td { border: 1px solid #003DA5; }
th, td { padding: 0.75rem; text-align: left; }
.btn { background: #003DA5; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; margin-right: 0.5rem; }
.modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background: rgba(0,0,0,0.5); }
.modal-content { background: #fff; margin: 5% auto; padding: 2rem; width: 50%; border-radius: 8px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; }
.close { cursor: pointer; font-size: 1.5rem; }
.org-filter-btn { background: none; border: 2px solid #003DA5; color: #003DA5; padding: 0.5rem 1rem; margin-right: 0.5rem; border-radius: 5px; cursor: pointer; font-weight: bold; transition: background 0.3s, color 0.3s; }
.org-filter-btn.active, .org-filter-btn:hover { background: #003DA5; color: #fff; }
</style>

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
            <p>Welcome, {{ session('volunteers_admin.first_name') }}!</p>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="side-button">
                    <img src="{{ asset('images/Side.png') }}" alt="Side" height="30">
                </button>
            </form>
        </div>
    </header>

<!-- ✅ Navigation -->
<header>
    <nav>
        <a href="{{ route('volunteers-admin.leader-board') }}">Leader board</a>
        <a href="{{ route('volunteers-admin.office') }}">Office</a>
        <a href="{{ route('volunteers-admin.student-volunteers') }}">Student Volunteers</a>
        <a href="{{ route('volunteers-admin.accounts') }}">Accounts</a>
        <a href="{{ route('volunteers-admin.inquiries') }}">Inquiries</a>
    </nav>
    </header>


<!-- ✅ JS Logic -->
<script>






</script>

</body>
</html>
