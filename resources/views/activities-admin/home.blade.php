<!DOCTYPE html>
<html>
<head>
    <title>Activities Admin</title>
</head>
<meta charset="UTF-8">
    <title>Home - NU Fairview SDAO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    @vite('resources/css/admin-activities.css' )
<body>

    <header class="custom-header">
        <div class="header-left">
            <div class="logo">
                <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
                <span>Student Developments and Activities Office - Activities</span>
            </div>
        </div>
        <div class="header-right-group">
            <p>Welcome, {{ session('activities_admin.first_name') }}!</p>
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
        <a href="{{ route('activities-admin.home') }}">Home</a>
        <a href="{{ route('activities-admin.activities-calendar') }}">Activities Calendar</a>
        <a href="{{ route('activities-admin.documents') }}">Documents</a>
        <a href="{{ route('activities-admin.profile') }}">Profile</a>
        <a href="{{ route('activities-admin.accounts') }}">Accounts</a>
        <a href="{{ route('activities-admin.inquiries') }}">Inquiries</a>
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
