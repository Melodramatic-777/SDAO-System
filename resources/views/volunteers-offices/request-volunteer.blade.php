@extends('volunteers-offices.layout')

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
        <p>Welcome, {{ session('volunteers_office.first_name') }}!</p>
    </div>
</header>

<main class="page-container">

    <!-- Status List -->
    <section class="status-section">

@php
    $allCount = $allOpportunities->count();

    $waitingApprovalCount = $allOpportunities->filter(function ($op) {
        return in_array(strtolower($op['status'] ?? ''), [
            'pending', 'waiting', 'waiting for approval', 'returned to sdao'
        ]);
    })->count();

$waitingVolunteersCount = $allOpportunities->filter(function ($op) {
    $participants = is_array($op['participants'] ?? null) ? count($op['participants']) : 0;
    $target = (int) ($op['schedule'][0]['target_number'] ?? 0);
    return strtolower($op['status'] ?? '') === 'approved' && $participants < $target;
})->count();

    $returnedCount = $allOpportunities->filter(function ($op) {
        return in_array(strtolower($op['status'] ?? ''), ['returned', 'returned to sdao']);
    })->count();
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



        @if(empty($opportunities))
            <p>No submitted activities yet</p>
        @else
            @foreach($opportunities as $opportunity)
                @php
                    $status = strtolower($opportunity['status'] ?? 'waiting');
                    switch($status) {
                        case 'approved': $badgeClass = 'badge bg-success'; break;
                        case 'returned': $badgeClass = 'badge bg-danger'; break;
                        default: $badgeClass = 'badge bg-warning text-dark'; $status = 'Waiting for Approval';
                    }
                @endphp

<div class="p-3 mb-3 border rounded status-card-container"
     data-status="{{ strtolower($opportunity['status'] ?? '') }}"
data-participants="{{ is_array($opportunity['participants'] ?? null) ? count($opportunity['participants']) : 0 }}"
     data-target="{{ (int) ($opportunity['schedule'][0]['target_number'] ?? 0) }}">
 <div class="d-flex justify-content-between align-items-center">
        <strong>{{ $opportunity['title'] ?? 'No Title' }}</strong>
        <div>
            @php
                $participantsCount = count($opportunity['participants'] ?? []);
                $targetNumber = (int) ($opportunity['schedule'][0]['target_number'] ?? 0);
            @endphp

            @if(strtolower($opportunity['status']) === 'approved' && $participantsCount < $targetNumber)
                <span class="badge bg-primary">Waiting for Volunteers</span>
            @else
                <span class="{{ $badgeClass }}">{{ ucfirst($status) }}</span>
            @endif

            {{-- ✅ NEW URGENT BADGE --}}
            @if(!empty($opportunity['is_urgent']) && ($opportunity['is_urgent'] === true || $opportunity['is_urgent'] === 'yes'))
                <span class="badge bg-danger ms-2">Urgent</span>
            @endif
        </div>
    </div>

    <div>
        <p class="mb-1">{{ $opportunity['location'] ?? 'No Location' }}</p>
        <small>Submitted by: {{ $opportunity['submitted_by_name'] }} ({{ $opportunity['submitted_by_role'] }})</small><br>
        <small>Date Submitted: {{ \Carbon\Carbon::parse($opportunity['submitted_at'])->format('F j, Y') }}</small>
  <button class="view-details-btn float-end" data-bs-toggle="modal" data-bs-target="#opportunityModal{{ $opportunity['opportunity_id'] }}">
    View Details
  </button>
  
    </div>
</div>

{{-- ✅ MODAL --}}
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
        {{-- 🔹 LEFT PANEL --}}
        <div class="col-md-6 left-panel">
          <p><strong>Submitted By:</strong> {{ $opportunity['submitted_by_name'] ?? 'N/A' }}</p>
          <p><strong>Location:</strong> {{ $opportunity['location'] ?? 'N/A' }}</p>

@php
  $isIntermittent = !empty($opportunity['is_intermittent']) && (int)$opportunity['is_intermittent'] === 1;
@endphp

@if($isIntermittent)
  {{-- 🔸 Table for intermittent opportunities --}}
  <div class="table-responsive">
    <table class="table align-middle table-sm table-bordered">
      <thead class="table-light">
        <tr>
          <th>Date</th>
          <th>Time</th>
          <th>Volunteers Needed</th>
          <th>Food Provided</th> {{-- ✅ New column --}}
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
            <td>
              @php
                $food = strtolower($s['food_provided'] ?? 'no');
              @endphp
              {{ $food === 'yes' ? 'Yes' : 'No' }}
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-center text-muted">No schedule details provided</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@else
  {{-- 🔹 Simple details for non-intermittent --}}
  @php
    $firstSchedule = $opportunity['schedule'][0] ?? [];
    $date = $firstSchedule['date']
        ?? $firstSchedule['start_date']
        ?? $opportunity['start_date']
        ?? null;
    $start = $firstSchedule['start_time'] ?? ($opportunity['start_time'] ?? null);
    $end = $firstSchedule['end_time'] ?? ($opportunity['end_time'] ?? null);
    $target = $firstSchedule['target_number'] ?? ($opportunity['target_number'] ?? 'N/A');
  @endphp

  <p><strong>Date:</strong>
    {{ !empty($date) ? \Carbon\Carbon::parse($date)->format('F j, Y') : 'N/A' }}
  </p>

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

        {{-- 🔹 RIGHT PANEL --}}
        <div class="col-md-6 right-panel">

@if(!empty($opportunity['participants']) || !empty($opportunity['schedule']))

    @php
        // ✅ Count only NON-declined participants
        $participantsCount = collect($opportunity['participants'] ?? [])
            ->filter(fn($p) => strtolower($p['status'] ?? '') !== 'declined')
            ->count();

        // ✅ Determine volunteers needed correctly for BOTH intermittent and regular schedules
        $schedule = collect($opportunity['schedule'] ?? []);

        if ($schedule->isNotEmpty() && isset($schedule[0]['target_number'])) {
            // Intermittent or structured schedule → Sum all
            $targetNumber = $schedule->sum('target_number');
        } else {
            // Regular schedule → Use main target number
            $targetNumber = $opportunity['target_number'] ?? 0;
        }
    @endphp

    <ul class="volunteers-list">
        @foreach($opportunity['participants'] ?? [] as $participant)
            @php
                $displayStatus = strtolower($participant['status'] ?? '') === 'declined'
                    ? 'Invite Declined'
                    : ($participant['status'] ?? 'Pending');
            @endphp

            <li>
                {{ $participant['name'] ?? 'Unnamed' }} -
                <span class="text-muted">{{ $displayStatus }}</span>
            </li>
        @endforeach
    </ul>

@endif



<h6><strong>Description</strong></h6>
<div></div>
    {{ $opportunity['description'] ?? 'No description provided.' }}
</div>

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
  <ul>
    <li>No tasks provided.</li>
  </ul>
@endif

          <hr>

{{-- ✅ CANCEL OPPORTUNITY (OFFICE SIDE) --}}
@php
    $statusLower = strtolower($opportunity['status'] ?? '');
@endphp

@if(!in_array($statusLower, ['returned', 'cancelled', 'completed']))
<form method="POST" action="{{ route('volunteers-admin.opportunities.cancel', $opportunity['opportunity_id']) }}" class="mt-3">
    @csrf
    <button type="submit" class="btn btn-danger"
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




@endsection
