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
<div id="Scholars" class="px-5 py-4 container-fluid">
    <div id="scholarsTable" class="rounded shadow-sm table-responsive">
        <table id="tableScholars" class="table table-striped">
            <thead>
                <tr class="table-title-row">
                    <th colspan="11" class="py-3 text-center fw-bold fs-5">
                        List of Student Volunteers A.Y. {{ $latestYear }}
                    </th>
                </tr>
                <tr>
                    <th>#</th>
                    <th>Student No.</th>
                    <th>Email</th>
                    <th>Full Name</th>
                    <th>Birthday</th>
                    <th>School</th>
                    <th>Program</th>
                    <th>Scholarship Type</th>
                    <th>Status</th>
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
                        <td>{{ $loop->iteration + ($scholars->currentPage() - 1) * $scholars->perPage() }}</td>
                        <td>{{ $scholar['student_no'] }}</td>
                        <td>{{ $scholar['email'] ?? 'N/A' }}</td>
                        <td>{{ $scholar['last_name'] }}, {{ $scholar['first_name'] }} {{ $scholar['middle_name'] ?? '' }} {{ $scholar['suffix'] ?? '' }}</td>
                        <td>{{ $scholar['birthday'] ? \Carbon\Carbon::parse($scholar['birthday'])->format('F d, Y') : 'N/A' }}</td>
                        <td>{{ $scholar['school'] ?? 'N/A' }}</td>
                        <td>{{ $scholar['program'] ?? '' }}</td>
                        <td>{{ $scholar['is_aspiring'] ?? false ? 'Aspiring Scholar' : ($scholar['scholarship_type'] ?? '') }}</td>
                        <td>
                            @if(isset($scholar['is_aspiring']) && $scholar['is_aspiring'])
                                <span class="badge bg-info text-dark">Aspiring Scholar</span>
                            @else
                                @php
                                    $year = $scholar['academic_year'];
                                    $status = $scholar['gwa_records'][$year]['status'] ?? 'N/A';
                                @endphp
                                @if(strtolower($status) === 'eligible')
                                    <span class="badge bg-success">Eligible</span>
                                @elseif(strtolower($status) === 'probationary')
                                    <span class="badge bg-warning text-dark">Probationary</span>
                                @elseif(strtolower($status) === 'ineligible' || strtolower($status) === 'revoked')
                                    <span class="badge bg-danger">Revoked</span>
                                @else
                                    <span class="badge bg-secondary">{{ $status }}</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            <div class="gap-2 d-flex justify-content-center align-items-center">
                                <input type="number" class="form-control form-control-sm" style="width: 90px;" placeholder="+ hours" min="1">
                                <span class="fw-bold">{{ $scholar['volunteer_hours'] ?? 0 }}</span>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('volunteers-admin.student-volunteer-detail', ['student_no' => $scholar['student_no']]) }}" class="icon-btn" title="View Profile">
                                <i class="bi bi-eye fs-5"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted">No student volunteers found</td>
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
</script>

@endsection
