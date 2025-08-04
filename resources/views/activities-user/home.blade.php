<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Home - NU Fairview SDAO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/activities.css'])
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
            <p>Welcome, {{ session('activities_user')['first_name'] ?? 'User' }}!</p>
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
        <a href="{{ route('activities-user.home') }}">Home</a>
        <a href="{{ route('activities-user.activities-calendar') }}">Activities Calendar</a>
        <a href="{{ route('activities-user.history') }}">History</a>
        <a href="{{ route('activities-user.profile') }}">Profile</a>
    </nav>
    </header>

            <a href="{{ route('activities-user.create-proposal') }}" class="btn btn-primary">Submit a Proposal</a>


    <div class="tabs">
        <button class="tab-link active" data-tab="Status">Status</button>
        <button class="tab-link" data-tab="Proposals" >Proposals</button>
        <button class="tab-link" data-tab="SARF" >SARF</button>
    </div>
  

      <div class="status-section">
        <h2>Status</h2>
        @forelse($proposals as $proposal)
        <div class="status-card">
            <div class="status-card-header">
            <h3 class="proposal-title">{{ $proposal['title'] }}</h3>
            <a href="{{ route('activities-user.proposals.show', $proposal['proposal_id']) }}" class="view-details-link">View Details</a>
            </div>
            <p class="proposal-id">
            PROP-{{ \Carbon\Carbon::parse($proposal['submitted_at'])->format('Y-m-d') }}
            </p>
            <p class="date-submitted">
            <strong>Date Submitted:</strong>
            {{ \Carbon\Carbon::parse($proposal['submitted_at'])->format('m/d/y') }}
            </p>
            <span class="status-badge {{ $proposal['status'] }}">{{ $proposal['status'] }}</span>
        </div>
        @empty
        <p>No submitted activity found.</p>
        @endforelse
    </div>
    


</body>
</html>
