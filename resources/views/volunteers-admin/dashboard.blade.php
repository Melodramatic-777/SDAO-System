<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Leaderboard - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  @vite('resources/css/volunteers.css')


    <style>

    .leaderboard-container {
      max-width: 1000px;
      margin: 40px auto;
      padding: 20px;
      border-radius: 12px;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 24px;
      color: #333;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      text-align: center;
      font-size: 15px;
    }

    table thead {
      background: #FFD500; /* Yellow Header */
    }

    table thead th {
      padding: 12px;
      font-weight: bold;
      color: #333;
      border: 1px solid #ddd;
    }

    table tbody td {
      padding: 12px;
      border: 1px solid #ddd;
      color: #444;
    }

    table tbody tr:nth-child(even) {
      background: #f7f7f7;
    }
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
    <a href="{{ route('volunteers-admin.dashboard') }}">Leader Board</a>
    <a href="{{ route('volunteers-admin.opportunities') }}">Volunteer Requests</a>
    <a href="{{ route('volunteers-admin.history') }}">History</a>
    <a href="{{ route('volunteers-admin.student-volunteers') }}">Student Volunteers</a>
    <a href="{{ route('volunteers-admin.accounts') }}">Accounts</a>
    <a href="{{ route('volunteers-admin.inquiries') }}">Contact</a>
    </nav>
    </header>
<div class="leaderboard-container">
  <h2>Volunteer Hours Leaderboard</h2>
  <table>
    <thead>
      <tr>
        <th>Rank #</th>
        <th>Scholarship Type</th>
        <th>Name</th>
        <th>Program</th>
        <th>Total Hours</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Blue</td>
        <td>Santos, John Carlo</td>
        <td>BS Information Technology</td>
        <td>112 hrs</td>
      </tr>
      <tr>
        <td>2</td>
        <td>White</td>
        <td>Ramirez, Alyssa Mae</td>
        <td>BS Psychology</td>
        <td>105 hrs</td>
      </tr>
      <tr>
        <td>3</td>
        <td>UAEB</td>
        <td>Cruz, Nathaniel R.</td>
        <td>BS Accountancy</td>
        <td>98 hrs</td>
      </tr>
      <tr>
        <td>4</td>
        <td>Blue</td>
        <td>Mendoza, Trisha Joy</td>
        <td>BS Computer Engineering</td>
        <td>94 hrs</td>
      </tr>
      <tr>
        <td>5</td>
        <td>White</td>
        <td>Villanueva, James R.</td>
        <td>BSBA Marketing Management</td>
        <td>90 hrs</td>
      </tr>
      <tr>
        <td>6</td>
        <td>UAEB</td>
        <td>Dela Cruz, Bianca F.</td>
        <td>BS Hospitality Management</td>
        <td>88 hrs</td>
      </tr>
      <tr>
        <td>7</td>
        <td>Blue</td>
        <td>Garcia, Elvin Ray</td>
        <td>BS Architecture</td>
        <td>85 hrs</td>
      </tr>
      <tr>
        <td>8</td>
        <td>White</td>
        <td>Tan, Isabelle Grace</td>
        <td>BS Tourism Management</td>
        <td>82 hrs</td>
      </tr>
      <tr>
        <td>9</td>
        <td>UAEB</td>
        <td>Torres, Kenneth L.</td>
        <td>BS Information Technology</td>
        <td>80 hrs</td>
      </tr>
      <tr>
        <td>10</td>
        <td>Blue</td>
        <td>Lim, Joanna Faith</td>
        <td>BS Civil Engineering</td>
        <td>78 hrs</td>
      </tr>
    </tbody>
  </table>
</div>

<!-- ✅ JS Logic -->
<script>






</script>

</body>
</html>
