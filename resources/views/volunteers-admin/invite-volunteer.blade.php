@extends('volunteers-admin.layout')

@section('content')

<header class="custom-header">
    <div class="header-left">

        <!-- SIDE BUTTON MOVED HERE -->
<button type="submit" class="side-button header-btn">
    <img src="{{ asset('images/Side.png') }}" alt="Side" height="30">
</button>


        <div class="logo">
            <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
            <span>Student Developments and Activities Office</span>
        </div>
    </div>

    <div class="header-right-group">
        <p>Welcome, {{ session('volunteers_admin.first_name') }}!</p>
    </div>
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

          <a href="{{ route('volunteers-admin.dashboard') }}" class="text-decoration-none">
        ← Back to Dashboard
    </a>
    
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

@endsection
