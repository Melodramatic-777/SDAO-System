@extends('volunteers-admin.layout')

@section('content')




    <!-- Main Content -->
    <div class="main-content">

      <div class="dashboard-row">

        <!-- Action Buttons -->
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

        <!-- Status Calculations -->
        @php
          $approved = collect($allOpportunities ?? [])->where('status', 'Approved')->count();
          $returned = collect($allOpportunities ?? [])->whereIn('status', ['Returned', 'Returned to SDAO'])->count();
          $waiting = collect($allOpportunities ?? [])->whereNotIn('status', ['Approved', 'Returned', 'Returned to SDAO'])->count();

          $urgent = collect($opportunities ?? [])->filter(function($op) {
              $isUrgent = !empty($op['is_urgent']) && ($op['is_urgent'] === true || $op['is_urgent'] === 'yes');
              if (!$isUrgent) return false;

              $status = strtolower($op['status'] ?? '');
              $participants = count($op['participants'] ?? []);
              $target = (int) ($op['schedule'][0]['target_number'] ?? 0);

              if (in_array($status, ['pending', 'waiting', 'waiting for approval'])) return true;
              if ($status === 'approved' && $participants < $target) return true;

              return false;
          })->count();
        @endphp

        <!-- Status Cards -->
        <div class="status-cards">
          <div class="status-card approved">Approved <span>{{ $approved }}</span></div>
          <div class="status-card waiting">Waiting for Approval <span>{{ $waiting }}</span></div>
          <div class="status-card returned">Returned <span>{{ $returned }}</span></div>
          <div class="status-card urgent">Urgent <span>{{ $urgent }}</span></div>
        </div>

      </div>

      <!-- Leaderboard -->
      <div class="leaderboard-container">
        <h2>Volunteer Hours Leaderboard</h2>
        <table>
          <thead>
            <tr>
              <th>Rank #</th>
              <th>Scholarship Type</th>
              <th>Name</th>
              <th>Program</th>
              <th>Total Hours</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>1</td><td>Blue</td><td>Santos, John Carlo</td><td>BS Information Technology</td><td>112 hrs</td></tr>
            <tr><td>2</td><td>White</td><td>Ramirez, Alyssa Mae</td><td>BS Psychology</td><td>105 hrs</td></tr>
            <tr><td>3</td><td>UAEB</td><td>Cruz, Nathaniel R.</td><td>BS Accountancy</td><td>98 hrs</td></tr>
            <tr><td>4</td><td>Blue</td><td>Mendoza, Trisha Joy</td><td>BS Computer Engineering</td><td>94 hrs</td></tr>
            <tr><td>5</td><td>White</td><td>Villanueva, James R.</td><td>BSBA Marketing Management</td><td>90 hrs</td></tr>
            <tr><td>6</td><td>UAEB</td><td>Dela Cruz, Bianca F.</td><td>BS Hospitality Management</td><td>88 hrs</td></tr>
            <tr><td>7</td><td>Blue</td><td>Garcia, Elvin Ray</td><td>BS Architecture</td><td>85 hrs</td></tr>
            <tr><td>8</td><td>White</td><td>Tan, Isabelle Grace</td><td>BS Tourism Management</td><td>82 hrs</td></tr>
            <tr><td>9</td><td>UAEB</td><td>Torres, Kenneth L.</td><td>BS Information Technology</td><td>80 hrs</td></tr>
            <tr><td>10</td><td>Blue</td><td>Lim, Joanna Faith</td><td>BS Civil Engineering</td><td>78 hrs</td></tr>
          </tbody>
        </table>
      </div>

    </div> <!-- End Main Content -->

    <!-- Right Panel -->
    <aside class="otsv-panel">
      <h3>On the Spot Volunteers</h3>
      <ul class="otsv-list"></ul>
    </aside>

  </div> <!-- End Wrapper -->


@endsection
