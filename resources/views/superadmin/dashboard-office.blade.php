@extends('superadmin.admin.layout')

@section('content')

@extends('superadmin.admin.layout')

@section('content')





        <div class="dashboard-row">
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
$approved = collect($allOpportunities ?? [])->where('status', 'Approved')->count();
$returned = collect($allOpportunities ?? [])->whereIn('status', ['Returned', 'Returned to SDAO'])->count();
$waiting = collect($allOpportunities ?? [])->whereNotIn('status', ['Approved', 'Returned', 'Returned to SDAO'])->count();

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


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


  <script>

  </script>



@endsection


  <main class="page-container">


        <div class="dashboard-row">
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
$approved = collect($allOpportunities ?? [])->where('status', 'Approved')->count();
$returned = collect($allOpportunities ?? [])->whereIn('status', ['Returned', 'Returned to SDAO'])->count();
$waiting = collect($allOpportunities ?? [])->whereNotIn('status', ['Approved', 'Returned', 'Returned to SDAO'])->count();

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


</main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


  <script>

  </script>



@endsection
