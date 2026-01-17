
@extends('scholars.layout')

@section('content')

<!-- OUTER GRID -->
<div class="dashboard-grid"
     style="display:grid; grid-template-columns: 1fr; gap:16px; align-items:start; max-width:100%; overflow-x:hidden;">




    <!-- ================= LEFT CONTENT ================= -->
    <div class="main-content" style="width:100%;">

        <!-- ================= PROGRESS + CHART ================= -->
<div class="mb-3">
    <div class="gap-3 d-flex align-items-start">


                <!-- ================= CHART SECTION ================= -->
          <!-- ================= CHART SECTION ================= -->
<!-- ================= CHART SECTION ================= -->
<!-- ================= CHART SECTION ================= -->
<!-- ================= CHART SECTION ================= -->
<div class="d-flex justify-content-start">
    <div>

        <h4 class="mb-3" style="color:var(--nu-blue);">
            Your Volunteer Progress
        </h4>

<div class="gap-4 d-flex">             <!-- Pie Chart Container -->
<div class="chart-container"
     style="flex:1; background:#fff; border-radius:12px; padding:20px;
            box-shadow:0 2px 6px rgba(0,0,0,.1);
            display:flex; gap:20px;">
<div style="flex:1; min-width:0; text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; max-width:450px;">
<canvas id="officeVolunteerChart" height="400" width="400" style="max-width:400px;"></canvas>
    <p class="mt-3 text-muted">Office Volunteer Hours</p>
</div>


<!-- RIGHT: SUMMARY -->
<div style="flex:0 0 320px;">
    <h6 class="mb-3 fw-bold">Office Participation Summary</h6>

    @forelse($officeHours as $office => $hours)
        <div class="pb-2 mb-3 border-bottom">
            <div class="fw-semibold">
                {{ $office === 'SDA Assistant' ? 'SDAO' : $office }}
            </div>

            <div class="text-muted small">
                {{ $hours }} hrs •
                {{ $officeParticipationCounts[$office] ?? 0 }} participation(s)
            </div>
        </div>
    @empty
        <p class="text-muted">No completed participation yet.</p>
    @endforelse
</div>
            </div>

            @php
    $goalHours = 10;
    $totalHours = $totalHours ?? 0; // just in case
    $remainingHours = max($goalHours - $totalHours, 0);
    $percent = min(round(($totalHours / $goalHours) * 100), 100);
@endphp


            <!-- Progress Doughnut Container -->
<div class="chart-container"
     style="flex:1; background:#fff; border-radius:12px; padding:20px;
            box-shadow:0 2px 6px rgba(0,0,0,.1);
            display:flex; gap:20px; align-items:center;">
    
    <!-- Doughnut -->
    <div style="flex:1; min-width:0; text-align:center;">

        <canvas id="progressCircleChart" height="280" width="280"></canvas>
        <p class="mt-3 text-muted" style="font-size:1rem;">
            Completed the required minimum 10 hours
        </p>
    </div>

    <!-- RIGHT: Summary -->
    <div style="flex:0 0 200px;">
        <h6 class="mb-3 fw-bold">Progress Summary</h6>
        <div class="pb-2 mb-2 border-bottom">
            <div class="fw-semibold">Completed Hours</div>
            <div class="text-muted small">
                {{ $totalHours }} hrs • {{ $percent }}%

            </div>
        </div>
        <div class="pb-2 mb-2 border-bottom">
            <div class="fw-semibold">Remaining Hours</div>
            <div class="text-muted small">
                {{ $remainingHours }} hrs • {{ 100 - $percent }}%

            </div>
        </div>
    </div>
</div>

        </div>
    </div>
</div>


                <!-- ================= STATUS COLUMN ================= -->
<div class="d-flex justify-content-start" style="width:100%">
    <div style="width:100%">
                    <h4 class="mb-3" style="color:var(--nu-blue);">
                        Volunteer Hour Rank
                    </h4>

                    <div class="status-grid"
                         style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">

<div class="status-card hoursCompleted">
    <h6>Hours Completed</h6>
    <h3>{{ $totalHours }}</h3>
</div>

<div class="status-card activitiesParticipated">
    <h6>Activities Participated</h6>
    <h3>{{ $activitiesDone }}</h3>
</div>

<div class="status-card hoursMonth">
    <h6>Hours This Term</h6>
    <h3>{{ $termHours }}</h3>
</div>


<div class="status-card remainingHours">
    <h6>Remaining Hours</h6>
    <h3>{{ $remainingHours }}</h3>
</div>

    </div>

                    </div>
                </div>
            </div>
        </div>

        <hr>

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

    $logDate = !empty($firstSchedule['date'])
        ? \Carbon\Carbon::parse($firstSchedule['date'])->toDateString()
        : null;

    $opportunityId = $opportunity['opportunity_id'] ?? null;

    $fpStatus = null;
    $logKey = null;

    if (
        $logDate &&
        $opportunityId &&
        isset($fingerprintStatus[$opportunityId][$logDate])
    ) {
        $fpStatus = $fingerprintStatus[$opportunityId][$logDate]['status'];
        $logKey   = $fingerprintStatus[$opportunityId][$logDate]['log_key'];
    }
