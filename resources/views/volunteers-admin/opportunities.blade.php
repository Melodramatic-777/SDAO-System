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
    $filteredOpportunities = collect($opportunities)->sortByDesc(function($opportunity) {
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
       $status = strtolower($opportunity['status'] ?? 'waiting');
       $submittedByRole = strtolower($opportunity['submitted_by_role'] ?? '');

       if ($submittedByRole === 'sda assistant') {
           $status = 'waiting for volunteers';
           $badgeClass = 'badge bg-primary';
       } elseif ($status === 'approved') {
           $badgeClass = 'badge bg-success';
       } elseif (in_array($status, ['returned','returned to sdao'])) {
           $badgeClass = 'badge bg-danger';
           $status = 'Returned';
       } else {
           $badgeClass = 'badge bg-secondary';
       }

       $participantsCount = count($opportunity['participants'] ?? []);
       $targetNumber = (int) ($opportunity['schedule'][0]['target_number'] ?? 0);
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
            @if($status === 'waiting for volunteers')
                <span class="badge bg-primary">Waiting for Volunteers</span>
            @elseif($status === 'returned')
                <span class="badge bg-danger">Returned</span>
            @elseif($status === 'approved')
                <span class="badge bg-success">Approved</span>
            @endif
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
                <h5 class="modal-title">{{ $opportunity['title'] ?? 'Untitled Opportunity' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body custom-modal-body row g-4">

                {{-- Left Panel --}}
               {{-- Left Panel --}}
<div class="col-md-6 left-panel">
    <p><strong>Office:</strong> {{ $opportunity['submitted_by_role'] ?? 'N/A' }}</p>
    <hr>
    <p><strong>Submitted By:</strong> {{ $opportunity['submitted_by_name'] ?? 'N/A' }}</p>
    <hr>
    <p><strong>Location:</strong> {{ $opportunity['location'] ?? 'N/A' }}</p>
    <hr>
    <h6><strong>Description</strong></h6>
    <div>{{ $opportunity['description'] ?? 'No description provided.' }}</div>
    <hr>

    {{-- Intermittent Table --}}
    @php
        $isIntermittent = !empty($opportunity['is_intermittent']) && (int)$opportunity['is_intermittent'] === 1;
    @endphp

    @if($isIntermittent)
        <h6 class="mb-3 fw-bold">Intermittent Schedule</h6>
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
                            <td class="text-center">
                                {{ !empty($s['date']) ? \Carbon\Carbon::parse($s['date'])->format('F j, Y') : 'N/A' }}
                            </td>
                            <td class="text-center">
                                @if(!empty($s['start_time']) && !empty($s['end_time']))
                                    {{ \Carbon\Carbon::parse($s['start_time'])->format('g:i A') }}
                                    – {{ \Carbon\Carbon::parse($s['end_time'])->format('g:i A') }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="text-center">
                                {{ strtolower($s['food_provided'] ?? 'no') === 'yes' ? 'Yes' : 'No' }}
                            </td>
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

</div>


            </div>

            {{-- Approve / Return Buttons --}}
{{-- Approve / Return Buttons --}}
<div class="modal-footer d-flex justify-content-start">
    @if(in_array(strtolower($opportunity['status'] ?? ''), ['pending', 'waiting for approval', 'returned to sdao']))
        <form method="POST" action="{{ route('volunteers-admin.opportunity.update-status', $opportunity['opportunity_id']) }}" class="d-inline me-2">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="Approved">
            <button type="submit" class="btn btn-approve">Approve</button>
        </form>

        <button type="button" class="btn btn-return" data-bs-toggle="modal" data-bs-target="#returnModal{{ $opportunity['opportunity_id'] }}">
            Return
        </button>
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

@endforeach

</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $pagination->links('pagination::bootstrap-5') }}
</div>
@endif

</main>

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
