<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  @vite('resources/css/volunteers.css')

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #f9f6f3;
    }

    .folder-card {
      border-radius: 12px;
      border: 1px solid #d8d3c3;
      padding: 1.5rem;
      transition: .2s;
      cursor: pointer;
    }

    .folder-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .folder-title {
      font-weight: 600;
    }

    .modal-content {
      padding: 1.5rem;
    }

    .custom-header {
      position: relative;
      z-index: 1 !important;
    }

    .modal-dialog.modal-xxl {
      max-width: 95% !important;
    }

    .modal-backdrop {
      z-index: -1 !important;
    }

    .modal-header {
      border-bottom: none;
    }

.modal-body {
  margin-left: 2rem;
  margin-right: 2rem;
}

    .modal-title {
      font-weight: bold;
      color: #0056b3;
    }

    .btn-success {
      background-color: #28a745;
      border-color: #28a745;
      font-weight: bold;
      padding: 10px 30px;
      border-radius: 50px;
    }

  
    .section-title {
      font-size: 1.25rem;
      font-weight: bold;
      color: #333;
    }

    .row .col-md-4 {
      margin-bottom: 20px;
    }

    .modal-dialog.modal-xxl {
      max-width: 80% !important;
    }

    .modal-body .row {
      display: flex;
      flex-wrap: wrap;
      gap: 2rem;
    }

    .modal-body .row > .col-md-4 {
      flex: 1 1 30%;
    }

    .modal-body .row > .col-md-12 {
      flex: 1 1 100%;
    }

.custom-wide-modal {
  max-width: 90% !important;   /* slightly narrower */
  width: auto !important;
}

    .custom-wide-modal .modal-content {
      width: 100%;
      border-radius: 8px;
      padding: 2rem;
    }

.modal.show .modal-dialog {
  margin: 2rem auto !important; /* add margin on left/right */
}

    .modal-close-x {
      position: absolute;
      top: 12px;
      right: 18px;
      font-size: 32px;
      font-weight: 300;
      color: #555;
      background: transparent;
      border: none;
      cursor: pointer;
      line-height: 1;
      z-index: 999;
      transition: 0.2s ease;
    }

    .modal-close-x:hover {
      color: #000;
      transform: scale(1.15);
    }


    .icon-btn {
  background: none;
  border: none;
  color: #000;
  cursor: pointer;
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  padding: 0;
}

