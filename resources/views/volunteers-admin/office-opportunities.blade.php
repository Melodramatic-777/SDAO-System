@extends('volunteers-admin.layout')

@section('content')

<header class="custom-header">
    <div class="header-left">

        <!-- SIDE BUTTON MOVED HERE -->
<button type="submit" class="side-button header-btn">
    <img src="{{ asset('images/Side.png') }}" alt="Side" height="30">
</button>


        <div class="logo">
            <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
            <span>Student Developments and Activities Office</span>
        </div>
    </div>

    <div class="header-right-group">
        <p>Welcome, {{ session('volunteers_admin.first_name') }}!</p>
    </div>
</header>

 

  <main class="p-4 container-fluid">
<a href="{{ route('volunteers-admin.history') }}" class="back-link text-decoration-none">
  ← Back to History
</a>

    <h2 class="mb-4 text-center">{{ $office }} Opportunities</h2>

    <div class="table-responsive">
      <table class="table mx-auto text-center align-middle table-bordered">
        <thead>
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
            @if(!in_array($opportunity['status'] ?? '', ['Approved', 'Returned']))
              @continue
            @endif
            <tr>
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
            <tr><td colspan="5" class="text-center text-muted">No opportunities for this office.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- ✅ Modals --}}
    @foreach ($opportunities as $opportunity)
      <div class="modal fade" id="opportunityModal{{ $opportunity['opportunity_id'] }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered custom-wide-modal">
          <div class="modal-content">
            <button type="button" class="modal-close-x" data-bs-dismiss="modal">×</button>
            <div class="modal-body">
              <h3 class="mb-4 text-center fw-bold">{{ $opportunity['title'] }}</h3>
              <hr>

              <div class="row">
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
<p><strong>Date:</strong>
  @if($s && !empty($s['date']))
    {{ \Carbon\Carbon::parse($s['date'])->format('F j, Y') }}
  @else
    N/A
  @endif
</p>
                    <p><strong>Time:</strong>
                      @if($s && isset($s['start_time']) && isset($s['end_time']))
                        {{ \Carbon\Carbon::parse($s['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($s['end_time'])->format('g:i A') }}
                      @else
                        N/A
                      @endif
                    </p>
                  @endif
                </div>

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

              <h5 class="fw-bold">Student Volunteers ({{ count($opportunity['participants'] ?? []) }})</h5>
              <div class="table-responsive">
                <table class="table text-center align-middle table-bordered">
                  <thead class="table-header-row">
                    <tr>
                      <th>#</th>
                      <th>Name</th>
                      <th>Time In</th>
                      <th>Time Out</th>
                      <th>Total Hours</th>
                      <th>Feedback</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($opportunity['participants'] ?? [] as $i => $vol)
                      <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $vol['name'] }}</td>
                        <td>{{ $vol['time_in'] ?? 'N/A' }}</td>
                        <td>{{ $vol['time_out'] ?? 'N/A' }}</td>
                        <td>{{ $vol['hours'] ?? 'N/A' }}</td>
                        <td>{{ $vol['feedback'] ?? 'N/A' }}</td>
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
    @endforeach
  </main>

@endsection
