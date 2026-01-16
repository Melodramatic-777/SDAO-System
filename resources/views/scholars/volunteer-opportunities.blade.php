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
    <div class="card-body position-relative">

    @php
// ===== TOTAL SLOTS =====
$total = 0;

if (!empty($op['is_intermittent']) && (int)$op['is_intermittent'] === 1) {
    foreach ($op['intermittent_tasks_by_date'] ?? [] as $date => $tasks) {
        foreach ($tasks as $task) {
            $total += (int) ($task['needed'] ?? 0);
        }
    }
} else {
    $total = collect($op['tasks'] ?? [])
        ->sum(fn($t) => (int) ($t['needed'] ?? 0));
}

// ===== JOINED COUNT (UNIQUE TASK JOINS) =====
$joined = 0;

foreach ($op['participants'] ?? [] as $p) {
    if (strtolower($p['status'] ?? '') === 'pending invite') continue;

    if (!empty($p['tasks']) && is_array($p['tasks'])) {
        foreach ($p['tasks'] as $date => $tasks) {
            $joined += count($tasks);
        }
    }
}
@endphp


<div class="mb-1 d-flex justify-content-between align-items-start">

    <!-- LEFT: Urgent + Title -->
    <div>
        @if(isset($op['is_urgent']) && $op['is_urgent'] === 'yes')
            <span class="mb-1 badge-urgent d-inline-block">Urgent</span>
        @endif

        <h5 class="mb-0 card-title">
            <strong>{{ $op['title'] }}</strong>
        </h5>
    </div>

    <!-- RIGHT: Total Slots -->
<div class="participant-counter {{ (isset($op['is_urgent']) && $op['is_urgent'] === 'yes') ? 'counter-urgent' : '' }}">
    <i class="bi bi-people"></i>
    <span>{{ $joined }}/{{ $total }}</span>
</div>


</div>


<p class="mb-1"><strong>Office: </strong>{{ ($op['submitted_by_role'] ?? '') === 'SDA Assistant' ? 'SDA Office' : ($op['submitted_by_role'] ?? 'Unknown') }}</p>
          <p class="mb-1"><strong>Location: </strong>{{ $op['location'] }}</p>
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
<!-- Right column -->
<div class="opportunity-detail flex-grow-1" id="opportunityDetail">

  <!-- Empty State (CENTERED) -->
  <div class="empty-state d-flex align-items-center justify-content-center h-100">
    <div class="text-center text-muted">

      <!-- Cursor Icon (flipped left) -->
      <div class="mb-3">
        <i class="bi bi-cursor fs-1 text-dark"
           style="transform: scaleX(-1); display: inline-block;"></i>
      </div>

      <!-- Title -->
      <h4 class="mb-2 fw-bold text-dark">Select an opportunity</h4>

      <!-- Subtitle -->
      <p class="mb-0">
        Click on any volunteer opportunity from the list to<br>
        view details and join
      </p>

    </div>
  </div>

</div>







<div class="modal fade" id="confirmJoinModal" tabindex="-1">
<div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Confirm Join</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

<div class="modal-body">
  <p class="mb-0">
    Are you sure you want to join this task?
    <br>
    <span class="fw-bold text-primary fs-5" id="confirmTaskName"></span>
  </p>
</div>


      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <button class="btn btn-success" id="confirmJoinBtn">Yes, Join</button>
      </div>
    </div>
  </div>
</div>



<div class="modal fade" id="joinSuccessModal" tabindex="-1">
<div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="p-4 text-center modal-content">
      <div class="modal-body">
        <i class="bi bi-check-circle-fill text-success fs-1"></i>
        <h5 class="mt-3 fw-bold">Successfully Joined Task</h5>
        <button class="mt-4 btn btn-primary" data-bs-dismiss="modal">
          OK
        </button>
      </div>
    </div>
  </div>
</div>