@endphp


        @php
    $logDate = !empty($firstSchedule['date'])
        ? \Carbon\Carbon::parse($firstSchedule['date'])->toDateString()
        : null;

    $isCompleted = false;
    $logKey = null;

    if ($logDate && isset($completedLogsByDate[$logDate])) {
        if (($completedLogsByDate[$logDate]['status'] ?? '') === 'Complete') {
            $isCompleted = true;
            $logKey = $completedLogsByDate[$logDate]['log_key'];
        }
    }
@endphp



<div class="p-3 mb-3 border rounded shadow-sm card opportunity-card"
     data-fp-status="{{ $fpStatus ?? 'none' }}">
                <h5 class="mb-1 card-title fw-bold">{{ $opportunity['title'] ?? 'No Title' }}</h5>

<p class="mb-1">
    <strong>Office:</strong> {{ $opportunity['submitted_by_role'] === 'SDA Assistant' ? 'SDAO' : ($opportunity['submitted_by_role'] ?? 'N/A') }} 
    ({{ $opportunity['submitted_by_name'] ?? 'N/A' }})
</p>
                                <div class="d-flex justify-content-between align-items-center">

                <p class="mb-1"><strong>Location:</strong> {{ $opportunity['location'] ?? 'N/A' }}</p>

                {{-- Flex row for Submitted By + View Details --}}

                    <button class="view-details-btn float-end"
                        data-bs-toggle="modal"
                        data-bs-target="#joinedOpportunityModal{{ $opportunity['opportunity_id'] }}">
                        View Details
                    </button>
                </div>
@php
    $firstSchedule = collect($opportunity['schedule'] ?? [])
        ->sortBy('date')
        ->first();

    $logDate = !empty($firstSchedule['date'])
        ? \Carbon\Carbon::parse($firstSchedule['date'])->toDateString()
        : null;

    $opportunityId = $opportunity['opportunity_id'] ?? null;

    $isCompleted = false;
    $logKey = null;

    if (
        $logDate &&
        $opportunityId &&
        isset($completedLogs[$opportunityId][$logDate])
    ) {
        $isCompleted = true;
        $logKey = $completedLogs[$opportunityId][$logDate]['log_key'];
    }
@endphp

@if($fpStatus === 'Complete')
<form method="POST"
      action="{{ route('scholars.fingerprint.markDone') }}"
      class="mt-3 text-end mark-done-form"
      data-log-key="{{ $logKey }}">
    @csrf
    <input type="hidden" name="log_key" value="{{ $logKey }}">

    <button type="button"
            class="btn btn-success btn-sm mark-done-btn">
        ✔ Mark as Done
    </button>
</form>
@endif






              
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
<p>
    <strong>Office:</strong> {{ $opportunity['submitted_by_role'] === 'SDA Assistant' ? 'SDAO' : ($opportunity['submitted_by_role'] ?? 'N/A') }}
</p>
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

    /* =====================================================
     * CHARTS
     * ===================================================== */
    Chart.register(ChartDataLabels);

// Office Volunteer Pie Chart
let officeHours = @json($officeHours);

let officeLabels = Object.keys(officeHours).map(label =>
    label === "SDA Assistant" ? "SDAO" : label
);

let officeData = Object.values(officeHours);


const officeColors = ['#457b9d', '#a8dadc', '#1d3557', '#e63946', '#f1faee']; // extend if needed

