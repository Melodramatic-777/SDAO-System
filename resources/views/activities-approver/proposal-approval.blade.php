<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Proposal Approval - NU Fairview SDAO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
    <style>
        body {
            font-family: Inter, sans-serif;
            background: #f9f9f9;
            padding: 2rem;
        }
        .proposal-box {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px #ddd;
            max-width: 900px;
            margin: 0 auto;
        }
        .approval-section {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #ddd;
        }
        .approval-section h3 {
            margin: 0 0 1rem;
        }
        .approval-section textarea {
            width: 100%;
            height: 100px;
            margin-bottom: 1rem;
            resize: vertical;
        }
        .approval-section button {
            margin-right: 1rem;
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body>

    <div class="proposal-box">
        <h2>{{ $proposal['title'] }}</h2>
        <p><strong>Organization:</strong> {{ $proposal['organization_name'] }}</p>
        <p><strong>Start Date:</strong> {{ $proposal['start_date'] }}</p>
        <p><strong>End Date:</strong> {{ $proposal['end_date'] }}</p>
        <p><strong>Objectives:</strong> {{ $proposal['objectives'] }}</p>
        <p><strong>Description:</strong> {{ $proposal['description'] }}</p>

        <h3>Approval Chain</h3>
        <ul>
            @foreach ($proposal['approval_chain'] as $approval)
                <li>
                    <strong>{{ $approval['role'] }}</strong> —
                    Status: {{ $approval['status'] }}
                </li>
            @endforeach
        </ul>

        @if (!empty($proposal['discussion']))
    <div class="discussion-thread">
        @foreach ($proposal['discussion'] as $entry)
            <div class="reply-item">
                <strong>{{ $entry['by'] }}</strong>: {{ $entry['reply'] }}<br>
                <small>{{ \Carbon\Carbon::parse($entry['replied_at'])->format('j F Y, h:i A') }}</small>
            </div>
        @endforeach
    </div>
@endif



        <div class="approval-section">
            <h3>Your Decision</h3>
<form method="POST" action="{{ route('activities-approver.proposals.approve', $proposal['proposal_id']) }}">
    @csrf
    <label for="comments">Comments (optional):</label>
    <textarea name="comments" placeholder="Add comments..."></textarea>
    <br>
    <button type="submit" name="action" value="approve">✅ Approve</button>
    <button type="submit" name="action" value="reject">❌ Return</button>
</form>

        </div>
    </div>

</body>
</html>
