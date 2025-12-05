@extends('scholars.layout')

@section('content')





<div class="flex-wrap mb-4 volunteer-progress-container d-flex justify-content-between align-items-stretch">

  <!-- LEFT SIDE: Charts -->
  <div class="chart-section flex-grow-1 me-4 d-flex flex-column justify-content-between">
    <h4 class="mb-3">Your Volunteer Progress</h4>

    <div class="flex-wrap gap-4 d-flex justify-content-center align-items-center flex-grow-1">
      <!-- Pie Chart -->
      <div class="text-center chart-box">
        <canvas id="officeVolunteerChart" width="320" height="320"></canvas>
        <p class="mt-2 text-muted"><em>Office Volunteer Hours</em></p>
      </div>

      <!-- Circular Progress -->
      <div class="text-center chart-box">
        <canvas id="progressCircleChart" width="220" height="220"></canvas>
        <p class="mt-2 text-muted"><em>Completed the required minimum 10 hours</em></p>
      </div>
    </div>
  </div>

  <!-- RIGHT SIDE: Status Cards -->
<div style="width: 500px;">
  <h4 class="mb-3">Volunteer Hour Rank:</h4>

  <div class="status-cards status-grid">
    <div class="status-card hoursCompleted">
      <h6>Hours Completed</h6>
      <h3>--</h3>
    </div>
    <div class="status-card activitiesParticipated">
      <h6>Activities Participated</h6>
      <h3>--</h3>
    </div>
    <div class="status-card hoursMonth">
      <h6>Hours This Month</h6>
      <h3>--</h3>
    </div>
    <div class="status-card remainingHours">
      <h6>Remaining Hours</h6>
      <h3>--</h3>
    </div>
  </div>
</div>

</div>




<h4 class="mb-4">My Joined Volunteer Opportunities</h4>

<!-- 🔹 Tabs Navigation -->
@php
  $upcomingCount = collect($joinedOpportunities ?? [])->filter(function ($op) {
      $status = strtolower($op['status'] ?? '');
      return in_array($status, ['approved', 'urgent', 'ongoing']);
  })->count();

  $accomplishedCount = collect($joinedOpportunities ?? [])->filter(function ($op) {
      $status = strtolower($op['status'] ?? '');
      return in_array($status, ['accomplished', 'completed', 'done']);
  })->count();
@endphp

<!-- 🔹 Tabs Navigation -->
<div class="gap-4 mb-4 status-filter d-flex">
  <span class="status-tab active" id="tabUpcoming">
    Upcoming Tasks
    <span class="text-white badge bg-primary ms-1" id="countUpcoming">{{ $upcomingCount }}</span>
  </span>
  <span class="status-tab" id="tabAccomplished">
    Accomplished Tasks
    <span class="badge bg-success ms-1" id="countAccomplished">{{ $accomplishedCount }}</span>
  </span>
</div>


@if($joinedOpportunities->isEmpty())
  <p class="text-muted">You haven’t joined any opportunities yet.</p>
