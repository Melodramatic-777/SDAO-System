@extends('volunteers-offices.layout')

@section('content')

<main class="page-container">

    <section class="status-section">

        @php
            $allCount = $allOpportunities->count();

            $waitingApprovalCount = $allOpportunities->filter(function ($op) {
                return in_array(strtolower($op['status'] ?? ''), [
                    'pending', 'waiting', 'waiting for approval', 'returned to sdao'
                ]);
            })->count();

            $waitingVolunteersCount = $allOpportunities->filter(function ($op) {
                $status = strtolower($op['status'] ?? '');
                $submittedByRole = strtolower($op['submitted_by_role'] ?? '');
                $participantsCount = count($op['participants'] ?? []);
                $targetNumber = (int) ($op['schedule'][0]['target_number'] ?? 0);
                return $submittedByRole === 'sda assistant' || ($status === 'approved' && $participantsCount < $targetNumber);
            })->count();

            $returnedCount = $allOpportunities->filter(function ($op) {
                return in_array(strtolower($op['status'] ?? ''), ['returned','returned to sdao']);
            })->count();

            $filteredOpportunities = $allOpportunities->sortByDesc(function($opportunity) {
                $status = strtolower($opportunity['status'] ?? '');
                $participants = count($opportunity['participants'] ?? []);
                $target = (int) ($opportunity['schedule'][0]['target_number'] ?? 0);
                $isUrgent = !empty($opportunity['is_urgent']) && ($opportunity['is_urgent'] === true || $opportunity['is_urgent'] === 'yes');

                if (in_array($status, ['pending','waiting','waiting for approval'])) return 4;
                if ($isUrgent) return 3;
                if ($status === 'approved' && $participants < $target) return 2;
                if (in_array($status, ['returned','returned to sdao'])) return 1;
                return 0;
            });
        @endphp

        {{-- Status Filters --}}
        <div class="gap-4 mb-4 status-filter d-flex">
            <span class="status-tab active" id="filterAll">
                All <span class="badge bg-secondary ms-1">{{ $allCount }}</span>
            </span>

            <span class="status-tab" id="filterUrgent">
                Urgent <span class="badge bg-danger ms-1">
                    {{ $allOpportunities->filter(fn($op) => !empty($op['is_urgent']) && ($op['is_urgent'] === true || $op['is_urgent'] === 'yes'))->count() }}
                </span>
            </span>

            <span class="status-tab" id="filterWaitingApproval">
                Waiting for Approval <span class="badge bg-warning text-dark ms-1">{{ $waitingApprovalCount }}</span>
            </span>

            <span class="status-tab" id="filterWaitingVolunteers">
                Waiting for Volunteers <span class="badge bg-primary ms-1">{{ $waitingVolunteersCount }}</span>
            </span>

            <span class="status-tab" id="filterReturned">
                Returned <span class="badge ms-1" style="background-color: #fd7e14; color: white;">{{ $returnedCount }}</span>
            </span>
        </div>

        @if($filteredOpportunities->isEmpty())
            <p>No opportunities.</p>
        @else
            <div id="opportunitiesContainer">
                @foreach($filteredOpportunities as $opportunity)
                    @php
                        $status = strtolower($opportunity['status'] ?? 'waiting');
                        $submittedByRole = strtolower($opportunity['submitted_by_role'] ?? '');
                        $participantsCount = count($opportunity['participants'] ?? []);
                        $targetNumber = (int) ($opportunity['schedule'][0]['target_number'] ?? 0);

                        if ($status === 'approved' && $participantsCount < $targetNumber) {
                            $displayStatus = 'waiting for volunteers';
                            $badgeClass = 'badge bg-primary text-white';
                        } elseif ($submittedByRole === 'sda assistant') {
                            $displayStatus = 'waiting for volunteers';
                            $badgeClass = 'badge bg-primary text-white';
                        } elseif (in_array($status, ['returned','returned to sdao'])) {
                            $displayStatus = 'returned';
                            $badgeClass = 'badge';
                            $badgeStyle = 'background-color: #fd7e14; color: white;';
                        } elseif (in_array($status, ['pending','waiting','waiting for approval'])) {
                            $displayStatus = 'waiting for approval';
                            $badgeClass = 'badge bg-warning text-dark';
                        } else {
                            $displayStatus = $status;
                            $badgeClass = 'badge bg-secondary';
                        }

                        $isUrgent = !empty($opportunity['is_urgent']) && ($opportunity['is_urgent'] === true || $opportunity['is_urgent'] === 'yes');
                    @endphp

                    {{-- Status Card --}}
                    <div class="p-3 mb-3 bg-white border rounded status-card-container"
                        data-status="{{ $status }}"
                        data-participants="{{ $participantsCount }}"
                        data-target="{{ $targetNumber }}">

                        @if($isUrgent)
                            <div class="mb-1"><span class="badge bg-danger">Urgent</span></div>
                        @endif

                        <div class="mb-1 d-flex justify-content-between align-items-center">
                            <strong>{{ $opportunity['title'] ?? 'No Title' }}</strong>
                            <span class="{{ $badgeClass }}" @isset($badgeStyle) style="{{ $badgeStyle }}" @endisset>
                                {{ ucfirst($displayStatus) }}
                            </span>
                        </div>

                   <div>
    <p class="mb-1">{{ $opportunity['location'] ?? 'No Location' }}</p>
    <small>Submitted by: {{ $opportunity['submitted_by_name'] ?? 'N/A' }} ({{ $opportunity['submitted_by_role'] ?? 'N/A' }})</small><br>
    <small>Date Submitted: {{ isset($opportunity['submitted_at']) ? \Carbon\Carbon::parse($opportunity['submitted_at'])->format('F j, Y') : 'N/A' }}</small>
    
    <button class="view-details-btn float-end" data-bs-toggle="modal" data-bs-target="#opportunityModal{{ $opportunity['opportunity_id'] }}">
        View Details
    </button>


