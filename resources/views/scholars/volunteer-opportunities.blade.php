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
<input type="text" id="filter-search" class="form-control search-input" placeholder="Search opportunities..." style="width: 220px;">
    <select class="form-select" id="filter-office" style="width: 220px;">
      <option value="">All Offices</option>
<option value="SDA Office">SDA Office</option>
      @foreach($offices as $office)
        <option value="{{ $office['name'] }}">{{ $office['name'] }}</option>
      @endforeach
    </select>

    <select class="form-select" id="filter-status" style="width: 180px;">
      <option value="">All Status</option>
      <option value="Urgent">Urgent</option>
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

  <!-- On the Spot Volunteer Button -->
  <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#onTheSpotModal">
    On the Spot Volunteer
  </button>
</div>

  </div>
</div>


<div class="container gap-3 px-3 d-flex">
  <div class="mb-2 opp-count-label">
    <h5 class="mb-0 fw-bold"><span id="oppCount">{{ count($opportunities) }}</span> Volunteer Opportunities</h5>
  </div>
</div>

<div class="container gap-3 mt-2 d-flex">


  <!-- Left column -->
<div class="opportunity-list" id="opportunityList">
    @forelse($opportunities as $op)
      <div class="card opportunity-card" data-id="{{ $op['opportunity_id'] }}" onclick="showDetail('{{ $op['opportunity_id'] }}')">
        <div class="card-body">
@if(isset($op['is_urgent']) && $op['is_urgent'] === 'yes')
  <span class="badge-urgent">Urgent</span>
@endif
          <h5 class="card-title">{{ $op['title'] }}</h5>
<p class="mb-1">Office: {{ ($op['submitted_by_role'] ?? '') === 'SDA Assistant' ? 'SDA Office' : ($op['submitted_by_role'] ?? 'Unknown') }}</p>
          <p class="mb-1">Location: {{ $op['location'] }}</p>
