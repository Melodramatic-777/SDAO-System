<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite('resources/css/volunteers.css')
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
    <a href="{{ route('scholars.dashboard') }}">Dashboard</a>
    <a href="{{ route('scholars.volunteer-opportunities') }}">Volunteer Opportunity</a>
    <a href="{{ route('scholars.invites') }}">Volunteer Invite</a>
    <a href="{{ route('scholars.profile') }}">Profile</a>
  </nav>
</header>

<!-- Filter controls -->
<div class="flex-wrap p-3 mb-3 bg-white border rounded shadow-sm filter-bar d-flex align-items-center justify-content-between">
  <div class="flex-wrap gap-2 d-flex align-items-center">
    <input type="text" class="form-control search-input" placeholder="Search opportunities..." style="width: 220px;">
    <select class="form-select" id="filter-office" style="width: 220px;">
      <option value="">All Offices</option>
      <option value="SDA Assistant">Student Development and Activities Office</option>
      @foreach($offices as $office)
        <option value="{{ $office['name'] }}">{{ $office['name'] }}</option>
      @endforeach
    </select>

    <select class="form-select" id="filter-status" style="width: 180px;">
      <option value="">All Status</option>
      <option value="Urgent">Urgent</option>
      <option value="Open">Open</option>
      <option value="Full">Full</option>
    </select>
  </div>

  <div class="gap-2 mt-2 d-flex align-items-center mt-md-0">
    <label class="text-muted small me-1">Sort by:</label>
    <select class="form-select" id="sort-by" style="width: 160px;">
      <option value="recent">Most Recent</option>
      <option value="upcoming">Upcoming</option>
      <option value="urgent">Urgent First</option>
    </select>
  </div>
</div>

