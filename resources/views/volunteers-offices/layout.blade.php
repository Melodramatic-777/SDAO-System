<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Volunteers Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    @vite('resources/css/Volunteer_Office.css')


  @vite('resources/css/volunteers.css')

  
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
        }
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
.sidebar {
    width: 250px;
    background: white;
    padding: 20px;
    color: #003DA5;
    flex-shrink: 0;
    border-right: 2px solid #e5e7eb; /* <-- adds a subtle line */
}
        .sidebar a {
            display: block;
            color: #003DA5;
            text-decoration: none;
            padding: 10px 0 10px 15px; 
            margin-bottom: 5px;
            border-radius: 5px;
        }
        .sidebar a:hover {
            background: #d9e4ff;
        }
        .sidebar a.active {
            background: #d9e4ff;
        }
        .content-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f3f4f6;
        }
        .custom-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
        }
        main.page-container {
            padding: 20px 30px;
            flex: 1;
            overflow-x: auto;
        }

        
    .sidebar.collapsed {
    width: 0;
    padding: 0;
    overflow: hidden;
}

.content-area.expanded {
    margin-left: 0;
}

.assistant-container {
    display: flex;
    align-items: center;
    justify-content: space-between; /* pushes button to the RIGHT */
    margin-bottom: 20px; /* <— bottom margin added */
}

.assistant-text {
    font-size: 18px;
    font-weight: 600;
    padding: 10px 0 10px 15px; 
}

.assistant-btn {
    display: none; /* hidden initially */
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
    <!-- BUTTON MOVED INSIDE (HIDDEN BY DEFAULT) -->

<div class="assistant-container">
    <span class="assistant-text">Office</span>

    <button class="assistant-btn">
        <img src="{{ asset('images/Side.png') }}" height="30">
    </button>
</div>
    <a href="{{ route('volunteers-offices.dashboard-office') }}">Dashboard</a>
    <a href="{{ route('volunteers-offices.request-volunteer') }}">Volunteer Requests</a>
    <a href="{{ route('volunteers-offices.history-office') }}">History</a>
    </div>

    <!-- CONTENT -->
    <div class="content-area" id="content-area">
        @yield('content')
    </div>

</div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
const headerBtn = document.querySelector('.header-btn'); // header button
const sidebarBtn = document.querySelector('.assistant-btn'); // sidebar button inside sidebar
const sidebar = document.getElementById('sidebar');
const contentArea = document.getElementById('content-area');

// Check localStorage on page load
document.addEventListener('DOMContentLoaded', () => {
    const sidebarState = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarState) {
        sidebar.classList.add('collapsed');
        contentArea.classList.add('expanded');
        headerBtn.style.display = "inline-flex";
        sidebarBtn.style.display = "none";
    } else {
        sidebar.classList.remove('collapsed');
        contentArea.classList.remove('expanded');
        headerBtn.style.display = "none";
        sidebarBtn.style.display = "inline-flex";
    }
});

function toggleSidebar() {
    sidebar.classList.toggle('collapsed');
    contentArea.classList.toggle('expanded');

    const isCollapsed = sidebar.classList.contains('collapsed');

    if (isCollapsed) {
        headerBtn.style.display = "inline-flex";
        sidebarBtn.style.display = "none";
    } else {
        headerBtn.style.display = "none";
        sidebarBtn.style.display = "inline-flex";
    }

    // Save current state to localStorage
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

// Attach click events
headerBtn.addEventListener('click', (e) => { e.preventDefault(); toggleSidebar(); });
sidebarBtn.addEventListener('click', (e) => { e.preventDefault(); toggleSidebar(); });




</script>

</body>
</html>
