@extends('volunteers-admin.layout')

@section('content')
<div class="container mt-4 text-center">

    <!-- Search & Filter -->
    <div class="mb-3 d-flex justify-content-center">
        <form id="filterForm" method="GET" action="{{ route('volunteers.invite') }}" 
              class="flex-wrap gap-2 d-flex justify-content-center">
            <label for="scholarship_type" class="d-flex align-items-center fw-bold me-2">Filter by:</label>
            <select name="scholarship_type" id="scholarship_type" class="form-select me-2" style="width: 180px;">
                <option value="">All</option>
                @foreach($scholarshipTypes as $type)
                    <option value="{{ $type }}" {{ request('scholarship_type') == $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                @endforeach
            </select>

            <div class="input-group" style="width: 250px;">
                <input type="text" name="search" id="searchInput" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
        </form>
    </div>

    <!-- Scholars Table -->
    <div id="scholarsTable" class="mt-3">
        <table class="table mx-auto text-center align-middle table-bordered">
            <thead>
                <tr class="table-title-row">
                    <th colspan="5" class="text-center fs-5">List of Student Volunteers</th>
                </tr>
                <tr class="table-header-row">
                    <th>#</th>
                    <th>Scholarship Type</th>
                    <th>Name</th>
                    <th>Total Volunteer Hour</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($scholars as $index => $scholar)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $scholar['scholarship_type'] ?? 'N/A' }}</td>
                        <td>{{ $scholar['last_name'] ?? '' }}, {{ $scholar['first_name'] ?? '' }}</td>
                        <td>{{ $scholar['volunteer_hours'] ?? 0 }}</td>
                        <td>
                            <button type="button" 
                                    class="btn btn-primary inviteBtn"
                                    data-id="{{ $scholar['student_no'] ?? '' }}"
                                    data-name="{{ $scholar['first_name'] ?? '' }} {{ $scholar['last_name'] ?? '' }}"
                                    data-hours="{{ $scholar['volunteer_hours'] ?? 0 }}"
                                            data-scholarship="{{ $scholar['scholarship_type'] ?? 'N/A' }}"

                                    data-bs-toggle="modal"
                                    data-bs-target="#inviteModal">
                                Invite
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-muted">No volunteers found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="inviteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="border-0 shadow-lg modal-content rounded-4">

            <form id="inviteForm" method="POST" action="{{ route('volunteers-admin.inviteScholar') }}">
                @csrf

                <!-- HEADER -->
                <div class="px-4 py-3 border-0 modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-plus me-2"></i>Invite Scholar
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- BODY -->
                <div class="px-4 py-3 modal-body">
                    <div class="row g-4">

                        <!-- LEFT COLUMN -->
                        <div class="col-lg-5">

<div class="mb-4 border-0 shadow-sm card">
    <div class="card-body text-start">
        <h5 id="scholarName" class="mb-1 fw-bold"></h5>

        <div class="mb-1 text-muted">
            Scholarship Type:
            <span id="scholarType" class="fw-semibold text-dark"></span>
        </div>

        <small class="text-muted">
            Total Hours:
            <span id="scholarHours" class="fw-semibold"></span>
        </small>

        <input type="hidden" id="scholarId" name="scholar_id">
    </div>
</div>


                            <div>
                                <label class="form-label fw-semibold">Select Opportunity</label>
                                <select id="activitySelect" name="opportunity_id"
                                        class="form-select form-select-lg" required>
                                    <option value="">— Choose an opportunity —</option>
 @foreach($opportunities as $op)
<option value="{{ $op['opportunity_id'] }}"
    data-intermittent="{{ $op['is_intermittent'] }}"
    data-tasks='@json($op["tasks"] ?? [])'
    data-intermittent-tasks='@json($op["intermittent_tasks_by_date"] ?? [])'
    data-schedule='@json($op["schedule"] ?? [])'>
    {{ $op['title'] }}
</option>


@endforeach
                                </select>
                            </div>

                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="col-lg-7">

                            <div id="tasksContainer" class="d-none h-100"></div>

                            <div id="tasksPlaceholder"
                                 class="text-center h-100 d-flex align-items-center justify-content-center text-muted">
                                <div>
                                    <i class="mb-2 bi bi-cursor fs-1 d-block"
                                       style="transform: scaleX(-1);"></i>
                                    <h6 class="fw-bold text-dark">Select an opportunity</h6>
                                    <p class="mb-0">
                                        Choose an opportunity to<br>
                                        view available tasks
                                    </p>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                
                <!-- FOOTER -->
                <div class="px-4 pb-4 border-0 modal-footer">
                    <button type="button" class="px-4 btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 btn btn-primary">
                        <i class="bi bi-send me-1"></i>Send Invite
                    </button>
                </div>
<div id="scheduleInputs"></div>

            </form>
        </div>
    </div>
</div>



