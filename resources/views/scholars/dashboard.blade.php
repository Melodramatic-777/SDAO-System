@extends('scholars.layout')

@section('content')

<!-- OUTER FLEX CONTAINER: LEFT MAIN CONTENT + RIGHT OTSV -->
<div class="gap-4 dashboard-grid d-flex align-items-start">


    <!-- LEFT MAIN CONTENT -->
    <div class="main-content flex-grow-1">

        <div class="gap-4 mb-4 d-flex align-items-start">

            <div class="flex-grow-1">
                <div class="flex-wrap volunteer-progress-container d-flex justify-content-between align-items-stretch">
                                        <div class="chart-section flex-grow-1 me-4 d-flex flex-column justify-content-between">

    <h4 class="mb-3">Your Volunteer Progress</h4>

    <div class="flex-wrap gap-4 d-flex justify-content-center align-items-center flex-grow-1">
        <!-- Pie Chart -->
        <div class="text-center chart-box">
            <canvas id="officeVolunteerChart" width="320" height="320"></canvas>
            <p class="mt-2 text-muted">Office Volunteer Hours</p>
        </div>

        <!-- Circular Progress -->
        <div class="text-center chart-box">
            <canvas id="progressCircleChart" width="220" height="220"></canvas>
            <p class="mt-2 text-muted">Completed the required minimum 10 hours</p>
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


         
 <!-- RIGHT PANEL: OTSV -->
  <!-- RIGHT PANEL: OTSV -->
<aside class="shadow-sm otsv-panel"
       style="
           width: 380px;
           padding: 20px;
           background: #fff;
           border-radius: 12px;
           flex-shrink: 0;
           position: sticky;
           top: 0;

       ">


    <h3><strong>My On the Spot Volunteers</strong></h3>

  <ul class="otsv-list" style="padding: 0; margin: 0; list-style: none;">
    @forelse ($otsvList as $v)
       <li class="otsv-item" style="margin-bottom: 18px; padding: 12px; border: 1px solid #ddd; border-radius: 10px;">

    {{-- Accepted Badge (TOP) --}}
    @if(isset($v['status']) && $v['status'] === 'accepted')
        <div class="mb-2 text-end">
            <span class="text-white badge bg-success">
                <i class="bi bi-check-circle-fill me-1"></i> Accepted
            </span>
        </div>
    @endif

    <strong>{{ $v['student_name'] }}</strong><br>

    <small>
        Available Date: {{ \Carbon\Carbon::parse($v['available_date'])->format('F j, Y') }}<br>
        Available Time:
        {{ \Carbon\Carbon::parse($v['available_start_time'])->format('g:i A') }}
        – {{ \Carbon\Carbon::parse($v['available_end_time'])->format('g:i A') }}<br>

        @php
            $reasons = $v['reasons'] ?? [];
            $other = $v['other_reason'] ?? null;
        @endphp

        @if(!empty($reasons) || $other)
            <strong>Reason:</strong><br>
            @foreach($reasons as $reason)
                • {{ $reason }}<br>
            @endforeach
            @if($other)
                • {{ $other }}
            @endif
        @endif
    </small>

    {{-- Accepted By --}}
    @if(isset($v['status']) && $v['status'] === 'accepted' && isset($v['action_by']))
        <small class="mt-2 text-muted d-block">
            <strong>Accepted by:</strong>
            {{ $v['action_by']['name'] ?? 'N/A' }}<br>
            <em>
                {{ $v['action_by']['role'] ?? '' }}
                @if(!empty($v['action_by']['office']))
                    – {{ $v['action_by']['office'] }}
                @endif
            </em>
        </small>
    @else
        {{-- Cancel Button --}}
        @if(($v['student_id'] ?? null) === session('scholar_accounts.student_no'))
            <button type="button" class="mt-2 text-white btn btn-sm w-100"
                    style="background-color: #dc3545; border-color: #dc3545;"
                    data-bs-toggle="modal"
                    data-bs-target="#cancelModal{{ $v['id'] }}">
                Cancel
            </button>
        @endif
    @endif

