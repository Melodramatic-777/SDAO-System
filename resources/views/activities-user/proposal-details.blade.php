<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Proposal Details - NU Fairview SDAO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/activities.css'])
    <style>


        .container {
            max-width: 2000px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: #003366;
            text-decoration: none;
        }

        .proposal-wrapper {
            display: flex;
            gap: 2rem;
        }

        .proposal-details {
            flex: 2;
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px #ddd;
        }

        .approval-timeline {
            flex: 1;
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px #ddd;
        }

        .approval-timeline h3 {
            margin-top: 0;
        }

        .timeline {
            position: relative;
            margin: 2rem 0;
        }

        .timeline-item {
            position: relative;
            padding-left: 2rem;
            margin-bottom: 2rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.2rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: #003366;
        }

        .timeline-item.complete::before {
            background: #22c55e; /* green for complete */
        }

        .timeline-item.pending::before {
            background: #f59e0b; /* yellow for pending */
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            left: 0.4rem;
            top: 1rem;
            width: 2px;
            height: calc(100% - 1rem);
            background: #ccc;
        }

        .timeline-item:last-child::after {
            display: none;
        }

        .proposal-doc {
            margin: 2rem 0;
        }

        iframe {
            border: 1px solid #ccc;
            border-radius: 6px;
        }

    </style>
</head>
<body>

<header class="custom-header">
    <div class="logo">
        <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="40">
        <span>Student Development and Activities Office</span>
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

<div class="container">
    <a href="{{ route('activities-user.home') }}" class="back-link">← Back to Home</a>

    <div class="proposal-wrapper">
        <div class="proposal-details">
            <h2>{{ $proposal['title'] }}</h2>
            <p>{{ $proposal['organization_name'] }}</p></br>
            

            <p><strong>Start Date</strong><br>{{ $proposal['start_date'] }}</p>
            <p><strong>End Date</strong><br>{{ $proposal['end_date'] }}</p></br>
            <p><strong>Target Participants</strong><br>#{{ $proposal['target_participants'] ?? 'N/A' }}</p></br>

            <h3>Objectives</h3>
            <p><strong>{{ $proposal['objectives'] }}</strong></p></br>

            <h3>Brief Description</h3>
            <p><strong>{{ $proposal['description'] }}</strong></p></br>
        </div>

        <div class="approval-timeline">
            <h3>Approval Timeline</h3>
            <div class="timeline">
                <div class="timeline-item complete">
                    <strong>Proposal submitted for approval</strong><br>
                    {{ \Carbon\Carbon::parse($proposal['submitted_at'])->format('j F Y, h:i A') }}
                </div>

@foreach ($proposal['approval_chain'] as $approval)
    <div class="timeline-item 
        {{ strtolower($approval['status']) === 'pending' ? 'pending' : 'complete' }}">
        <strong>{{ $approval['role'] }}</strong><br>

        {{-- ✅ Show Acknowledged only if approved --}}
        @if (strtolower($approval['status']) === 'approved')
            <em>Acknowledged and signed</em><br>
            <small>Signed at: {{ \Carbon\Carbon::parse($approval['actioned_at'])->format('j F Y, h:i A') }}</small><br>
        @endif

        Status: {{ $approval['status'] }}
    </div>
@endforeach

@php
    $allApproved = collect($proposal['approval_chain'])
        ->every(fn($approval) => strtolower($approval['status']) === 'approved');
@endphp

@if ($allApproved)
    <div style="margin-top: 2rem;">
        <a href="#" class="btn btn-primary">Proceed to SARF</a>
    </div>
@endif

@if ($proposal['status'] === 'Returned')
    <div style="margin: 2rem 0;">
        <form method="POST" action="{{ route('activities-user.proposals.resubmit', $proposal['proposal_id']) }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                Resubmit Proposal
            </button>
        </form>
    </div>
@endif

@if ($proposal['status'] === 'Returned')
    <div style="margin: 2rem 0;">
        <form method="POST" action="{{ route('activities-user.proposals.resubmit', $proposal['proposal_id']) }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                Resubmit Proposal
            </button>
        </form>

        <a href="{{ route('activities-user.proposals.edit', $proposal['proposal_id']) }}" class="btn btn-secondary" style="margin-left: 1rem;">
            Edit Proposal
        </a>
    </div>
@endif




            </div>
        </div>
    </div>

    <div class="proposal-comments">
    <h3>Comments</h3>

    @php
        $hasComments = false;
    @endphp

@foreach ($proposal['approval_chain'] as $approval)
    @if (!empty($approval['comments']))
        @php $hasComments = true; @endphp
        <div class="comment-box">
           <strong>{{ $approval['by_name'] ?? $approval['role'] }}</strong>
 — 
            <span class="status">{{ $approval['status'] }}</span><br>
            <div class="comment-text">{{ $approval['comments'] }}</div>
            <small>{{ \Carbon\Carbon::parse($approval['actioned_at'])->format('j F Y, h:i A') }}</small>
        </div>
    @endif

    {{-- Replies inside this approval block --}}
    @if (!empty($approval['replies']))
        <div class="replies">
            @foreach ($approval['replies'] as $reply)
                <div class="reply-item">
                    <strong>{{ $reply['by'] }}</strong>: {{ $reply['reply'] }}<br>
                    <small>{{ \Carbon\Carbon::parse($reply['replied_at'])->format('j F Y, h:i A') }}</small>
                </div>
            @endforeach
        </div>
    @endif
@endforeach

@if (!empty($proposal['discussion']))
    <div class="discussion-thread">
        @foreach ($proposal['discussion'] as $discussion)
            <div class="reply-item">
                <strong>{{ $discussion['by'] }}</strong>: {{ $discussion['reply'] }}<br>
                <small>{{ \Carbon\Carbon::parse($discussion['replied_at'])->format('j F Y, h:i A') }}</small>
            </div>
        @endforeach
    </div>
@endif



    @unless ($hasComments)
        <p>No comments yet.</p>
    @endunless

    {{-- Example static reply box - if you want dynamic replies later --}}
<div class="reply-box">
    <form method="POST" action="{{ route('activities-user.proposals.reply', $proposal['proposal_id']) }}">
        @csrf
        <textarea name="reply" placeholder="Add a reply..." rows="3"></textarea>
        @error('reply')
            <p style="color: red;">{{ $message }}</p>
        @enderror
        <div class="reply-actions">
            <button type="submit" class="reply-btn">Reply</button>
        </div>
    </form>
</div>


    <div class="proposal-doc">
        <h3>Proposal Document</h3>
        <iframe src="{{ asset('storage/' . $proposal['pdf_file_path']) }}" width="100%" height="600px"></iframe>
        <br>
        <a href="{{ asset('storage/' . $proposal['pdf_file_path']) }}" target="_blank" download>Download Proposal PDF</a>
    </div>
</div>

</body>
</html>