<!-- On the Spot Volunteer Modal -->
<div class="modal fade" id="onTheSpotModal" tabindex="-1"
     aria-labelledby="onTheSpotVolunteerLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="p-3 modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="onTheSpotVolunteerLabel">
          ON THE SPOT VOLUNTEER
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Form -->
      <form id="onTheSpotForm"
            method="POST"
            action="{{ route('scholars.ontheSpotVolunteer') }}">
        @csrf

        <!-- Modal Body -->
        <div class="modal-body">
          <div class="row g-3">

            <!-- Office + Description -->
            <div class="col-12">

              <!-- DESCRIPTION (NOW ON TOP OF OFFICE LABEL) -->
              <p class="mb-3 text-muted">
                By submitting, you will be added to the pool of available
                on-the-spot volunteers. Offices may select you if help is needed.
              </p>

              <label class="form-label fw-semibold">Office</label>
              <select name="office" class="form-select" required>
                <option value="">Choose office</option>
                <option value="SDA Office">SDA Office</option>
                @foreach($offices as $office)
                  <option value="{{ $office['name'] }}">
                    {{ $office['name'] }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Transfer Willingness -->
            <div class="col-12">
              <label class="form-label fw-semibold">
                If your chosen office does not select you, are you willing to be transferred?
              </label>

              <div class="mb-2 form-check">
                <input class="form-check-input"
                       type="radio"
                       name="willing_to_transfer"
                       id="transferYes"
                       value="yes"
                       required>
                <label class="form-check-label" for="transferYes">
                  Yes, I am willing to transfer to other offices
                </label>
              </div>

              <div class="form-check">
                <input class="form-check-input"
                       type="radio"
                       name="willing_to_transfer"
                       id="transferNo"
                       value="no"
                       required>
                <label class="form-check-label" for="transferNo">
                  No, only make me available for my chosen office
                </label>
              </div>
            </div>

            <!-- Available Date -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Available Date</label>
              <input type="date"
                     name="available_date"
                     class="form-control"
                     required>
            </div>

            <!-- Available Time -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Available Time</label>
              <div class="gap-2 d-flex align-items-center">
                <input type="time"
                       name="available_start_time"
                       class="form-control"
                       required>
                <span class="text-muted">to</span>
                <input type="time"
                       name="available_end_time"
                       class="form-control"
                       required>
              </div>
            </div>

            <!-- Reasons -->
            <div class="col-12">
              <label class="form-label fw-semibold">Reason for Availability</label>

              <div class="mb-2 form-check">
                <input class="form-check-input"
                       type="checkbox"
                       name="reasons[]"
                       value="Vacant Schedule"
                       id="reasonVacant">
                <label class="form-check-label" for="reasonVacant">
                  Vacant Schedule
                </label>
              </div>

              <div class="mb-2 form-check">
                <input class="form-check-input"
                       type="checkbox"
                       name="reasons[]"
                       value="Lack of Volunteer Hours"
                       id="reasonHours">
                <label class="form-check-label" for="reasonHours">
                  Lack of Volunteer Hours
                </label>
              </div>

              <div class="mb-2 form-check">
                <input class="form-check-input"
                       type="checkbox"
                       name="reasons[]"
                       value="Desire to be Productive"
                       id="reasonProductive">
                <label class="form-check-label" for="reasonProductive">
                  Desire to be Productive
                </label>
              </div>

              <div class="form-check">
                <input class="form-check-input"
                       type="checkbox"
                       id="otherReasonCheck">
                <label class="form-check-label" for="otherReasonCheck">
                  Other
                </label>
              </div>

              <input type="text"
                     class="mt-2 form-control"
                     placeholder="Please specify"
                     name="other_reason"
                     id="otherReasonInput"
                     disabled>
            </div>

          </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary w-100">
            Mark Myself Available
          </button>
        </div>

      </form>

    </div>
  </div>
</div>

<!-- Already Joined Date Modal -->
<div class="modal fade" id="alreadyJoinedDateModal" tabindex="-1">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="p-4 text-center modal-content">
      <div class="modal-body">
        <i class="bi bi-exclamation-circle-fill text-warning fs-1"></i>
        <h5 class="mt-3 fw-bold">Already Joined</h5>
        <p class="mb-0">
          You can only join <strong>one task per date</strong>.<br>
          Please choose another date.
        </p>
        <button class="mt-4 btn btn-primary" data-bs-dismiss="modal">
          OK
        </button>
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
const allOpportunities = @json($allOpportunities);
const opportunities = allOpportunities;
const scholarId = "{{ session('scholar_accounts.student_no') }}";
const scholarName = "{{ session('scholar_accounts.first_name') }} {{ session('scholar_accounts.last_name') }}";
const csrfToken = "{{ csrf_token() }}";

let pendingJoin = null;
let currentSelectedId = null;
let selectedTaskDate = null;

/* ================= SHOW DETAILS ================= */
function showDetail(id, selectedDate = null) {
    currentSelectedId = id;
    const op = opportunities.find(o => o.opportunity_id === id);
    if (!op) return;

    const isIntermittent = op.is_intermittent == 1;
    const tasksByDate = op.intermittent_tasks_by_date || {};

    if (isIntermittent) {
        selectedTaskDate = selectedDate ?? Object.keys(tasksByDate)[0] ?? null;
    }

    /* -------- LEFT COLUMN -------- */
const counts = getParticipantCounts(op, selectedTaskDate);

const leftColumn = `
    <div class="col-md-6">
        <h6 class="gap-2 mb-2 fw-bold d-flex align-items-center">


            <i class="bi bi-people"></i>
                        <span>
                ${counts.joined}/${counts.total}
            </span>
            Participants Joined
            <span class="text-muted fw-normal">
                (${selectedTaskDate
                    ? new Date(selectedTaskDate).toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric'
                    })
                    : ''})
            </span>
        </h6>

        ${renderParticipants(op, selectedTaskDate)}
    </div>
`;




    /* -------- RIGHT COLUMN -------- */
/* -------- RIGHT COLUMN -------- */
let rightColumnContent = '';

// ================= INTERMITTENT =================
// ================= INTERMITTENT =================
if (isIntermittent && selectedTaskDate && tasksByDate[selectedTaskDate]) {

    const tasks = Object.values(tasksByDate[selectedTaskDate]);

    let timeStr = 'N/A';
    if (tasks[0]?.start_time && tasks[0]?.end_time) {
        timeStr = `${formatTime(tasks[0].start_time)} – ${formatTime(tasks[0].end_time)}`;
    }

    // ✅ DATE-SPECIFIC FOOD LOGIC
// ✅ FOOD COMES FROM SCHEDULE (PER DATE)
const scheduleEntry = (op.schedule || []).find(s => s.date === selectedTaskDate);

const foodProvided =
    scheduleEntry?.food_provided === true ||
    scheduleEntry?.food_provided === 1 ||
    scheduleEntry?.food_provided === '1' ||
    (typeof scheduleEntry?.food_provided === 'string'
        && scheduleEntry.food_provided.toLowerCase() === 'yes');


    rightColumnContent = `
    <div class="task-panel">

        <div class="section-label">
            <i class="bi bi-calendar-event me-1"></i> Select Date
        </div>

        <div class="mb-3 date-pill-group">
            ${Object.keys(tasksByDate).map(d => `
                <button class="date-pill ${d === selectedTaskDate ? 'active' : ''}"
                    onclick="showDetail('${id}', '${d}')">
                    ${new Date(d).toLocaleDateString('en-US',{
                        weekday:'short',month:'short',day:'numeric'
                    })}
                </button>
            `).join('')}
        </div>

        ${renderTimeAndFood(timeStr, foodProvided)}
        <hr>

        ${renderTaskList(tasks, id, selectedTaskDate)}
    </div>`;
}


// ================= REGULAR =================
// ================= REGULAR =================
else if (!isIntermittent && op.schedule?.length) {

    const s = op.schedule[0];
    const taskDate = s.date;

    const tasks = op.tasks || [];

    let timeStr = 'N/A';
    if (s.start_time && s.end_time) {
        timeStr = `${formatTime(s.start_time)} – ${formatTime(s.end_time)}`;
    }

    const foodProvided = op.food_provided || op.food || '';

    rightColumnContent = `
    <div class="task-panel">

        <div class="section-label">
            <i class="bi bi-calendar-event me-1"></i> Schedule
        </div>

        <!-- BOLD DATE (NO BOX) -->
        <div class="mb-2 fw-bold fs-6 text-dark">
            ${new Date(taskDate).toLocaleDateString('en-US',{
                weekday:'long',
                month:'long',
                day:'numeric',
                year:'numeric'
            })}
        </div>

        ${renderTimeAndFood(timeStr, foodProvided)}
        <hr>

        ${renderTaskList(tasks, id, taskDate)}
    </div>
    `;
}


    const rightColumn = `<div class="col-md-6">${rightColumnContent || '<p>No tasks available.</p>'}</div>`;

    /* -------- RENDER DETAIL -------- */
document.getElementById('opportunityDetail').innerHTML = `
    <div class="container-fluid text-start">


        <div class="mb-1 volunteer-heading">ACTIVITY TITLE</div>
        <p class="opportunity-title text-start">${op.title || 'Untitled Opportunity'}</p>

        <div class="mt-2 row g-3">
            <div class="col-md-6">
                <div class="info-tile">
                    <div class="info-label"><i class="bi bi-building me-1"></i> Office</div>
                    <div class="info-value text-start">${op.submitted_by_role || 'N/A'}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-tile">
                    <div class="info-label"><i class="bi bi-person-circle me-1"></i> Submitted by</div>
                    <div class="info-value text-start">${op.submitted_by_name || 'N/A'}</div>
                </div>
            </div>
        </div>

        <div class="mt-3 info-tile">
            <div class="info-label"><i class="bi bi-geo-alt me-1"></i> Location</div>
            <div class="info-value text-start">${op.location || 'N/A'}</div>
        </div>

        <div class="mt-4">
            <div class="about-heading">
                <i class="bi bi-info-circle me-1"></i> About this Activity
            </div>
            <div class="about-box text-start">${op.description || 'No description provided.'}</div>
        </div>

        <hr class="my-4">
        <div class="row">
            ${leftColumn}
            ${rightColumn}
        </div>
    </div>`;

    document.querySelectorAll('.opportunity-card').forEach(c => c.classList.remove('selected'));
    document.querySelector(`.opportunity-card[data-id="${id}"]`)?.classList.add('selected');
}

/* ================= JOIN SELECTED TASK ================= */
function joinSelectedTask(opportunityId, date) {
    const selected = document.querySelector(
        `input[name="taskSelect_${opportunityId}"]:checked`
    );

    if (!selected) {
        alert("Please select a task.");
        return;
    }

    const taskName = selected.closest('.task-card')
        .querySelector('.task-title').innerText;

    pendingJoin = {
        opportunityId,
        date,
        taskId: selected.value,
        taskName
    };

    document.getElementById('confirmTaskName').innerText = taskName;

    new bootstrap.Modal(
        document.getElementById('confirmJoinModal')
    ).show();
}

document.getElementById('confirmJoinBtn').addEventListener('click', function () {
    if (!pendingJoin) return;

    joinTask(
        pendingJoin.opportunityId,
        pendingJoin.date,
        pendingJoin.taskId,
        pendingJoin.taskName
    );

    bootstrap.Modal.getInstance(
        document.getElementById('confirmJoinModal')
    ).hide();

    pendingJoin = null;
});

/* ================= HELPERS ================= */
function formatTime(time) {
    const [h,m] = time.split(':');
    const hour = h % 12 || 12;
    return `${hour}:${m} ${h >= 12 ? 'PM' : 'AM'}`;
}

function renderParticipants(op, selectedDate) {
    if (!op.participants?.length || !selectedDate) {
        return '<em class="text-muted">No participants for this date.</em>';
    }

    const filtered = op.participants.filter(p =>
        p.tasks &&
        p.tasks[selectedDate] &&
        p.status?.toLowerCase() !== 'pending invite'
    );

    if (!filtered.length) {
        return '<em class="text-muted">No participants for this date.</em>';
    }

    return `
        <div class="list-group">
            ${filtered.map(p => `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">${p.name}</div>
                        <small class="text-muted">
                            ${p.tasks[selectedDate].join(', ')}
                        </small>
                    </div>
                    <span class="badge bg-success-subtle text-success">
                        ${p.status}
                    </span>
                </div>
            `).join('')}
        </div>
    `;
}



document.addEventListener('change', function (e) {
    if (e.target.matches('input[type="radio"][name^="taskSelect_"]')) {
        const opportunityId = e.target.name.replace('taskSelect_', '');
        const joinBtn = document.getElementById(`joinBtn_${opportunityId}`);

        if (joinBtn) {
            joinBtn.disabled = false;
            joinBtn.classList.remove('btn-secondary');
            joinBtn.classList.add('btn-success');

            // 🔥 Update button text
            joinBtn.innerHTML = `
                <i class="bi bi-check-circle me-1"></i>
                Join this Activity
            `;
        }
    }
});




function joinTask(opportunityId, date, taskId, taskName) {
fetch("{{ route('scholars.joinTask') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            opportunity_id: opportunityId,
            date: date,
            task: taskName,
            task_id: taskId
        })
    })
    .then(res => res.json())
  .then(data => {

    // ❌ 1 TASK PER DATE — SHOW ALERT MODAL
    if (data.message === 'You can only join 1 task per date') {
        new bootstrap.Modal(
            document.getElementById('alreadyJoinedDateModal')
        ).show();
        return;
    }

    // ❌ TASK FULL / OTHER ERRORS
    if (!data.message || data.message !== 'Successfully joined task') {
        alert(data.message || 'Unable to join task.');
        return;
    }

    // ✅ SUCCESS
    const btn = document.getElementById(`joinBtn_${opportunityId}`);
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Joined';
        btn.classList.remove('btn-success');
        btn.classList.add('btn-secondary');
    }

    new bootstrap.Modal(
        document.getElementById('joinSuccessModal')
    ).show();
})

    .catch(err => {
        console.error(err);
        alert("Failed to join task.");
    });
}

