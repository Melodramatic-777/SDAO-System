<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scholars Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Fonts & CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Vite CSS -->
    @vite('resources/css/Scholar_Admin.css')
    @vite('resources/css/volunteers.css')

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* FIXED SIDEBAR */
        .sidebar {
            width: 250px;
            background: white;
            padding: 20px;
            color: #003DA5;
            border-right: 2px solid #e5e7eb;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow-y: auto;
            z-index: 1000; /* keep above content */
        }

        .sidebar a {
            display: block;
            color: #003DA5;
            text-decoration: none;
            padding: 10px 0 10px 15px;
            margin-bottom: 5px;
            border-radius: 5px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #d9e4ff;
        }

        .logout-container {
            margin-top: auto;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }

        /* CONTENT AREA */
.content-wrapper {
    margin-left: 250px; /* same as sidebar width */
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    overflow-x: auto;
}

.page-container {
    padding: 80px 30px 30px 30px; /* top padding to avoid fixed header overlap */
    width: 100%;
    max-width: calc(100% - 0px); /* same width as header */
    box-sizing: border-box;
}

        /* CUSTOM HEADER */
        .custom-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            position: fixed;
            top: 0;
            left: 250px; /* default when sidebar is visible */
            right: 0;
            z-index: 900;
            height: 60px;
            transition: left 0.3s; /* smooth transition when sidebar toggles */
        }

        /* COLLAPSED SIDEBAR */
        .sidebar.collapsed {
            width: 0;
            padding: 0;
            overflow: hidden;
        }

        .content-wrapper.expanded {
            margin-left: 0;
        }

        .content-wrapper.expanded .custom-header {
            left: 0; /* fill top when sidebar is hidden */
        }

        .assistant-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .assistant-text {
            font-size: 18px;
            font-weight: 600;
            padding: 10px 0 10px 15px;
        }

        .assistant-btn {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- SIDEBAR -->
        <div class="sidebar" id="sidebar">
            <div class="assistant-container">
                <span class="assistant-text">SDA Associate</span>
                <button class="assistant-btn">
                    <img src="{{ asset('images/Side.png') }}" height="30">
                </button>
            </div>
<a href="{{ route('scholars-admin.dashboard') }}"
   class="{{ Request::is('scholars-admin/dashboard') ? 'active' : '' }}">
   Dashboard
</a>

<a href="{{ route('scholars-admin.scholars_accounts') }}"
   class="{{ Request::is('scholars-admin/scholars-accounts') ? 'active' : '' }}">
   Accounts
</a>

<a href="{{ route('scholars-admin.scholars') }}"
   class="{{ Request::routeIs('scholars-admin.scholars') ? 'active' : '' }}">
   Scholars
</a>


<a href="{{ route('scholars-admin.scholarships') }}"
   class="{{ Request::is('scholars-admin/scholarships') ? 'active' : '' }}">
   Scholarships
</a>


<a href="{{ route('scholars-admin.contactUs') }}"
   class="{{ Request::is('scholars-admin/contactUs') ? 'active' : '' }}">
   Contact
</a>

            <div class="logout-container">
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="logout-btn">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
                <form id="logout-form" action="{{ route('scholars-admin.logout') }}" method="POST" style="display:none;">
                    @csrf
                </form>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="content-wrapper" id="content-wrapper">
            <header class="custom-header">
                <div class="header-left">
                    <button type="submit" class="side-button header-btn">
                        <img src="{{ asset('images/Side.png') }}" alt="Side" height="30">
                    </button>
                    <div class="logo">
                        <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
                        <span>Student Developments and Activities Office</span>
                    </div>
                </div>
                <div class="header-right-group">
                    <p>Welcome, {{ session('scholars_admin.first_name') }}!</p>
                </div>
            </header>

            <main class="page-container">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const headerBtn = document.querySelector('.header-btn');
        const sidebarBtn = document.querySelector('.assistant-btn');
        const sidebar = document.getElementById('sidebar');
        const contentWrapper = document.getElementById('content-wrapper');

        document.addEventListener('DOMContentLoaded', () => {
            const sidebarState = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarState) {
                sidebar.classList.add('collapsed');
                contentWrapper.classList.add('expanded');
                headerBtn.style.display = "inline-flex";
                sidebarBtn.style.display = "none";
            } else {
                sidebar.classList.remove('collapsed');
                contentWrapper.classList.remove('expanded');
                headerBtn.style.display = "none";
                sidebarBtn.style.display = "inline-flex";
            }
        });

        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            contentWrapper.classList.toggle('expanded');
            const isCollapsed = sidebar.classList.contains('collapsed');

            if (isCollapsed) {
                headerBtn.style.display = "inline-flex";
                sidebarBtn.style.display = "none";
            } else {
                headerBtn.style.display = "none";
                sidebarBtn.style.display = "inline-flex";
            }

            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }

        headerBtn.addEventListener('click', (e) => {
            e.preventDefault();
            toggleSidebar();
        });

        sidebarBtn.addEventListener('click', (e) => {
            e.preventDefault();
            toggleSidebar();
        });
    </script>
</body>
</html>