</div>

                    </div>

{{-- View Details Modal --}}
<div class="modal fade"
     id="opportunityModal{{ $opportunity['opportunity_id'] }}"
     tabindex="-1"
     aria-hidden="true"
     data-bs-backdrop="false">

<div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content custom-modal-content">

            {{-- HEADER --}}
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body row g-4">

                {{-- LEFT PANEL --}}
                <div class="col-md-6 left-panel">
                    <div class="left-panel-scroll">

                        {{-- Status + Submitted --}}
                        <div class="gap-2 mb-3 d-flex align-items-center">
                            <span class="status-pill {{ $badgeClass }}"
                                  @isset($badgeStyle) style="{{ $badgeStyle }}" @endisset>
                                {{ ucfirst($displayStatus) }}
                            </span>

                            <span class="text-muted small">
                                Submitted
                                {{ isset($opportunity['submitted_at'])
                                    ? \Carbon\Carbon::parse($opportunity['submitted_at'])->format('M d, Y, g:i A')
                                    : 'N/A'
                                }}
                            </span>
                        </div>

                        {{-- Title --}}
                        <div class="mb-1 volunteer-heading">ACTIVITY TITLE</div>
                        <p class="opportunity-title">
                            {{ $opportunity['title'] ?? 'Untitled Opportunity' }}
                        </p>

                        {{-- Office + Submitted By --}}
                        <div class="mt-2 row g-3">
                            <div class="col-md-6">
                                <div class="info-tile">
                                    <div class="info-label">    <i class="bi bi-building me-1"></i> Office
