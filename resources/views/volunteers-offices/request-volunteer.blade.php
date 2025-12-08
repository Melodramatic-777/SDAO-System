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
            <p>No opportunities found.</p>
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

  <div class="modal fade" id="opportunityModal{{ $opportunity['opportunity_id'] }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
                        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title">{{ $opportunity['title'] ?? 'Untitled Opportunity' }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body row g-4">
                                    {{-- LEFT PANEL --}}
                                    <div class="col-md-6 left-panel">
                                        <p><strong>Office:</strong> {{ $opportunity['submitted_by_role'] ?? 'N/A' }}</p>
                                        <p><strong>Submitted By:</strong> {{ $opportunity['submitted_by_name'] ?? 'N/A' }}</p>
<p><strong>Location:</strong> {{ $opportunity['location'] ?? 'N/A' }}</p>

{{-- Display Date and Time separately only if NOT intermittent --}}
@php
    $isIntermittent = !empty($opportunity['is_intermittent']) && (int)$opportunity['is_intermittent'] === 1;
@endphp

@if(!$isIntermittent && !empty($opportunity['schedule']))
    @foreach($opportunity['schedule'] as $s)
        @if(!empty($s['date']))
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($s['date'])->format('F j, Y') }}</p>
        @endif
        @if(!empty($s['start_time']) && !empty($s['end_time']))
            <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($s['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($s['end_time'])->format('g:i A') }}</p>
        @endif
        <hr>
    @endforeach
@endif



<h6><strong>Description</strong></h6>
<div>{{ $opportunity['description'] ?? 'No description provided.' }}</div>
<hr>


                                        {{-- Intermittent Schedule --}}
                                        @php
                                            $isIntermittent = !empty($opportunity['is_intermittent']) && (int)$opportunity['is_intermittent'] === 1;
                                        @endphp

                                        @if($isIntermittent)
                                            <h6 class="mb-3 fw-bold">Schedule</h6>
                                            <div class="table-responsive">
                                                <table class="table align-middle shadow-sm table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th class="text-center">Date</th>
                                                            <th class="text-center">Time</th>
                                                            <th class="text-center">Food Provided</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($opportunity['schedule'] ?? [] as $s)
                                                            <tr>
                                                                <td class="text-center">{{ !empty($s['date']) ? \Carbon\Carbon::parse($s['date'])->format('F j, Y') : 'N/A' }}</td>
                                                                <td class="text-center">
                                                                    @if(!empty($s['start_time']) && !empty($s['end_time']))
                                                                        {{ \Carbon\Carbon::parse($s['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($s['end_time'])->format('g:i A') }}
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">{{ strtolower($s['food_provided'] ?? 'no') === 'yes' ? 'Yes' : 'No' }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3" class="text-center text-muted">No schedule details provided</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
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

                                    {{-- RIGHT PANEL --}}
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
        <p class="fw-bold text-primary">Total Volunteers Needed: {{ $totalVolunteersNeededOverall }}</p>
    @endif

    @if(isset($opportunity['intermittent_tasks_by_date']) && is_array($opportunity['intermittent_tasks_by_date']))
        @foreach($opportunity['intermittent_tasks_by_date'] as $date => $tasksForDate)
            @if($isIntermittent)
                {{-- Intermittent: collapsible --}}
                @php
                    $collapseId = 'tasksCollapse_' . md5($opportunity['opportunity_id'] . $date);
                    $totalVolunteersNeeded = collect($tasksForDate)->sum(fn($task) => isset($task['needed']) ? (int)$task['needed'] : 0);
                @endphp
                <div class="mb-3">
                    <button class="btn w-100 text-start" type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#{{ $collapseId }}" 
                            aria-expanded="false" 
                            aria-controls="{{ $collapseId }}"
                            style="background-color: #cce7ff; color: #000; border: 1px solid #99d1ff;">
                        {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                        @if($totalVolunteersNeeded > 0)
                            - {{ $totalVolunteersNeeded }} Volunteers Needed
                        @endif
                    </button>

                    <div class="mt-2 collapse" id="{{ $collapseId }}">
                        <div class="p-3 border rounded bg-light">
                            @foreach($tasksForDate as $taskItem)
                                <div class="pb-2 mb-3 border-bottom">
                                    <p class="mb-1"><strong>{{ $taskItem['task'] ?? 'Unnamed Task' }}</strong></p>
                                    @if(!empty($taskItem['description']))
                                        <p class="mb-1 text-muted" style="margin-left: 12px;">
                                            {{ $taskItem['description'] }}
                                        </p>
                                    @endif
                                    @if(!empty($taskItem['skills']))
                                        <p class="mb-1" style="margin-left: 12px;">
                                            <strong class="text-primary">Skills:</strong> {{ $taskItem['skills'] }}
                                        </p>
                                    @endif
                                    @if(!empty($taskItem['needed']))
                                        <p class="mb-0" style="margin-left: 12px;">
                                            <strong class="text-dark">Volunteers Needed:</strong> {{ $taskItem['needed'] }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                {{-- Regular: show all tasks expanded, no collapse, no volunteers needed beside date --}}
                <div class="mb-3">
                    <h6 class="mb-2">{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</h6>
                    <div class="p-3 border rounded bg-light">
                        @foreach($tasksForDate as $taskItem)
                            <div class="pb-2 mb-3 border-bottom">
                                <p class="mb-1"><strong>{{ $taskItem['task'] ?? 'Unnamed Task' }}</strong></p>
                                @if(!empty($taskItem['description']))
                                    <p class="mb-1 text-muted" style="margin-left: 12px;">
                                        {{ $taskItem['description'] }}
                                    </p>
                                @endif
                                @if(!empty($taskItem['skills']))
                                    <p class="mb-1" style="margin-left: 12px;">
                                        <strong class="text-primary">Skills:</strong> {{ $taskItem['skills'] }}
                                    </p>
                                @endif
                                @if(!empty($taskItem['needed']))
                                    <p class="mb-0" style="margin-left: 12px;">
                                        <strong class="text-dark">Volunteers Needed:</strong> {{ $taskItem['needed'] }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    @else
        <p class="text-muted">No tasks found.</p>
    @endif




                                        {{-- Cancel Opportunity --}}
                                        @php $statusLower = strtolower($opportunity['status'] ?? ''); @endphp
                                        @if(!in_array($statusLower, ['returned', 'cancelled', 'completed']))
                                            <form method="POST" action="{{ route('volunteers-admin.opportunities.cancel', $opportunity['opportunity_id']) }}" class="mt-3">
                                                @csrf
                                                <button type="submit" class="btn btn-return ms-2"
                                                    onclick="return confirm('Are you sure you want to cancel this opportunity? This will remove invitations and move the record to canceled opportunities.')">
                                                    Cancel Opportunity
                                                </button>
                                            </form>

                                            
                                        @endif

                                            {{-- Inside the modal-footer --}}

                                    </div>
    <div class="modal-footer d-flex flex-column align-items-end">
    {{-- Return Comment Section --}}
@if(in_array(strtolower($opportunity['status'] ?? ''), ['returned', 'returned to sdao']))
    <div class="mb-3 shadow-sm card w-100 border-danger">
        <div class="p-3 card-body">
            <h6 class="mb-3 card-title text-danger">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Returned Details
            </h6>

            @if(!empty($opportunity['returned_at']))
                <p class="mb-1 text-muted small">
                    <strong>Returned At:</strong> {{ \Carbon\Carbon::parse($opportunity['returned_at'])->format('F j, Y, g:i A') }}
                </p>
            @endif

            @if(!empty($opportunity['return_reason']))
                <p class="mb-2">
                    <strong>Reason:</strong> {{ $opportunity['return_reason'] }}
                </p>
            @endif

@if(!empty($opportunity['return_comment']))
    <div class="p-3 mb-0 border rounded bg-light">
        <div class="d-flex flex-column">
            <span class="mb-1 fw-bold">Comment:</span>
            <span style="white-space: pre-wrap;">{{ $opportunity['return_comment'] }}</span>
        </div>
    </div>
@endif

        </div>
    </div>
@endif

    {{-- Edit Button --}}
    @if(in_array(strtolower($opportunity['status'] ?? ''), ['returned', 'returned to sdao']))
        <a href="{{ route('volunteers-offices.edit-opportunity', $opportunity['opportunity_id']) }}" 
           class="btn btn-warning me-2">
            Edit
        </a>
    @endif

    {{-- Cancel Opportunity --}}
    @php $statusLower = strtolower($opportunity['status'] ?? ''); @endphp
    @if(!in_array($statusLower, ['returned', 'cancelled', 'completed']))
        <form method="POST" action="{{ route('volunteers-admin.opportunities.cancel', $opportunity['opportunity_id']) }}">
            @csrf
            <button type="submit" class="btn btn-return"
                onclick="return confirm('Are you sure you want to cancel this opportunity? This will remove invitations and move the record to canceled opportunities.')">
                Cancel Opportunity
            </button>
        </form>
    @endif
</div>

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
        card.style.display =
            ['pending', 'waiting', 'waiting for approval', 'returned to sdao'].includes(status)
            ? "block" : "none";
    });
});

document.getElementById('filterWaitingVolunteers').addEventListener('click', () => {
    setActive(filterWaitingVolunteers);
    cards.forEach(card => {
        const status = card.dataset.status;
        const participants = parseInt(card.dataset.participants);
        const target = parseInt(card.dataset.target);
        card.style.display = (status === 'approved' && participants < target)
            ? "block" : "none";
    });
});

document.getElementById('filterReturned').addEventListener('click', () => {
    setActive(filterReturned);
    cards.forEach(card => {
        const status = card.dataset.status;
        card.style.display = ['returned', 'returned to sdao'].includes(status)
            ? "block" : "none";
    });
});

// Default view: All
document.getElementById('filterAll').click();
  </script>

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
</script>



@endsection
