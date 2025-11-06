<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Opportunity Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

  <h2>{{ $opportunity['title'] }}</h2>
  <p><strong>Location:</strong> {{ $opportunity['location'] }}</p>
  <p><strong>Start Date:</strong> {{ $opportunity['start_date'] }}</p>
  <p><strong>End Date:</strong> {{ $opportunity['end_date'] }}</p>
  <p><strong>Task Details:</strong> {{ $opportunity['task_details'] }}</p>
  <p><strong>Skills Needed:</strong> {{ $opportunity['skills_needed'] }}</p>
  <p><strong>Status:</strong> {{ $opportunity['status'] }}</p>

  @if(!empty($opportunity['schedule']))
    <h4>Schedule</h4>
    <ul>
      @foreach($opportunity['schedule'] as $event)
        <li>
          {{ $event['date'] ?? '' }} - {{ $event['event_name'] ?? '' }}
          ({{ $event['start_time'] ?? '' }} - {{ $event['end_time'] ?? '' }})
          Target: {{ $event['target_participants'] ?? 'N/A' }} ({{ $event['target_number'] ?? 'N/A' }})
        </li>
      @endforeach
    </ul>
  @endif

  @php
    $user = session('volunteers_admin') ?? [];
    $userEmail = $user['email'] ?? null;
    $userRole  = $user['role'] ?? null;
  @endphp

  @if($userRole === 'SDA Assistant' && $opportunity['submitted_by'] === $userEmail)
      <!-- SDA Assistant (Volunteer Admin) viewing their own submission -->
      <form method="POST" action="{{ route('volunteers-admin.opportunities.cancel', $opportunity['opportunity_id']) }}">
        @csrf
        <button type="submit" class="btn btn-danger">Cancel Opportunity</button>
        <a href="{{ route('volunteers-admin.opportunities') }}" class="btn btn-secondary">Back</a>
      </form>
  @else
      <!-- Approve / Return Form for Office submissions -->
      <form method="POST" action="{{ route('volunteers-admin.opportunities.updateStatus', $opportunity['opportunity_id']) }}">
        @csrf

        <div class="mb-3">
          <label class="form-label">Action</label>
          <select name="status" id="statusSelect" class="form-select" required>
            <option value="Approved">Approve</option>
            <option value="Returned">Return</option>
          </select>
        </div>

        <div class="mb-3" id="commentBox" style="display:none;">
          <label class="form-label">Return Comment</label>
          <textarea name="comment" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
        <a href="{{ route('volunteers-admin.opportunities') }}" class="btn btn-secondary">Back</a>
      </form>

      <script>
        document.getElementById('statusSelect').addEventListener('change', function () {
          document.getElementById('commentBox').style.display = this.value === 'Returned' ? 'block' : 'none';
        });
      </script>
  @endif

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
