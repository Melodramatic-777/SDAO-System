@extends('scholars-admin.layout')

@section('content')


<!-- ✅ Scholars Section -->

<!-- ✅ FILTER BAR -->
<form method="GET" 
      class="flex-wrap gap-2 p-3 mb-3 bg-white border rounded shadow-sm filter-bar d-flex align-items-center justify-content-between">

  <!-- Left: Filter Dropdowns -->
  <div class="flex-wrap gap-2 d-flex align-items-center">
<select name="academic_year" class="form-select" style="width: 180px;">

    <!-- Show CURRENT / LATEST A.Y. first -->
    <option value="{{ $latestYear }}" 
        {{ request('academic_year', $latestYear) == $latestYear ? 'selected' : '' }}>
        {{ $latestYear }}
    </option>

    <!-- Show the rest EXCEPT the latest -->
    @foreach($academicYears as $year)
        @if($year !== $latestYear)
            <option value="{{ $year }}" {{ request('academic_year') == $year ? 'selected' : '' }}>
                {{ $year }}
            </option>
        @endif
    @endforeach

</select>


<select name="term" class="form-select" style="width: 150px;">

    <!-- Latest Term First -->
    <option value="{{ $latestTerm }}"
        {{ request('term', $latestTerm) == $latestTerm ? 'selected' : '' }}>
        {{ $latestTerm }}
    </option>

    <!-- Other Terms -->
    @foreach($terms as $term)
        @if($term !== $latestTerm)
            <option value="{{ $term }}" {{ request('term') == $term ? 'selected' : '' }}>
                {{ $term }}
            </option>
        @endif
    @endforeach

</select>


    <select name="scholarship_type" class="form-select" style="width: 200px;">
      <option value="">All Scholarship Types</option>
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

    <button type="button" 
            class="btn btn-secondary ms-2" 
            onclick="window.location.href='{{ route('scholars-admin.scholars_accounts') }}'">
      <i class="bi bi-arrow-clockwise me-1"></i> Reset
    </button>

      <input type="text" id="searchScholars" 
         class="form-control search-input ms-auto" 
         placeholder="Search scholars..." 
         style="width: 260px;">

            
  </div>

</form>

<div class="px-5 mb-3 container-fluid d-flex justify-content-end">
    <button class="btn btn-primary ms-2"
            onclick="document.getElementById('importScholarsModal').style.display='block'">
        <i class="bi bi-file-earmark-arrow-up me-1"></i> Import Scholars
    </button>
</div>


<div id="Scholars" class="px-5 py-4 container-fluid">

  <!-- Scholars Table -->
  <div class="rounded shadow-sm table-responsive">
    <table id="tableScholars">
<thead>
      <tr class="table-title-row">
        <th colspan="13" class="py-3 text-center fw-bold fs-5">
          Scholars A.Y. {{ request('academic_year') ?? $latestYear }} — {{ request('term') ?? $latestTerm }} Term
        </th>
      </tr>
          <tr>
          <th>#</th>
          <th>Student No.</th>
          <th>Email</th>
<th>First Name</th>
<th>Middle Name</th>
<th>Last Name</th>
<th>Suffix</th>
          <th>Birthday</th>
          <th>School</th>
          <th>Program</th>
          <th>Type</th>
          <th>Status</th>
          <th>Created At</th>
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
<td>{{ $scholar['first_name'] ?? 'N/A' }}</td>
<td>{{ $scholar['middle_name'] ?? 'N/A' }}</td>
<td>{{ $scholar['last_name'] ?? 'N/A' }}</td>
<td>{{ $scholar['suffix'] ?? '' }}</td>
<td>
@php
try {
    $birthday = $scholar['birthday'] ? \Carbon\Carbon::parse($scholar['birthday'])->format('F d, Y') : 'N/A';
} catch (\Exception $e) {
    $birthday = 'N/A';
}
@endphp
{{ $birthday }}
</td>
       <td>{{ $scholar['school'] ?? 'N/A' }}</td>
