<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  @vite('resources/css/volunteers.css')

    <style>


    .container-dashboard {
      max-width: 1200px;
      margin: 20px auto;
    }

    .row-stats {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .card-stat {
      flex: 1;
      min-width: 200px;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      text-align: center;
    }

    .card-stat h3 {
      margin: 10px 0;
      font-size: 1.5rem;
    }

    .card-stat p {
      margin: 0;
      color: #6c757d;
    }

    .pie-section {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }

    .pie-chart, .completion-circle {
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      flex: 1;
      min-width: 250px;
      text-align: center;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .pie-chart canvas, .completion-circle canvas {
      margin: 0 auto;
      display: block;
    }

    .tasks-section {
      margin-top: 30px;
    }

    .task-card {
      background-color: #fff;
      border: 1px solid #007bff;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .task-info {
      flex: 1;
    }

    .task-info h5 {
      margin: 0;
    }

    .task-info small {
      color: #6c757d;
    }

    .btn-accomplished {
      background-color: #28a745;
      border: none;
      color: #fff;
      padding: 6px 12px;
      border-radius: 5px;
      cursor: pointer;
    }

  </style>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<header class="custom-header">
    <div class="header-left">
        <div class="logo">
            <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
            <span>Student Developments and Activities Office - Scholars</span>
        </div>
    </div>
    <div class="header-right-group">
        <p>Welcome, {{ session('scholar_accounts.first_name') }}!</p>
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
    <a href="{{ route('scholars.dashboard') }}">Dashboard</a>
    <a href="{{ route('scholars.volunteer-opportunities') }}">Volunteer Opportunity</a>
    <a href="{{ route('scholars.invites') }}">Volunteer Invite</a>
    <a href="{{ route('scholars.profile') }}">Profile</a>
</nav>
</header>

<div class="container-dashboard">

  <div class="pie-section">
    <div class="pie-chart">
      <canvas id="volunteerPie"></canvas>
      <p>Office Volunteer Hours</p>
    </div>
    <div class="completion-circle">
      <canvas id="completionCircle"></canvas>
      <p>Completed the required minimum 10 hours</p>
    </div>
  </div>

  <div class="row-stats">
    <div class="card-stat">
      <p>Volunteer Hour Rank</p>
      <h3>10th</h3>
    </div>
    <div class="card-stat">
      <p>Hours Completed</p>
      <h3>45</h3>
    </div>
    <div class="card-stat">
      <p>Activities Participated</p>
      <h3>12</h3>
    </div>
    <div class="card-stat">
      <p>Hours This Month</p>
      <h3>8</h3>
    </div>
    <div class="card-stat">
      <p>Remaining Hours</p>
      <h3>0</h3>
    </div>
  </div>

  <div class="tasks-section">
    <h4>Your Upcoming Tasks</h4>
    <div class="task-card">
      <div class="task-info">
        <h5>Enrollment ID Picture Taking</h5>
        <small>IT Systems Office | At Room: AVR</small><br>
        <small>Date: March 1, 2025 | Time: 7:00 AM - 5:00 PM</small>
      </div>
      <div>
        <button class="btn-accomplished">Mark as Accomplished</button>
      </div>
    </div>
  </div>

</div>

<script>
    const scholarId = "{{ session('scholar_accounts.student_no') }}";
const scholarName = "{{ session('scholar_accounts.first_name') }} {{ session('scholar_accounts.last_name') }}";


const ctxPie = document.getElementById('volunteerPie').getContext('2d');
new Chart(ctxPie, {
  type: 'pie',
  data: {
    labels: ['IT Systems Office', 'SDAO', 'Faculty'],
    datasets: [{
      data: [9, 3, 2],
      backgroundColor: ['#375a7f', '#4bc0c0', '#a1e7e3']
    }]
  }
});

const ctxCircle = document.getElementById('completionCircle').getContext('2d');
new Chart(ctxCircle, {
  type: 'doughnut',
  data: {
    labels: ['Completed', 'Remaining'],
    datasets: [{
      data: [100, 0],
      backgroundColor: ['#007bff', '#e9ecef']
    }]
  },
  options: {
    cutout: '80%',
    plugins: {
      legend: { display: false },
      tooltip: { enabled: false }
    }
  }
});


</script>
</body>
</html>
