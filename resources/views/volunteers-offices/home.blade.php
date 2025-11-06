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
})->count();      @endphp

      <div class="status-cards">
        <div class="status-card approved">Approved <span>{{ $approved }}</span></div>
        <div class="status-card waiting">Waiting for Approval <span>{{ $waiting }}</span></div>
        <div class="status-card returned">Returned <span>{{ $returned }}</span></div>
        <div class="status-card urgent">Urgent <span>{{ $urgent }}</span></div>
      </div>

    </div>

    <!-- Status List -->
    <section class="status-section">
        <h2 class="mb-3">Status</h2>

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

<div class="p-3 mb-3 border rounded status-card-container">
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
        <small>Date Submitted: {{ date('d/m/Y', strtotime($opportunity['submitted_at'])) }}</small>
        <a href="{{ route('volunteers-offices.history-office') }}" class="float-end">View Details</a>
    </div>
</div>
                   
            @endforeach
        @endif
    </section>


</main>




</body>
</html>
