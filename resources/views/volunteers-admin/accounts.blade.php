<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Accounts - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite('resources/css/volunteers.css')


</head>
<body>

<header class="custom-header">
  <div class="header-left">
    <div class="logo">
      <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
      <span>Student Developments and Activities Office - Activities</span>
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

<!-- ✅ Navigation -->
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



<form id="filterForm" method="GET" action="{{ route('volunteers-admin.accounts') }}"
      class="flex-wrap gap-2 p-3 mb-3 bg-white border rounded shadow-sm filter-bar d-flex align-items-center justify-content-between">

       <div class="flex-wrap gap-2 d-flex align-items-center">

        <select name="school" class="form-select" style="width: 180px;">
            <option value="">All Schools</option>
            @foreach($schools as $schoolData)
                <option value="{{ $schoolData['name'] }}" 
                        {{ request('school') == $schoolData['name'] ? 'selected' : '' }}>
                    {{ $schoolData['name'] }}
                </option>
            @endforeach
        </select>

        <select name="program" class="form-select" style="width: 200px;">
            <option value="">All Programs</option>
            @foreach($schools as $schoolData)
                @foreach($schoolData['programs'] as $program)
                    <option value="{{ $program }}" 
                            {{ request('program') == $program ? 'selected' : '' }}>
                        {{ $program }}
                    </option>
                @endforeach
            @endforeach
        </select>

        <button type="submit" class="btn btn-primary ms-2"><i class = "bi bi-funnel me-1"></i>Apply</button>
    <button type="button" 
            class="btn btn-secondary ms-2" 
            onclick="window.location.href='{{ route('volunteers-admin.accounts') }}'">
      <i class="bi bi-arrow-clockwise me-1"></i> Reset
    </button>        
    </div>

    <!-- RIGHT SIDE: Create Account -->
    <button type="button" class="btn btn-success"
            onclick="document.getElementById('createAspiringScholarAccount').style.display='block'">
        + Create Aspiring Scholar Account
    </button>

</form>



    <!-- LEFT SIDE: Manage + Delete Selected -->
<div class="gap-2 mb-3 d-flex align-items-center">

    <!-- MANAGE BUTTON -->
    <button class="gap-1 btn btn-primary d-flex align-items-center" id="manageBtn" type="button">
        <i class="bi bi-gear"></i> Manage
    </button>

    <!-- DELETE FORM (POST + hidden by default) -->
    <form method="POST" action="{{ route('volunteers-admin.archive-aspiring-scholar') }}"
          id="deleteForm" class="d-inline" style="display: none;">
        @csrf
        <button type="submit" class="gap-1 btn btn-danger d-flex align-items-center">
            <i class="bi bi-trash"></i> Delete Selected
        </button>
    </form>

</div>
    
<div id="scholarsTable" class="px-5 py-4 container-fluid">
<div id="apiringScholarsTable" class="rounded shadow-sm table-responsive">

<table id="apiringScholarsTable">
  <thead>

    <!-- TITLE ROW -->
    <tr class="table-title-row">
      <th colspan="11" class="py-3 text-center fw-bold fs-5">
        Aspiring Scholars
      </th>
    </tr>

    <!-- ACTUAL HEADER ROW -->
    <tr>
      <th class="manage-col" style="display:none; width: 80px;">
        <input type="checkbox" id="selectAll"> Select All
      </th>
      
      <th>#</th> 
      <th>Student No.</th>
      <th>First Name</th>
      <th>Middle Name</th>
      <th>Last Name</th>
      <th>Suffix</th>
      <th>Email</th>
      <th>School</th>
      <th>Program</th>
      <th>Created At</th>
    </tr>

  </thead>

  <tbody>
 @foreach ($aspiringScholars as $index => $scholar)
