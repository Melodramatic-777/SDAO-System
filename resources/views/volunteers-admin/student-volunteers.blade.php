@extends('volunteers-admin.layout')

@section('content')


<!-- Sort & Search -->
<form id="filterForm" method="GET" action="{{ route('volunteers-admin.student-volunteers') }}" 
      class="flex-wrap gap-2 p-3 mb-3 bg-white border rounded shadow-sm filter-bar d-flex align-items-center justify-content-between">
    <div class="flex-wrap gap-2 d-flex align-items-center">
        <select name="academic_year" class="form-select" style="width: 180px;">
            <option value="">All Academic Years</option>
            @foreach($academicYears as $year)
                <option value="{{ $year }}" {{ request('academic_year') == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>

        <select name="term" class="form-select" style="width: 150px;">
            <option value="">All Terms</option>
            @foreach($terms as $term)
                <option value="{{ $term }}" {{ request('term') == $term ? 'selected' : '' }}>
                    {{ $term }} Term
                </option>
            @endforeach
        </select>

        <select name="scholarship_type" id="scholarship_type" class="form-select" style="width: 200px;">
            <option value="">All Scholarship Types</option>
            <option value="Aspiring Scholar" {{ request('scholarship_type') == 'Aspiring Scholar' ? 'selected' : '' }}>
                Aspiring Scholar
            </option>
            @foreach($scholarshipTypes as $type)
                <option value="{{ $type }}" {{ request('scholarship_type') == $type ? 'selected' : '' }}>
                    {{ $type }}
                </option>
            @endforeach
        </select>

        <select name="program" class="form-select" style="width: 200px;">
            <option value="">All Programs</option>
            @foreach($programs as $program)
                <option value="{{ $program }}" {{ request('program') == $program ? 'selected' : '' }}>
                    {{ $program }}
                </option>
            @endforeach
        </select>

        <select name="school" class="form-select" style="width: 200px;">
            <option value="">All Schools</option>
            @foreach($schools as $school)
                <option value="{{ $school }}" {{ request('school') == $school ? 'selected' : '' }}>
                    {{ $school }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-primary ms-2">
            <i class="bi bi-funnel me-1"></i> Apply
        </button>

        <button type="button" class="btn btn-secondary ms-2" 
                onclick="window.location.href='{{ route('volunteers-admin.student-volunteers') }}'">
            <i class="bi bi-arrow-clockwise me-1"></i> Reset
        </button>

        <input type="text" name="search" id="searchInput" class="form-control search-input ms-auto" 
               placeholder="Search student volunteers..." style="width: 260px;" value="{{ request('search') }}">
    </div>
</form>

<!-- Table -->
<div id="Scholars">
  <!-- Table -->
<div id="scholarsTable">
<div class="overflow-hidden shadow-sm rounded-4 table-responsive">

    <table id="tableScholars">
      <thead>

        <!-- TITLE ROW -->
        <tr class="table-title-row">
          <th colspan="11" class="py-3 text-center fw-bold fs-5">
            List of Student Volunteers A.Y. {{ $latestYear }}
          </th>
        </tr>

        <!-- HEADER ROW (MATCHING DESIGN) -->
        <tr>
          <th>#</th>
          <th>Student No.</th>
          <th>Email</th>
          <th>Full Name</th>
          <th class="text-center">Birthday</th>
          <th class="text-center">School</th>
          <th class="text-center">Program</th>
          <th class="text-center">Scholarship Type</th>
          <th class="text-center">Status</th>
          <th>Total Volunteer Hour</th>
          <th>View</th>
        </tr>

      </thead>

      <tbody>
        @forelse ($scholars as $index => $scholar)
          @php
            $year = $scholar['academic_year'] ?? null;
            $status = $year && isset($scholar['gwa_records'][$year]['status'])
                      ? $scholar['gwa_records'][$year]['status']
                      : 'N/A';
          @endphp

          <tr>
            <td class="text-center fw-bold">
              {{ ($scholars->currentPage() - 1) * $scholars->perPage() + ($index + 1) }}
            </td>

            <td class="text-center">{{ $scholar['student_no'] }}</td>
            <td class="text-center">{{ $scholar['email'] ?? 'N/A' }}</td>

            <td class="text-center">
              {{ $scholar['last_name'] }},
              {{ $scholar['first_name'] }}
              {{ $scholar['middle_name'] ?? '' }}
              {{ $scholar['suffix'] ?? '' }}
            </td>

            <td class="text-center">
              {{ $scholar['birthday']
                ? \Carbon\Carbon::parse($scholar['birthday'])->format('F d, Y')
                : 'N/A' }}
            </td>

            <td class="text-center">{{ $scholar['school'] ?? 'N/A' }}</td>
            <td class="text-center">{{ $scholar['program'] ?? 'N/A' }}</td>

            <td class="text-center">
              {{ $scholar['is_aspiring'] ?? false
                ? 'Aspiring Scholar'
                : ($scholar['scholarship_type'] ?? 'N/A') }}
            </td>

            <td class="text-center">
              @if(isset($scholar['is_aspiring']) && $scholar['is_aspiring'])
                <span class="badge bg-info text-dark">Aspiring Scholar</span>
              @else
                @if(strtolower($status) === 'eligible')
                  <span class="badge bg-success">Eligible</span>
                @elseif(strtolower($status) === 'probationary')
                  <span class="badge bg-warning text-dark">Probationary</span>
                @elseif(in_array(strtolower($status), ['ineligible', 'revoked']))
                  <span class="badge bg-danger">Revoked</span>
                @else
                  <span class="badge bg-secondary">{{ $status }}</span>
                @endif
              @endif
            </td>

<td class="text-center position-relative">
    <!-- Centered Volunteer Hours -->
    <span class="fw-bold">
        {{ $scholar['volunteer_hours'] ?? 0 }}
    </span>

    <!-- Edit Icon (same color, no background, with right margin) -->
<button
    type="button"
    class="edit-hours-btn position-absolute end-0 top-50 translate-middle-y"
    title="Edit Hours"
    data-student-no="{{ $scholar['student_no'] }}"
    data-student-name="{{ $scholar['last_name'] }}, {{ $scholar['first_name'] }}"
    data-hours="{{ $scholar['volunteer_hours'] ?? 0 }}"
    style="background: none !important; border: none !important; padding: 0; margin-right: 8px; box-shadow: none !important; color: inherit;">
    <i class="bi bi-pencil-square fs-6"></i>
</button>

</td>





            <td class="text-center">
              <a href="{{ route('volunteers-admin.student-volunteer-detail', ['student_no' => $scholar['student_no']]) }}"
                 class="icon-btn"
                 title="View Profile">
                <i class="bi bi-eye fs-5"></i>
              </a>
            </td>
          </tr>

        @empty
          <tr>
            <td colspan="11" class="text-center text-muted">
              No student volunteers found
            </td>
          </tr>
        @endforelse
      </tbody>

    </table>
  </div>
</div>




<!-- Optional: Pagination -->
<div class="mt-4 d-flex justify-content-center">
    {{ $scholars->links('pagination::bootstrap-5') }}
</div>




<!-- EDIT TOTAL HOURS MODAL -->
<div class="modal fade" id="edithoursmodal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="shadow modal-content rounded-4">

      <div class="border-0 modal-header">
        <h5 class="modal-title fw-bold">Edit Total Hours</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <p class="mb-1 text-muted">
          Student: <span id="modalStudentName"></span>
        </p>
        <p class="mb-3 text-muted">
          Current Total Hours:
          <strong><span id="currentHoursText"></span> hours</strong>
        </p>

        <!-- Add Hours / Minutes -->
        <div class="mb-3 row g-2">
          <div class="col">
            <label class="form-label">Hours</label>
            <input type="number" id="addHours" class="form-control" min="0" value="0">
          </div>
          <div class="col">
            <label class="form-label">Minutes</label>
            <input type="number" id="addMinutes" class="form-control" min="0" max="59" value="0">
          </div>
        </div>

        <!-- Reason -->
        <div class="mb-3">
          <label class="form-label">Reason for Adjustment</label>
          <select class="form-select" id="adjustReason">
            <option value="">Choose a reason</option>
            <option value="Manual Adjustment">Manual Adjustment</option>
            <option value="Correction">Correction</option>
            <option value="Late Submission">Late Submission</option>
            <option value="Admin Update">Admin Update</option>
          </select>
        </div>

        <!-- New Total -->
        <div class="p-3 text-center rounded bg-light fw-bold text-primary">
          New Total:
          <span id="newTotalText">0 hours & 0 mins</span>
        </div>

        <input type="hidden" id="modalStudentNo">

      </div>

      <div class="border-0 modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" onclick="submitHourUpdate()">Update Hours</button>
      </div>

    </div>
  </div>
</div>


<!-- JS for AJAX Search & Filter -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    let searchInput = document.getElementById("searchInput");
    let scholarshipSelect = document.getElementById("scholarship_type");
    let tableContainer = document.getElementById("scholarsTable");
    

    function fetchData() {
        let search = searchInput.value;
        let scholarship = scholarshipSelect.value;

        tableContainer.style.opacity = "0.5";

        fetch(`{{ route('volunteers-admin.student-volunteers') }}?search=${encodeURIComponent(search)}&scholarship_type=${encodeURIComponent(scholarship)}`) 
            .then(response => response.text())
            .then(html => {
                let doc = new DOMParser().parseFromString(html, "text/html");
                let newTable = doc.querySelector("#scholarsTable").innerHTML;

                tableContainer.style.opacity = "0";
                setTimeout(() => {
                    tableContainer.innerHTML = newTable;
                    tableContainer.style.opacity = "1";
                }, 200);
            });
    }

    searchInput.addEventListener("keyup", fetchData);
    scholarshipSelect.addEventListener("change", fetchData);
});


let currentHours = 0;

function openEditHoursModal(studentNo, studentName, hours) {
    currentHours = parseFloat(hours) || 0;

    document.getElementById("modalStudentNo").value = studentNo;
    document.getElementById("modalStudentName").innerText = studentName;
    document.getElementById("currentHoursText").innerText = currentHours; // Current total

    document.getElementById("addHours").value = 0;
    document.getElementById("addMinutes").value = 0;
    document.getElementById("adjustReason").value = "";

    updateNewTotal(); // Initialize New Total

    new bootstrap.Modal(document.getElementById("edithoursmodal")).show();
}

function updateNewTotal() {
    let addH = parseInt(document.getElementById("addHours").value) || 0;
    let addM = parseInt(document.getElementById("addMinutes").value) || 0;

    // Total in minutes
    let totalMinutes = currentHours * 60 + addH * 60 + addM;
    let totalH = Math.floor(totalMinutes / 60);
    let totalM = totalMinutes % 60;

    document.getElementById("newTotalText").innerText =
        `${totalH} hours & ${totalM} mins`;
}



function submitHourUpdate() {
    let studentNo = document.getElementById("modalStudentNo").value;
    let reason = document.getElementById("adjustReason").value;

    if (!reason) {
        alert("Please choose a reason for adjustment.");
        return;
    }

    // 🔧 PLACE AJAX / FORM SUBMISSION HERE
    console.log("Update hours for:", studentNo);

    bootstrap.Modal.getInstance(
    document.getElementById("edithoursmodal")
    ).hide();
}

document.addEventListener("DOMContentLoaded", function () {
    const addHours = document.getElementById("addHours");
    const addMinutes = document.getElementById("addMinutes");

    if (addHours && addMinutes) {
        addHours.addEventListener("input", updateNewTotal);
        addMinutes.addEventListener("input", updateNewTotal);
    }
});


document.addEventListener("click", function(e) {
    if (e.target.closest(".edit-hours-btn")) {
        const btn = e.target.closest(".edit-hours-btn");

        const studentNo = btn.dataset.studentNo;
        const studentName = btn.dataset.studentName;
        const hours = btn.dataset.hours;

        openEditHoursModal(studentNo, studentName, hours);
    }
});

</script>



@endsection
