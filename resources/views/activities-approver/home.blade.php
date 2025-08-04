<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approver Dashboard - NU Fairview SDAO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/activities.css'])
</head>
<body>

<header class="custom-header">
    <div class="header-left">
        <div class="logo">
            <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
            <span>Student Development and Activities Office - Approver</span>
        </div>
    </div>
    <div class="header-right-group">
        <p>Welcome, {{ session('activities_approver')['first_name'] ?? 'Approver' }}!</p>
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
        <a href="{{ route('activities-approver.home') }}">Home</a>
    </nav>
</header>

<div class="container">
    <h2>Pending Proposals for Approval</h2>

    @forelse($pendingProposals as $proposal)
        <div class="status-card">
            <div class="status-card-header">
                <h3 class="proposal-title">{{ $proposal['title'] }}</h3>
                <a href="{{ route('activities-approver.proposals.review', $proposal['proposal_id']) }}" class="btn btn-primary">View Details</a>
            </div>
            <p class="proposal-id">
                PROP-{{ \Carbon\Carbon::parse($proposal['submitted_at'])->format('Y-m-d') }}
            </p>
            <p class="date-submitted">
                <strong>Date Submitted:</strong>
                {{ \Carbon\Carbon::parse($proposal['submitted_at'])->format('m/d/y') }}
            </p>
            <span class="status-badge">{{ $proposal['status'] }}</span>
        </div>
    @empty
        <p>No pending proposals to review.</p>
    @endforelse
</div>

</body>
</html>