<small>
  @php
    $isIntermittent = !empty($op['is_intermittent']) && (int)$op['is_intermittent'] === 1;
  @endphp

  @if($isIntermittent)
    <strong>Schedule:</strong><br>
    @foreach($op['schedule'] ?? [] as $s)
      <div>
        {{ \Carbon\Carbon::parse($s['date'])->format('F j, Y') }}:
        @if(isset($s['start_time']) && isset($s['end_time']))
          {{ \Carbon\Carbon::parse($s['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($s['end_time'])->format('g:i A') }}
        @else
          N/A
        @endif
      </div>
    @endforeach
  @else
    {{-- Regular schedule --}}
    @php $s = $op['schedule'][0] ?? null; @endphp
    <strong>Date:</strong>
    {{ $s['date'] ?? 'N/A' ? \Carbon\Carbon::parse($s['date'])->format('F j, Y') : 'N/A' }} <br>

    <strong>Time:</strong>
    @if($s && isset($s['start_time']) && isset($s['end_time']))
      {{ \Carbon\Carbon::parse($s['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($s['end_time'])->format('g:i A') }}
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
  <h4>
    <span style="font-weight: normal; margin-right: 6px;">←</span>
    <strong>Select an opportunity</strong>
  </h4>
  <p class="detail-placeholder">Display details here</p>
</div>
</div>





<!-- ON THE SPOT VOLUNTEER MODAL -->
<div class="modal fade" id="onTheSpotModal" tabindex="-1" aria-labelledby="onTheSpotVolunteerLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="p-3 modal-content">

      <div class="modal-header">
        <h5 class="modal-title fw-bold">ON THE SPOT VOLUNTEER</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="onTheSpotForm" method="POST" action="{{ route('scholars.ontheSpotVolunteer') }}">
        @csrf

        <div class="modal-body">

          <label class="form-label">Office</label>
          <select name="office" class="form-select" required>
            <option value="">Choose office</option>
            @foreach($offices as $office)
              <option value="{{ $office['name'] }}">{{ $office['name'] }}</option>
            @endforeach
          </select>

          <label class="mt-3 form-label">Reason</label>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="reasons[]" value="Vacant Schedule">
            <label class="form-check-label">Vacant Schedule</label>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="reasons[]" value="Lack of Volunteer Hours">
            <label class="form-check-label">Lack of Volunteer Hours</label>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="reasons[]" value="Desire to be Productive">
            <label class="form-check-label">Desire to be Productive</label>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="otherReasonCheck">
            <label class="form-check-label">Others..</label>
          </div>

          <input type="text" class="mt-2 form-control" placeholder="Please Specify" name="other_reason" id="otherReasonInput" disabled>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary w-100">Submit</button>
        </div>
      </form>

    </div>
  </div>
</div>

<script>






const opportunities = @json($opportunities);
const scholarId = "{{ session('scholar_accounts.student_no') }}";
const scholarName = "{{ session('scholar_accounts.first_name') }} {{ session('scholar_accounts.last_name') }}";
const csrfToken = "{{ csrf_token() }}";



let currentSelectedId = null;
// ---------- SHOW DETAILS ----------
function showDetail(id) {
  currentSelectedId = id; // ✅ store selected opportunity
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
const isIntermittent = op.is_intermittent == 1 || op.intermittent == 1;

if (isIntermittent) {
  scheduleContent = `
    <strong>Schedule:</strong><br>
    ${op.schedule.map(schedule => {
      const date = schedule.date ? formatDatePretty(schedule.date) : 'TBD';
      const startTime = schedule.start_time ? formatTime(schedule.start_time) : 'TBD';
      const endTime = schedule.end_time ? formatTime(schedule.end_time) : 'TBD';

      return `
        <div style="margin-left: 4px;">
          ${date}: ${startTime} – ${endTime}
        </div>
      `;
    }).join('')}
  `;
} else {
  const s = op.schedule?.[0] ?? null;

  scheduleContent = `
    <strong>Date:</strong> ${s?.date ? formatDatePretty(s.date) : "N/A"}<br>
    <strong>Time:</strong> ${
      s && s.start_time && s.end_time
        ? `${formatTime(s.start_time)} – ${formatTime(s.end_time)}`
        : "N/A"
    } <br>
  `;
}

document.getElementById('opportunityDetail').innerHTML = `
  <h4><strong>${op.title}</strong></h4>
  <p><strong>Location:</strong> ${op.location}</p>
  <p><strong>Submitted by:</strong> ${(op.submitted_by_role === "SDA Assistant") ? "SDA Office" : (op.submitted_by_role ?? "Unknown")}</p>

<small>${scheduleContent}</small>

  ${
    alreadyJoined
      ? `<br><button class="px-4 btn btn-secondary" disabled>Joined</button>`
      : isFull
        ? `<button class="px-4 btn btn-dark" disabled>Full</button>`
        : `<button class="px-4 btn btn-primary" onclick="joinOpportunity('${op.opportunity_id}')">Join</button>`
  }

  <div class="gap-4 mt-3 d-flex">
    <div style="flex:2">
      <h5>Task details</h5>
      <ul>${op.task_details.split("\n").map(t => `<li>${t}</li>`).join('')}</ul>
    </div>
    <div style="flex:1">
      <h5>Current Volunteers (${acceptedVolunteers.length}/${target ?? '?'})</h5>
      <ol>
        ${acceptedVolunteers.length ? acceptedVolunteers.map(p => `<li>${p.name}</li>`).join('') : '<li><em>No volunteers yet.</em></li>'}
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

function formatTime(time) {
  if (!time) return "N/A";
  const [hour, minute] = time.split(":").map(Number);
  const period = hour >= 12 ? "PM" : "AM";
  const adjusted = hour % 12 || 12;
  return `${adjusted}:${minute.toString().padStart(2, "0")} ${period}`;
}

function formatDatePretty(dateString) {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    month: "long",
    day: "numeric",
    year: "numeric"
  });
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

document.getElementById("otherReasonCheck").addEventListener("change", function () {
  document.getElementById("otherReasonInput").disabled = !this.checked;
});


const oppList = document.getElementById("opportunityList");
const filterSearch = document.getElementById("filter-search");
const filterOffice = document.getElementById("filter-office");
const filterStatus = document.getElementById("filter-status");
const sortBy = document.getElementById("sort-by");

// === Apply Filters & Re-render List ===
function applyFilters() {
  let filtered = [...opportunities];

  // ✅ ALWAYS place urgent opportunities first (default behavior)
  filtered.sort((a, b) => (b.is_urgent === "yes") - (a.is_urgent === "yes"));

  // Search Filter
  const searchVal = filterSearch.value.toLowerCase();
  if (searchVal) {
    filtered = filtered.filter(op =>
      op.title.toLowerCase().includes(searchVal) ||
      (op.location ?? "").toLowerCase().includes(searchVal)
    );


    renderOpportunities(filtered);
clearDetailPanel();


  }

  // Office Filter
const officeVal = filterOffice.value;
if (officeVal) {
  filtered = filtered.filter(op => {
    let submittedOffice = op.submitted_by_role ?? "Unknown";

    // Convert "SDA Assistant" to UI office label "SDA Office"
    if (submittedOffice === "SDA Assistant") {
      submittedOffice = "SDA Office";
    }

    return submittedOffice === officeVal;
  });
}

  // Status Filter
  const statusVal = filterStatus.value;
  if (statusVal) {
    filtered = filtered.filter(op => (op.is_urgent === "yes" ? "Urgent" : "Open") === statusVal);
  }

  // Sorting Behavior
  switch (sortBy.value) {
    case "recent":
      filtered.sort((a, b) => new Date(b.submitted_at) - new Date(a.submitted_at));
      break;

    case "upcoming":
      filtered.sort((a, b) => new Date(a.start_date) - new Date(b.start_date));
      break;

    case "urgent":
      // ✅ Force urgent to top again if user selects it specifically
      filtered.sort((a, b) => (b.is_urgent === "yes") - (a.is_urgent === "yes"));
      break;
  }

  // Re-render
  renderOpportunities(filtered);

  if (currentSelectedId && !filtered.some(op => op.opportunity_id === currentSelectedId)) {
  clearDetailPanel();
}

}

function clearDetailPanel() {
  currentSelectedId = null;

  document.getElementById('opportunityDetail').innerHTML = `
    <h4>
      <span style="font-weight: normal; margin-right: 6px;">←</span>
      <strong>Select an opportunity</strong>
    </h4>
    <p class="detail-placeholder">Display details here</p>
  `;

  document.querySelectorAll('.opportunity-card').forEach(c => c.classList.remove('selected'));
}

function getStatusBadge(status) {
  switch ((status || "").toLowerCase()) {
    case "approved":
      return `<span class="status-card approved">Approved</span>`;
    case "waiting":
      return `<span class="status-card waiting">Waiting</span>`;
    case "returned":
      return `<span class="status-card returned">Returned</span>`;
    case "completed":
      return `<span class="status-card volunteers">Completed</span>`;
    default:
      return `<span class="status-card waiting">Pending</span>`;
  }
}

function renderOpportunities(list) {
  list.sort((a, b) => (b.is_urgent === "yes") - (a.is_urgent === "yes"));

  document.getElementById("oppCount").textContent = list.length;

  if (!list.length) {
    oppList.innerHTML = `<p class="mt-3">No opportunities found.</p>`;
    return;
  }

  oppList.innerHTML = list.map(op => {

let scheduleContent = "";
const isIntermittent = op.is_intermittent == 1 || op.intermittent == 1;

if (isIntermittent) {
  scheduleContent = `
    <strong>Schedule:</strong><br>
    ${op.schedule.map(s => `
      <div class="schedule-block">
        ${formatDatePretty(s.date)}:
        ${s.start_time && s.end_time ? `${formatTime(s.start_time)} – ${formatTime(s.end_time)}` : `N/A`}
      </div>
    `).join('')}
  `;
} else {
  const s = op.schedule?.[0] ?? null;
  scheduleContent = `
    <strong>Date:</strong> ${s?.date ? formatDatePretty(s.date) : "N/A"}<br>
    <strong>Time:</strong> ${
      s && s.start_time && s.end_time
        ? `${formatTime(s.start_time)} – ${formatTime(s.end_time)}`
        : "N/A"
    } <br>
  `;
}

    const taskList = (op.task_details || "").split("\n").map(t => `<li>${t}</li>`).join('');
    const count = (op.participants ?? []).length;
    const target = op.schedule?.[0]?.target_number ?? "?";
    const submittedOffice = (op.submitted_by_role === "SDA Assistant") ? "SDA Office" : (op.submitted_by_role ?? "Unknown");

    return `
      <div class="card opportunity-card" data-id="${op.opportunity_id}" onclick="showDetail('${op.opportunity_id}')">
        <div class="card-body">

          ${op.is_urgent === "yes" ? `<span class="badge-urgent">Urgent</span>` : ``}

          <h5 class="card-title">${op.title}</h5>
          <p class="mb-1">Office: ${submittedOffice}</p>
          <p class="mb-1">Location: ${op.location}</p>

<small>${scheduleContent}</small>

          <ul class="mt-2 mb-0">${taskList}</ul>

          <div class="mt-2 text-end">
            <small class="participant-count">${count}/${target}</small>
          </div>

        </div>
      </div>
    `;
  }).join('');
}


// === Event Listeners ===
filterSearch.addEventListener("input", applyFilters);
filterOffice.addEventListener("change", applyFilters);
filterStatus.addEventListener("change", applyFilters);
sortBy.addEventListener("change", applyFilters);






</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>
