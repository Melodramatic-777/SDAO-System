<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Profile - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  @vite('resources/css/volunteers.css')

  <style>
  #scholarsTable { transition: opacity 0.3s ease-in-out; max-width: 900px; margin: 0 auto; }

  .table-title-row th {
    background-color: #004aad !important; color: white !important; text-align: center; font-size: 1.25rem;
  }

  .table-header-row th {
    background-color: #FFD700 !important; color: black !important; font-weight: bold !important; text-align: center;
  }

  .status-card {
    background: white;
    border-radius: 12px;
    padding: 18px;
    text-align: center;
    box-shadow: 0 3px 12px rgba(0,0,0,0.08);
    transition: 0.2s;
  }

  .status-card:hover { transform: translateY(-3px); }

  .status-card h6 { font-size: 0.9rem; margin-bottom: 6px; color: #555; font-weight: 600; }
  .status-card h3 { font-size: 2rem; margin: 0; font-weight: 700; }

  .status-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
  }

  .info-label { font-weight: 600; color: #333; }
  .info-value { font-weight: 500; color: #000; text-align: right; }

  /* ✅ Make the container wider */
  .container {
      max-width: 2000px !important;
  }
  </style>

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
    <form method="POST" action="">
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

<div class="mx-5 mt-4 row align-items-center">

{{-- LEFT STUDENT INFO --}}
<div class="col-md-4">

    <h4 class="fw-bold">{{ $studentData['last_name'] }}, {{ $studentData['first_name'] }}</h4>
    <p class="text-muted">Program: {{ $studentData['program'] }}</p>

    <div class="mb-2 info-item d-flex align-items-center">
        <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
            <path d="M3 5h18v14H3V5zm2 2v10h14V7H5zm2 2h4v2H7V9zm0 3h6v2H7v-2z"/>
        </svg>
        <span class="info-label">Student ID Number:</span>
        <span class="info-value ms-auto">{{ $studentData['student_no'] }}</span>
    </div>

    <div class="mb-2 info-item d-flex align-items-center">
        <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
            <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
        </svg>
        <span class="info-label">School:</span>
        <span class="info-value ms-auto">{{ $studentData['school'] }}</span>
    </div>

    <div class="mb-2 info-item d-flex align-items-center">
        <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
            <path d="M4 4h16v16H4V4zm8 7l8-5H4l8 5zm8 7V9l-8 5-8-5v9h16z"/>
        </svg>
        <span class="info-label">Email Address:</span>
        <span class="info-value ms-auto">{{ $studentData['email'] }}</span>
    </div>

    <div class="mb-2 info-item d-flex align-items-center">
        <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
            <path d="M12 2l4 7h6l-4.9 4.2L18 22l-6-4-6 4 1.9-8.8L2 9h6l4-7z"/>
        </svg>
        <span class="info-label">Scholarship Type:</span>
        <span class="info-value ms-auto">{{ $studentData['scholarship_type'] }}</span>
    </div>

</div>



</div>

</div>


</div> <!-- END OF ROW -->

<hr class="my-4">

<div class="p-4 container-fluid">

<div class="mb-3 d-flex align-items-center">
  <strong class="me-2">Sort by:</strong>

  <!-- Sort Dropdown -->
  <select class="form-select" id="sort-newest" style="width: 220px; margin-right: 10px;">
      <option value="newest">Newest</option>
      <option value="oldest">Oldest</option>
  </select>

  <!-- Office Dropdown (same exact style as Volunteer Opportunities page) -->
<select class="form-select" id="filter-office" style="width: 220px;">
  <option value="">All Offices</option>
  <option value="SDA Assistant">Student Development and Activities Office</option> <!-- ✅ Added -->
  @foreach($offices as $office)
    <option value="{{ $office['name'] }}">{{ $office['name'] }}</option>
  @endforeach
</select>
</div>





    <div class="table-responsive">
        <table class="table align-middle table-bordered">
            <thead>
                <tr class="table-title-row">
                    <th colspan="8">Volunteer History</th>
                </tr>
                <tr class="table-header-row">
                    <th>Date</th>
                    <th>Office</th>
                    <th>Volunteer Activity</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Total Time</th>
                    <th>Office Feedback</th>
                    <th>Volunteer Feedback to the Office</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td colspan="8" class="py-4 text-center text-muted">
                        No volunteer records yet.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>
</html>
