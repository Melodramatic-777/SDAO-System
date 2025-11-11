<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>{{ $office }} - Opportunities</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />  
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

<main class="p-4 container-fluid">

  <a href="{{ route('volunteers-admin.history') }}" class="text-decoration-none">
    ← Back to History
  </a>

  
  <h2 class="mb-4 text-center">{{ $office }} Opportunities</h2>

  <div class="row g-4">
      @forelse ($opportunities as $opportunity)

          {{-- ✅ Only show Approved or Returned --}}
          @if(!in_array($opportunity['status'] ?? '', ['Approved', 'Returned']))
              @continue
          @endif

          <div class="col-md-4">
              <div class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#opportunityModal{{ $opportunity['opportunity_id'] }}">
                  <div class="p-4 text-center folder-card">
                      <div class="mb-3 fs-1 text-warning"><i class="bi bi-folder-fill"></i></div>
                      <div class="folder-title">{{ $opportunity['title'] }}</div>
                      <div class="mt-2 small text-muted">{{ $opportunity['submitted_at'] }}</div>
                  </div>
              </div>
          </div>

      {{-- ✅ Modal for each opportunity --}}
      <div class="modal fade" id="opportunityModal{{ $opportunity['opportunity_id'] }}" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered custom-wide-modal">
          <div class="modal-content" style="border-radius: 8px; padding: 1.5rem;">
<button type="button" class="modal-close-x" data-bs-dismiss="modal">×</button>



            <div class="modal-body">

              {{-- Opportunity Title --}}
              <div class="mb-4 d-flex flex-column align-items-center">

                <div class="gap-4 d-flex">
                </div>
              </div>

              <hr>

              {{-- 1st Row Layout - 3 Columns --}}
              <div class="row">
                {{-- Opportunity Details --}}
                <div class="col-md-4">
                <h4 class="fw-bold">{{ $opportunity['title'] }}</h4>
                <p class="text-muted">At Room: <strong>{{ $opportunity['location'] ?? 'N/A' }}</strong></p>
                  <p><strong>Submitted by:</strong> {{ $opportunity['submitted_by_name'] ?? 'N/A' }}</p>


                   <div>
                    <strong>Date:</strong> 
                    {{ isset($opportunity['schedule'][0]['date']) ? \Carbon\Carbon::parse($opportunity['schedule'][0]['date'])->format('F j, Y') : 'N/A' }}
                  </div>
                  <div>
                    <strong>Time:</strong> 
                    @php
                      $isIntermittent = !empty($opportunity['is_intermittent']) && (int)$opportunity['is_intermittent'] === 1;
                    @endphp

                    @if($isIntermittent)
                      {{-- Multiple Dates for Intermittent Schedule --}}
                      @foreach($opportunity['schedule'] ?? [] as $schedule)
                        <div>
                          <strong>{{ \Carbon\Carbon::parse($schedule['date'])->format('F j, Y') }}:</strong> 
                          @if(isset($schedule['start_time']) && isset($schedule['end_time']))
                            {{ \Carbon\Carbon::parse($schedule['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($schedule['end_time'])->format('g:i A') }}
                          @else
                            N/A
                          @endif
                        </div>
                      @endforeach
                    @else
                      {{-- Regular Schedule (single date) --}}
                      @php
                        $schedule = $opportunity['schedule'][0] ?? null;
                      @endphp
                      @if ($schedule && isset($schedule['start_time']) && isset($schedule['end_time']))
                        {{ \Carbon\Carbon::parse($schedule['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($schedule['end_time'])->format('g:i A') }}
                      @else
                        N/A
                      @endif
                    @endif
                  </div>
                </div>

                {{-- Task Details --}}
                <div class="col-md-4">
                  <h6 class="fw-bold">Task details</h6>
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
                  <h6 class="fw-bold">Skills needed</h6>
                  <ul>
                    @foreach(explode("\n", $opportunity['skills_needed'] ?? '') as $skill)
                      @if(trim($skill) != '')
                        <li>{{ trim($skill) }}</li>
                      @endif
                    @endforeach
                  </ul>
                </div>
              </div>

              {{-- 2nd Row Layout --}}
              <div class="row">
                {{-- Student Volunteers Table --}}
                <div class="col-md-12">
                  <h5 class="fw-bold">
                    Student Volunteers 
                    <i class="bi bi-people-fill"></i>
                    {{ count($opportunity['participants'] ?? []) }} / {{ $opportunity['schedule'][0]['target_number'] ?? '?' }}
                  </h5>

                  <div class="table-responsive">
                    <table class="table text-center align-middle table-bordered">
                      <thead style="background: #f6d629;">
                        <tr>
                          <th>Name</th>
                          <th>Time in</th>
                          <th>Time out</th>
                          <th>Total Hours</th>
                          <th>Feedback</th>
                          <th>Your Feedback</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($opportunity['participants'] ?? [] as $participant)
                          <tr>
                            <td>{{ $participant['name'] }}</td>
                            <td>N/A</td>
                            <td>N/A</td>
                            <td>N/A</td>
                            <td>N/A</td>
                            <td>N/A</td>
                          </tr>
                        @empty
                          <tr><td colspan="6" class="text-muted">No volunteers yet.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

            </div>

          </div>
        </div>
      </div>

      @empty
          <p class="text-center text-muted">No opportunities for this office.</p>
      @endforelse
  </div>


</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
