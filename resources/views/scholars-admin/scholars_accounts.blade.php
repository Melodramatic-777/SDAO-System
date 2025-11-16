<!DOCTYPE html>
<html>
<head>
    <title>Activities Admin</title>
</head>
<meta charset="UTF-8">
<title>Home - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite('resources/css/volunteers.css')

<style>
table {
  border-collapse: collapse;
  width: 100%;
  border: 1px solid #ddd;
}

table thead {
  background: #FFD700;       /* gold header */
  color: #000;               /* black text */
}

table th, 
table td {
  border: 1px solid #ddd;
  text-align: left;
  font-size: 14px;
  padding: 12px;
}

table th {
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  text-align: center !important;
  vertical-align: middle !important;
}

table tbody tr:nth-child(even) {
  background-color: #f9f9f9; /* zebra striping */
}

table tbody tr:hover {
  background-color: #fff8dc; /* soft highlight */
}

.search-input {
    width: 300px;
    max-width: 100%;
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    font-size: 14px;
    transition: all 0.2s ease-in-out;
}

/* Focus Effect */
.search-input:focus {
    outline: none;
    border-color: #003DA5;        /* matching primary color */
    box-shadow: 0 2px 8px rgba(0,61,165,0.3);
}

/* Import Button for consistency */
.import-btn {
    background-color: #003DA5;
    color: #fff;
    border: none;
    padding: 8px 18px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    transition: all 0.2s ease-in-out;
}

.import-btn:hover {
    background-color: #00297d;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

table thead tr:first-child {
  background-color: #003DA5 !important;
  color: white !important;
}

.status-cell {
    text-align: center !important;
}

.center-cell {
    text-align: center !important;
}

  </style>
<body>

<header class="custom-header">
    <div class="header-left">
        <div class="logo">
            <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
            <span>Student Developments and Activities Office - SDA Associate</span>
        </div>
    </div>
    <div class="header-right-group">
        <p>Welcome, {{ session('scholars_admin.first_name') }}!</p>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="side-button">
                <img src="{{ asset('images/Side.png') }}" alt="Side" height="30">
            </button>
        </form>
    </div>
</header>

<header>
<nav>
    <a href="{{ route('scholars-admin.dashboard') }}">Dashboard</a>
    <a href="{{ route('scholars-admin.scholars_accounts') }}">Accounts</a>
    <a href="{{ route('scholars-admin.scholars') }}">Scholars</a>
    <a href="{{ route('scholars-admin.scholarships') }}">Scholarships</a>
</nav>
</header>

<!-- ✅ Scholars Section -->

<!-- ✅ FILTER BAR -->
<form method="GET" 
      class="flex-wrap gap-2 p-3 mb-3 bg-white border rounded shadow-sm filter-bar d-flex align-items-center justify-content-between">

  <!-- Left: Filter Dropdowns -->
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

             <button class="btn btn-primary ms-2" 
          onclick="document.getElementById('importScholarsModal').style.display='block'">
    <i class="bi bi-file-earmark-arrow-up me-1"></i> Import New Scholars CSV
  </button>
  </div>

</form>

<div id="Scholars" class="px-5 py-4 container-fluid">

  <!-- Scholars Table -->
  <div class="rounded shadow-sm table-responsive">
    <table id="tableScholars">
<thead>
      <tr class="table-title-row">
        <th colspan="10" class="py-3 text-center fw-bold fs-5">
          Scholars A.Y. {{ request('academic_year') ?? $latestYear }} — {{ request('term') ?? $latestTerm }} Term
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
          <td>{{ $scholar['last_name'] }}, {{ $scholar['first_name'] }} {{ $scholar['middle_name'] ?? '' }} {{ $scholar['suffix'] ?? '' }}</td>
<td>
    {{ $scholar['birthday'] ? \Carbon\Carbon::parse($scholar['birthday'])->format('F d, Y') : 'N/A' }}
</td>          <td>{{ $scholar['school'] ?? 'N/A' }}</td>
<td class="center-cell">
    {{ $scholar['program'] ?? '' }}
</td>
<td class="center-cell">
    {{ $scholar['scholarship_type'] ?? '' }}
</td>
          <td class="status-cell">
            @if(strtolower($status) === 'eligible')
              <span class="badge bg-success">Eligible</span>
            @elseif(strtolower($status) === 'probationary')
              <span class="badge bg-warning text-dark">Probationary</span>
            @elseif(strtolower($status) === 'ineligible' || strtolower($status) === 'revoked')
              <span class="badge bg-danger">Revoked</span>
            @else
              <span class="badge bg-secondary">{{ $status }}</span>
            @endif
          </td>
          <td>{{ $scholar['created_at'] ?? 'N/A' }}</td>
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
<div id="importScholarsModal" class="modal">
  <div class="p-4 rounded shadow-lg modal-content">
    <div class="mb-3 modal-header d-flex justify-content-between align-items-center">
      <h3 class="m-0">Import New Scholars CSV</h3>
      <span class="close fs-3" style="cursor:pointer"
        onclick="document.getElementById('importScholarsModal').style.display='none'">&times;</span>
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

  <button class="btn btn-success w-100" type="submit">Import</button>
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

</body>
</html>