const ctx1 = document.getElementById('officeVolunteerChart');
if (ctx1) {
  ctx1.width = 350;
    ctx1.height = 350;
    new Chart(ctx1.getContext('2d'), {
        type: 'pie',
        data: {
            labels: officeLabels,
            datasets: [{
                data: officeData,
                backgroundColor: officeColors.slice(0, officeLabels.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,  // ensures it uses the fixed canvas size
            plugins: {
                legend: { 
                    display: true,
                    position: 'bottom',
                    align: 'center',
                    labels: { boxWidth: 20, padding: 15 }
                },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: 16 },
                    anchor: 'center',
                    align: 'center',
                    textAlign: 'center',
                    formatter: (value, ctx) => {
                        const label = ctx.chart.data.labels[ctx.dataIndex];
                        return `${label}\n${value}`;
                    }
                }
            },
            layout: { padding: { top:10, bottom:30 } }
        },
        plugins: [ChartDataLabels]
    });
}




const totalHours = @json($totalHours);  // from Blade
const goalHours = 10;
const remainingHours = Math.max(goalHours - totalHours, 0);
const percent = Math.min(Math.round((totalHours / goalHours) * 100), 100);

const ctx2 = document.getElementById('progressCircleChart');
if (ctx2) {
    new Chart(ctx2.getContext('2d'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [totalHours, remainingHours],
                backgroundColor: ['#0056b3', '#e0e0e0'],
                cutout: '55%'
            }]
        },
        options: {
            responsive: false,
            plugins: {
                tooltip: { enabled: false },  // remove tooltip
                legend: { display: false }    // remove legend
            }
        },
        plugins: [{
            id: 'centerText',
            beforeDraw: function(chart) {
                const width = chart.width;
                const height = chart.height;
                const ctx = chart.ctx;
                ctx.restore();

                const fontSize = 28; // bigger center text
                ctx.font = `bold ${fontSize}px Arial`;
                ctx.fillStyle = '#0056b3';
                ctx.textBaseline = "middle";

                const text = `${percent}%`;
                const textX = Math.round((width - ctx.measureText(text).width) / 2);
                const textY = height / 2;

                ctx.fillText(text, textX, textY);
                ctx.save();
            }
        }]
    });
}





    /* =====================================================
     * TAB FILTERING (Upcoming vs Accomplished)
     * ===================================================== */
    const tabUpcoming = document.getElementById("tabUpcoming");
    const tabAccomplished = document.getElementById("tabAccomplished");
    const cards = document.querySelectorAll(".opportunity-card");

    function showUpcoming() {
        cards.forEach(card => {
            const status = card.dataset.fpStatus;

            if (
                status === "Pending" ||
                status === "Ongoing" ||
                status === "Complete" ||
                status === "none"
            ) {
                card.style.display = "";
            } else {
                card.style.display = "none";
            }
        });
    }

    function showAccomplished() {
        cards.forEach(card => {
            const status = card.dataset.fpStatus;
            card.style.display = (status === "Done") ? "" : "none";
        });
    }

    if (tabUpcoming && tabAccomplished) {
        tabUpcoming.addEventListener("click", () => {
            tabUpcoming.classList.add("active");
            tabAccomplished.classList.remove("active");
            showUpcoming();
        });

        tabAccomplished.addEventListener("click", () => {
            tabAccomplished.classList.add("active");
            tabUpcoming.classList.remove("active");
            showAccomplished();
        });
    }

    // Default view
    showUpcoming();

    /* =====================================================
     * MARK AS DONE FLOW
     * ===================================================== */
    let selectedForm = null;
    let selectedLogKey = null;

    document.querySelectorAll(".mark-done-btn").forEach(btn => {
        btn.addEventListener("click", function () {
            selectedForm = this.closest("form");
            selectedLogKey = selectedForm.dataset.logKey;

            document.getElementById("feedbackLogKey").value = selectedLogKey;

            new bootstrap.Modal(
                document.getElementById("feedbackChoiceModal")
            ).show();
        });
    });

    // Feedback YES → submit feedback
    const feedbackForm = document.getElementById("feedbackForm");
    if (feedbackForm) {
        feedbackForm.addEventListener("submit", function () {
            // normal submit
        });
    }

    // Feedback NO → confirm done
    const feedbackSkip = document.getElementById("feedbackSkip");
    if (feedbackSkip) {
        feedbackSkip.addEventListener("click", function () {
            bootstrap.Modal.getInstance(
                document.getElementById("feedbackChoiceModal")
            ).hide();

            new bootstrap.Modal(
                document.getElementById("confirmDoneModal")
            ).show();
        });
    }

    // Final confirm → submit mark done form
    const confirmDoneYes = document.getElementById("confirmDoneYes");
    if (confirmDoneYes) {
        confirmDoneYes.addEventListener("click", function () {
            if (selectedForm) {
                selectedForm.submit();
            }
        });
    }

});
</script>


<div class="modal fade" id="feedbackChoiceModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST"
          action="{{ route('scholars.fingerprint.submitFeedback') }}"
          class="modal-content"
          id="feedbackForm">

      @csrf
      <input type="hidden" name="log_key" id="feedbackLogKey">

      <div class="modal-header">
        <h5 class="modal-title">Submit Feedback</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Your feedback</label>
          <textarea name="feedback"
                    class="form-control"
                    rows="4"
                    required></textarea>
        </div>

        <p class="mb-0 text-center text-muted">
          Do you want to submit feedback to the office?
        </p>
      </div>

      <div class="modal-footer justify-content-center">
        <button type="submit" class="btn btn-primary">
          Yes, Submit
        </button>

        <button type="button"
                class="btn btn-outline-secondary"
                id="feedbackSkip">
          No
        </button>
      </div>

    </form>
  </div>
</div>

</div>

<div class="modal fade" id="confirmDoneModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Action</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="text-center modal-body">
        <p>Mark this opportunity as done?</p>
      </div>

      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-success" id="confirmDoneYes">
          Yes
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          No
        </button>
      </div>
    </div>
  </div>
</div>


@endsection 