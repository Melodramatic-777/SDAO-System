@extends('volunteers-admin.layout')

@section('content')

<main class="page-container">

<div class="status-section">

@php
    // Calculate counts
    $allCount = collect($opportunities)->count();
    $waitingApprovalCount = collect($opportunities)->filter(fn($op) => in_array(strtolower($op['status'] ?? ''), ['pending','waiting','waiting for approval','returned to sdao']))->count();
$waitingVolunteersCount = collect($opportunities)->filter(function($op) {
    $status = strtolower($op['status'] ?? '');
    $submittedByRole = strtolower($op['submitted_by_role'] ?? '');
    $participantsCount = count($op['participants'] ?? []);
    $targetNumber = (int) ($op['schedule'][0]['target_number'] ?? 0);

    if ($submittedByRole === 'sda assistant') return true;
    if ($status === 'approved' && $participantsCount < $targetNumber) return true;
    return false;
})->count();
    $returnedCount = collect($opportunities)->filter(fn($op) => in_array(strtolower($op['status'] ?? ''), ['returned','returned to sdao']))->count();

    // Sort opportunities
// Sort opportunities
$filteredOpportunities = collect($opportunities)->sortByDesc(function($opportunity) {
    $isUrgent = !empty($opportunity['is_urgent']) && ($opportunity['is_urgent'] === true || $opportunity['is_urgent'] === 'yes');
    $status = strtolower($opportunity['status'] ?? '');
    $participants = count($opportunity['participants'] ?? []);
    $target = (int) ($opportunity['schedule'][0]['target_number'] ?? 0);

    if ($isUrgent) return 5; // <- Urgent goes to the top
    if (in_array($status, ['pending','waiting','waiting for approval'])) return 4;
    if ($status === 'approved' && $participants < $target) return 3;
    if (in_array($status, ['returned','returned to sdao'])) return 2;
    return 0;
});

@endphp

<div class="gap-4 mb-4 status-filter d-flex">
    <span class="status-tab active" id="filterAll">
        All <span class="badge bg-secondary ms-1" id="countAll">{{ $allCount }}</span>
    </span>

    <span class="status-tab" id="filterUrgent">
        Urgent <span class="badge bg-danger ms-1" id="countUrgent">
            {{ collect($opportunities)->filter(fn($op) => !empty($op['is_urgent']) && ($op['is_urgent'] === true || $op['is_urgent'] === 'yes'))->count() }}
        </span>
    </span>

    <span class="status-tab" id="filterWaitingApproval">
        Waiting for Approval <span class="badge bg-warning text-dark ms-1" id="countWaitingApproval">{{ $waitingApprovalCount }}</span>
    </span>
    <span class="status-tab" id="filterWaitingVolunteers">
        Waiting for Volunteers <span class="badge bg-primary ms-1" id="countWaitingVolunteers">{{ $waitingVolunteersCount }}</span>
    </span>
    <span class="status-tab" id="filterReturned">
        Returned <span class="badge ms-1" id="countReturned" style="background-color: #fd7e14; color: white;">{{ $returnedCount }}</span>
    </span>
</div>

@if($filteredOpportunities->isEmpty())
    <p>No opportunities.</p>
@else
<div id="opportunitiesContainer">

@foreach($filteredOpportunities as $opportunity)
@php
   $badgeStyle = ''; // reset for each loop
   $status = strtolower($opportunity['status'] ?? 'waiting');
   $submittedByRole = strtolower($opportunity['submitted_by_role'] ?? '');
   $participantsCount = count($opportunity['participants'] ?? []);
   $targetNumber = (int) ($opportunity['schedule'][0]['target_number'] ?? 0);

   // Display logic with correct colors
   if ($status === 'approved' && $participantsCount < $targetNumber) {
       $displayStatus = 'waiting for volunteers';
       $badgeClass = 'badge bg-primary text-white'; // white text
   } elseif ($status === 'approved') {
       $displayStatus = 'waiting for volunteers';
       $badgeClass = 'badge bg-primary text-white'; // white text
   } elseif ($submittedByRole === 'sda assistant') {
       $displayStatus = 'waiting for volunteers';
       $badgeClass = 'badge bg-primary text-white'; // white text
   } elseif (in_array($status, ['returned','returned to sdao'])) {
       $displayStatus = 'returned';
       $badgeClass = 'badge';
       $badgeStyle = 'background-color: #fd7e14; color: white;'; // orange with white text
   } elseif (in_array($status, ['pending','waiting','waiting for approval'])) {
       $displayStatus = 'waiting for approval';
       $badgeClass = 'badge bg-warning text-dark'; // yellow
   } else {
       $displayStatus = $status;
       $badgeClass = 'badge bg-secondary';
   }