</li>

    @empty
        <li>No on-the-spot volunteers.</li>
    @endforelse
</ul>


</aside>

     <h4 class="mb-4">Your Upcoming Tasks</h4>

@php
    // Filter only opportunities where this scholar's participation status is 'Joined'
    $joinedOpportunities = collect($joinedOpportunities ?? [])->filter(function ($op) {
        return collect($op['participants'] ?? [])->contains(function ($p) {
            return ($p['scholar_id'] ?? '') === session('scholar_accounts.student_no')
                && strtolower($p['status'] ?? '') === 'joined';
        });
    });
@endphp

@if($joinedOpportunities->isEmpty())
    <p class="text-muted">You haven’t joined any opportunities.</p>
@else
    @foreach($joinedOpportunities as $opportunity)
        @php
            // Get participant info for the logged-in scholar
            $participant = collect($opportunity['participants'])->firstWhere('scholar_id', session('scholar_accounts.student_no'));

            $isIntermittent = !empty($opportunity['is_intermittent']) && $opportunity['is_intermittent'] == true;
            $joinedDates = $participant['selected_dates'] ?? [];

            // For intermittent tasks, get start and end dates
            if ($isIntermittent && !empty($joinedDates)) {
                $joinedSchedules = collect($joinedDates)->map(fn($d) => \Carbon\Carbon::parse($d))->sort()->values();
                $startDate = $joinedSchedules->first();
                $endDate = $joinedSchedules->last();
            } else {
                // For regular tasks
                $schedule = $opportunity['schedule'] ?? [];
                $s = !empty($schedule['start_date']) ? $schedule['start_date'] : null;
            }
        @endphp

        @php
    // Get first schedule entry (earliest)
    $firstSchedule = collect($opportunity['schedule'] ?? [])
        ->sortBy('date')
        ->first();

    $cardDate = !empty($firstSchedule['date'])
        ? \Carbon\Carbon::parse($firstSchedule['date'])->format('F j, Y')
        : null;

    $cardTime = (!empty($firstSchedule['start_time']) && !empty($firstSchedule['end_time']))
        ? \Carbon\Carbon::parse($firstSchedule['start_time'])->format('g:i A')
            . ' - ' .
          \Carbon\Carbon::parse($firstSchedule['end_time'])->format('g:i A')
        : null;
@endphp


<div class="p-3 mb-3 bg-white border rounded status-card-container d-flex justify-content-between align-items-start">
<div class="task-card-content">

    <strong>{{ $opportunity['title'] ?? 'No Title' }}</strong>
    <br>

    <small class="mb-1 text-muted d-block">
        <i class="bi bi-building me-1"></i>
        {{ $opportunity['submitted_by_role'] ?? 'Office N/A' }}
    </small>

    <p class="mb-1">{{ $opportunity['location'] ?? 'No Location' }}</p>

    <small class="text-muted d-block">
        Submitted by: {{ $opportunity['submitted_by_name'] ?? 'N/A' }}
    </small>


@if($cardDate || $cardTime)
    <div class="flex-wrap gap-3 mt-2 d-flex align-items-center">

        @if($cardDate)
            <div class="gap-2 d-flex align-items-center small">
                <span class="fw-semibold">Date:</span>
                <span class="px-3 py-1 border rounded-pill bg-light">
                    {{ $cardDate }}
                </span>
            </div>
        @endif

        @if($cardTime)
            <div class="gap-2 d-flex align-items-center small">
                <span class="fw-semibold">Time:</span>
                <span class="px-3 py-1 border rounded-pill bg-light">
                    {{ $cardTime }}
                </span>
            </div>
        @endif

@endif

<div class="ms-auto d-flex align-items-start">
    <button class="view-details-btn"
            data-bs-toggle="modal"
            data-bs-target="#joinedOpportunityModal{{ $opportunity['opportunity_id'] }}">
        View Details
    </button>
</div>



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

  

</div> <!-- END OUTER FLEX CONTAINER -->



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