@extends('volunteers-admin.layout')

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
        <p>Welcome, {{ session('volunteers_admin.first_name') }}!</p>
    </div>
</header>


<main class="page-container">

<div class="status-section">

@php
    // Calculate counts
    $allCount = collect($opportunities)->count();
    $waitingApprovalCount = collect($opportunities)->filter(fn($op) => in_array(strtolower($op['status'] ?? ''), ['pending','waiting','waiting for approval','returned to sdao']))->count();
    $waitingVolunteersCount = collect($opportunities)->filter(fn($op) => strtolower($op['status'] ?? '') === 'approved' && count($op['participants'] ?? []) < (int)($op['schedule'][0]['target_number'] ?? 0))->count();
    $returnedCount = collect($opportunities)->filter(fn($op) => in_array(strtolower($op['status'] ?? ''), ['returned','returned to sdao']))->count();

    // Filter opportunities (remove duplicates)
    $filteredOpportunities = collect($opportunities)->filter(function($opportunity) {
        $status = strtolower($opportunity['status'] ?? '');
        $participantsCount = count($opportunity['participants'] ?? []);
        $targetNumber = (int) ($opportunity['schedule'][0]['target_number'] ?? 0);

        return in_array($status, ['returned','returned to sdao','pending','waiting','waiting for approval'])
            || ($status === 'approved' && $participantsCount < $targetNumber);
    });

    // Sort urgent + waiting first
    $filteredOpportunities = $filteredOpportunities->sortByDesc(function($opportunity) {
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
    <span class="status-tab" id="filterWaitingApproval">
        Waiting for Approval <span class="badge bg-warning text-dark ms-1" id="countWaitingApproval">{{ $waitingApprovalCount }}</span>
    </span>
    <span class="status-tab" id="filterWaitingVolunteers">
        Waiting for Volunteers <span class="badge bg-primary ms-1" id="countWaitingVolunteers">{{ $waitingVolunteersCount }}</span>
    </span>
    <span class="status-tab" id="filterReturned">
        Returned <span class="badge bg-danger ms-1" id="countReturned">{{ $returnedCount }}</span>
    </span>
</div>

@if($filteredOpportunities->isEmpty())
    <p>No opportunities waiting for approval or volunteers.</p>
@else
<div id="opportunitiesContainer">

@foreach($filteredOpportunities as $opportunity)
    @php
        $status = strtolower($opportunity['status'] ?? 'waiting');
        switch($status) {
            case 'approved': $badgeClass = 'badge bg-success'; break;
            case 'returned':
            case 'returned to sdao': $badgeClass = 'badge bg-danger'; $status = 'Returned'; break;
            default: $badgeClass = 'badge bg-warning text-dark'; $status = 'Waiting for Approval';
        }
        $participantsCount = count($opportunity['participants'] ?? []);
        $targetNumber = (int) ($opportunity['schedule'][0]['target_number'] ?? 0);
    @endphp

    <div class="p-3 mb-3 border rounded status-card-container"
         data-status="{{ strtolower($opportunity['status'] ?? '') }}"
         data-participants="{{ $participantsCount }}"
         data-target="{{ $targetNumber }}">
        <div class="d-flex justify-content-between align-items-center">
            <strong>{{ $opportunity['title'] ?? 'No Title' }}</strong>
            <div>
                @if($status === 'approved' && $participantsCount < $targetNumber && strtolower($opportunity['submitted_by_role'] ?? '') === 'sda assistant')
                    <span class="badge bg-primary">Waiting for Volunteers</span>
                @elseif(strtolower($opportunity['status'] ?? '') === 'returned to sdao')
                    <span class="badge bg-danger">Returned</span>
                    <span class="badge bg-warning text-dark">Waiting for Approval</span>
                @else
                    <span class="{{ $badgeClass }}">{{ ucfirst($status) }}</span>
                @endif

                @if(!empty($opportunity['is_urgent']) && ($opportunity['is_urgent'] === true || $opportunity['is_urgent'] === 'yes'))
                    <span class="badge bg-danger ms-2">Urgent</span>
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

    {{-- ✅ Modal --}}
    <div class="modal fade" id="opportunityModal{{ $opportunity['opportunity_id'] }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content custom-modal-content">

                {{-- Header --}}
                <div class="modal-header">
                    <h5 class="modal-title">{{ $opportunity['title'] ?? 'Untitled Opportunity' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                {{-- Body --}}
                <div class="modal-body custom-modal-body row g-4">
                    {{-- LEFT PANEL --}}
                    <div class="col-md-6 left-panel">
                        <p><strong>Office:</strong> {{ $opportunity['submitted_by_role'] ?? 'N/A' }}</p>
                        <p><strong>Submitted By:</strong> {{ $opportunity['submitted_by_name'] ?? 'N/A' }}</p>
                        <p><strong>Location:</strong> {{ $opportunity['location'] ?? 'N/A' }}</p>

                        @php
                            $isIntermittent = !empty($opportunity['is_intermittent']) && (int)$opportunity['is_intermittent'] === 1;
                        @endphp

                        @if($isIntermittent)
                            <div class="table-responsive">
                                <table class="table align-middle table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Volunteers Needed</th>
                                            <th>Food Provided</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($opportunity['schedule'] ?? [] as $s)
                                            <tr>
                                                <td>{{ !empty($s['date']) ? \Carbon\Carbon::parse($s['date'])->format('F j, Y') : 'N/A' }}</td>
                                                <td>
                                                    @if(!empty($s['start_time']) && !empty($s['end_time']))
                                                        {{ \Carbon\Carbon::parse($s['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($s['end_time'])->format('g:i A') }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>{{ $s['target_number'] ?? 'N/A' }}</td>
                                                <td>{{ strtolower($s['food_provided'] ?? 'no') === 'yes' ? 'Yes' : 'No' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted">No schedule details provided</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @else
                            @php
                                $firstSchedule = $opportunity['schedule'][0] ?? [];
                                $date = $firstSchedule['date'] ?? $firstSchedule['start_date'] ?? $opportunity['start_date'] ?? null;
                                $start = $firstSchedule['start_time'] ?? ($opportunity['start_time'] ?? null);
                                $end = $firstSchedule['end_time'] ?? ($opportunity['end_time'] ?? null);
                                $target = $firstSchedule['target_number'] ?? ($opportunity['target_number'] ?? 'N/A');
                            @endphp
                            <p><strong>Date:</strong> {{ !empty($date) ? \Carbon\Carbon::parse($date)->format('F j, Y') : 'N/A' }}</p>
                            <p><strong>Time:</strong>
                                @if(!empty($start) && !empty($end))
                                    {{ \Carbon\Carbon::parse($start)->format('g:i A') }} – {{ \Carbon\Carbon::parse($end)->format('g:i A') }}
                                @else
                                    N/A
                                @endif
                            </p>
                        @endif

                        @if(!empty($opportunity['return_comment']) && strtolower($opportunity['status']) === 'returned')
                            <div class="mt-2 alert alert-danger">
                                <strong>Return Comment:</strong><br>{{ $opportunity['return_comment'] }}
                            </div>
                        @endif
                    </div>

                    {{-- RIGHT PANEL --}}
                    <div class="col-md-6 right-panel">
                        @if(!empty($opportunity['participants']) || !empty($opportunity['schedule']))
                            @php
                                $participantsCount = collect($opportunity['participants'] ?? [])
                                    ->filter(fn($p) => strtolower($p['status'] ?? '') !== 'declined')
                                    ->count();
                                $schedule = collect($opportunity['schedule'] ?? []);
                                $targetNumber = $schedule->isNotEmpty() && isset($schedule[0]['target_number']) ? $schedule->sum('target_number') : ($opportunity['target_number'] ?? 0);
                            @endphp

                            <ul class="volunteers-list">
                                @foreach($opportunity['participants'] ?? [] as $participant)
                                    @php
                                        $displayStatus = strtolower($participant['status'] ?? '') === 'declined' ? 'Invite Declined' : ($participant['status'] ?? 'Pending');
                                    @endphp
                                    <li>{{ $participant['name'] ?? 'Unnamed' }} - <span class="text-muted">{{ $displayStatus }}</span></li>
                                @endforeach
                            </ul>
                        @endif

                        <h6><strong>Description</strong></h6>
                        <div>{{ $opportunity['description'] ?? 'No description provided.' }}</div>

                        <hr>

                        <h6 class="task-title"><strong>Tasks</strong></h6>
                        @if(!empty($opportunity['tasks']) && is_array($opportunity['tasks']))
                            <ul class="task-list" style="list-style-type: disc; padding-left: 20px;">
                                @foreach($opportunity['tasks'] as $taskItem)
                                    <li class="mb-2">
                                        <strong>{{ $taskItem['task'] ?? 'Unnamed Task' }}</strong><br>
                                        <span><em>{{ $taskItem['description'] ?? 'No description provided.' }}</em></span><br>
                                        <span>Skills: {{ $taskItem['skills'] ?? 'Not specified' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <ul><li>No tasks provided.</li></ul>
                        @endif

                        <hr>

                        @php
                            $user = session('volunteers_admin') ?? [];
                            $userEmail = $user['email'] ?? null;
                            $userRole  = $user['role'] ?? null;
                        @endphp

                        @if($userRole === 'SDA Assistant' && ($opportunity['submitted_by'] ?? '') === $userEmail)
                            <form method="POST" action="{{ route('volunteers-admin.opportunities.cancel', $opportunity['opportunity_id']) }}">
                                @csrf
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this opportunity? This will remove invitations and move the record to canceled opportunities.')">Cancel Opportunity</button>
                            </form>
                        @else
                            <div class="p-0 border-0 modal-footer">
                                @if(in_array(strtolower($opportunity['status'] ?? ''), ['pending', 'waiting for approval', 'returned to sdao']))
                                    <form method="POST" action="{{ route('volunteers-admin.opportunity.update-status', $opportunity['opportunity_id']) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="Approved">
                                        <button type="submit" class="btn btn-success">Approve</button>
                                    </form>

                                    <button type="button" class="btn btn-danger ms-2" data-bs-toggle="collapse" data-bs-target="#returnReason{{ $opportunity['opportunity_id'] }}">Return</button>
                                @elseif(strtolower($opportunity['status'] ?? '') === 'approved')
                                    <span class="text-success fw-bold">This opportunity is already approved.</span>
                                @endif
                            </div>

                            <div class="collapse" id="returnReason{{ $opportunity['opportunity_id'] }}">
                                <form method="POST" action="{{ route('volunteers-admin.opportunity.update-status', $opportunity['opportunity_id']) }}" class="mt-3">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="Returned">
                                    <div class="mb-2">
                                        <label class="form-label">Reason for Return</label>
                                        <textarea name="comment" class="form-control" rows="2" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger">Submit</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- ✅ End Modal --}}
@endforeach

</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $pagination->links('pagination::bootstrap-5') }}
</div>
@endif

</main>


<script>
  //opportunities
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


</script>

@endsection
