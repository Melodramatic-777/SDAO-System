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

  @vite('resources/css/volunteers.css')

  <style>
    body {
      font-family: 'Inter', sans-serif;
    }

    .custom-modal-content {
      background: #fff;
      margin: 5% auto;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .custom-modal-body {
      padding: 1.5rem;
    }

    .left-panel {
      border-right: 1px solid #f0e6cc;
    }

    .status-badge {
      background: #ffa857;
      color: white;
      font-size: 0.85rem;
      font-weight: bold;
      padding: 4px 12px;
      border-radius: 20px;
    }

    .volunteer-count {
      font-weight: bold;
      color: #0056b3;
    }

    .volunteers-list {
      list-style: none;
      padding-left: 0;
      margin-top: 0.5rem;
    }

    .volunteers-list li {
      font-size: 0.9rem;
      padding: 2px 0;
    }

    .right-panel h6 {
      font-weight: bold;
      margin-bottom: 0.5rem;
    }

    .right-panel ul {
      padding-left: 20px;
      font-size: 0.9rem;
    }

    .view-details-btn {
      background: none;
      border: none;
      color: #0d6efd;
      text-decoration: underline;
      cursor: pointer;
    }

    .modal-dialog.modal-xl {
      max-width: 100% !important;
      min-width: 1500px;
    }

    .modal-content.custom-modal-content {
      max-height: 95vh;
      overflow-y: auto;
    }


    .status-card {
    background: white;
    border-radius: 12px;
    padding: 18px;
    text-align: center;
    box-shadow: 0 3px 12px rgba(0,0,0,0.08);
    transition: 0.2s;
  }

  .status-card:hover { transform: translateY(-3px); }

  .status-card h6 { font-size: 0.9rem; margin-bottom: 6px; color: #555; font-weight: 600; }
  .status-card h3 { font-size: 2rem; margin: 0; font-weight: 700; }

  .status-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
  }





.status-filter {
  font-size: 16px;
  font-weight: 500;
  display: flex;
  gap: 1.5rem;
  cursor: pointer;
  user-select: none;
  margin-top: 12px; /* <-- Added spacing */

}

.status-tab {
  padding-bottom: 4px;
  transition: 0.25s ease;
}

/* Light Mode (Default) */
@media (prefers-color-scheme: light) {
  .status-tab {
    color: #6c6c6c;
  }
  .status-tab.active {
    color: #000;
    border-bottom: 2px solid #0056b3; /* NU Yellow Accent */
  }
  .status-tab:hover {
    color: #000;
  }
}
  

.status-tab .badge {
  font-size: 0.75rem;
  vertical-align: text-top;
  padding: 3px 7px;
  border-radius: 8px;
}


  </style>
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
      <a href="{{ route('volunteers-admin.dashboard') }}">Leader Board</a>
      <a href="{{ route('volunteers-admin.opportunities') }}">Volunteer Requests</a>
      <a href="{{ route('volunteers-admin.history') }}">History</a>
      <a href="{{ route('volunteers-admin.student-volunteers') }}">Student Volunteers</a>
      <a href="{{ route('volunteers-admin.accounts') }}">Accounts</a>
      <a href="{{ route('volunteers-admin.inquiries') }}">Contact</a>
    </nav>
  </header>

  <main class="page-container">

    <div class="dashboard-row">
      <div class="action-buttons">
        <a href="{{ route('volunteers-admin.create-opportunity') }}" class="action-card">
          <div class="card-icon">📝</div>
          <h3>Submit a Volunteer Opportunity</h3>
          <p>Propose a new volunteer activity for approval.</p>
        </a>

        <a href="{{ route('volunteers-admin.invite-volunteer') }}" class="action-card">
          <div class="card-icon">📩</div>
          <h3>Invite a Student Volunteer</h3>
          <p>Send an invitation for student volunteers to join your activity.</p>
        </a>
      </div>

      @php
$approved = collect($opportunities ?? [])->where('status', 'Approved')->count();
$returned = collect($opportunities ?? [])->whereIn('status', ['Returned', 'Returned to SDAO'])->count();
$waiting = collect($opportunities ?? [])->whereNotIn('status', ['Approved', 'Returned', 'Returned to SDAO'])->count();

        $total = collect($opportunities ?? [])->count();
$urgent = collect($opportunities ?? [])->filter(function($op) {
    $isUrgent = !empty($op['is_urgent']) && ($op['is_urgent'] === true || $op['is_urgent'] === 'yes');
    if (!$isUrgent) return false;

    $status = strtolower($op['status'] ?? '');
    $participants = count($op['participants'] ?? []);
    $target = (int) ($op['schedule'][0]['target_number'] ?? 0);

    // Only count if:
    // 1) Waiting for approval (Pending)
    if (in_array($status, ['pending', 'waiting', 'waiting for approval'])) return true;

    // 2) Approved but not full
    if ($status === 'approved' && $participants < $target) return true;

    return false; // Otherwise do not count
})->count();
      @endphp

      <div class="status-cards">
        <div class="status-card approved">Approved <span>{{ $approved }}</span></div>
        <div class="status-card waiting">Waiting for Approval <span>{{ $waiting }}</span></div>
        <div class="status-card returned">Returned <span>{{ $returned }}</span></div>
        <div class="status-card urgent">Urgent <span>{{ $urgent }}</span></div>
      </div>
        </div>

<section class="status-section">
  


@php
    $allCount = collect($allOpportunities)->count();

    $waitingApprovalCount = collect($allOpportunities)->filter(function ($op) {
        $status = strtolower($op['status'] ?? '');
        return in_array($status, ['pending', 'waiting', 'waiting for approval', 'returned to sdao']);
    })->count();

    $waitingVolunteersCount = collect($allOpportunities)->filter(function ($op) {
        $status = strtolower($op['status'] ?? '');
        $participants = count($op['participants'] ?? []);
        $target = (int) ($op['schedule'][0]['target_number'] ?? 0);
        return $status === 'approved' && $participants < $target;
    })->count();

    $returnedCount = collect($allOpportunities)->filter(function ($op) {
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
@php
  $filteredOpportunities = collect($opportunities)->filter(function($opportunity) {
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

@foreach($opportunities as $opportunity)
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
@if(strtolower($opportunity['status']) === 'approved' && $participantsCount < $targetNumber)

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
          <ul>
            @if(!empty($opportunity['task_details']))
              @foreach(explode("\n", $opportunity['task_details']) as $task)
                <li>{{ trim($task) }}</li>
              @endforeach
            @else
              <li>No tasks provided.</li>
            @endif
          </ul>

          <h6>Skills Needed</h6>
          <ul>
            @if(!empty($opportunity['skills_needed']))
              @foreach(is_array($opportunity['skills_needed']) ? $opportunity['skills_needed'] : explode("\n", $opportunity['skills_needed']) as $skill)
                <li>{{ trim($skill) }}</li>
              @endforeach
            @else
              <li>No skills provided.</li>
            @endif
          </ul>

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
