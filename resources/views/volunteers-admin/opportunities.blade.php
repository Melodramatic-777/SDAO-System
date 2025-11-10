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
        $returned = collect($opportunities ?? [])->where('status', 'Returned')->count();
        $waiting = collect($opportunities ?? [])->whereNotIn('status', ['Approved', 'Returned'])->count();
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
      <h2 class="mb-3">Status</h2>

      @if(empty($opportunities))
        <p>No submitted activities yet</p>
      @else
@php
  $filteredOpportunities = collect($opportunities)->filter(function($opportunity) {
      $status = strtolower($opportunity['status'] ?? '');
      $participantsCount = count($opportunity['participants'] ?? []);
      $targetNumber = (int) ($opportunity['schedule'][0]['target_number'] ?? 0);

      if (!in_array($status, ['approved', 'returned'])) return true;
      if ($status === 'approved' && $participantsCount < $targetNumber) return true;
      return false;
  });

  // ✅ Sort: Urgent and Waiting for Approval on top
  $filteredOpportunities = $filteredOpportunities->sortByDesc(function($opportunity) {
      $isUrgent = !empty($opportunity['is_urgent']) &&
                  ($opportunity['is_urgent'] === true || $opportunity['is_urgent'] === 'yes');
      $status = strtolower($opportunity['status'] ?? '');

      $isWaiting = in_array($status, ['waiting for approval', 'waiting', 'pending']);

      // Prioritize: Urgent (2) → Waiting (1) → Others (0)
      return ($isUrgent ? 2 : ($isWaiting ? 1 : 0));
  });
@endphp

        @if($filteredOpportunities->isEmpty())
          <p>No opportunities waiting for approval or volunteers.</p>
        @else
          @foreach($filteredOpportunities as $opportunity)
            @php
              $status = strtolower($opportunity['status'] ?? 'waiting');
              switch($status) {
                  case 'approved':
                      $badgeClass = 'badge bg-success';
                      break;
                  case 'returned':
                      $badgeClass = 'badge bg-danger';
                      break;
                  default:
                      $badgeClass = 'badge bg-warning text-dark';
                      $status = 'Waiting for Approval';
              }

              $participantsCount = count($opportunity['participants'] ?? []);
              $targetNumber = (int) ($opportunity['schedule'][0]['target_number'] ?? 0);
            @endphp

            <div class="p-3 mb-3 border rounded status-card-container">
              <div class="d-flex justify-content-between align-items-center">
                <strong>{{ $opportunity['title'] ?? 'No Title' }}</strong>
                <div>
                  @if(strtolower($opportunity['status']) === 'approved' && $participantsCount < $targetNumber)
                    <span class="badge bg-primary">Waiting for Volunteers</span>
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
                <small>Date Submitted: {{ isset($opportunity['submitted_at']) ? date('d/m/Y', strtotime($opportunity['submitted_at'])) : 'N/A' }}</small>

@if(
  in_array(strtolower($opportunity['status']), ['pending', 'waiting', 'waiting for approval']) ||
  (strtolower($opportunity['status']) === 'approved' && $participantsCount < $targetNumber)
)
  <button class="view-details-btn float-end" data-bs-toggle="modal" data-bs-target="#opportunityModal{{ $opportunity['opportunity_id'] }}">
    View Details
  </button>
@endif

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

<h6>Schedule</h6>

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
          </tr>
        @empty
          <tr><td colspan="3" class="text-center text-muted">No schedule details provided</td></tr>
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

  <p><strong>Volunteers Needed:</strong> {{ $target }}</p>
@endif




          @if(!empty($opportunity['decline_comment']) && strtolower($opportunity['status']) === 'returned')
            <div class="mt-2 alert alert-danger">
              <strong>Return Comment:</strong><br>{{ $opportunity['decline_comment'] }}
            </div>
          @endif

          @if(!empty($opportunity['participants']))
            <h6>Volunteers</h6>
            <ul class="volunteers-list">
              @foreach($opportunity['participants'] as $participant)
                <li>{{ $participant['name'] ?? 'Unnamed' }} - <span class="text-muted">{{ $participant['status'] ?? 'Pending' }}</span></li>
              @endforeach
            </ul>
            @php
              $participantsCount = count($opportunity['participants']);
              $targetNumber = collect($opportunity['schedule'])->sum('target_number');
            @endphp
            <p class="mt-2"><strong>Total Volunteers:</strong> <span class="volunteer-count">{{ $participantsCount }} / {{ $targetNumber }}</span></p>
          @endif
        </div>

        {{-- 🔹 RIGHT PANEL --}}
        <div class="col-md-6 right-panel">
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
              <button type="submit" class="btn btn-danger">Cancel Opportunity</button>
            </form>
          @else
            <div class="p-0 border-0 modal-footer">
              @if(strtolower($opportunity['status'] ?? '') === 'pending')
                <form method="POST" action="{{ route('volunteers-admin.opportunity.update-status', $opportunity['opportunity_id']) }}" class="d-inline">
                  @csrf @method('PATCH')
                  <input type="hidden" name="status" value="Approved">
                  <button type="submit" class="btn btn-success">Approve</button>
                </form>
                <button type="button" class="btn btn-danger ms-2" data-bs-toggle="collapse" data-bs-target="#declineReason{{ $opportunity['opportunity_id'] }}">Decline</button>
              @endif
            </div>

            <div class="collapse" id="declineReason{{ $opportunity['opportunity_id'] }}">
              <form method="POST" action="{{ route('volunteers-admin.opportunity.update-status', $opportunity['opportunity_id']) }}" class="mt-3">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="Returned">
                <div class="mb-2">
                  <label class="form-label">Reason for Decline</label>
                  <textarea name="comment" class="form-control" rows="2" required></textarea>
                </div>
                <button type="submit" class="btn btn-danger">Submit Decline</button>
              </form>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

          @endforeach
      @endif
  @endif
</section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
