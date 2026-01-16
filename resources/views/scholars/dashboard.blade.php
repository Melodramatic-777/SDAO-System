@extends('scholars.layout')

@section('content')

<!-- OUTER FLEX CONTAINER -->
<div class="dashboard-grid align-items-start" style="display:grid; grid-template-columns: 1fr 380px; gap:16px;">

    <!-- ================= LEFT MAIN CONTENT ================= -->
    <div class="main-content flex-grow-1">

<div class="mb-4 top-dashboard-section">
        <div class="flex-grow-1">
        <div class="gap-4 volunteer-progress-container d-flex align-items-start">

    <!-- ================= CHART COLUMN ================= -->
    <div class="flex-grow-1 d-flex flex-column">

        <!-- LABEL -->
        <h4 style="margin:0 0 12px 0; color:var(--nu-blue);">
            Your Volunteer Progress
        </h4>

        <!-- CHART CARD -->
<div class="chart-section"
     style="
        background-color:#fff;
        border-radius:12px;
        padding:20px;
        box-shadow:0 2px 8px rgba(0,0,0,0.1);
        max-width:100%;
     ">


<div class="chart-grid">

<div class="text-center chart-box">
<div class="chart-wrapper large">
        <canvas id="officeVolunteerChart"></canvas>
    </div>

                    <p class="mt-2 text-muted">Office Volunteer Hours</p>
                </div>

<div class="text-center chart-box">
    <div class="chart-wrapper large">
        <canvas id="progressCircleChart"></canvas>
    </div>

                    <p class="mt-2 text-muted">Completed the required minimum 10 hours</p>
                </div>

            </div>
        </div>
    </div>

    <!-- ================= STATUS CARD COLUMN ================= -->
<div class="status-column d-flex flex-column">

        <!-- LABEL -->
        <h4 style="margin:0 0 12px 0; color:var(--nu-blue);">
            Volunteer Hour Rank
        </h4>

        <!-- STATUS CARDS -->
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

            </div>
        </div>


<div class="gap-4 mb-4 d-flex align-items-center">

    <span class="status-tab active" id="tabUpcoming">
        Upcoming Tasks
    </span>

    <span class="status-tab" id="tabAccomplished">
        Accomplished Tasks
    </span>

</div>

<div id="upcomingTasksContainer" class="gap-3 d-flex flex-column">
    @if($joinedOpportunities->isEmpty())
        <p class="text-muted">You haven’t joined any opportunities.</p>
    @else
        @foreach($joinedOpportunities as $opportunity)
            @php
                $firstSchedule = collect($opportunity['schedule'] ?? [])
                    ->sortBy('date')
                    ->first();

                $cardDate = !empty($firstSchedule['date'])
                    ? \Carbon\Carbon::parse($firstSchedule['date'])->format('F j, Y')
                    : null;

                $cardTime = (!empty($firstSchedule['start_time']) && !empty($firstSchedule['end_time']))
                    ? \Carbon\Carbon::parse($firstSchedule['start_time'])->format('g:i A')
                        . ' - ' . \Carbon\Carbon::parse($firstSchedule['end_time'])->format('g:i A')
                    : null;
            @endphp

            <div class="p-3 mb-3 border rounded shadow-sm card opportunity-card">
                <h5 class="mb-1 card-title fw-bold">{{ $opportunity['title'] ?? 'No Title' }}</h5>

                <p class="mb-1"><strong>Office:</strong> {{ $opportunity['submitted_by_role'] ?? 'N/A' }} ({{ $opportunity['submitted_by_name'] ?? 'N/A' }})</p>
                                <div class="d-flex justify-content-between align-items-center">

                <p class="mb-1"><strong>Location:</strong> {{ $opportunity['location'] ?? 'N/A' }}</p>

                {{-- Flex row for Submitted By + View Details --}}

                    <button class="view-details-btn float-end"
                        data-bs-toggle="modal"
                        data-bs-target="#joinedOpportunityModal{{ $opportunity['opportunity_id'] }}">
                        View Details
                    </button>
                </div>

              
            </div>
       



       {{-- Modal --}}
