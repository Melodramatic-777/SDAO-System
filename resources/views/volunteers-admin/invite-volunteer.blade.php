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
                                        data-schedule='@json($opportunity['schedule'] ?? [])'>
                                    {{ $opportunity['title'] ?? 'Untitled' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Intermittent Schedule -->
                    <div id="intermittentDatesContainer" class="mt-3 d-none">
                        <p>Select dates for intermittent schedule:</p>
                        <div id="intermittentDatesList" class="list-group"></div>
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
    const intermittentContainer = document.getElementById("intermittentDatesContainer");
    const intermittentList = document.getElementById("intermittentDatesList");

    // Bind Invite buttons
    document.querySelectorAll(".inviteBtn").forEach(btn => {
        btn.addEventListener("click", () => {
            scholarNameEl.textContent = btn.dataset.name;
            scholarHoursEl.textContent = btn.dataset.hours + " Hours";
            scholarIdEl.value = btn.dataset.id;

            // Reset modal
            activitySelect.value = "";
            intermittentContainer.classList.add("d-none");
            intermittentList.innerHTML = "";
        });
    });

    // When an opportunity is selected
    activitySelect.addEventListener("change", function() {
        const option = this.selectedOptions[0];
        const isIntermittent = parseInt(option.dataset.intermittent) === 1;
        const schedule = JSON.parse(option.dataset.schedule || "[]");

        if(isIntermittent && schedule.length > 0){
            intermittentContainer.classList.remove("d-none");
            intermittentList.innerHTML = schedule.map((s, i) => `
                <label class="list-group-item d-flex align-items-center">
                    <input class="form-check-input me-3" type="checkbox" name="schedule_indices[]" value="${i}">
                    <span><strong>${formatDate(s.date)}</strong> ${s.start_time || 'N/A'} - ${s.end_time || 'N/A'}</span>
                </label>
            `).join('');
        } else {
            intermittentContainer.classList.add("d-none");
            intermittentList.innerHTML = "";
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