<script>
document.addEventListener("DOMContentLoaded", () => {

    const scholarNameEl   = document.getElementById("scholarName");
    const scholarHoursEl  = document.getElementById("scholarHours");
    const scholarTypeEl   = document.getElementById("scholarType");
    const scholarIdEl     = document.getElementById("scholarId");

    const activitySelect = document.getElementById("activitySelect");
    const tasksContainer = document.getElementById("tasksContainer");
    const placeholder    = document.getElementById("tasksPlaceholder");

    let selectedTaskDate = null;
    let currentTasks = {};
    let currentIsIntermittent = false;

    /* ================= INVITE BUTTON ================= */
    document.querySelectorAll(".inviteBtn").forEach(btn => {
        btn.addEventListener("click", () => {

            scholarNameEl.textContent  = btn.dataset.name;
            scholarHoursEl.textContent = btn.dataset.hours + " Hours";
            scholarTypeEl.textContent  = btn.dataset.scholarship || "N/A";
            scholarIdEl.value          = btn.dataset.id;

            activitySelect.value = "";
            tasksContainer.innerHTML = "";
            tasksContainer.classList.add("d-none");
            placeholder.classList.remove("d-none");

            selectedTaskDate = null;
            currentTasks = {};
        });
    });

    /* ================= OPPORTUNITY CHANGE ================= */
    activitySelect.addEventListener("change", function () {

        const opt = this.options[this.selectedIndex];
        if (!opt.value) return;

        currentIsIntermittent = opt.dataset.intermittent == 1;

const tasks = JSON.parse(opt.dataset.tasks || "[]");
const tasksByDate = JSON.parse(opt.dataset.intermittentTasks || "{}");
const schedule = JSON.parse(opt.dataset.schedule || "[]");

currentTasks = { tasks, tasksByDate, schedule };


        if (currentIsIntermittent) {
            selectedTaskDate = Object.keys(tasksByDate)[0] ?? null;
        }

        renderRightPanel();
    });

    /* ================= RENDER PANEL ================= */
    function renderRightPanel() {

        let html = "";

        /* ===== INTERMITTENT ===== */
        if (currentIsIntermittent && selectedTaskDate) {

            const dayTasks = Object.values(
                currentTasks.tasksByDate[selectedTaskDate] || []
            );

            if (!dayTasks.length) return;

            const task = dayTasks[0];

            const timeStr =
                task.start_time && task.end_time
                    ? `${formatTime(task.start_time)} – ${formatTime(task.end_time)}`
                    : "N/A";

const sched = currentTasks.schedule.find(s => s.date === selectedTaskDate);
const foodProvided = sched?.food_provided ?? "no";

            html = `
            <div class="task-panel">

                <div class="section-label">
                    <i class="bi bi-calendar-event me-1"></i> Select Date
                </div>

                <div class="mb-3 date-pill-group">
                    ${Object.keys(currentTasks.tasksByDate).map(d => `
                        <button type="button"
                                class="date-pill ${d === selectedTaskDate ? 'active' : ''}"
                                onclick="selectInviteDate('${d}')">
                            ${new Date(d).toLocaleDateString('en-US', {
                                weekday:'short', month:'short', day:'numeric'
                            })}
                        </button>
                    `).join("")}
                </div>

                ${renderTimeAndFood(timeStr, foodProvided)}

                ${renderTaskList(dayTasks, selectedTaskDate)}
            </div>`;
        }

        /* ===== REGULAR ===== */
        else if (!currentIsIntermittent && currentTasks.tasks.length) {

            const task = currentTasks.tasks[0];

            const timeStr =
                task.start_time && task.end_time
                    ? `${formatTime(task.start_time)} – ${formatTime(task.end_time)}`
                    : "N/A";

const foodProvided = opt?.dataset?.food ?? task.food_provided ?? "no";

            html = `
            <div class="task-panel">

                <div class="section-label">
                    <i class="bi bi-calendar-event me-1"></i> Schedule
                </div>

                ${renderTimeAndFood(timeStr, foodProvided)}

                ${renderTaskList(currentTasks.tasks, task.date)}
            </div>`;
        }

        tasksContainer.innerHTML = html;
        tasksContainer.classList.remove("d-none");
        placeholder.classList.add("d-none");
    }

    /* ================= TASK LIST ================= */
    window.renderTaskList = function (tasks, dateKey) {
        return `
            <div class="section-label">
                <i class="bi bi-list-check me-1"></i> Choose Your Task
            </div>

            <div class="task-list">
                ${tasks.map(t => `
                    <label class="task-card">
                        <div class="d-flex">
                            <input type="radio"
                                   name="tasks[${dateKey}][]"
                                   value="${t.task}"
                                   class="mt-1 form-check-input me-3"
                                   required>
                            <div class="flex-grow-1">
                                <div class="task-title">${t.task}</div>
                            </div>
                        </div>
                    </label>
                `).join("")}
            </div>`;
    };

    /* ================= DATE SELECT ================= */
    window.selectInviteDate = function (date) {
        selectedTaskDate = date;
        updateScheduleInputs([date]);
        renderRightPanel();
    };

    function formatTime(time) {
        const [h, m] = time.split(":");
        const hour = h % 12 || 12;
        return `${hour}:${m} ${h >= 12 ? "PM" : "AM"}`;
    }
});

/* ================= FOOD + TIME ================= */
function renderTimeAndFood(timeStr, food) {

    const hasFood = String(food).toLowerCase() === "yes";

    return `
        <div class="flex-wrap gap-2 mb-3 date-meta d-flex align-items-center">

            <span class="time-badge">
                <i class="bi bi-clock me-1"></i>
                ${timeStr || "Time TBA"}
            </span>

            <span class="food-badge ${hasFood ? "food-yes" : "food-no"}">
                <i class="bi ${hasFood ? "bi-check-circle-fill" : "bi-x-circle-fill"} me-1"></i>
                ${hasFood ? "Food Provided" : "No Food"}
            </span>

        </div>`;
}

/* ================= SCHEDULE INPUTS ================= */
function updateScheduleInputs(indices) {
    const container = document.getElementById("scheduleInputs");
    container.innerHTML = "";

    indices.forEach(i => {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = "schedule_indices[]";
        input.value = i;
        container.appendChild(input);
    });
}
</script>




@endsection