<div class="container gap-3 mt-3 d-flex">

  <!-- Left column -->
  <div class="opportunity-list">
    @forelse($opportunities as $op)
      <div class="card opportunity-card" data-id="{{ $op['opportunity_id'] }}" onclick="showDetail('{{ $op['opportunity_id'] }}')">
        <div class="card-body">
          @if(isset($op['intermittent']) && $op['intermittent'])
            <span class="mb-2 badge bg-danger">Volunteers urgently needed</span>
          @endif
          <h5 class="card-title">{{ $op['title'] }}</h5>
          <p class="mb-1">Submitted by: {{ $op['submitted_by_role'] ?? 'Unknown' }}</p>
          <p class="mb-1">Location: {{ $op['location'] }}</p>
          <small>
            Date: 
            @if(isset($op['schedule'][0]['date']))
              {{ \Carbon\Carbon::parse($op['schedule'][0]['date'])->format('F j, Y') }}
            @else
              N/A
            @endif
            <br>
            Time: 
            @php
              $isIntermittent = !empty($op['is_intermittent']) && (int)$op['is_intermittent'] === 1;
            @endphp

            @if($isIntermittent)
              {{-- Multiple Dates for Intermittent Schedule --}}
              @foreach($op['schedule'] ?? [] as $schedule)
                <div>
                  <strong>{{ \Carbon\Carbon::parse($schedule['date'])->format('F j, Y') }}:</strong> 
                  @if(isset($schedule['start_time']) && isset($schedule['end_time']))
                    {{ \Carbon\Carbon::parse($schedule['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($schedule['end_time'])->format('g:i A') }}
                  @else
                    N/A
                  @endif
                </div>
              @endforeach
            @else
              {{-- Regular Schedule (single date) --}}
              @php
                $schedule = $op['schedule'][0] ?? null;
              @endphp
              @if ($schedule && isset($schedule['start_time']) && isset($schedule['end_time']))
                {{ \Carbon\Carbon::parse($schedule['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($schedule['end_time'])->format('g:i A') }}
              @else
                N/A
              @endif
            @endif
          </small>

          <ul class="mt-2 mb-0">
            @foreach(explode("\n", $op['task_details']) as $task)
              <li>{{ $task }}</li>
            @endforeach
          </ul>
          <div class="mt-2 text-end">
            <small class="participant-count">{{ count($op['participants'] ?? []) }}/{{ $op['schedule'][0]['target_number'] ?? '?' }}</small>
          </div>
        </div>
      </div>
    @empty
      <p>No approved opportunities available.</p>
    @endforelse
  </div>

  <!-- Right column -->
  <div class="opportunity-detail flex-grow-1" id="opportunityDetail">
    <h4>Select an opportunity</h4>
    <p>Click a card on the left to view full details.</p>
  </div>
</div>

<script>
const opportunities = @json($opportunities);
const scholarId = "{{ session('scholar_accounts.student_no') }}";
const scholarName = "{{ session('scholar_accounts.first_name') }} {{ session('scholar_accounts.last_name') }}";
const csrfToken = "{{ csrf_token() }}";

// ---------- SHOW DETAILS ----------
function showDetail(id) {
  const op = opportunities.find(o => o.opportunity_id === id);
  if (!op) return;

  const participants = op.participants || [];
  const acceptedVolunteers = participants.filter(p => p.status === "Accepted");
  const skills = (op.skills_needed || "").split(",");
  const target = op.schedule?.[0]?.target_number ?? null;
  const alreadyJoined = participants.some(p => p.scholar_id == scholarId);
  const isFull = target !== null && acceptedVolunteers.length >= target;

  // Handle schedule for regular and intermittent opportunities
  let scheduleContent = '';
  const isIntermittent = op.is_intermittent === true;

  if (isIntermittent) {
    // Multiple Dates for Intermittent Schedule
    scheduleContent = op.schedule.map(schedule => {
      const date = schedule.date ? new Date(schedule.date) : 'TBD';
      const startTime = schedule.start_time ? formatTime(schedule.start_time) : 'TBD';
      const endTime = schedule.end_time ? formatTime(schedule.end_time) : 'TBD';
      
      return `
        <div>
          <strong>${date.toLocaleDateString()}</strong>: ${startTime} – ${endTime}
        </div>
      `;
    }).join('');
  } else {
    // Regular Schedule (single date)
    const schedule = op.schedule[0] ?? null;
    const date = schedule?.date ?? schedule?.start_date ?? op.start_date ?? 'TBD';
    const startTime = schedule?.start_time ? formatTime(schedule.start_time) : 'TBD';
    const endTime = schedule?.end_time ? formatTime(schedule.end_time) : 'TBD';

    scheduleContent = `
      <div>
        <strong>${new Date(date).toLocaleDateString()}</strong>: ${startTime} – ${endTime}
      </div>
    `;
  }

  document.getElementById('opportunityDetail').innerHTML = `
    <h4>${op.title}</h4>
    <p><strong>Location:</strong> ${op.location}</p>
    <p><strong>Submitted by:</strong> ${op.submitted_by_role ?? 'Unknown'}</p>
    <p><strong>Date:</strong> ${op.start_date}</p>
    <p><strong>Time:</strong> ${scheduleContent}</p>

    ${
      alreadyJoined
        ? `<button class="btn btn-secondary" disabled id="joinBtn">Joined</button>`
        : isFull
          ? `<button class="btn btn-secondary" disabled id="joinBtn">Full</button>`
          : `<button class="btn btn-primary" id="joinBtn" onclick="joinOpportunity('${op.opportunity_id}')">Join</button>`
    }

    <div class="gap-4 mt-3 d-flex">
      <div style="flex:2">
        <h5>Task details</h5>
        <ul>${op.task_details.split("\n").map(t => `<li>${t}</li>`).join('')}</ul>
      </div>
      <div style="flex:1">
        <h5>Current Volunteers (<span id="volCountNumber">${acceptedVolunteers.length}</span>/<span id="volCountTarget">${target ?? '?'}</span>)</h5>
        <ol id="volList">
          ${
            acceptedVolunteers.length > 0
              ? acceptedVolunteers.map(p => `<li>${p.name}</li>`).join('')
              : '<li><em>No accepted volunteers yet.</em></li>'
          }
        </ol>
        <h5>Skills needed</h5>
        <ul>${skills.map(s => `<li>${s.trim()}</li>`).join('')}</ul>
      </div>
    </div>
  `;

  document.querySelectorAll('.opportunity-card').forEach(c => c.classList.remove('selected'));
  const card = document.querySelector(`.opportunity-card[data-id="${id}"]`);
  if (card) card.classList.add('selected');
}

// Helper function to format time without timezone
function formatTime(time) {
  const [hours, minutes] = time.split(':');
  let period = 'AM';
  let hour = parseInt(hours);

  if (hour >= 12) {
    period = 'PM';
    if (hour > 12) hour -= 12; // convert to 12-hour format
  }

  return `${hour}:${minutes} ${period}`;
}

// ---------- JOIN OPPORTUNITY ----------
async function joinOpportunity(id) {
  const btn = document.getElementById("joinBtn");
  btn.disabled = true;
  btn.innerText = "Joining...";

  try {
    const response = await fetch(`/scholars/opportunities/join/${id}`, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": csrfToken,
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      body: JSON.stringify({})
    });

    const data = await response.json();
    if (response.ok) {
      alert('Joined successfully!');
      location.reload();
    } else {
      alert(data.error || data.message || 'Failed to join.');
    }
  } catch (err) {
    console.error(err);
    alert('Error joining opportunity.');
  }
}
</script>

</body>
</html>
