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
      max-width: 95% !important; /* Make the modal wider */
    }


    .modal-backdrop {
      z-index: -1 !important;
    }

    .modal-header {
      border-bottom: none;
    }

    .modal-body {
      padding: 2rem;
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

    .table {
      background-color: #ffffff;
      border-radius: 8px;
    }

    .table th {
      background-color: #f6d629;
      color: #000;
      font-weight: bold;
    }

    .table td, .table th {
      text-align: center;
      padding: 15px;
    }

    .table-bordered {
      border: 1px solid #ddd;
    }

    .table-responsive {
      margin-top: 20px;
    }

    .section-title {
      font-size: 1.25rem;
      font-weight: bold;
      color: #333;
    }

    .row .col-md-4 {
      margin-bottom: 20px;
    }

    /* Modal layout - three columns for the modal content */
.modal-dialog.modal-xxl {
  max-width: 95% !important;
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
  margin: 0;                     /* Remove auto margins */
  max-width: 100% !important;    /* Ignore Bootstrap max width */
  width: 100% !important;        /* Take full width */
}

/* Ensure the modal content touches left/right edges cleanly */
.custom-wide-modal .modal-content {
  width: 100%;
  border-radius: 8px;
  padding: 2rem;                /* Adjust interior spacing */
}

.modal.show .modal-dialog {
  margin: 0 !important;
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
    <h2 class="mb-4 text-center">Your Submitted Opportunities</h2>

 <div class="row g-4">

    @forelse ($opportunities as $opportunity)
        <div class="col-md-4">
            <div class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#opportunityModal{{ $opportunity['opportunity_id'] }}">
                <div class="p-4 text-center folder-card">
                    <div class="mb-3 fs-1 text-warning"><i class="bi bi-folder-fill"></i></div>
                    <div class="folder-title">{{ $opportunity['title'] }}</div>
                    <div class="mt-2 small text-muted">{{ $opportunity['submitted_at'] }}</div>
                </div>
            </div>
        </div>

        {{-- ✅ Modal --}}
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
                        <strong>Schedule:</strong><br>
                        @foreach($opportunity['schedule'] ?? [] as $schedule)
                          <div>
                            {{ \Carbon\Carbon::parse($schedule['date'])->format('F j, Y') }}:
                            @if(isset($schedule['start_time']) && isset($schedule['end_time']))
                              {{ \Carbon\Carbon::parse($schedule['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($schedule['end_time'])->format('g:i A') }}
                            @else
                              N/A
                            @endif
                          </div>
                        @endforeach
                      @else
                        @php $s = $opportunity['schedule'][0] ?? null; @endphp
                        <p><strong>Date:</strong> {{ $s ? \Carbon\Carbon::parse($s['date'])->format('F j, Y') : 'N/A' }}</p>
                        <p><strong>Time:</strong>
                          @if($s && isset($s['start_time']) && isset($s['end_time']))
                            {{ \Carbon\Carbon::parse($s['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($s['end_time'])->format('g:i A') }}
                          @else
                            N/A
                          @endif
                        </p>
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
                      <thead style="background: #f6d629;">
                        <tr>
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

    @empty
        <p class="text-center text-muted">No opportunities found.</p>
    @endforelse

</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