function renderTaskList(tasks, opportunityId, date) {
    if (!tasks?.length) {
        return '<p class="text-muted">No tasks available.</p>';
    }

    return `
        <div class="section-label">
            <i class="bi bi-list-check me-1"></i> Choose Your Task
        </div>

        <div class="task-list">
            ${tasks.map(t => `
                <label class="task-card">
                    <div class="d-flex">
                        <input type="radio"
                            name="taskSelect_${opportunityId}"
                            value="${t.task_id}"
                            class="mt-1 form-check-input me-3">

                        <div class="flex-grow-1">
                            <div class="task-title">${t.task}</div>
                            ${t.description ? `<div class="task-desc">${t.description}</div>` : ''}
                        </div>

                        <div class="task-slots">
                            <div class="slots">${t.needed} slots</div>
                        </div>
                    </div>
                </label>
            `).join('')}
        </div>

        <button class="mt-3 btn btn-secondary w-100 join-btn"
            id="joinBtn_${opportunityId}"
            disabled
            onclick="joinSelectedTask('${opportunityId}','${date}')">
            <i class="bi bi-person-plus me-1"></i> Select a Task to Join
        </button>
    `;
}

function renderTimeAndFood(timeStr, food) {

    const hasFood =
        food === true ||
        food === 1 ||
        food === '1' ||
        (typeof food === 'string' && food.toLowerCase() === 'yes');

    return `
        <div class="flex-wrap gap-2 mt-1 date-meta d-flex">
            ${
                timeStr
                    ? `
                    <span class="time-badge">
                        <i class="bi bi-clock me-1"></i>
                        ${timeStr}
                    </span>
                    `
                    : ''
            }

            <span class="food-badge ${hasFood ? 'food-yes' : 'food-no'}">
                <i class="bi ${hasFood ? 'bi-check-circle-fill' : 'bi-x-circle-fill'} me-1"></i>
                ${hasFood ? 'Food Provided' : 'No Food'}
            </span>
        </div>
    `;
}


function getParticipantCounts(op, selectedDate) {
    if (!selectedDate) {
        return { joined: 0, total: 0 };
    }

    // COUNT JOINED
    const joined = (op.participants || []).filter(p =>
        p.tasks &&
        p.tasks[selectedDate] &&
        p.status?.toLowerCase() !== 'pending invite'
    ).length;

    // COUNT TOTAL SLOTS
    let total = 0;

    if (op.is_intermittent == 1 && op.intermittent_tasks_by_date?.[selectedDate]) {
        const tasks = Object.values(op.intermittent_tasks_by_date[selectedDate]);
        total = tasks.reduce((sum, t) => sum + (parseInt(t.needed) || 0), 0);
    } else if (op.tasks?.length) {
        total = op.tasks.reduce((sum, t) => sum + (parseInt(t.needed) || 0), 0);
    }

    return { joined, total };
}


</script>


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





@endsection