</div>
                                    <div class="info-value">
                                        {{ $opportunity['submitted_by_role'] ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-tile">
                                    <div class="info-label"> <i class="bi bi-person-circle me-1"></i> Submitted by</div>
                                    <div class="info-value">
                                        {{ $opportunity['submitted_by_name'] ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Location --}}
                        <div class="mt-3 info-tile">
                            <div class="info-label">    <i class="bi bi-geo-alt me-1"></i> Location
</div>
                            <div class="info-value">
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

                        {{-- Schedule --}}
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

<br>

  


                    </div>
                </div>

                {{-- RIGHT PANEL --}}
                <div class="col-md-6 right-panel">

                    {{-- Returned Details --}}
             

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

   <div class="mb-3 date-card task-toggle-card">

    {{-- Clickable Header --}}
{{-- Clickable Header --}}
<div class="date-card-header d-flex justify-content-between align-items-center">

    {{-- Left: Date + Meta --}}
    <div>
        <strong>{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</strong>

        <div class="mt-1 date-meta">
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
    </div>

    {{-- Right: Volunteers + Chevron --}}
    <div class="gap-2 d-flex align-items-center">
        <span class="date-volunteers">
            {{ $dateTotal }} volunteers
        </span>
        <i class="bi bi-chevron-down toggle-icon"></i>
    </div>

</div>

    {{-- Tasks (Hidden by default) --}}
    <div class="task-content d-none">
        <hr>

        <ul class="task-list">
            @foreach($tasksForDate as $task)
                <li class="task-item">
                    <span>• {{ $task['task'] ?? 'Unnamed Task' }}</span>
                    <span class="needed">{{ $task['needed'] ?? 0 }} needed</span>
                </li>
            @endforeach
        </ul>
    </div>

</div>


@endforeach
</div>


@else
    <p class="text-muted">No tasks found.</p>
@endif



                </div>
            </div>

            {{-- FOOTER --}}
            <div class="gap-2 modal-footer d-flex justify-content-end">

                {{-- RETURNED FOOTER ALERT --}}
{{-- RETURNED FOOTER ALERT --}}
@if(in_array(strtolower($opportunity['status'] ?? ''), ['returned', 'returned to sdao']))

<div class="mb-2 returned-footer-alert w-100">

    <div class="gap-3 d-flex align-items-start">

        <i class="mt-1 bi bi-x-circle-fill text-danger fs-4"></i>

        <div class="flex-grow-1">

            <div class="fw-bold text-danger">
                Request Not Approved
            </div>

            @if(!empty($opportunity['return_reason']))
                <div class="mt-1 small">
                    <strong>Reason:</strong> {{ $opportunity['return_reason'] }}
                </div>
            @endif

         @if(!empty($opportunity['return_comment']))
    <div class="returned-comment small">
        <div class="comment-box">
            <span class="comment-label">Comment:</span>
            <span class="comment-text">
                {{ trim($opportunity['return_comment']) }}
            </span>
        </div>
    </div>
@endif


        </div>
    </div>

    {{-- ✅ RIGHT-ALIGNED BUTTON --}}
    <div class="mt-3 d-flex justify-content-end">
        <a href="{{ route('volunteers-offices.edit-opportunity', $opportunity['opportunity_id']) }}"
           class="edit-returned-btn">
            <i class="bi bi-pencil-square me-1"></i> Edit & Resubmit
        </a>
    </div>

</div>

@endif



                {{-- Edit (Returned Only) --}}
        

                {{-- Cancel Opportunity --}}
{{-- Cancel Opportunity --}}
@if(!in_array(strtolower($opportunity['status'] ?? ''), ['returned', 'cancelled', 'completed']))

<div class="approval-alert-footer d-flex align-items-center justify-content-between w-100">

    {{-- LEFT: Awaiting Approval --}}
    <div class="gap-2 approval-header d-flex align-items-center">
        <i class="bi bi-clock-history"></i>
        <div>
            <strong>Awaiting Approval</strong>
            <div class="approval-subtext">
                Your request is being reviewed by the SDA Assistant.
            </div>
        </div>
    </div>

    {{-- RIGHT: Cancel Button --}}
    <form method="POST"
          action="{{ route('volunteers-admin.opportunities.cancel', $opportunity['opportunity_id']) }}"
          onsubmit="return confirm('Are you sure you want to cancel this opportunity?')"
          class="ms-3">
        @csrf

        <button type="submit" class="cancel-opportunity-btn wide-btn">
            <i class="bi bi-x-lg me-1"></i> Cancel Opportunity
        </button>
    </form>

</div>

@endif


            </div>

        </div>
    </div>

                    </div>

                    
                @endforeach

<div class="mt-4 d-flex justify-content-center">
    {{ $opportunities->links('pagination::bootstrap-5') }}
</div>
        @endif
    </section>


</main>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


  <script>
document.addEventListener('DOMContentLoaded', function () {

    /* ==============================
       STATUS FILTERING
    ============================== */

    const cards = document.querySelectorAll('.status-card-container');
    const tabs  = document.querySelectorAll('.status-tab');

    function setActive(tab) {
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
    }

    document.getElementById('filterAll')?.addEventListener('click', function () {
        setActive(this);
        cards.forEach(card => card.style.display = 'block');
    });

    document.getElementById('filterWaitingApproval')?.addEventListener('click', function () {
        setActive(this);
        cards.forEach(card => {
            const status = card.dataset.status;
            card.style.display =
                ['pending', 'waiting', 'waiting for approval', 'returned to sdao']
                    .includes(status) ? 'block' : 'none';
        });
    });

    document.getElementById('filterWaitingVolunteers')?.addEventListener('click', function () {
        setActive(this);
        cards.forEach(card => {
            const status = card.dataset.status;
            const participants = parseInt(card.dataset.participants || 0);
            const target = parseInt(card.dataset.target || 0);

            card.style.display =
                (status === 'approved' && participants < target) ||
                status === 'waiting for volunteers'
                    ? 'block' : 'none';
        });
    });

    document.getElementById('filterReturned')?.addEventListener('click', function () {
        setActive(this);
        cards.forEach(card => {
            const status = card.dataset.status;
            card.style.display =
                ['returned', 'returned to sdao'].includes(status)
                    ? 'block' : 'none';
        });
    });

    document.getElementById('filterUrgent')?.addEventListener('click', function () {
        setActive(this);
        cards.forEach(card => {
            const urgentBadge = card.querySelector('.badge.bg-danger');
            card.style.display = urgentBadge ? 'block' : 'none';
        });
    });

    // Default view
    document.getElementById('filterAll')?.click();


    /* ==============================
       TASK TOGGLE (MODAL SAFE)
    ============================== */

    document.addEventListener('click', function (e) {

        const header = e.target.closest('.date-card-header');
        if (!header) return;

        const card = header.closest('.task-toggle-card');
        if (!card) return;

        const content = card.querySelector('.task-content');
        const icon = card.querySelector('.toggle-icon');

        if (!content || !icon) return;

        content.classList.toggle('d-none');
        icon.classList.toggle('rotate');
    });

});
</script>



@endsection
