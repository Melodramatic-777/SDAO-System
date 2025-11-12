<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Student Volunteers - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  @vite('resources/css/volunteers.css')

  <style>
    body { font-family: 'Inter', sans-serif; background: #f9f6f3; }
    #scholarsTable { transition: opacity 0.3s ease-in-out; max-width: 900px; margin: 0 auto; }

    .table-title-row th {
      background-color: #004aad !important; color: white !important; text-align: center; font-size: 1.25rem;
    }

    .table-header-row th {
      background-color: #FFD700 !important; color: black !important; font-weight: bold !important; text-align: center;
    }

 
    .icon-btn {
  background: none;
  border: none;
  color: #000;
  cursor: pointer;
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  padding: 0;
}

.icon-btn:hover {
  color: #0056b3;
  transform: scale(1.1);
  transition: 0.2s;
}



  </style>
</head>

<body>
<header class="custom-header">
  <div class="header-left">
    <div class="logo">
      <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
      <span>Student Developments and Activities Office - SDA Assistant</span>
    </div>
  </div>

  <div class="header-right-group">
    <p>Welcome, {{ session('volunteers_admin.first_name') }}!</p>
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
    <a href="{{ route('volunteers-admin.dashboard') }}">Leader Board</a>
    <a href="{{ route('volunteers-admin.opportunities') }}">Volunteer Requests</a>
    <a href="{{ route('volunteers-admin.history') }}">History</a>
    <a href="{{ route('volunteers-admin.student-volunteers') }}">Student Volunteers</a>
    <a href="{{ route('volunteers-admin.accounts') }}">Accounts</a>
    <a href="{{ route('volunteers-admin.inquiries') }}">Contact</a>
  </nav>
</header>

<div class="container mt-4 text-center">

  <!-- Sort & Search -->
  <div class="mb-3 d-flex justify-content-center">
    <form id="filterForm" method="GET" action="{{ route('volunteers-admin.student-volunteers') }}" 
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
<!-- Table -->
<div id="scholarsTable" class="mt-3">
  <table class="table mx-auto text-center align-middle table-bordered">
    <thead>
      <tr class="table-title-row">
        <th colspan="5" class="text-center fs-5">
          List of Student Volunteers A.Y. {{ $latestYear }}
        </th>
      </tr>
      <tr class="table-header-row">
        <th>#</th>
        <th>Scholarship Type</th>
        <th>Name</th>
        <th>Total Volunteer Hour</th>
        <th>View</th>
      </tr>
    </thead>
    <tbody>
      @forelse($scholars as $index => $scholar)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $scholar['scholarship_type'] ?? 'N/A' }}</td>
          <td>
            {{ $scholar['last_name'] ?? '' }},
            {{ $scholar['first_name'] ?? '' }}
            {{ $scholar['middle_name'] ?? '' }}
            {{ $scholar['suffix'] ?? '' }}
          </td>

          <!-- ✅ Add volunteer hours input + total hours -->
          <td>
            <div class="gap-2 d-flex justify-content-center align-items-center">
              <input 
                type="number" 
                class="form-control form-control-sm" 
                style="width: 90px;" 
                placeholder="+ hours"
                min="1"
              >
              <span class="fw-bold">{{ $scholar['volunteer_hours'] ?? 0 }}</span>
            </div>
          </td>
          <td>
            <a href="{{ route('volunteers-admin.student-volunteer-detail', $scholar['student_no'] ?? '') }}"
              class="icon-btn {{ empty($scholar['student_no']) ? 'disabled text-muted' : '' }}"
              title="View Profile">
              <i class="bi bi-eye fs-5"></i>
            </a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="5" class="text-center text-muted">No student volunteers found</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>


    @if($scholars instanceof \Illuminate\Pagination\LengthAwarePaginator)
      <div class="mt-2 d-flex justify-content-center align-items-center">
        <span class="me-3">
          Showing {{ $scholars->firstItem() }} to {{ $scholars->lastItem() }} of {{ $scholars->total() }}
        </span>
        <div>
          {{ $scholars->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
      </div>
    @endif
  </div>
</div>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