@endphp






<div class="p-3 mb-3 bg-white border rounded status-card-container"
data-status="{{ $status }}"
data-participants="{{ $participantsCount }}"
data-target="{{ $targetNumber }}">

    @php
        $isUrgent = !empty($opportunity['is_urgent']) && ($opportunity['is_urgent'] === true || $opportunity['is_urgent'] === 'yes');
    @endphp

    @if($isUrgent)
        <div class="mb-1">
            <span class="badge bg-danger">Urgent</span>
        </div>
    @endif

    <div class="mb-1 d-flex justify-content-between align-items-center">
        <strong>{{ $opportunity['title'] ?? 'No Title' }}</strong>
        <div>
<span class="{{ $badgeClass }}" @isset($badgeStyle) style="{{ $badgeStyle }}" @endisset>
    {{ ucfirst($displayStatus) }}
</span>


        </div>
    </div>

    <div>
        <p class="mb-1">{{ $opportunity['location'] ?? 'No Location' }}</p>
        <small>Submitted by: {{ $opportunity['submitted_by_name'] }} ({{ $opportunity['submitted_by_role'] }})</small><br>
        <small>Date Submitted: {{ isset($opportunity['submitted_at']) ? \Carbon\Carbon::parse($opportunity['submitted_at'])->format('F j, Y') : 'N/A' }}</small>
        <button class="view-details-btn float-end" data-bs-toggle="modal" data-bs-target="#opportunityModal{{ $opportunity['opportunity_id'] }}">
            View Details
        </button>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="opportunityModal{{ $opportunity['opportunity_id'] }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content custom-modal-content">

<div class="modal-header">
    <h5 class="modal-title">Request Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

            <div class="modal-body custom-modal-body row g-4">

                {{-- Left Panel --}}
               {{-- Left Panel --}}
<div class="col-md-6 left-panel">
    <div class="left-panel-scroll">

        {{-- Status + Submitted At --}}
        <div class="gap-2 mb-2 d-flex align-items-center status-meta">
            <span class="status-pill {{ $badgeClass }}"
                  @isset($badgeStyle) style="{{ $badgeStyle }}" @endisset>
                <i class="bi bi-clock-history me-1"></i>
                {{ ucfirst($displayStatus) }}
            </span>

            <span class="submitted-at text-muted">
                Submitted
                {{ isset($opportunity['submitted_at'])
                    ? \Carbon\Carbon::parse($opportunity['submitted_at'])->format('M d, Y, g:i A')
                    : 'N/A'
                }}
            </span>
        </div>
<BR>
        {{-- Opportunity Title --}}
        <div class="mb-2 volunteer-heading">
            ACTIVITY TITLE
        </div>

        <p class="opportunity-title">
            {{ $opportunity['title'] ?? 'Untitled Opportunity' }}
        </p>


    {{-- Office + Submitted By (SIDE BY SIDE) --}}
    <div class="row g-3">
        <div class="col-md-6">
            <div class="info-tile">
<div class="info-label">
    <i class="bi bi-building me-1"></i> Office
</div>                <div class="info-value">
                    {{ $opportunity['submitted_by_role'] ?? 'N/A' }}
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="info-tile">

<div class="info-label">
    <i class="bi bi-person-circle me-1"></i> Submitted by
</div>                <div class="info-value">
                    {{ $opportunity['submitted_by_name'] ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Location --}}
    <div class="mt-3 info-tile">
<div class="info-label">
    <i class="bi bi-geo-alt me-1"></i> Location
</div>        <div class="info-value">
            {{ $opportunity['location'] ?? 'N/A' }}
        </div>
    </div>

    {{-- Description --}}
    <div class="mt-3 description-tile">
        <div class="info-label">Description</div>
        <p class="mb-0">
            {{ $opportunity['description'] ?? 'No description provided.' }}
        </p>
    </div>


{{-- Schedule Breakdown --}}
{{-- Schedule Breakdown --}}
@if(!empty($opportunity['schedule']) && is_array($opportunity['schedule']))

