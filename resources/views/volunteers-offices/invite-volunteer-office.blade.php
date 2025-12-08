@extends('volunteers-offices.layout')

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

<!-- Invite Modal -->
<!-- Invite Modal -->
<div class="modal fade" id="inviteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="inviteForm" method="POST" action="{{ route('volunteers-admin.inviteScholar') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Invite Scholar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Scholar Info -->
                    <div class="mb-3">
                        <h5 id="scholarName" class="fw-bold"></h5>
                        <p><strong>Total Hours:</strong> <span id="scholarHours"></span></p>
                        <input type="hidden" id="scholarId" name="scholar_id">
                    </div>

                    <!-- Opportunity Dropdown -->
                    <div class="mb-3">
                        <label for="activitySelect" class="form-label fw-bold">Opportunity</label>
                        <select id="activitySelect" name="opportunity_id" class="form-select" required>
                            <option value="">Select Opportunity</option>
                            @foreach($opportunities as $opportunity)
                                <option value="{{ $opportunity['opportunity_id'] ?? '' }}"
                                        data-intermittent="{{ $opportunity['is_intermittent'] ?? 0 }}"
                                        data-tasks='@json($opportunity['tasks'] ?? [])'
                                        data-intermittent-tasks='@json($opportunity['intermittent_tasks_by_date'] ?? [])'>
                                    {{ $opportunity['title'] ?? 'Untitled' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tasks by date -->
                    <div id="tasksContainer" class="mt-3 d-none">
                        <p><strong>Tasks:</strong></p>
                        <div id="tasksList"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Send Invite</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const scholarNameEl = document.getElementById("scholarName");
    const scholarHoursEl = document.getElementById("scholarHours");
    const scholarIdEl = document.getElementById("scholarId");
    const activitySelect = document.getElementById("activitySelect");
    const tasksContainer = document.getElementById("tasksContainer");
    const tasksList = document.getElementById("tasksList");

    document.querySelectorAll(".inviteBtn").forEach(btn => {
        btn.addEventListener("click", () => {
            scholarNameEl.textContent = btn.dataset.name;
            scholarHoursEl.textContent = btn.dataset.hours + " Hours";
            scholarIdEl.value = btn.dataset.id;

            // Reset modal
            activitySelect.value = "";
            tasksContainer.classList.add("d-none");
            tasksList.innerHTML = "";
        });
    });

   activitySelect.addEventListener("change", function() {
    const option = this.selectedOptions[0];
    const isIntermittent = parseInt(option.dataset.intermittent) === 1;
    const tasks = JSON.parse(option.dataset.tasks || "[]");
    const intermittentTasks = JSON.parse(option.dataset.intermittentTasks || "{}");

    tasksList.innerHTML = "";

    if (isIntermittent && Object.keys(intermittentTasks).length > 0) {
        tasksContainer.classList.remove("d-none");
        for (const [date, taskObj] of Object.entries(intermittentTasks)) {
            const dateFormatted = formatDate(date);
            let taskHtml = `<p class="mb-1 fw-bold">${dateFormatted}</p><ul class="mb-2 list-group">`;
            for (const taskKey in taskObj) {
                const task = taskObj[taskKey];
                // Use radio buttons for intermittent/multiple schedule (1 per date)
                taskHtml += `
                    <li class="list-group-item d-flex align-items-center">
                        <input type="radio" class="form-check-input me-2" 
                               name="tasks[${date}][]" value="${task.task}">
                        <span>${task.task}</span>
                    </li>`;
            }
            taskHtml += "</ul>";
            tasksList.innerHTML += taskHtml;
        }
    } else if (tasks.length > 0) {
        tasksContainer.classList.remove("d-none");
        // Regular tasks can still be multiple checkboxes
        tasksList.innerHTML = `<ul class="list-group">${tasks.map(t => `
            <li class="list-group-item d-flex align-items-center">
                <input type="checkbox" class="form-check-input me-2" name="tasks[regular][]" value="${t.task}">
                <span>${t.task}</span>
            </li>`).join('')}</ul>`;
    } else {
        tasksContainer.classList.add("d-none");
    }
});


    function formatDate(dateStr){
        const d = new Date(dateStr);
        if(isNaN(d)) return "N/A";
        return d.toLocaleDateString('en-US', { month:'long', day:'numeric', year:'numeric' });
    }
});
</script>

@endsection