<tr>

  <!-- Manage column checkbox -->
  <td class="manage-col" style="display:none;">
    <input type="checkbox" name="selected_accounts[]" 
           form="deleteForm" value="{{ $scholar['id'] }}">
  </td>

  <!-- Row number (#) – auto counts with pagination -->
  <td class="text-center fw-bold">
    {{ ($aspiringScholars->currentPage() - 1) * $aspiringScholars->perPage() + ($index + 1) }}
  </td>

  <td>{{ $scholar['student_no'] }}</td>
  <td>{{ $scholar['first_name'] }}</td>
  <td>{{ $scholar['middle_name'] }}</td>
  <td>{{ $scholar['last_name'] }}</td>
  <td>{{ $scholar['suffix'] }}</td>
  <td>{{ $scholar['email'] }}</td>
  <td>{{ $scholar['school'] }}</td>
  <td>{{ $scholar['program'] }}</td>
  <td>{{ $scholar['created_at'] }}</td>

</tr>
@endforeach

  </tbody>

</table>
</div>


  {{-- Pagination links --}}
  {{ $aspiringScholars->links() }}
</div>

<!-- ✅ Create Aspiring Scholar Account Modal -->
<div id="createAspiringScholarAccount" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Aspiring Scholar Account</h3>
      <span class="close" onclick="document.getElementById('createAspiringScholarAccount').style.display='none'">&times;</span>
    </div>

    <form method="POST" action="{{ route('volunteers-admin.create-aspiring-scholar-account') }}">
      @csrf

      <label>School</label>
<select name="school" id="schoolDropdown" class="form-control" required>
  <option value="" disabled selected>Select School</option>
  @foreach ($schools as $school)
    <option value="{{ $school['name'] }}" data-programs='@json($school["programs"])'>
      {{ strtoupper($school['name']) }}
    </option>
  @endforeach
</select>

<br/>

<label>Program</label>
<select name="program" id="programDropdown" class="form-control" required>
  <option value="" disabled selected>Select a School First</option>
</select>
<br/>

<label>Student Number</label>
<input type="text" name="student_no" required><br/>

      <label>First Name</label>
      <input type="text" name="first_name" required><br/>

      <label>Middle Name</label>
      <input type="text" name="middle_name"><br/>

      <label>Last Name</label>
      <input type="text" name="last_name" required><br/>

      <label>Suffix</label>
      <input type="text" name="suffix"><br/>

      <label>Email</label>
      <input type="email" name="email" required><br/>

      <button class="btn" type="submit">Create</button>
    </form>
  </div>
</div>

<!-- ✅ JS Search -->
<script>

document.getElementById('schoolDropdown').addEventListener('change', function () {
  let selectedOption = this.options[this.selectedIndex];
  let programs = JSON.parse(selectedOption.getAttribute('data-programs'));
  let programDropdown = document.getElementById('programDropdown');

  programDropdown.innerHTML = '';
  programs.forEach(program => {
    let opt = document.createElement('option');
    opt.value = program;
    opt.textContent = program;
    programDropdown.appendChild(opt);
  });
});


 const manageBtn = document.querySelector('#manageBtn');
  const deleteForm = document.querySelector('#deleteForm');
  const manageCols = document.querySelectorAll('.manage-col');
  const selectAll = document.querySelector('#selectAll');
  let manageMode = false;

  manageBtn.addEventListener('click', () => {
    manageMode = !manageMode;

    // Toggle Manage columns visibility
    manageCols.forEach(col => {
      col.style.display = manageMode ? '' : 'none';
    });

    // Toggle Delete Selected button
    deleteForm.style.display = manageMode ? 'inline-block' : 'none';

    // Toggle button label
    manageBtn.textContent = manageMode ? 'Cancel' : 'Manage';

    // Reset all checkboxes when exiting manage mode
    if (!manageMode) {
      selectAll.checked = false;
      document.querySelectorAll('input[name="selected_accounts[]"]').forEach(cb => cb.checked = false);
    }
  });

  // ✅ SELECT ALL CHECKBOX FUNCTIONALITY
  selectAll.addEventListener('change', function() {
    const checked = this.checked;
    document.querySelectorAll('input[name="selected_accounts[]"]').forEach(cb => {
      cb.checked = checked;
    });
  });


</script>

</body>
</html>
