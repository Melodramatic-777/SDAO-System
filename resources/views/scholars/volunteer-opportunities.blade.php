@extends('scholars.layout')

@section('content')


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
<!-- Office Selection -->
<label class="form-label">Office</label>
<select name="office" class="form-select" required>
  <option value="">Choose office</option>
  @foreach($offices as $office)
    <option value="{{ $office['name'] }}">{{ $office['name'] }}</option>
  @endforeach
</select>

<!-- Transfer Willingness -->
<div class="mt-3">
    <label class="form-label">If your chosen office does not select you, are you willing to be transferred?</label>

    <div class="mt-2 form-check">
        <input class="form-check-input" type="radio" name="willing_to_transfer" id="transferYes" value="yes" required>
        <label class="form-check-label" for="transferYes">
            Yes, I am willing to transfer to other offices
        </label>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="radio" name="willing_to_transfer" id="transferNo" value="no" required>
        <label class="form-check-label" for="transferNo">
            No, just make me available for my chosen office
        </label>
    </div>
</div>



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
// ---------- SHOW DETAILS ----------
function showDetail(id) {
    currentSelectedId = id;
    const op = opportunities.find(o => o.opportunity_id === id);
    if (!op) return;

    const isIntermittent = op.is_intermittent == 1 || op.is_intermittent === true;
    const tasksByDate = op.intermittent_tasks_by_date || {};

    // LEFT column: Description + Participants
    const leftColumn = `
        <div class="col-md-6">
            <h6><strong>Description</strong></h6>
            <div class="mb-3">${op.description || 'No description provided.'}</div>
            <h6><strong>Participants</strong></h6>
            <div>${renderParticipants(op)}</div>
        </div>
    `;

    // RIGHT column: Tasks
    let rightColumnContent = '';
Object.entries(tasksByDate).forEach(([date, tasksObj]) => {
    const tasks = Object.values(tasksObj);
    const collapseId = `tasksCollapse_${id}_${date.replace(/\s+/g,'')}`;

   
   if (isIntermittent) {
    const formattedDate = new Date(date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });

    // Pick only the first task's start and end time to avoid duplicates
    let timeStr = 'N/A';
    const firstTask = tasks[0];
    if (firstTask && firstTask.start_time && firstTask.end_time) {
        timeStr = `${formatTime(firstTask.start_time)} – ${formatTime(firstTask.end_time)}`;
    }

    rightColumnContent += `
    <div class="mb-3">
        <button class="btn w-100 text-start toggle-collapse-btn" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#${collapseId}"
                aria-expanded="false"
                aria-controls="${collapseId}"
                style="background-color: #cce7ff; color: #000; border: 1px solid #99d1ff;">
            <strong>${formattedDate} | ${timeStr}</strong>
        </button>

        <div class="mt-2 collapse" id="${collapseId}">
            <div class="p-3 border rounded bg-light">
                ${tasks.map(t => `
                    <div class="pb-2 mb-3 border-bottom">
                        <p class="mb-1"><strong>${t.task}</strong></p>
                        ${t.description ? `<p class="mb-1 text-muted" style="margin-left:12px;">${t.description}</p>` : ''}
                        ${t.skills ? `<p class="mb-1" style="margin-left:12px;"><strong class="text-primary">Skills:</strong> ${t.skills}</p>` : ''}
                        <p class="mb-0" style="margin-left:12px;"><strong class="text-dark">Volunteers Needed:</strong> ${t.needed}</p>
                        <button 
                            class="mt-2 btn btn-primary btn-sm"
                            onclick="event.stopPropagation(); joinTask('${id}', '${date}', '${t.task_id || ''}', '${t.task.replace(/'/g, "\\'")}')">
                            Join Task
                        </button>
                    </div>
                `).join('')}
            </div>
        </div>
    </div>
    `;
}



else {
            // Regular: do not display date above tasks
            rightColumnContent += `
                <div class="mb-3">
                    <div class="p-3 border rounded bg-light">
                        ${tasks.map(t => `
                            <div class="pb-2 mb-3 border-bottom">
                                <p class="mb-1"><strong>${t.task}</strong></p>
                                ${t.description ? `<p class="mb-1 text-muted" style="margin-left:12px;">${t.description}</p>` : ''}
                                ${t.skills ? `<p class="mb-1" style="margin-left:12px;"><strong class="text-primary">Skills:</strong> ${t.skills}</p>` : ''}
                                ${t.needed ? `<p class="mb-0" style="margin-left:12px;"><strong class="text-dark">Volunteers Needed:</strong> ${t.needed}</p>` : ''}
                                <button 
                                    class="mt-2 btn btn-primary btn-sm"
                                    onclick="event.stopPropagation(); joinTask('${id}', '${date}', '${t.task_id || ''}', '${t.task.replace(/'/g, "\\'")}')">
                                    Join Task
                                </button>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }
    });

    const rightColumn = `<div class="col-md-6">${rightColumnContent || '<p>No tasks available.</p>'}</div>`;

    // Render the panel
    document.getElementById('opportunityDetail').innerHTML = `
    <div class="container-fluid">
        <!-- Row 1: Office -->
        <div class="mb-2 row">
            <div class="col"><strong>Office:</strong> ${op.submitted_by_role || 'N/A'}</div>
        </div>
        <!-- Row 2: Submitted By -->
        <div class="mb-2 row">
            <div class="col"><strong>Submitted By:</strong> ${op.submitted_by_name || 'N/A'}</div>
        </div>
        <!-- Row 3: Location -->
        <div class="mb-2 row">
            <div class="col"><strong>Location:</strong> ${op.location || 'N/A'}</div>
        </div>
        <!-- Row 4: Date (Regular schedules only) -->
        ${!isIntermittent && op.schedule && op.schedule[0] ? `
        <div class="mb-2 row">
            <div class="col"><strong>Date:</strong> ${formatDatePretty(op.schedule[0].date)}</div>
        </div>` : ''}
        <!-- Row 5: Time (Regular schedules only) -->
        ${!isIntermittent && op.schedule && op.schedule[0] ? `
        <div class="mb-3 row">
            <div class="col"><strong>Time:</strong> ${formatTime(op.schedule[0].start_time)} – ${formatTime(op.schedule[0].end_time)}</div>
        </div>` : ''}
        <!-- Row 6: Two columns -->
        <hr>
        <div class="row">
            ${leftColumn}
            ${rightColumn}
        </div>
    </div>
`;



    document.querySelectorAll('.opportunity-card').forEach(c => c.classList.remove('selected'));
    document.querySelector(`.opportunity-card[data-id="${id}"]`)?.classList.add('selected');
}




