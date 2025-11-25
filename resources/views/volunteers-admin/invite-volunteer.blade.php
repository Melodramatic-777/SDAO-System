<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  @vite('resources/css/volunteers.css')

  <style>
    body {
      font-family: 'Inter', sans-serif;
    }

    #scholarsTable {
      transition: opacity 0.3s ease-in-out;
      max-width: 900px;
      margin: 0 auto;
    }

    .table-title-row th {
      background-color: #004aad !important;
      color: white !important;
      text-align: center;
      font-size: 1.25rem;
    }

    .table-header-row th {
      background-color: #FFD700 !important;
      color: black !important;
      font-weight: bold !important;
      text-align: center;
    }

    .custom-modal-content {
      background: #fff;
      margin: 5% auto;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    .custom-modal-content .modal-header {
      border-bottom: 2px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .custom-modal-content .modal-body {
      max-height: 400px;
      overflow-y: auto;
    }
    .custom-modal-content .modal-footer {
      border-top: 2px solid #eee;
    }

    .view-details-btn {
      background: none;
      border: none;
      padding: 0;
      margin: 0;
      font: inherit;
      color: inherit;
      cursor: pointer;
      text-decoration: none;
    }
    .view-details-btn:hover {
      color: #0d6efd;
      text-decoration: underline;
    }

    .logout-btn {
      background-color: #f8f9fa !important;
      border: 0px solid #ddd !important;
    }

    .logout-btn:focus,
    .logout-btn:active {
      outline: none !important;
      box-shadow: none !important;
      border-color: #ddd !important;
    }
  </style>
</head>
<body>

<header class="p-3 custom-header d-flex justify-content-between align-items-center bg-light">
  <div class="header-left d-flex align-items-center">
    <div class="logo d-flex align-items-center">
      <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30" class="me-2">
      <span>Student Developments and Activities Office - SDA Assistant</span>
    </div>
  </div>
  <div class="header-right-group d-flex align-items-center">
    <p class="mb-0 me-3">Welcome, {{ session('volunteers_admin.first_name') }}!</p>
    <form method="POST" action="{{ route('admin.logout') }}">
      @csrf
      <button type="submit" class="p-1 border btn btn-light rounded-circle logout-btn">
        <img src="{{ asset('images/Side.png') }}" alt="Side" height="30">
      </button>
    </form>
  </div>
</header>

<header>
  <nav>
    <a href="{{ route('volunteers-admin.dashboard') }}">Dashboard</a>
    <a href="{{ route('volunteers-admin.opportunities') }}">Volunteer Requests</a>
    <a href="{{ route('volunteers-admin.history') }}">History</a>
    <a href="{{ route('volunteers-admin.student-volunteers') }}">Student Volunteers</a>
    <a href="{{ route('volunteers-admin.accounts') }}">Accounts</a>
    <a href="{{ route('volunteers-admin.inquiries') }}">Contact</a>
  </nav>
</header>

<div class="container mt-4 text-center">

  <!-- Sort and Search -->
  <div class="mb-3 d-flex justify-content-center">
    <form id="filterForm" method="GET" action="{{ route('volunteers.invite') }}" 
          class="flex-wrap gap-2 d-flex justify-content-center">
      <label for="scholarship_type" class="d-flex align-items-center fw-bold me-2">
        Filter by:
      </label>
      <select name="scholarship_type" id="scholarship_type"
              class="form-select me-2" style="width: 180px;">
        <option value="">All</option>
        @foreach($scholarshipTypes as $type)
          <option value="{{ $type }}" {{ request('scholarship_type') == $type ? 'selected' : '' }}>
            {{ $type }}
          </option>
        @endforeach
      </select>

      <div class="input-group" style="width: 250px;">
        <input type="text" name="search" id="searchInput" class="form-control"
               placeholder="Search..." value="{{ request('search') }}">
      </div>
    </form>
  </div>

  <!-- Table -->
  <div id="scholarsTable" class="mt-3">
<table class="table mx-auto text-center align-middle table-bordered">
  <thead>
    <tr class="table-title-row">
      <th colspan="5" class="text-center fs-5">
        List of Student Volunteers A.Y. {{ $latestYear }} - {{ ucfirst($latestTerm) }} Term
      </th>
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
        <td>
          {{ $scholar['last_name'] ?? '' }},
          {{ $scholar['first_name'] ?? '' }}
          {{ $scholar['middle_name'] ?? '' }}
          {{ $scholar['suffix'] ?? '' }}
        </td>
        <td>{{ $scholar['volunteer_hours'] ?? 0 }}</td>
        <td class="text-center">
          <button type="button" 
                  class="btn btn-primary inviteBtn"
                  data-id="{{ $scholar['student_no'] ?? '' }}"
                  data-name="{{ $scholar['first_name'] ?? '' }} {{ $scholar['last_name'] ?? '' }}"
                  data-scholarship="{{ $scholar['scholarship_type'] ?? 'N/A' }}"
                  data-hours="{{ $scholar['volunteer_hours'] ?? 0 }}"
                  data-bs-toggle="modal"
                  data-bs-target="#inviteModal">
            Invite
          </button>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="5" class="text-center text-muted">No volunteers found</td>
      </tr>
    @endforelse
  </tbody>
</table>


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

<!-- Invite Modal -->
<div class="modal fade" id="inviteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content custom-modal-content">
      <form id="inviteForm" method="POST" action="{{ route('volunteers-admin.inviteScholar') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Invite Scholar</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <!-- Activity Dropdown -->
          <div class="mb-3">
            <label for="activitySelect" class="form-label fw-bold">Activity</label>
            <select id="activitySelect" name="opportunity_id" class="form-select" required>
              <option value="">Select Activity</option>
              @foreach($opportunities as $opportunity)
                <option value="{{ $opportunity['opportunity_id'] ?? '' }}">
                  {{ $opportunity['title'] ?? 'Untitled' }}
                </option>
              @endforeach
            </select>
          </div>

          <!-- Scholar Info -->
          <div class="mt-3">
            <h5 id="scholarName" class="fw-bold"></h5>
            <p><strong>Scholarship Type:</strong> <span id="scholarScholarship"></span></p>
            <p><strong>Total Hours:</strong> <span id="scholarHours"></span></p>
          </div>

          <input type="hidden" id="scholarId" name="scholar_id">
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Submit Invite</button>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- Scripts -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  let searchInput = document.getElementById("searchInput");
  let scholarshipSelect = document.getElementById("scholarship_type");
  let tableContainer = document.getElementById("scholarsTable");

  function bindInviteButtons() {
    const inviteButtons = document.querySelectorAll(".inviteBtn");
    inviteButtons.forEach(btn => {
      btn.addEventListener("click", () => {
        document.getElementById("scholarName").textContent = btn.dataset.name;
        document.getElementById("scholarScholarship").textContent = btn.dataset.scholarship;
        document.getElementById("scholarHours").textContent = btn.dataset.hours + " Hours";
        document.getElementById("scholarId").value = btn.dataset.id;
      });
    });
  }

  function fetchData() {
    let search = searchInput.value;
    let scholarship = scholarshipSelect.value;
    tableContainer.style.opacity = "0.5";

    fetch(`{{ route('volunteers.invite') }}?search=${encodeURIComponent(search)}&scholarship_type=${encodeURIComponent(scholarship)}`)
      .then(response => response.text())
      .then(html => {
        let parser = new DOMParser();
        let doc = parser.parseFromString(html, "text/html");
        let newTable = doc.querySelector("#scholarsTable").innerHTML;

        tableContainer.style.opacity = "0";
        setTimeout(() => {
          tableContainer.innerHTML = newTable;
          tableContainer.style.opacity = "1";
          bindInviteButtons(); // rebind after refresh
        }, 200);
      })
      .catch(() => {
        tableContainer.innerHTML = `<div class="p-3 text-center text-danger">Error loading data</div>`;
      });
  }

  // Bind filters
  searchInput.addEventListener("keyup", fetchData);
  scholarshipSelect.addEventListener("change", fetchData);

  // Initial bind
  bindInviteButtons();
});


 
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
