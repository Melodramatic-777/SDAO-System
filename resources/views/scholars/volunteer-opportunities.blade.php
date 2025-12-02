@extends('scholars.layout')

@section('content')

<header class="custom-header">
    <div class="header-left">

        <!-- SIDE BUTTON MOVED HERE -->
<button type="submit" class="side-button header-btn">
    <img src="{{ asset('images/Side.png') }}" alt="Side" height="30">
</button>


        <div class="logo">
            <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
            <span>Student Developments and Activities Office</span>
        </div>
    </div>

    <div class="header-right-group">
        <p>Welcome, {{ session('scholar_accounts.first_name') }}!</p>
    </div>
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


  <!-- On the Spot Volunteer Button -->
  <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#onTheSpotModal">
    On the Spot Volunteer
  </button>
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
    @php
      $dates = collect($op['schedule'] ?? [])
          ->pluck('date')
          ->filter()
          ->map(fn($d) => \Carbon\Carbon::parse($d))
          ->sort()
          ->values();

      $startDate = $dates->first();
      $endDate = $dates->last();
    @endphp

    <strong>Schedule:</strong><br>
    @if($startDate && $endDate && !$startDate->equalTo($endDate))
      {{ $startDate->format('F j, Y') }} – {{ $endDate->format('F j, Y') }}
    @elseif($startDate)
      {{ $startDate->format('F j, Y') }}
    @else
      N/A
    @endif

  @else
    {{-- Regular schedule --}}
    @php $s = $op['schedule'][0] ?? null; @endphp
    <strong>Date:</strong>
    {{ !empty($s['date']) ? \Carbon\Carbon::parse($s['date'])->format('F j, Y') : 'N/A' }} <br>

    <strong>Time:</strong>
    @if($s && isset($s['start_time']) && isset($s['end_time']))
      {{ \Carbon\Carbon::parse($s['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($s['end_time'])->format('g:i A') }}
    @else
      N/A
    @endif
  @endif
</small>


<div class="mt-2 text-end">
  <small class="participant-count">
    <i class="bi bi-person-fill me-1"></i>
    {{ count($op['participants'] ?? []) }}/{{ $op['schedule'][0]['target_number'] ?? '?' }}
  </small>
</div>

        </div>
      </div>
    @empty
      <p>No opportunities available.</p>
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




<div class="modal fade" id="onTheSpotModal" tabindex="-1" aria-labelledby="onTheSpotVolunteerLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="p-3 modal-content">

      <div class="modal-header">
        <h5 class="modal-title fw-bold">ON THE SPOT VOLUNTEER</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="onTheSpotForm" method="POST" action="{{ route('scholars.ontheSpotVolunteer') }}">
        @csrf

        <div class="modal-body">
          <p>By submitting, you will be added to the pool of available on-the-spot volunteers. Offices will be able to select you if they need help.</p>

          <!-- Office Selection -->
          <label class="form-label">Office</label>
          <select name="office" class="form-select" required>
            <option value="">Choose office</option>
            @foreach($offices as $office)
              <option value="{{ $office['name'] }}">{{ $office['name'] }}</option>
            @endforeach
          </select>

          <small class="mb-2 text-muted d-block">
  If your chosen office does not select you, you will be transfered to other offices.
</small>

          <!-- Availability Date -->
          <label class="mt-2 form-label">Available Date</label>
          <input type="date" name="available_date" class="form-control" required>

          <!-- Availability Time -->
          <label class="mt-2 form-label">Available Time</label>
          <div class="gap-2 d-flex">
            <input type="time" name="available_start_time" class="form-control" required>
            <span class="align-self-center">to</span>
            <input type="time" name="available_end_time" class="form-control" required>
          </div>

          <label class="mt-3 form-label">Reason for Availability</label>
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

          <input type="text" class="mt-2 form-control" placeholder="Please Specify"
                 name="other_reason" id="otherReasonInput" disabled>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary w-100">Mark Myself Available</button>
        </div>
      </form>

    </div>
  </div>
</div>

<script>






const allOpportunities = @json($allOpportunities);
const opportunities = allOpportunities;
const scholarId = "{{ session('scholar_accounts.student_no') }}";
const scholarName = "{{ session('scholar_accounts.first_name') }} {{ session('scholar_accounts.last_name') }}";
const csrfToken = "{{ csrf_token() }}";



let currentSelectedId = null;
// ---------- SHOW DETAILS ----------
// ---------- SHOW DETAILS ----------
function showDetail(id) {
    currentSelectedId = id;
    const op = opportunities.find(o => o.opportunity_id === id);
    if (!op) return;

    const participants = op.participants || [];
    const acceptedVolunteers = participants.filter(p => p.status === "Accepted");

    // FIX: use boolean for intermittent
    const isIntermittent = op.is_intermittent === true || op.is_intermittent === "true";

    // FIX: tasks structure
    const tasks = op.tasks || [];

    // FIX: skills gathered from tasks
    const skillsList = tasks.flatMap(t => (t.skills || "").split(",")).map(s => s.trim());

    // FIX: For full/limit
    const target = op.schedule?.[0]?.target_number ?? null;
    const alreadyJoined = participants.some(p => p.scholar_id == scholarId);
    const isFull = target !== null && acceptedVolunteers.length >= target;

    // ---------------------
    // BUILD SCHEDULE TABLE
    // ---------------------
    let scheduleContent = "";

    if (isIntermittent) {

      const validDates = (op.schedule || [])
  .map(s => s.date)
  .filter(d => d && !isNaN(new Date(d)))
  .map(d => new Date(d))
  .sort((a, b) => a - b);


        let dateRangeText = "N/A";
        if (validDates.length > 0) {
            const start = validDates[0];
            const end = validDates[validDates.length - 1];
            dateRangeText =
                start.getTime() === end.getTime()
                    ? formatDatePretty(start.toISOString())
                    : `${formatDatePretty(start.toISOString())} – ${formatDatePretty(end.toISOString())}`;
        }

        scheduleContent = `
            <h6 class="mt-3 fw-bold">Schedule (Intermittent)</h6>
            <p><strong>Estimated Dates:</strong> ${dateRangeText}</p>

            <div class="table-responsive">
              <table class="table mt-2 text-center align-middle table-bordered">
                <thead class="table-light">
                  <tr>
                    <th>Date</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Volunteers Needed</th>
                    <th>Food</th>
                  </tr>
                </thead>
                <tbody>
                  ${op.schedule.map(s => `
                    <tr>
                      <td>${formatDatePretty(s.date)}</td>
                      <td>${formatTime(s.start_time)}</td>
                      <td>${formatTime(s.end_time)}</td>
                      <td>${s.target_number}</td>
                      <td>${(s.food_provided || "no").toLowerCase() === "yes" ? "Yes" : "No"}</td>
                    </tr>
                  `).join("")}
                </tbody>
              </table>
            </div>
        `;
    } else {

        const s = op.schedule?.[0] ?? null;

        scheduleContent = `
            <h6 class="mt-3 fw-bold">Schedule</h6>
            <table class="table text-center align-middle table-bordered">
                <thead class="table-light">
                  <tr>
                    <th>Date</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Volunteers Needed</th>
                    <th>Food</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>${formatDatePretty(s?.date)}</td>
                    <td>${formatTime(s?.start_time)}</td>
                    <td>${formatTime(s?.end_time)}</td>
                    <td>${s?.target_number}</td>
                    <td>${(s?.food_provided || "no").toLowerCase() === "yes" ? "Yes" : "No"}</td>
                  </tr>
                </tbody>
            </table>
        `;
    }

    // ---------------------
    // BUILD HTML (SAFE)
    // ---------------------
    document.getElementById("opportunityDetail").innerHTML = `
        <h4><strong>${op.title}</strong></h4>
        <p><strong>Location:</strong> ${op.location}</p>
        <p><strong>Submitted by:</strong> ${op.submitted_by_name}</p>

        ${scheduleContent}

        ${alreadyJoined
            ? `<button class="mt-3 btn btn-secondary" disabled>Joined</button>`
            : isFull
                ? `<button class="mt-3 btn btn-dark" disabled>Full</button>`
                : `<button id="joinBtn" class="mt-3 btn btn-primary" onclick="joinOpportunity('${op.opportunity_id}')">Join</button>`
        }

        <hr class="my-4">

        <h5 class="fw-bold">Tasks</h5>
        <ul>
          ${tasks.map(t => `
            <li><strong>${t.task}</strong> – ${t.description}</li>
          `).join("")}
        </ul>

        <h5 class="mt-4 fw-bold">Skills Needed</h5>
        <ul>
          ${skillsList.map(s => `<li>${s}</li>`).join("")}
        </ul>
    `;

    // highlight selected
    document.querySelectorAll(".opportunity-card").forEach(c => c.classList.remove("selected"));
    document.querySelector(`.opportunity-card[data-id="${id}"]`)?.classList.add("selected");
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

  // If Firebase sends "YYYY/MM/DD" convert it first
  if (isNaN(date)) {
    // Try replacing / with -
    const cleaned = dateString.replace(/\//g, "-");
    const tryAgain = new Date(cleaned);
    if (!isNaN(tryAgain)) {
      return tryAgain.toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
        year: "numeric"
      });
    }
    return "N/A"; // invalid date completely
  }

  return date.toLocaleDateString("en-US", {
    month: "long",
    day: "numeric",
    year: "numeric"
  });
}


// ---------- JOIN OPPORTUNITY ----------
async function joinOpportunity(id) {
  const btn = document.getElementById("joinBtn");
  if (btn) {
    btn.disabled = true;
    btn.innerText = "Joining...";
  }

  try {
    const response = await fetch(`{{ url('/scholars/opportunities/join') }}/${id}`, {
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
      alert("Joined successfully!");
      location.reload();
    } else {
      alert(data.error || data.message || "Failed to join.");
      if (btn) {
        btn.disabled = false;
        btn.innerText = "Join";
      }
    }

  } catch (err) {
    console.error(err);
    alert("Error connecting to server.");
    if (btn) {
      btn.disabled = false;
      btn.innerText = "Join";
    }
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
  let filtered = [...allOpportunities]; 

  // 🔍 Apply search filter
  const searchVal = filterSearch.value.toLowerCase();
  if (searchVal) {
    filtered = filtered.filter(op =>
      op.title.toLowerCase().includes(searchVal) ||
      (op.location ?? "").toLowerCase().includes(searchVal)
    );
  }

  // 🏢 Apply office filter
  const officeVal = filterOffice.value;
  if (officeVal) {
    filtered = filtered.filter(op => {
      let submittedOffice = op.submitted_by_role ?? "Unknown";
      if (submittedOffice === "SDA Assistant") submittedOffice = "SDA Office";
      return submittedOffice === officeVal;
    });
  }

  // ⚙️ Apply status filter
  const statusVal = filterStatus.value;
  if (statusVal) {
    filtered = filtered.filter(op => {
      const participants = op.participants || [];
      const target = op.schedule?.[0]?.target_number ?? null;
      const accepted = participants.filter(p => p.status === "Accepted").length;
      const isFull = target !== null && accepted >= target;

      if (statusVal === "Urgent") return op.is_urgent === "yes";
      if (statusVal === "Full") return isFull;
      return true;
    });
  }

  // 🔽 Apply sort selection
  switch (sortBy.value) {
    case "recent":
      filtered.sort((a, b) => new Date(b.submitted_at) - new Date(a.submitted_at));
      break;
    case "upcoming":
      filtered.sort((a, b) => new Date(a.start_date) - new Date(b.start_date));
      break;
  }

  // 🚨 Always prioritize urgent opportunities on top
  filtered.sort((a, b) => (b.is_urgent === "yes") - (a.is_urgent === "yes"));

  renderOpportunities(filtered);

  // If the selected opportunity no longer exists after filtering, clear detail
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
    const isIntermittent = op.is_intermittent == 1 || op.intermittent == 1;
    let scheduleContent = "";

    if (isIntermittent) {
      // ✅ Compute date range for intermittent schedules
    const validDates = (op.schedule || [])
  .map(s => s.date)
  .filter(d => d && !isNaN(new Date(d)))
  .map(d => new Date(d))
  .sort((a, b) => a - b);


      let dateRangeText = "N/A";
      if (validDates.length > 0) {
        const start = validDates[0];
        const end = validDates[validDates.length - 1];
        if (start.getTime() === end.getTime()) {
          dateRangeText = formatDatePretty(start.toISOString());
        } else {
dateRangeText = `${formatDatePretty(start.toISOString())} – ${formatDatePretty(end.toISOString())}`;

        }
      }

      scheduleContent = `
        <strong>Schedule:</strong><br>
        ${dateRangeText}
      `;
    } else {
      const s = op.schedule?.[0] ?? null;
      scheduleContent = `
        <strong>Date:</strong> ${s?.date ? formatDatePretty(s.date) : "N/A"}<br>
        <strong>Time:</strong> ${
          s && s.start_time && s.end_time
            ? `${formatTime(s.start_time)} – ${formatTime(s.end_time)}`
            : "N/A"
        }
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
<div class="mt-2 text-end">
  <small class="participant-count">
    <i class="bi bi-person-fill me-1"></i>
    {{ count($op['participants'] ?? []) }}/{{ $op['schedule'][0]['target_number'] ?? '?' }}
  </small>
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



function openIntermittentModal(opportunityId) {
  const op = opportunities.find(o => o.opportunity_id === opportunityId);
  if (!op) return;

  const list = document.getElementById('intermittentScheduleList');
  const schedules = op.schedule || [];

  if (!schedules.length) {
    list.innerHTML = `<p class="text-muted">No schedules available.</p>`;
    return;
  }

  list.innerHTML = `
    <div class="table-responsive">
      <table class="table text-center align-middle table-bordered">
        <thead class="table-light">
          <tr>
            <th>Date</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Volunteers Needed</th>
            <th>Join</th>
          </tr>
        </thead>
        <tbody>
          ${schedules.map((s, i) => {
            const date = s.date ? formatDatePretty(s.date) : "N/A";
            const start = s.start_time ? formatTime(s.start_time) : "N/A";
            const end = s.end_time ? formatTime(s.end_time) : "N/A";
            const targetNum = s.target_number ?? "N/A";
            return `
              <tr>
                <td>${date}</td>
                <td>${start}</td>
                <td>${end}</td>
                <td>${targetNum}</td>
                <td>
                  <button class="btn btn-sm btn-primary" onclick="joinIntermittent('${opportunityId}', ${i})">Join</button>
                </td>
              </tr>
            `;
          }).join('')}
        </tbody>
      </table>
    </div>
  `;
}

async function joinIntermittent(opportunityId, index) {
  const btn = event.target;
  btn.disabled = true;
  btn.innerText = "Joining...";

  try {
    const response = await fetch(`/scholars/opportunities/join/${opportunityId}`, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": csrfToken,
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      body: JSON.stringify({ schedule_index: index })
    });

    const data = await response.json();
    if (response.ok) {
      alert("Joined successfully!");
      location.reload();
    } else {
      alert(data.error || data.message || "Failed to join.");
    }
  } catch (err) {
    console.error(err);
    alert("Error joining opportunity.");
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const sorted = [...opportunities].sort((a, b) => (b.is_urgent === "yes") - (a.is_urgent === "yes"));
  renderOpportunities(sorted);
});

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- INTERMITTENT JOIN MODAL -->
<div class="modal fade" id="intermittentJoinModal" tabindex="-1" aria-labelledby="intermittentJoinLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="p-3 modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Join Schedule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="intermittentScheduleList">Loading...</div>
      </div>
    </div>
  </div>
</div>

<!-- Pagination -->
<div class="mt-4 d-flex justify-content-center">
    {{ $pagination->links('pagination::bootstrap-5') }}
</div>

@endsection

