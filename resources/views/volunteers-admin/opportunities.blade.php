<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Opportunities - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Fonts & Libraries -->
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

  @vite('resources/css/Volunteer_Admin.css')

</head>

<body>
  <header class="custom-header">
    <div class="header-left">
      <div class="logo">
        <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
        <span>Student Developments and Activities Office - SDA Assistant</span>
      </div>
    </div>

    <div class="header-right-group">
      <p>Welcome, {{ session('volunteers_admin.first_name') }}!</p>
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
      <a href="{{ route('volunteers-admin.dashboard') }}">Dashboard</a>
      <a href="{{ route('volunteers-admin.opportunities') }}">Volunteer Requests</a>
      <a href="{{ route('volunteers-admin.history') }}">History</a>
      <a href="{{ route('volunteers-admin.student-volunteers') }}">Student Volunteers</a>
      <a href="{{ route('volunteers-admin.accounts') }}">Accounts</a>
      <a href="{{ route('volunteers-admin.inquiries') }}">Contact</a>
    </nav>
  </header>

  <main class="page-container">



<section class="status-section">
  

@php
    $allCount = collect($opportunities)->count();

    $waitingApprovalCount = collect($opportunities)->filter(function ($op) {
        $status = strtolower($op['status'] ?? '');
        return in_array($status, ['pending', 'waiting', 'waiting for approval', 'returned to sdao']);
    })->count();

    $waitingVolunteersCount = collect($opportunities)->filter(function ($op) {
        $status = strtolower($op['status'] ?? '');
        $participants = count($op['participants'] ?? []);
        $target = (int) ($op['schedule'][0]['target_number'] ?? 0);
        return $status === 'approved' && $participants < $target;
    })->count();

    $returnedCount = collect($opportunities)->filter(function ($op) {
        return in_array(strtolower($op['status'] ?? ''), ['returned', 'returned to sdao']);
    })->count();

    // MAIN FILTER
    $filteredOpportunities = collect($opportunities)->filter(function($opportunity) {
        $status = strtolower($opportunity['status'] ?? '');
        $participantsCount = count($opportunity['participants'] ?? []);
        $targetNumber = (int) ($opportunity['schedule'][0]['target_number'] ?? 0);

        if (in_array($status, ['returned', 'returned to sdao'])) return true;

        if (in_array($status, ['pending', 'waiting', 'waiting for approval'])) return true;

        if ($status === 'approved' && $participantsCount < $targetNumber) return true;

        return false;
    });

    // Sorting Logic
    $filteredOpportunities = $filteredOpportunities->sortByDesc(function($opportunity) {
        $status = strtolower($opportunity['status'] ?? '');
        $participants = count($opportunity['participants'] ?? []);
        $target = (int) ($opportunity['schedule'][0]['target_number'] ?? 0);
        $isUrgent = !empty($opportunity['is_urgent']) &&
                    ($opportunity['is_urgent'] === true || $opportunity['is_urgent'] === 'yes');

        if (in_array($status, ['pending', 'waiting', 'waiting for approval'])) return 4;
        if ($isUrgent) return 3;
        if ($status === 'approved' && $participants < $target) return 2;
        if (in_array($status, ['returned', 'returned to sdao'])) return 1;

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

      @if(empty($opportunities))
        <p>No submitted activities yet</p>
      @else
@php
  $filteredOpportunities = collect($allOpportunities)->filter(function($opportunity) {
      $status = strtolower($opportunity['status'] ?? '');
      $participantsCount = count($opportunity['participants'] ?? []);
      $targetNumber = (int) ($opportunity['schedule'][0]['target_number'] ?? 0);

      // ✅ Always include Returned
if (in_array($status, ['returned', 'returned to sdao'])) return true;

      // ✅ Include Waiting / Pending opportunities
      if(in_array($status, ['pending', 'waiting', 'waiting for approval'])) return true;

      // ✅ Include Approved but not full
      if ($status === 'approved' && $participantsCount < $targetNumber) return true;

      // ❌ Otherwise exclude (approved and full)
      return false;
  });

  // Sort urgent + waiting first
$filteredOpportunities = $filteredOpportunities->sortByDesc(function($opportunity) {

    $status = strtolower($opportunity['status'] ?? '');
    $participants = count($opportunity['participants'] ?? []);
    $target = (int) ($opportunity['schedule'][0]['target_number'] ?? 0);
    $isUrgent = !empty($opportunity['is_urgent']) &&
                ($opportunity['is_urgent'] === true || $opportunity['is_urgent'] === 'yes');

    // 1️⃣ Waiting for Approval first
    if (in_array($status, ['pending', 'waiting', 'waiting for approval'])) {
        return 4;
    }

    // 2️⃣ Urgent next (regardless of status)
    if ($isUrgent) {
        return 3;
    }

    // 3️⃣ Approved but NOT full = Waiting for Volunteers
    if ($status === 'approved' && $participants < $target) {
        return 2;
    }

    // 4️⃣ Returned / Returned to SDAO
    if (in_array($status, ['returned', 'returned to sdao'])) {
        return 1;
    }

    // 5️⃣ Everything else
    return 0;
});


@endphp

        @if($filteredOpportunities->isEmpty())
          <p>No opportunities waiting for approval or volunteers.</p>
        @else
        <div id="opportunitiesContainer">

@foreach($filteredOpportunities as $opportunity)
            @php
              $status = strtolower($opportunity['status'] ?? 'waiting');
   switch($status) {
    case 'approved':
        $badgeClass = 'badge bg-success';
        break;

    case 'returned':
    case 'returned to sdao':
        $badgeClass = 'badge bg-danger';
        $status = 'Returned';
        break;

    default:
        $badgeClass = 'badge bg-warning text-dark';
        $status = 'Waiting for Approval';
}

              $participantsCount = count($opportunity['participants'] ?? []);
              $targetNumber = (int) ($opportunity['schedule'][0]['target_number'] ?? 0);
            @endphp

<div class="p-3 mb-3 border rounded status-card-container"
     data-status="{{ strtolower($opportunity['status'] ?? '') }}"
     data-participants="{{ count($opportunity['participants'] ?? []) }}"
     data-target="{{ (int) ($opportunity['schedule'][0]['target_number'] ?? 0) }}">
                  <div class="d-flex justify-content-between align-items-center">
                <strong>{{ $opportunity['title'] ?? 'No Title' }}</strong>
                <div>

                  @if(
    strtolower($opportunity['status']) === 'approved' 
    && $participantsCount < $targetNumber
    && strtolower($opportunity['submitted_by_role'] ?? '') === 'sda assistant'
)

    <span class="badge bg-primary">Waiting for Volunteers</span>

@elseif(strtolower($opportunity['status']) === 'returned to sdao')

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
<small>Date Submitted:
    {{ isset($opportunity['submitted_at'])
        ? \Carbon\Carbon::parse($opportunity['submitted_at'])->format('F j, Y')
        : 'N/A'
    }}
</small>
<button class="view-details-btn float-end" data-bs-toggle="modal" data-bs-target="#opportunityModal{{ $opportunity['opportunity_id'] }}">
  View Details
</button>

              </div>
            </div>

{{-- ✅ Modal --}}
<div class="modal fade" id="opportunityModal{{ $opportunity['opportunity_id'] }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
  <div class="modal-dialog modal-xl modal-dialog-centered">
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
          <p><strong>Office:</strong> {{ $opportunity['submitted_by_role'] ?? 'N/A' }}</p>
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




           
<h6>Tasks</h6>

@if(!empty($opportunity['tasks']) && is_array($opportunity['tasks']))
  <ul style="list-style-type: disc; padding-left: 20px;">
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

          @php
            $user = session('volunteers_admin') ?? [];
            $userEmail = $user['email'] ?? null;
            $userRole  = $user['role'] ?? null;
          @endphp

          @if($userRole === 'SDA Assistant' && ($opportunity['submitted_by'] ?? '') === $userEmail)
<form method="POST" action="{{ route('volunteers-admin.opportunities.cancel', $opportunity['opportunity_id']) }}">
              @csrf
    <button type="submit" class="btn btn-danger"
        onclick="return confirm('Are you sure you want to cancel this opportunity? This will remove invitations and move the record to canceled opportunities.')">
        Cancel Opportunity
    </button>            </form>
          @else
            <div class="p-0 border-0 modal-footer">
@if(in_array(strtolower($opportunity['status'] ?? ''), ['pending', 'waiting for approval', 'returned to sdao']))
    {{-- Approve --}}
    <form method="POST" action="{{ route('volunteers-admin.opportunity.update-status', $opportunity['opportunity_id']) }}" class="d-inline">
      @csrf @method('PATCH')
      <input type="hidden" name="status" value="Approved">
      <button type="submit" class="btn btn-success">Approve</button>
    </form>

    {{-- Return --}}
    <button type="button" class="btn btn-danger ms-2" data-bs-toggle="collapse"
      data-bs-target="#returnReason{{ $opportunity['opportunity_id'] }}">
      Return
    </button>

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

          @endforeach
          </div>

          <div class="mt-4 d-flex justify-content-center">
{{ $pagination->links('pagination::bootstrap-5') }}
</div>

      @endif
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



  </script>
</body>
</html>
