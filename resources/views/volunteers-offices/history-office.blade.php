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
</head>
<body>

<header class="custom-header">
    <div class="header-left">
        <div class="logo">
            <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
            <span>Student Developments and Activities Office - Office</span>
        </div>
    </div>
    <div class="header-right-group">
        <p>Welcome, {{ session('volunteers_office.first_name') }}!</p>
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
    <a href="{{ route('volunteers-offices.home') }}">Home</a>
    <a href="{{ route('volunteers-offices.history-office') }}">History</a>
</nav>
</header>

<main class="page-container">
    <h2 class="mb-4 text-center">Your Submitted Opportunities</h2>

    <div class="row g-4">

        @forelse ($opportunities as $opportunity)
            <div class="col-md-4">
                <div class="p-4 text-center folder-card">
                    <div class="mb-3 fs-1 text-warning"><i class="bi bi-folder-fill"></i></div>
                    <div class="folder-title">{{ $opportunity['title'] }}</div>
                    <div class="mt-2 small text-muted">{{ $opportunity['submitted_at'] }}</div>

                    <div class="mt-2">
                        <span class="badge 
                            @if($opportunity['status'] === 'Approved') bg-success
                            @elseif($opportunity['status'] === 'Returned') bg-danger
                            @else bg-secondary
                            @endif">
                            {{ $opportunity['status'] }}
                        </span>
                    </div>
                </div>
            </div>

        @empty
            <p class="text-center text-muted">No opportunities found.</p>
        @endforelse

    </div>
</main>

</body>
</html>
