<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>History - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Fonts & Libraries -->
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

  @vite('resources/css/volunteers.css')

    <style>
    body {
            font-family: 'Inter', sans-serif;
            background: #f9f6f3;
        }

        main.page-container {
            padding: 3rem 2rem;
            max-width: 1000px;
            margin: 0 auto;
        }


        .folder-card {
            border-radius: 12px;
            border: 1px solid ;
            padding: 1.5rem;
            transition: transform 0.2s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }


        .folder-icon {
            font-size: 2rem;
        }

        .folder-title {
            font-weight: 600;
        }

        .folder-count {
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
  <header class="custom-header">
    <div class="header-left">
      <div class="logo">
        <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
        <span>Student Developments and Activities Office - SDA Assistant</span>
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

<main class="page-container">
  <h2 class="mb-4 text-center">Opportunities by Office</h2>

  <div class="row g-4">
    @forelse ($groupedOpportunities as $office => $opportunities)

      <div class="col-md-4">
<a href="{{ route('volunteers-admin.office-opportunities', ['office' => urlencode($office)]) }}" class="text-decoration-none">

          <div class="p-4 text-center folder-card">
            <div class="mb-3 folder-icon">
              <i class="bi bi-folder-fill text-warning fs-1"></i>
            </div>
            <div class="folder-title fs-5">{{ $office }}</div>
            <div class="mt-2 folder-count">{{ count($opportunities) }} Opportunities</div>
          </div>
        </a>
      </div>
    @empty
      <p class="text-center text-muted">No opportunities found.</p>
    @endforelse
  </div>
</main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



  </body>
  </html>