// ---------- RENDER PARTICIPANTS FUNCTION ----------
function renderParticipants(op) {
    if (!op.participants || !op.participants.length) {
        return '<p style="font-style: italic;">No participants yet.</p>';
    }

    // Collect participants grouped by date
    const participantsByDate = {};

    op.participants.forEach(p => {
        if (p.tasks && Object.keys(p.tasks).length) {
            Object.entries(p.tasks).forEach(([date, taskNames]) => {
                if (!participantsByDate[date]) participantsByDate[date] = [];
                // Check if participant already added for this date
                let existing = participantsByDate[date].find(x => x.name === p.name);
                if (existing) {
                    // Merge tasks without duplicates
                    existing.tasks = [...new Set([...existing.tasks, ...taskNames])];
                } else {
                    participantsByDate[date].push({
                        name: p.name,
                        status: p.status || 'N/A',
                        tasks: taskNames
                    });
                }
            });
        } else {
            // If no tasks, group under 'No Date'
            const noDateKey = 'No Date';
            if (!participantsByDate[noDateKey]) participantsByDate[noDateKey] = [];
            participantsByDate[noDateKey].push({ name: p.name, status: p.status || 'N/A', tasks: [] });
        }
    });

    // Sort dates
    const sortedDates = Object.keys(participantsByDate).sort((a, b) => {
        if (a === 'No Date') return 1;
        if (b === 'No Date') return -1;
        return new Date(a) - new Date(b);
    });

    // Build HTML
    let html = '';
    sortedDates.forEach(date => {
        const formattedDate = date === 'No Date' ? date : new Date(date).toLocaleDateString('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });

        html += `<strong style="color:#004aad;">${formattedDate}</strong><ul class="mb-2 list-unstyled ms-3">`;
        participantsByDate[date].forEach(p => {
            const tasksStr = p.tasks.length ? ` - ${p.tasks.join(', ')}` : '';
            html += `<li>${p.name} (${p.status})${tasksStr}</li>`;
        });
        html += `</ul>`;
    });

    return html;
}




// ---------- FORMAT FUNCTIONS ----------
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
  if (isNaN(date)) return "N/A";
  return date.toLocaleDateString("en-US", {
    month: "long",
    day: "numeric",
    year: "numeric"
  });
}

// ---------- JOIN TASK ----------
async function joinTask(opportunityId, date, taskId, taskName) {
    if (!scholarId || !scholarName || !taskName) {
        alert("Missing required info.");
        return;
    }

    try {
        const response = await fetch("{{ route('scholars.joinTask') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({
                opportunity_id: opportunityId,
                date: date,
                task_name: taskName,
                scholar_id: scholarId,
                name: scholarName
            })
        });

        const data = await response.json();
        if (response.ok) {
            alert("You have successfully joined this task!");
            location.reload();
        } else {
            alert(data.message || "Failed to join task.");
        }

    } catch (error) {
        console.error(error);
        alert("Server error — could not join task.");
    }
}

// ---------- FILTERS ----------
const oppList = document.getElementById("opportunityList");
const filterSearch = document.getElementById("filter-search");
const filterOffice = document.getElementById("filter-office");
const filterStatus = document.getElementById("filter-status");
const sortBy = document.getElementById("sort-by");

