<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>History - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Fonts & Libraries -->
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  @vite('resources/css/volunteers.css')

  <style>
    .folder-card {
      display: flex;
      align-items: center;
      border-radius: 12px;
      border: 1px solid #d8d3c3;
      background-color: #fff;
      padding: 1.5rem;
      transition: 0.2s ease;
      cursor: pointer;
    }

    .folder-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .folder-icon {
      font-size: 2.5rem;
      color: #f6d629;
      margin-right: 1rem;
      flex-shrink: 0;
    }

    .folder-info {
      text-align: left;
    }

    .folder-title {
      font-weight: 600;
      color: #333;
      font-size: 1.1rem;
    }

    .folder-count {
      font-size: 0.9rem;
      color: #666;
      margin-top: 0.25rem;
    }

    main {
      max-width: 1100px;
      margin: 0 auto;
    }

  </style>
</head>

<body>
  <header class="custom-header">
    <div class="header-left">
      <div class="gap-2 logo d-flex align-items-center">
        <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
        <span>Student Developments and Activities Office - SDA Assistant</span>
      </div>
    </div>

    <div class="gap-3 header-right-group d-flex align-items-center">
      <p class="m-0">Welcome, {{ session('volunteers_admin.first_name') }}!</p>
      <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="bg-transparent border-0 side-button">
          <img src="{{ asset('images/Side.png') }}" alt="Side" height="30">
        </button>
      </form>
    </div>
  </header>

  <header>
    <nav>
      <a href="{{ route('volunteers-admin.dashboard') }}">Dashboard</a>
      <a href="{{ route('volunteers-admin.opportunities') }}">Volunteer Requests</a>
      <a href="{{ route('volunteers-admin.history') }}">History</a>
      <a href="{{ route('volunteers-admin.student-volunteers') }}">Student Volunteers</a>
      <a href="{{ route('volunteers-admin.accounts') }}">Accounts</a>
      <a href="{{ route('volunteers-admin.inquiries') }}">Contact</a>
    </nav>
  </header>

  <main class="p-4 container-fluid">
    <h2 class="mb-4 text-center">Opportunities by Office</h2>

    <div class="row g-4">
      @forelse ($groupedOpportunities as $office => $opportunities)
        <div class="col-md-4">
          <a href="{{ route('volunteers-admin.office-opportunities', ['office' => urlencode($office)]) }}" class="text-decoration-none text-dark">
            <div class="folder-card">
              <div class="folder-icon">
                <i class="bi bi-folder-fill"></i>
              </div>
              <div class="folder-info">
                <div class="folder-title">{{ $office }}</div>
                <div class="folder-count">{{ count($opportunities) }} Opportunities</div>
              </div>
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