@else
  <!-- 🔹 Upcoming Tasks Container -->
  <div id="upcomingTasksContainer">
    @foreach($joinedOpportunities as $opportunity)
      @php
        $participantsCount = count($opportunity['participants'] ?? []);
        $targetNumber = (int) ($opportunity['schedule'][0]['target_number'] ?? 0);
      @endphp

      <div class="p-3 mb-3 border rounded status-card-container"
           data-status="{{ strtolower($opportunity['status'] ?? '') }}"
           data-participants="{{ count($opportunity['participants'] ?? []) }}"
           data-target="{{ (int) ($opportunity['schedule'][0]['target_number'] ?? 0) }}">
        <div class="d-flex justify-content-between align-items-center">
          <strong>{{ $opportunity['title'] ?? 'No Title' }}</strong>
        </div>

        <div>
          <p class="mb-1">{{ $opportunity['location'] ?? 'No Location' }}</p>
          <small>Submitted by: {{ $opportunity['submitted_by_name'] ?? 'N/A' }}</small><br>

          <small>
            @php
              $participant = collect($opportunity['participants'] ?? [])
                  ->firstWhere('scholar_id', session('scholar_accounts.student_no'));

              $joinedIndices = $participant['schedule_indices'] ?? [];
              $isIntermittent = !empty($opportunity['is_intermittent']) && (int)$opportunity['is_intermittent'] === 1;
            @endphp

            @if($isIntermittent && !empty($joinedIndices))
              @php
                $joinedSchedules = collect($opportunity['schedule'] ?? [])
                    ->only($joinedIndices)
                    ->filter(fn($s) => !empty($s['date']))
                    ->map(fn($s) => \Carbon\Carbon::parse($s['date']))
                    ->sort()
                    ->values();

                $startDate = $joinedSchedules->first();
                $endDate = $joinedSchedules->last();
              @endphp

              <strong>Schedule Joined:</strong><br>
              @if($startDate && $endDate && !$startDate->equalTo($endDate))
                {{ $startDate->format('F j, Y') }} – {{ $endDate->format('F j, Y') }}
              @elseif($startDate)
                {{ $startDate->format('F j, Y') }}
              @else
                N/A
              @endif
            @else
              @php
                $s = $opportunity['schedule'][$participant['schedule_index'] ?? 0] ?? null;
              @endphp

              <strong>Date:</strong>
              {{ !empty($s['date']) ? \Carbon\Carbon::parse($s['date'])->format('F j, Y') : 'N/A' }} <br>

              <strong>Time:</strong>
              @if($s && isset($s['start_time'], $s['end_time']))
                {{ \Carbon\Carbon::parse($s['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($s['end_time'])->format('g:i A') }}
              @else
                N/A
              @endif
            @endif
          </small>

          <button class="view-details-btn float-end" data-bs-toggle="modal" data-bs-target="#joinedOpportunityModal{{ $opportunity['id'] }}">
            View Details
          </button>
        </div>
      </div>

        {{-- ✅ Modal (unchanged) --}}
        <div class="modal fade" id="joinedOpportunityModal{{ $opportunity['id'] }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
          <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content custom-modal-content">

              <div class="modal-header">
                <h5 class="modal-title">{{ $opportunity['title'] ?? 'Untitled Opportunity' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body custom-modal-body row g-4">
                {{-- LEFT PANEL --}}
                <div class="col-md-6 left-panel">
                  <p><strong>Office:</strong> {{ $opportunity['submitted_by_role'] ?? 'N/A' }}</p>
                  <p><strong>Submitted By:</strong> {{ $opportunity['submitted_by_name'] ?? 'N/A' }}</p>
                  <p><strong>Location:</strong> {{ $opportunity['location'] ?? 'N/A' }}</p>

@php
  $isIntermittent = !empty($opportunity['is_intermittent']) && (int)$opportunity['is_intermittent'] === 1;
  $currentScholarId = session('scholar_accounts.student_no');
  $participant = collect($opportunity['participants'] ?? [])->firstWhere('scholar_id', $currentScholarId);
  $joinedIndices = $participant['schedule_indices'] ?? [];
  $schedules = $opportunity['schedule'] ?? [];
@endphp

<div class="table-responsive">
  <table class="table align-middle table-sm table-bordered">
    <thead class="table-light">
      <tr>
        <th>Date</th>
        <th>Time</th>
      </tr>
    </thead>
    <tbody>
      @if($isIntermittent)
        {{-- ✅ Intermittent: only joined schedules --}}
        @if(!empty($joinedIndices))
          @foreach($joinedIndices as $index)
            @php $s = $schedules[$index] ?? null; @endphp
            @if($s && !empty($s['date']))
              <tr>
                <td>
                  {{ \Carbon\Carbon::parse($s['date'])->format('F j, Y') }}
                  @if(strtolower($opportunity['status'] ?? '') === 'urgent')
                    <span class="badge bg-danger ms-2">Urgent</span>
                  @endif
                </td>
                <td>
                  @if(!empty($s['start_time']) && !empty($s['end_time']))
                    {{ \Carbon\Carbon::parse($s['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($s['end_time'])->format('g:i A') }}
                  @else
                    N/A
                  @endif
                </td>
              </tr>
            @endif
          @endforeach
        @else
          <tr><td colspan="2" class="text-center text-muted">No joined dates available</td></tr>
        @endif
      @else
        {{-- ✅ Regular: all schedules --}}
        @if(!empty($schedules))
          @foreach($schedules as $s)
            @if(!empty($s['date']))
              <tr>
                <td>
                  {{ \Carbon\Carbon::parse($s['date'])->format('F j, Y') }}
                  @if(strtolower($opportunity['status'] ?? '') === 'urgent')
                    <span class="badge bg-danger ms-2">Urgent</span>
                  @endif
                </td>
                <td>
                  @if(!empty($s['start_time']) && !empty($s['end_time']))
                    {{ \Carbon\Carbon::parse($s['start_time'])->format('g:i A') }} – {{ \Carbon\Carbon::parse($s['end_time'])->format('g:i A') }}
                  @else
                    N/A
                  @endif
                </td>
              </tr>
            @endif
          @endforeach
        @else
          <tr><td colspan="2" class="text-center text-muted">No schedule available</td></tr>
        @endif
      @endif
    </tbody>
  </table>
</div>
                  
                </div>

                {{-- RIGHT PANEL --}}
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
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>


  <div id="accomplishedTasksContainer" style="display: none;">
    <p class="mt-3 text-center text-muted">No accomplished tasks yet.</p>
  </div>

    <div class="mt-4 d-flex justify-content-center">
      {{ $pagination->links('pagination::bootstrap-5') }}
    </div>
  @endif



<script>

document.addEventListener("DOMContentLoaded", function () {
    Chart.register(ChartDataLabels);

const ctx1 = document.getElementById('officeVolunteerChart').getContext('2d');
new Chart(ctx1, {
  type: 'pie',
  data: {
    labels: ['IT Systems Office', 'Faculty', 'SDAO'],
    datasets: [{
      data: [9, 2, 3],
      backgroundColor: ['#457b9d', '#a8dadc', '#1d3557'],
      borderWidth: 2,
    }]
  },
  options: {
    layout: { padding: 10 },
    plugins: {
      legend: { display: false },
      datalabels: {
        color: '#fff',
        font: { weight: 'bold', size: 14 },
        formatter: (value, ctx) => {
          const label = ctx.chart.data.labels[ctx.dataIndex];
          return `${label}\n${value}`;
        },
        anchor: 'center',
        align: 'center',
        clamp: true,
        clip: false
      }
    }
  },
  plugins: [ChartDataLabels]
});


  const ctx2 = document.getElementById('progressCircleChart').getContext('2d');
  new Chart(ctx2, {
    type: 'doughnut',
    data: {
      datasets: [{
        data: [100, 0],
        backgroundColor: ['#0056b3', '#e0e0e0'],
        cutout: '80%',
      }]
    },
    options: {
      plugins: {
        tooltip: { enabled: false },
        legend: { display: false },
        title: {
          display: true,
          text: '100%',
          position: 'center'
        }
      }
    },
    plugins: [{
      id: 'textCenter',
      afterDraw(chart) {
        const { ctx, chartArea: { width, height } } = chart;
        ctx.save();
ctx.font = 'bold 24px Inter';
        ctx.fillStyle = '#0056b3';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
ctx.fillText('100%', chart.width / 2, chart.height / 2);
      }
    }]
  });
});


document.addEventListener("DOMContentLoaded", function () {
  const tabUpcoming = document.getElementById("tabUpcoming");
  const tabAccomplished = document.getElementById("tabAccomplished");
  const upcomingContainer = document.getElementById("upcomingTasksContainer");
  const accomplishedContainer = document.getElementById("accomplishedTasksContainer");

  tabUpcoming.addEventListener("click", () => {
    tabUpcoming.classList.add("active");
    tabAccomplished.classList.remove("active");
    upcomingContainer.style.display = "block";
    accomplishedContainer.style.display = "none";
  });

  tabAccomplished.addEventListener("click", () => {
    tabAccomplished.classList.add("active");
    tabUpcoming.classList.remove("active");
    upcomingContainer.style.display = "none";
    accomplishedContainer.style.display = "block";
  });
});
</script>

@endsection