<div class="mt-4 schedule-section">
    <div class="schedule-card">

        {{-- Header --}}
        <div class="schedule-header">
            <div class="gap-2 d-flex align-items-center">
                <span class="schedule-icon">
                    <i class="bi bi-calendar-event"></i>
                </span>
                <div>
                    <div class="schedule-title">Schedule Breakdown</div>
                    <div class="schedule-subtitle">
                        {{ count($opportunity['schedule']) }} dates
                    </div>
                </div>
            </div>
        </div>
<hr>
        {{-- Items --}}
        <div class="schedule-list">
            @foreach($opportunity['schedule'] as $s)
                @php
                    $dateText = !empty($s['date'])
                        ? \Carbon\Carbon::parse($s['date'])->format('F j, Y')
                        : 'N/A';

                    $timeText = (!empty($s['start_time']) && !empty($s['end_time']))
                        ? \Carbon\Carbon::parse($s['start_time'])->format('g:i A') . ' – ' .
                          \Carbon\Carbon::parse($s['end_time'])->format('g:i A')
                        : null;
                @endphp

                <div class="schedule-item">
                    <div class="schedule-date">
                        <i class="bi bi-calendar3 me-1"></i> {{ $dateText }}
                    </div>

                    @if($timeText)
                        <div class="schedule-time">
                            <i class="bi bi-clock me-1"></i> {{ $timeText }}
                        </div>
                    @endif
                </div>

            @endforeach
        </div>

    </div>
</div>

@endif




    @if(!empty($opportunity['return_comment']) && strtolower($opportunity['status']) === 'returned')
        <div class="mt-2 alert alert-danger">
            <strong>Return Comment:</strong><br>{{ $opportunity['return_comment'] }}
        </div>
    @endif

    {{-- Participants List --}}
    @if(!empty($opportunity['participants']))
        <hr>
        <h6><strong>Participants</strong></h6>
        <ul class="list-group list-group-flush">
            @foreach($opportunity['participants'] as $participant)
                @php
                    $displayStatus = strtolower($participant['status'] ?? '') === 'declined' ? 'Invite Declined' : ($participant['status'] ?? 'Pending');
                @endphp
                <li class="px-0 list-group-item">
                    {{ $participant['name'] ?? 'Unnamed' }} 
                    <span class="text-muted">({{ $displayStatus }})</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
</div>   
   {{-- Right Panel --}}
<div class="col-md-6 right-panel">

    @php
        $isIntermittent = !empty($opportunity['is_intermittent']) && (int)$opportunity['is_intermittent'] === 1;
        $allTasks = $opportunity['intermittent_tasks_by_date'] ?? [];
        $totalVolunteersNeededOverall = collect($allTasks)
            ->map(fn($tasks) => collect($tasks)
            ->sum(fn($t) => isset($t['needed']) ? (int)$t['needed'] : 0))
            ->sum();
    @endphp

@if($totalVolunteersNeededOverall > 0)
<div class="mb-2 volunteer-heading">
    TOTAL VOLUNTEERS NEEDED
</div>

<div class="mb-3 volunteer-summary">
    <div class="volunteer-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
            <circle cx="9" cy="7" r="4" stroke="white" stroke-width="2"/>
            <circle cx="17" cy="7" r="3" stroke="white" stroke-width="2"/>
            <path d="M3 21c0-3 3-6 6-6s6 3 6 6" stroke="white" stroke-width="2"/>
            <path d="M14 21c.5-2 2-4 4-4 2.5 0 3 2 3 4" stroke="white" stroke-width="2"/>
        </svg>
    </div>

    <div class="volunteer-text">
        <div class="volunteer-count">{{ $totalVolunteersNeededOverall }}</div>
        <div class="volunteer-label">volunteers required</div>
    </div>
</div>


@endif

@if(isset($opportunity['intermittent_tasks_by_date']) && is_array($opportunity['intermittent_tasks_by_date']))

<div class="mb-2 volunteer-heading">
    DATES & TASKS BREAKDOWN
</div>

