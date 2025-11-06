<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Invites - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite('resources/css/volunteers.css')

  <style>

    .table-title-row th {
      background-color: #004aad !important;
      color: white !important;
      text-align: center;
      font-size: 1.25rem;
    }

    .table-header-row th {
      background-color: #FFD700 !important;
      color: black !important;
      font-weight: bold !important;
      text-align: center;
    }

    .custom-modal-content {
      background: #fff;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }

    .logout-btn {
      background-color: #f8f9fa !important;
      border: 0px solid #ddd !important;
    }

    .logout-btn:focus,
    .logout-btn:active {
      outline: none !important;
      box-shadow: none !important;
      border-color: #ddd !important;
    }

    .badge {
      font-size: 0.9rem;
    }


    /* Fix modal overlay blocking clicks */
.modal {
  z-index: 1055 !important;
}

.modal-backdrop {
  z-index: 1050 !important;
}

.modal-dialog {
  z-index: 1060 !important;
}

.custom-modal-content {
  position: relative;
  z-index: 1065;
  background: #fff;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}


  </style>
</head>

<body>

<header class="custom-header">
  <div class="header-left">
    <div class="logo">
      <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
      <span>Student Developments and Activities Office - Scholars</span>
    </div>
  </div>
  <div class="header-right-group">
    <p>Welcome, {{ session('scholar_accounts.first_name') }}!</p>
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
    <a href="{{ route('scholars.dashboard') }}">Dashboard</a>
    <a href="{{ route('scholars.volunteer-opportunities') }}">Volunteer Opportunity</a>
    <a href="{{ route('scholars.invites') }}">Volunteer Invite</a>
    <a href="{{ route('scholars.profile') }}">Profile</a>
</nav>
</header>

<div class="container mt-4 text-center">
  <h3 class="mb-4">Volunteer Invites</h3>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @elseif (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <div id="invitesTable" class="mt-3">
    <table class="table mx-auto text-center align-middle table-bordered" style="max-width: 900px;">
      <thead>
        <tr class="table-title-row">
          <th colspan="5" class="text-center fs-5">List of Volunteer Invites</th>
        </tr>
        <tr class="table-header-row">
          <th>Office</th>
          <th>Opportunity Name</th>
          <th>Invited At</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($invites as $invite)
          <tr>
            <td>{{ $invite['office'] }}</td>
            <td>{{ $invite['title'] }}</td>
            <td>{{ $invite['invited_at'] }}</td>
            <td>
              <span class="badge 
                @if($invite['status'] === 'Accepted') bg-success 
                @elseif($invite['status'] === 'Declined') bg-danger 
                @else bg-warning text-dark 
                @endif">
                {{ $invite['status'] }}
              </span>
            </td>
            <td>
              <button 
                class="btn btn-sm btn-primary" 
                data-bs-toggle="modal"
                data-bs-target="#detailsModal{{ $invite['opportunity_id'] }}">
                View
              </button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-muted">No invites found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- ✅ Moved all modals outside the table to fix the overlay issue -->
@foreach ($invites as $invite)
  <div class="modal fade" id="detailsModal{{ $invite['opportunity_id'] }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content custom-modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ $invite['title'] }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-start">
          <p><strong>Office:</strong> {{ $invite['office'] }}</p>
          <p><strong>Invited At:</strong> {{ $invite['invited_at'] }}</p>
          <p><strong>Status:</strong> {{ $invite['status'] }}</p>
        </div>
        <div class="modal-footer">
          @if($invite['status'] === 'Pending')
            <form action="{{ route('scholars.invites.respond', $invite['opportunity_id']) }}" method="POST" class="d-inline">
              @csrf
              <input type="hidden" name="response" value="Accepted">
              <button class="btn btn-success btn-sm">Accept</button>
            </form>
            <form action="{{ route('scholars.invites.respond', $invite['opportunity_id']) }}" method="POST" class="d-inline">
              @csrf
              <input type="hidden" name="response" value="Declined">
              <button class="btn btn-danger btn-sm">Decline</button>
            </form>
          @else
            <button class="btn btn-secondary btn-sm" disabled>{{ $invite['status'] }}</button>
          @endif
        </div>
      </div>
    </div>
  </div>
@endforeach

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
