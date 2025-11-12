<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
            <span>Student Developments and Activities Office - Office</span>
        </div>
    </div>
    <div class="header-right-group">
        <p>Welcome, {{ session('volunteers_office.first_name') }}!</p>
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
    <a href="{{ route('volunteers-offices.home') }}">Home</a>
    <a href="{{ route('volunteers-offices.history-office') }}">History</a>
</nav>
</header>

<main class="page-container">

    <div class="dashboard-row">
        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('volunteers-offices.create-opportunity') }}" class="action-card">
                <div class="card-icon">📝</div>
                <h3>Submit a Volunteer Opportunity</h3>
                <p>Propose a new volunteer activity for approval.</p>
            </a>

            <a href="{{ route('volunteers-offices.invite-volunteer-office') }}" class="action-card">
                <div class="card-icon">📩</div>
                <h3>Invite a Student Volunteer</h3>
                <p>Send an invitation for student volunteers to join your activity.</p>
            </a>
        </div>


@php
// make sure we operate on a collection (controller already does this)
$all = isset($allOpportunities) ? collect($allOpportunities) : collect([]);

// normalize status helper
$normalize = fn($s) => trim(strtolower($s ?? ''));

// Approved / Returned / Waiting counts (case-insensitive, trimmed)
$approved = $all->filter(fn($op) => $normalize($op['status']) === 'approved')->count();
$returned = $all->filter(fn($op) => in_array($normalize($op['status']), ['returned', 'returned to sdao']))->count();
$waiting = $all->filter(fn($op) => !in_array($normalize($op['status']), ['approved','returned','returned to sdao']))->count();
$total = $all->count();

// Urgent: same criteria as you used elsewhere — count 'is_urgent' and either pending or approved-but-not-full
$urgent = $all->filter(function($op) use ($normalize) {
    $isUrgent = !empty($op['is_urgent']) && ($op['is_urgent'] === true || $op['is_urgent'] === 'yes' || $op['is_urgent'] === '1');
    if (!$isUrgent) return false;

    $status = $normalize($op['status'] ?? '');

    // ✅ safely handle participants count
    $participants = 0;
    if (isset($op['participants']) && is_array($op['participants'])) {
        $participants = count($op['participants']);
    }

    // ✅ safely compute target
    $target = 0;
    if (!empty($op['schedule']) && is_array($op['schedule'])) {
        foreach ($op['schedule'] as $s) {
            $target += isset($s['target_number']) ? (int)$s['target_number'] : 0;
        }
    } else {
        $target = (int)($op['target_number'] ?? 0);
    }

    // ✅ logic
    if (in_array($status, ['pending','waiting','waiting for approval','returned to sdao'])) {
        return true;
    }

    if ($status === 'approved' && $target > 0 && $participants < $target) {
        return true;
    }

    return false;
})->count();

@endphp

      <div class="status-cards">
        <div class="status-card approved">Approved <span>{{ $approved }}</span></div>
        <div class="status-card waiting">Waiting for Approval <span>{{ $waiting }}</span></div>
        <div class="status-card returned">Returned <span>{{ $returned }}</span></div>
        <div class="status-card urgent">Urgent <span>{{ $urgent }}</span></div>
      </div>

    </div>

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

  {{-- 🔹 Non-Intermittent Schedule Info --}}
    @php
    $s = $opportunity['schedule'][0] ?? [];
    $date = $s['date'] ?? $opportunity['start_date'] ?? null;
    $start = $s['start_time'] ?? $opportunity['start_time'] ?? null;
    $end = $s['end_time'] ?? $opportunity['end_time'] ?? null;
  @endphp

  <div class="table-responsive">
    <table class="table text-center align-middle table-sm table-bordered">
      <thead class="table-light">
        <tr>
          <th>Date</th>
          <th>Start Time</th>
          <th>End Time</th>
          <th>Volunteers Needed</th>
          <th>Food Provided</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>{{ !empty($date) ? \Carbon\Carbon::parse($date)->format('F j, Y') : 'N/A' }}</td>
          <td>{{ !empty($start) ? \Carbon\Carbon::parse($start)->format('g:i A') : 'N/A' }}</td>
          <td>{{ !empty($end) ? \Carbon\Carbon::parse($end)->format('g:i A') : 'N/A' }}</td>
          <td>{{ $s['target_number'] ?? $opportunity['target_number'] ?? 'N/A' }}</td>
          <td class="border-0">
            @php
              $food = strtolower($s['food_provided'] ?? $opportunity['food_provided'] ?? 'no');
            @endphp
{{ $food === 'yes' ? 'Yes' : 'No' }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
@endif

        </div>


        {{-- 🔹 RIGHT PANEL --}}
        <div class="col-md-6 right-panel">

@if(!empty($opportunity['participants']) || !empty($opportunity['schedule']))

    @php
        // ✅ Count only NON-declined participants
$participantsCount = is_array($opportunity['participants'] ?? null)
    ? collect($opportunity['participants'])
        ->filter(fn($p) => strtolower($p['status'] ?? '') !== 'declined')
        
        ->count()
    : 0;

        // ✅ Volunteers Needed (works for intermittent + regular)
        $schedule = collect($opportunity['schedule'] ?? []);
        $targetNumber = $schedule->isNotEmpty() && isset($schedule[0]['target_number'])
            ? $schedule->sum('target_number')
            : ($opportunity['target_number'] ?? 0);
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


{{-- ✅ Returned Block Here --}}
@if(strtolower($opportunity['status'] ?? '') === 'returned')
<hr>
<p><strong>Returned On:</strong> {{ !empty($opportunity['returned_at']) ? \Carbon\Carbon::parse($opportunity['returned_at'])->format('F j, Y g:i A') : 'N/A' }}</p>

@if(!empty($opportunity['return_comment']))
<div class="alert alert-warning">
    <strong>Return Comment:</strong><br>
    {{ $opportunity['return_comment'] }}
</div>
@endif

<a href="{{ route('volunteers-offices.edit-opportunity', $opportunity['opportunity_id']) }}" class="mt-3 btn btn-primary">
  Edit and Resubmit
</a>
@endif

<hr>


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




</body>
</html>