<div class="dates-tasks-scroll">
@foreach($opportunity['intermittent_tasks_by_date'] as $date => $tasksForDate)

    @php
        $schedule = collect($opportunity['schedule'] ?? [])
            ->firstWhere('date', $date);

        $start = $schedule['start_time'] ?? null;
        $end   = $schedule['end_time'] ?? null;
        $food  = strtolower($schedule['food_provided'] ?? 'no') === 'yes';

        $dateTotal = collect($tasksForDate)
            ->sum(fn($task) => (int)($task['needed'] ?? 0));
    @endphp

    <div class="mb-3 date-card">

        {{-- Header --}}
        <div class="date-header d-flex justify-content-between">
            <strong>{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</strong>
            <span class="date-volunteers">{{ $dateTotal }} volunteers</span>
        </div>

        {{-- Time + Food --}}
        <div class="date-meta">
            @if($start && $end)
<span class="time-badge">
    <i class="bi bi-clock me-1"></i>
    {{ \Carbon\Carbon::parse($start)->format('g:i A') }} –
    {{ \Carbon\Carbon::parse($end)->format('g:i A') }}
</span>


                <span class="food-badge {{ $food ? 'food-yes' : 'food-no' }}">
                    <i class="bi {{ $food ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                    {{ $food ? 'Food Provided' : 'No Food' }}
                </span>
            @endif
        </div>

        <hr>

        {{-- Tasks --}}
        <ul class="task-list">
            @foreach($tasksForDate as $task)
                <li class="task-item">
                    <span>• {{ $task['task'] ?? 'Unnamed Task' }}</span>
                    <span class="needed">{{ $task['needed'] ?? 0 }} needed</span>
                </li>
            @endforeach
        </ul>

    </div>

@endforeach
</div>


@else
    <p class="text-muted">No tasks found.</p>
@endif


</div>


            </div>

{{-- Approve / Return Buttons --}}
<div class="gap-3 modal-footer d-flex justify-content-end">

    @if(in_array(strtolower($opportunity['status'] ?? ''), ['pending', 'waiting for approval', 'returned to sdao']))

        {{-- Return Request --}}
        <button
            type="button"
            class="btn btn-return-outline"
            data-bs-toggle="modal"
            data-bs-target="#returnModal{{ $opportunity['opportunity_id'] }}"
        >
            <i class="bi bi-x-lg me-2"></i> Return Request
        </button>

        {{-- Approve Request --}}
<form
    method="POST"
    action="{{ route('volunteers-admin.opportunity.update-status', $opportunity['opportunity_id']) }}"
    id="returnForm{{ $opportunity['opportunity_id'] }}"
>

    @csrf
    @method('PATCH')
    <input type="hidden" name="status" value="Approved">

<button
    type="button"
    class="btn btn-approve-solid action-btn"
    data-title="Confirm Approval"
    data-message="Are you sure you want to approve this request?"
    data-form-id="approveForm{{ $opportunity['opportunity_id'] }}"
    data-confirm-class="btn-success"
    data-bs-toggle="modal"
    data-bs-target="#globalModal"
>
    <i class="bi bi-check-lg me-2"></i> Approve Request
</button>

        </form>

    @elseif(strtolower($opportunity['status'] ?? '') === 'approved' && strtolower($opportunity['submitted_by_role'] ?? '') !== 'sda assistant')
        <span class="text-success fw-bold">This opportunity is already approved.</span>
    @endif

</div>




            {{-- Cancel Opportunity --}}
            @php
                $user = session('volunteers_admin') ?? [];
                $userEmail = $user['email'] ?? null;
                $userRole  = $user['role'] ?? null;
            @endphp
            @if($userRole === 'SDA Assistant' && ($opportunity['submitted_by'] ?? '') === $userEmail)
                <div class="mt-0 modal-footer border-top">
                    <form method="POST" action="{{ route('volunteers-admin.opportunities.cancel', $opportunity['opportunity_id']) }}">
                        @csrf
                        <button type="submit" class="btn btn-return w-100" onclick="return confirm('Are you sure you want to cancel this opportunity? This will remove invitations and move the record to canceled opportunities.')">
                            Cancel Opportunity
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>
</div>
{{-- End Modal --}}


{{-- Return Modal --}}
{{-- Return Modal --}}
<div class="modal fade" id="returnModal{{ $opportunity['opportunity_id'] }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
<div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Return Opportunity: {{ $opportunity['title'] }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('volunteers-admin.opportunity.update-status', $opportunity['opportunity_id']) }}">
                @csrf
                @method('PATCH')

                <input type="hidden" name="status" value="Returned">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="return_reason_{{ $opportunity['opportunity_id'] }}" class="form-label">Select a Reason for Return</label>
                        <select class="form-select" id="return_reason_{{ $opportunity['opportunity_id'] }}" name="reason" required>
                            <option value="" selected disabled>-- Select Reason --</option>
                            <option value="Change date/time">Change date/time</option>
                            <option value="Overlapping event times / conflict with another activity">Overlapping event times / conflict with another activity</option>
                            <option value="Duplicate or conflicting task entries">Duplicate or conflicting task entries</option>
                            <option value="Incomplete location details (missing floor/room)">Incomplete location details (missing floor/room)</option>
                            <option value="Request needs rescheduling due to time sensitivity">Request needs rescheduling due to time sensitivity</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="return_comment_{{ $opportunity['opportunity_id'] }}" class="form-label">Additional Comments (optional)</label>
                        <textarea class="form-control" id="return_comment_{{ $opportunity['opportunity_id'] }}" name="comment" rows="4"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