<div class="modal fade" id="joinedOpportunityModal{{ $opportunity['opportunity_id'] }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content custom-modal-content">

            <div class="modal-header">
                <h5 class="modal-title">{{ $opportunity['title'] ?? 'Untitled Opportunity' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body custom-modal-body row g-4">

                {{-- Left Panel --}}
                <div class="col-md-6 left-panel">
                    <p><strong>Office:</strong> {{ $opportunity['submitted_by_role'] ?? 'N/A' }}</p>
                    <hr>
                    <p><strong>Submitted By:</strong> {{ $opportunity['submitted_by_name'] ?? 'N/A' }}</p>
                    <hr>
                    <p><strong>Location:</strong> {{ $opportunity['location'] ?? 'N/A' }}</p>
                    <hr>
                    <h6><strong>Description</strong></h6>
                    <div>{{ $opportunity['description'] ?? 'No description provided.' }}</div>
                    <hr>

                    {{-- Intermittent Table --}}
                    @php
                        $isIntermittent = !empty($opportunity['is_intermittent']) && $opportunity['is_intermittent'] == true;
                    @endphp

                    @if($isIntermittent)
                        <h6 class="mb-3 fw-bold">Intermittent Schedule</h6>
                        <div class="table-responsive">
                            <table class="table align-middle shadow-sm table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">Date</th>
                                        <th class="text-center">Time</th>
                                        <th class="text-center">Food Provided</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($opportunity['schedule'] ?? [] as $s)
                                        <tr>
                                            <td class="text-center">{{ !empty($s['date']) ? \Carbon\Carbon::parse($s['date'])->format('F j, Y') : 'N/A' }}</td>
                                            <td class="text-center">
                                                @if(!empty($s['start_time']) && !empty($s['end_time']))
                                                    {{ \Carbon\Carbon::parse($s['start_time'])->format('g:i A') }}
                                                    – {{ \Carbon\Carbon::parse($s['end_time'])->format('g:i A') }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="text-center">{{ strtolower($s['food_provided'] ?? 'no') === 'yes' ? 'Yes' : 'No' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No schedule details provided</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- Participants List --}}
                    @if(!empty($opportunity['participants']))
                        <hr>
                        <h6><strong>Participants</strong></h6>
                        <ul class="list-group list-group-flush">
                            @foreach($opportunity['participants'] as $participant)
                                @php
                                    $displayStatus = strtolower($participant['status'] ?? '') === 'declined' ? 'Invite Declined' : ($participant['status'] ?? 'Joined');
                                @endphp
                                <li class="px-0 list-group-item">
                                    {{ $participant['name'] ?? 'Unnamed' }} 
                                    <span class="text-muted">({{ $displayStatus }})</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- Right Panel --}}
      {{-- Right Panel --}}
{{-- Right Panel --}}
<div class="col-md-6 right-panel">
    @php
        $participant = collect($opportunity['participants'] ?? [])->firstWhere('scholar_id', session('scholar_accounts.student_no'));
        $isIntermittent = !empty($opportunity['is_intermittent']) && $opportunity['is_intermittent'] == true;

        if ($isIntermittent) {
            $joinedDates = $participant['selected_dates'] ?? [];
            $tasksByDate = $participant['tasks'] ?? [];
        } else {
            $selectedTasks = $participant['tasks']['regular'] ?? [];
        }
    @endphp

    @if($isIntermittent && !empty($joinedDates))
        <h6 class="mb-3 fw-bold">My Joined Tasks</h6>
        @foreach($joinedDates as $date)
            @php
                $tasksForDate = $tasksByDate[$date] ?? [];
            @endphp
            <p><strong>{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</strong></p>
            @if(!empty($tasksForDate))
                <ul class="mb-3">
                    @foreach($tasksForDate as $task)
                        <li>{{ $task }}</li>
                    @endforeach
                </ul>
            @else
                <p class="mb-3 text-muted">No tasks for this date.</p>
            @endif
        @endforeach
    @elseif(!empty($selectedTasks))
        <h6 class="mb-3 fw-bold">My Joined Tasks</h6>
        <ul>
            @foreach($selectedTasks as $task)
                <li>{{ $task }}</li>
            @endforeach
        </ul>
    @else
        <p class="text-muted">No tasks joined.</p>
    @endif
</div>



            </div>
        </div>
    </div>
</div>
{{-- End Modal --}}


    @endforeach
@endif


          <div id="accomplishedTasksContainer" style="display: none;">
            <p class="mt-3 text-center text-muted">No accomplished tasks.</p>
          </div>

          <div class="mt-4 d-flex justify-content-center">
            {{ $pagination->links('pagination::bootstrap-5') }}
          </div>

    </div> <!-- END LEFT MAIN CONTENT -->



</div>
<!-- END OUTER FLEX CONTAINER -->



<!-- Scripts remain unchanged -->
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
    data: { datasets: [{ data: [100, 0], backgroundColor: ['#0056b3', '#e0e0e0'], cutout: '80%' }] },
    options: {
      plugins: {
        tooltip: { enabled: false },
        legend: { display: false },
        title: { display: true, text: '100%', position: 'center' }
      }
    },
    plugins: [{
      id: 'textCenter',
      afterDraw(chart) {
        const { ctx } = chart;
        ctx.save();
        ctx.font = 'bold 24px Inter';
        ctx.fillStyle = '#0056b3';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('100%', chart.width / 2, chart.height / 2);
      }
    }]
  });

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