function applyFilters() {
    let filtered = [...allOpportunities];

    const searchVal = filterSearch.value.toLowerCase();
    if (searchVal) {
        filtered = filtered.filter(op =>
            op.title.toLowerCase().includes(searchVal) ||
            (op.location ?? "").toLowerCase().includes(searchVal)
        );
    }

    const officeVal = filterOffice.value;
    if (officeVal) {
        filtered = filtered.filter(op => {
            let submittedOffice = op.submitted_by_role ?? "Unknown";
            if (submittedOffice === "SDA Assistant") submittedOffice = "SDA Office";
            return submittedOffice === officeVal;
        });
    }

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

    switch (sortBy.value) {
        case "recent":
            filtered.sort((a, b) => new Date(b.submitted_at) - new Date(a.submitted_at));
            break;
        case "upcoming":
            filtered.sort((a, b) => new Date(a.start_date) - new Date(b.start_date));
            break;
    }

    filtered.sort((a, b) => (b.is_urgent === "yes") - (a.is_urgent === "yes"));

    renderOpportunities(filtered);

    if (currentSelectedId && !filtered.some(op => op.opportunity_id === currentSelectedId)) {
        clearDetailPanel();
    }
}

function clearDetailPanel() {
    currentSelectedId = null;
    document.getElementById('opportunityDetail').innerHTML = `
        <h4><strong>Select an opportunity</strong></h4>
        <p class="detail-placeholder">Display details here</p>
    `;
    document.querySelectorAll('.opportunity-card').forEach(c => c.classList.remove('selected'));
}

// ---------- RENDER OPPORTUNITIES ----------
function renderOpportunities(list) {
    list.sort((a, b) => (b.is_urgent === "yes") - (a.is_urgent === "yes"));
    document.getElementById("oppCount").textContent = list.length;

    if (!list.length) {
        oppList.innerHTML = `<p class="mt-3">No opportunities found.</p>`;
        return;
    }

   oppList.innerHTML = list.map(op => {
    const isIntermittent = op.is_intermittent == 1 || op.intermittent == 1;
    const s = op.schedule?.[0] ?? null;
    let scheduleContent = "";

    if (isIntermittent) {
        const validDates = (op.schedule || []).map(d => new Date(d.date)).sort((a, b) => a - b);
        if (validDates.length === 0) {
            scheduleContent = "N/A";
        } else {
            const start = validDates[0];
            const end = validDates[validDates.length - 1];
            scheduleContent = start.getTime() === end.getTime() 
                ? formatDatePretty(start.toISOString())
                : `${formatDatePretty(start.toISOString())} – ${formatDatePretty(end.toISOString())}`;
        }
    } else {
        scheduleContent = s 
            ? `${formatDatePretty(s.date)}`
            : "N/A";
    }

    const submittedOffice = (op.submitted_by_role === "SDA Assistant") ? "SDA Office" : (op.submitted_by_role ?? "Unknown");
    const participantsCount = op.participants?.length ?? 0;

    // For intermittent, sum all volunteers needed; else use target_number
// For intermittent, sum all volunteers needed; else sum all target_number in schedule
let totalNeeded = 0;

if (isIntermittent && op.intermittent_tasks_by_date) {
    totalNeeded = Object.values(op.intermittent_tasks_by_date) // per date
        .flatMap(t => Object.values(t))                        // per task
        .reduce((sum, t) => sum + (t.needed || 0), 0);        // sum needed
} else if (op.schedule && op.schedule.length) {
    // sum all target_number in schedule for regular opportunities
    totalNeeded = op.schedule.reduce((sum, sc) => sum + (sc.target_number || 0), 0);
} else {
    totalNeeded = '?';
}



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
        <i class="bi bi-person-fill me-1"></i>${participantsCount} / ${totalNeeded}
    </small>
</div>

            </div>
        </div>
    `;
}).join('');

}

// ---------- INIT ----------
document.addEventListener("DOMContentLoaded", () => {
    const sorted = [...opportunities].sort((a, b) => (b.is_urgent === "yes") - (a.is_urgent === "yes"));
    renderOpportunities(sorted);

    filterSearch.addEventListener("input", applyFilters);
    filterOffice.addEventListener("change", applyFilters);
    filterStatus.addEventListener("change", applyFilters);
    sortBy.addEventListener("change", applyFilters);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Intermittent Join Modal -->
<div class="modal fade" id="intermittentJoinModal" tabindex="-1" aria-labelledby="intermittentJoinLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="p-3 modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Join Schedule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <p>Select the dates you want to join:</p>
        <div id="intermittentScheduleList" class="list-group">
          <!-- Dates injected by JS -->
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary w-100" onclick="submitIntermittentJoin()">Submit Selected Dates</button>
      </div>
    </div>
  </div>
</div>




<!-- Pagination -->
<div class="mt-4 d-flex justify-content-center">
    {{ $pagination->links('pagination::bootstrap-5') }}
</div>

@endsection