<td class="center-cell">
    {{ $scholar['program'] ?? '' }}
</td>
<td class="center-cell">
    {{ $scholar['scholarship_type'] ?? '' }}
</td>
@php
    // Check if scholar has status in their GWA records for the current year
    $year = $scholar['academic_year'] ?? null;
    $statusFromGwa = $year && isset($scholar['gwa_records'][$year]['status'])
        ? $scholar['gwa_records'][$year]['status']
        : null;

    // Use GWA status if exists, otherwise fallback to 'Eligible'
    $displayStatus = $statusFromGwa ?? ($scholar['status'] ?? 'Eligible');
@endphp

<td class="status-cell">
  @if(strtolower($displayStatus) === 'eligible')
    <span id="status-{{ $scholar['student_no'] }}" class="status-eligible">Eligible</span>
  @elseif(strtolower($displayStatus) === 'probationary')
    <span id="status-{{ $scholar['student_no'] }}" class="status-probationary">Probationary</span>
  @elseif(strtolower($displayStatus) === 'ineligible' || strtolower($displayStatus) === 'revoked')
    <span id="status-{{ $scholar['student_no'] }}" class="status-revoked">Revoked</span>
  @else
    <span id="status-{{ $scholar['student_no'] }}" class="status-na">{{ $displayStatus }}</span>
  @endif
</td>

<td>
@php
try {
    $createdAt = $scholar['created_at'] ? \Carbon\Carbon::parse($scholar['created_at'])->format('F d, Y g:i A') : 'N/A';
} catch (\Exception $e) {
    $createdAt = 'N/A';
}
@endphp
{{ $createdAt }}
</td>

        </tr>
      @empty
        <tr>
          <td colspan="10" class="text-center text-muted">No Scholars found.</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $scholars->links() }}
  </div>
</div>


<!-- Modal -->
<!-- Modal -->
<div id="importScholarsModal" class="modal">
  <div class="p-4 rounded shadow-lg modal-content">
    <div class="mb-3 modal-header d-flex justify-content-between align-items-center">
      <h3 class="m-0">Import New Scholars CSV</h3>
      <span class="close fs-3" style="cursor:pointer"
        onclick="document.getElementById('importScholarsModal').style.display='none'">&times;</span>
    </div>

    <!-- Download Template -->
<div class="mb-3">
    <span style="color: grey; font-style: italic;">Don't have a template yet? </span>
    <a href="{{ url('scholars-admin/download-template') }}" 
       class="text-primary text-decoration-underline">
        <i class="bi bi-download me-1"></i> Download
    </a>
</div>





    <form method="POST" action="{{ route('scholars-admin.import-scholars') }}" enctype="multipart/form-data">
      @csrf

      <div class="mb-3">
        <label class="form-label fw-bold">Select CSV or Excel File</label>
        <input type="file" name="scholars_file" class="form-control" accept=".csv, .xlsx" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-bold">Academic Year</label>
        <input type="text" name="academic_year" class="form-control" placeholder="e.g., 2024-2025" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-bold">Term</label>
        <select name="term" class="form-control" required>
          <option value="" disabled selected>Select term</option>
          <option value="1st">1st Term</option>
          <option value="2nd">2nd Term</option>
          <option value="3rd">3rd Term</option>
        </select>
      </div>

<div class="mb-3 d-flex justify-content-end">
  <button class="btn btn-success" type="submit">Import</button>
</div>

    </form>
  </div>
</div>


<!-- ✅ JS Logic -->
<script>
function setupTableSearch(inputId, tableId) {
  document.getElementById(inputId).addEventListener("keyup", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll(`#${tableId} tbody tr`);
    rows.forEach(row => {
      let text = row.textContent.toLowerCase();
      row.style.display = text.includes(filter) ? "" : "none";
    });
  });
}
setupTableSearch("searchScholars", "tableScholars");
</script>

@endsection