.icon-btn:hover {
  color: #0056b3;
  transform: scale(1.1);
  transition: 0.2s;
}

    .table-title-row th {
      background-color: #004aad !important; color: white !important; text-align: center; font-size: 1.25rem;
    }

    .table-header-row th {
      background-color: #FFD700 !important; color: black !important; font-weight: bold !important; text-align: center;
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

<main class="p-4 container-fluid">
  <div class="row g-4">
    <div class="table-responsive">
<table class="table mx-auto text-center align-middle table-bordered">
  <thead>
    <tr class="table-title-row">
      <h2 class="mb-4 text-center">Volunteer Opportunities History</h2>
    </tr>
    <tr class="table-header-row">
      <th>#</th>
      <th>Date Submitted</th>
      <th>Title</th>
      <th>Submitted By</th>
      <th>View</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($opportunities as $index => $opportunity)
      <tr class="table-header-row">
        <td>{{ $index + 1 }}</td>
              <td>
        {{ !empty($opportunity['submitted_at']) ? \Carbon\Carbon::parse($opportunity['submitted_at'])->format('F j, Y') : 'N/A' }}
      </td>  
        <td>{{ $opportunity['title'] }}</td>
        <td>{{ $opportunity['submitted_by_name'] ?? 'N/A' }}</td>

<td>
          <button 
            class="icon-btn"
            data-bs-toggle="modal"
            data-bs-target="#opportunityModal{{ $opportunity['opportunity_id'] }}"
            title="View Opportunity">
            <i class="bi bi-eye fs-5"></i>
          </button>
        </td>
      </tr>
    @empty
      <tr><td colspan="5" class="text-center text-muted">No opportunities found.</td></tr>
    @endforelse
  </tbody>
</table>

    </div>
  </div>

  {{-- ✅ All Modals --}}
  @foreach ($opportunities as $opportunity)
  <div class="modal fade" id="opportunityModal{{ $opportunity['opportunity_id'] }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered custom-wide-modal">
      <div class="modal-content" style="border-radius: 8px; padding: 1.5rem;">
        <button type="button" class="modal-close-x" data-bs-dismiss="modal">×</button>

        <div class="modal-body">
          {{-- Title --}}
          <h3 class="mb-4 text-center fw-bold">{{ $opportunity['title'] }}</h3>

          <hr>

          <div class="row">
            {{-- Details --}}
            <div class="col-md-4">
              <h6 class="fw-bold">Details</h6>
              <p><strong>Location:</strong> {{ $opportunity['location'] ?? 'N/A' }}</p>
              <p><strong>Submitted by:</strong> {{ $opportunity['submitted_by_name'] ?? 'N/A' }}</p>

@php
  $isIntermittent = !empty($opportunity['is_intermittent']) && (int)$opportunity['is_intermittent'] === 1;
@endphp

@if($isIntermittent)
  <h6 class="fw-bold">Schedule (Intermittent)</h6>
  <div class="mt-2 table-responsive">
    <table class="table text-center align-middle table-bordered">
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
        @forelse($opportunity['schedule'] ?? [] as $schedule)
          <tr>
            <td>{{ \Carbon\Carbon::parse($schedule['date'])->format('F j, Y') }}</td>
            <td>
              {{ isset($schedule['start_time'])
                  ? \Carbon\Carbon::parse($schedule['start_time'])->format('g:i A')
                  : 'N/A' }}
            </td>
            <td>
              {{ isset($schedule['end_time'])
                  ? \Carbon\Carbon::parse($schedule['end_time'])->format('g:i A')
                  : 'N/A' }}
            </td>
            <td>{{ $schedule['target_number'] ?? 'N/A' }}</td>
            <td>
              @php
                $food = strtolower($schedule['food_provided'] ?? 'no');
              @endphp
{{ $food === 'yes' ? 'Yes' : 'No' }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-muted">No schedule details available.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@else
  @php $s = $opportunity['schedule'][0] ?? null; @endphp
  <h6 class="fw-bold">Schedule</h6>
  <table class="table mt-2 text-center align-middle table-bordered">
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
        <td>{{ $s ? \Carbon\Carbon::parse($s['date'])->format('F j, Y') : 'N/A' }}</td>
        <td>{{ $s && isset($s['start_time']) ? \Carbon\Carbon::parse($s['start_time'])->format('g:i A') : 'N/A' }}</td>
        <td>{{ $s && isset($s['end_time']) ? \Carbon\Carbon::parse($s['end_time'])->format('g:i A') : 'N/A' }}</td>
        <td>{{ $s['target_number'] ?? 'N/A' }}</td>
        <td>
          @php
            $food = strtolower($s['food_provided'] ?? 'no');
          @endphp
{{ $food === 'yes' ? 'Yes' : 'No' }}
        </td>
      </tr>
    </tbody>
  </table>
@endif

            </div>

            {{-- Task Details --}}
            <div class="col-md-4">
              <h6 class="fw-bold">Task Details</h6>
              <ul>
                @foreach(explode("\n", $opportunity['task_details'] ?? '') as $task)
                  @if(trim($task) != '')
                    <li>{{ trim($task) }}</li>
                  @endif
                @endforeach
              </ul>
            </div>

            {{-- Skills Needed --}}
            <div class="col-md-4">
              <h6 class="fw-bold">Skills Needed</h6>
              <ul>
                @foreach(explode("\n", $opportunity['skills_needed'] ?? '') as $skill)
                  @if(trim($skill) != '')
                    <li>{{ trim($skill) }}</li>
                  @endif
                @endforeach
              </ul>
            </div>
          </div>

          <hr>

          {{-- Student Volunteers --}}
          <h5 class="fw-bold">
            Student Volunteers ({{ count($opportunity['participants'] ?? []) }})
          </h5>

          <div class="table-responsive">
<table class="table text-center align-middle table-bordered">
  <thead>
    <tr class="table-header-row">
      <th>Name</th>
      <th>Time In</th>
      <th>Time Out</th>
      <th>Total Hours</th>
      <th>Feedback</th>
    </tr>
  </thead>


              <tbody>
                @forelse($opportunity['participants'] ?? [] as $vol)
                  <tr>
                    <td>{{ $vol['name'] }}</td>
                    <td>{{ $vol['time_in'] ?? 'N/A' }}</td>
                    <td>{{ $vol['time_out'] ?? 'N/A' }}</td>
                    <td>{{ $vol['hours'] ?? 'N/A' }}</td>
                    <td>{{ $vol['feedback'] ?? 'N/A' }}</td>
                  </tr>
                @empty
                  <tr><td colspan="5" class="text-muted">No volunteers yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