<button
    type="button"
    class="btn btn-outline-secondary action-btn"
    data-title="Cancel Return"
    data-message="Are you sure you want to cancel returning this request?"
    data-dismiss-only="true"
    data-bs-toggle="modal"
    data-bs-target="#globalModal"
>
    Cancel
</button>
<button
    type="button"
    class="btn btn-success action-btn"
    data-title="Confirm Return"
    data-message="Are you sure you want to return this request?"
    data-form-id="returnForm{{ $opportunity['opportunity_id'] }}"
    data-confirm-class="btn-danger"
    data-bs-toggle="modal"
    data-bs-target="#globalModal"
>
    Submit Return
</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endforeach

</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $pagination->links('pagination::bootstrap-5') }}
</div>
@endif



</main>


<div class="modal fade" id="globalModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="globalModalTitle">
          Confirm Action
        </h5>
      </div>

      <div class="modal-body" id="globalModalBody">
        Are you sure?
      </div>

      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
          Cancel
        </button>
        <button type="button" class="btn btn-primary" id="modalConfirmBtn">
          Confirm
        </button>
      </div>

    </div>
  </div>
</div>
<script>
const cards = document.querySelectorAll('.status-card-container');

function setActive(tab) {
    document.querySelectorAll('.status-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
}

document.getElementById('filterAll').addEventListener('click', () => {
    setActive(filterAll);
    cards.forEach(card => card.style.display = "block");
});

document.getElementById('filterWaitingApproval').addEventListener('click', () => {
    setActive(filterWaitingApproval);
    cards.forEach(card => {
        const status = card.dataset.status;
        card.style.display = ['pending', 'waiting', 'waiting for approval', 'returned to sdao'].includes(status) ? "block" : "none";
    });
});

document.getElementById('filterWaitingVolunteers').addEventListener('click', () => {
    setActive(filterWaitingVolunteers);
    cards.forEach(card => {
        const status = card.dataset.status;
        const participants = parseInt(card.dataset.participants);
        const target = parseInt(card.dataset.target);
        card.style.display = (status === 'waiting for volunteers' || (status === 'approved' && participants < target)) ? "block" : "none";
    });
});

document.getElementById('filterReturned').addEventListener('click', () => {
    setActive(filterReturned);
    cards.forEach(card => {
        const status = card.dataset.status;
        card.style.display = ['returned', 'returned to sdao'].includes(status) ? "block" : "none";
    });
});


document.getElementById('filterUrgent').addEventListener('click', () => {
    setActive(filterUrgent);
    cards.forEach(card => {
        const isUrgent = card.querySelector('.badge.bg-danger')?.textContent.trim() === 'Urgent';
        card.style.display = isUrgent ? "block" : "none";
    });
});

let formToSubmit = null;

document.querySelectorAll('.approve-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        formToSubmit = document.getElementById(this.dataset.formId);
    });
});

document.getElementById('modalConfirmBtn').addEventListener('click', function () {
    if (formToSubmit) {
        formToSubmit.submit();
    }
});


let pendingForm = null;
let dismissOnly = false;

document.querySelectorAll('.action-btn').forEach(btn => {
    btn.addEventListener('click', function () {

        // Modal text
        document.getElementById('globalModalTitle').textContent = this.dataset.title;
        document.getElementById('globalModalBody').textContent = this.dataset.message;

        // Button styling
        const confirmBtn = document.getElementById('modalConfirmBtn');
        confirmBtn.className = 'btn ' + (this.dataset.confirmClass || 'btn-primary');

        // Action behavior
        dismissOnly = this.dataset.dismissOnly === "true";
        pendingForm = this.dataset.formId
            ? document.getElementById(this.dataset.formId)
            : null;
    });
});

document.getElementById('modalConfirmBtn').addEventListener('click', function () {

    if (dismissOnly) {
        bootstrap.Modal.getInstance(document.getElementById('globalModal')).hide();
        return;
    }

    if (pendingForm) {
        pendingForm.submit();
    }
});
</script>





@endsection
