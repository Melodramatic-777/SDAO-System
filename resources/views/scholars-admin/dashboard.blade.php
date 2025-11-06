<!DOCTYPE html>
<html>
<head>
    <title>Activities Admin</title>
    <meta charset="UTF-8">
    <title>Home - NU Fairview SDAO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    @vite('resources/css/admin-activities.css')
</head>
<body>

    <!-- Header -->
    <header class="custom-header">
        <div class="header-left">
            <div class="logo">
                <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
                <span>Student Developments and Activities Office - SDA Associate</span>
            </div>
        </div>
        <div class="header-right-group">
            <p>Welcome, {{ session('scholars_admin.first_name') }}!</p>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="side-button">
                    <img src="{{ asset('images/Side.png') }}" alt="Side" height="30">
                </button>
            </form>
        </div>
    </header>

    <!-- Navigation -->
    <header>
        <nav>
            <a href="{{ route('scholars-admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('scholars-admin.scholars_accounts') }}">Accounts</a>
            <a href="{{ route('scholars-admin.scholars') }}">Scholars</a>
            <a href="{{ route('scholars-admin.scholarships') }}">Scholarships</a>
        </nav>
    </header>

    <!-- Dashboard -->
<div class="dashboard">
    <!-- Left Section -->
    <div class="dashboard-left">
        <!-- Top Row -->
        <div class="cards">
            <div class="card">
                <div class="card-title">Total Scholars</div>
                <div class="card-value">{{ $total }}</div>
            </div>
            @foreach($byScholarship as $type => $count)
                <div class="card">
                    <div class="card-title">{{ strtoupper($type) }}</div>
                    <div class="card-value">{{ $count }}</div>
                </div>
            @endforeach
  
        </div>

        <!-- Chart + Extra Cards -->
        <div class="chart-row">
            <div class="chart-container">
                <canvas id="scholarChart"></canvas>
            </div>
            <div class="extra-cards">

            <div class="card">
                <div class="card-title">Probationary</div>
                <div class="card-value">{{ $probationary }}</div>
            </div>


                <div class="card">
                    <div class="card-title">Graduates this AY</div>
                    <div class="card-value" style="color:green;">0 </div>
                </div>
                <div class="card">
                    <div class="card-title">Retention Rate</div>
                    <div class="card-value" style="color:blue;">90%</div>
                </div>
                <div class="card">
                    <div class="card-title">Scholars with Below Required Hours</div>
                    <div class="card-value" style="color:red;">0</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications always on the right -->
    <div class="dashboard-right">
        <h3>Notifications</h3>
        <div class="notifications">
            <div class="notification gray">
                <em>No notifications yet...</em>
            </div>
        </div>
    </div>
</div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('scholarChart').getContext('2d');
        const scholarChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: {!! json_encode(array_keys($byScholarship)) !!},
                datasets: [{
                    data: {!! json_encode(array_values($byScholarship)) !!},
                    backgroundColor: ['#3b82f6','#6b7280','#facc15','#f59e0b','#10b981','#ef4444'],
                }]
            },
            options: {
        responsive: false,          // stop auto resizing
        maintainAspectRatio: false, // fill container
        plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>

</body>
</html>
