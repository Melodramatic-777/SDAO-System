<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Scholarships Admin</title>
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
    </style>
</head>
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

@if(session('success'))
    <p style="color:green">{{ session('success') }}</p>
@endif


<!-- Scholarships Table -->

<div class="px-5 py-4 container-fluid">
  <div class="mb-3 d-flex justify-content-between align-items-center">
    <h4 class="fw-bold text-primary">Scholarship Types</h4>
    <button type="button"  class="btn btn-primary ms-2"  onclick="openAddModal()">
      <i class="bi bi-funnel me-1"></i> Add Scholarship Type
    </button>
  </div> 

  <div class="rounded shadow-sm table-responsive">
    <table id="scholarshipsTable">
      <thead>
        <tr class="table-title-row" >
          <th colspan="10" class="py-3 text-center fw-bold fs-5" >
            Scholarship Type List
          </th>
        </tr>
        <tr>
          <th>Name</th>
          <th>GWA Requirement</th>
          <th>Grade Cutoff</th>
          <th>Agreement PDF</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($types as $id => $type)
        <tr>
          <td>{{ $type['scholarship_name'] }}</td>
          <td>≥ {{ $type['grade_requirement'] }}</td>
          <td>< {{ $type['eligibility_cutoff'] }}</td>
          <td>
            @if(isset($type['requirements_pdf']))
              <a href="{{ $type['requirements_pdf'] }}" target="_blank" class="text-primary fw-semibold">View PDF</a>
            @else
              <span class="text-muted">No PDF</span>
            @endif
          </td>
          <td>
            <button class="btn btn-secondary ms-2" type="button" onclick='openManageModal("{{ $id }}", @json($type))'>
              <i class="bi bi-pencil-square me-1"></i> Manage
            </button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- Add Scholarship Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Scholarship Type</h3>
            <span class="close" onclick="closeAddModal()">&times;</span>
        </div>

        <form method="POST" action="{{ route('scholars-admin.scholarships.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="text" name="scholarship_name" placeholder="Scholarship Name" required>
            <input type="number" step="0.01" name="grade_requirement" placeholder="GWA Requirement" required>
            <input type="number" step="0.01" name="eligibility_cutoff" placeholder="Grade Cutoff" required>
            <input type="file" name="requirements_pdf" accept="application/pdf" required>

            <button type="submit">Add Scholarship</button>
        </form>
    </div>
</div>

<!-- Existing Manage Modal (unchanged) -->
<div id="manageModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Manage Scholarship</h3>
            <span class="close" onclick="closeManageModal()">&times;</span>
        </div>

        <form id="manageForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <input type="text" id="edit_name" name="scholarship_name" required>
            <input type="number" step="0.01" id="edit_grade" name="grade_requirement" required>
            <input type="number" step="0.01" id="edit_cutoff" name="eligibility_cutoff" required>

            <p>Current PDF:</p>
            <a id="edit_pdf" href="#" target="_blank">View PDF</a>
            <div id="pdfPreview" style="margin-top:10px; width:100%; height:400px; border:1px solid #ccc; display:none;">
                <iframe id="pdfFrame" src="" width="100%" height="100%"></iframe>
            </div>
            <input type="file" name="requirements_pdf" accept="application/pdf">

            <button type="submit">Update Scholarship</button>
        </form>

        <hr>

        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" onclick="return confirm('Delete this scholarship?')">
                Delete Scholarship
            </button>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
}
function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
}

function openManageModal(id, type) {
    document.getElementById('manageForm').action = '/scholars-admin/scholarships/' + id;
    document.getElementById('deleteForm').action = '/scholars-admin/scholarships/' + id;

    document.getElementById('edit_name').value = type.scholarship_name ?? '';
    document.getElementById('edit_grade').value = type.grade_requirement ?? '';
    document.getElementById('edit_cutoff').value = type.eligibility_cutoff ?? '';

    if (type.requirements_pdf) {
        document.getElementById('edit_pdf').href = type.requirements_pdf;
        document.getElementById('edit_pdf').innerText = "View PDF";
        document.getElementById('pdfFrame').src = type.requirements_pdf;
        document.getElementById('pdfPreview').style.display = 'block';
    } else {
        document.getElementById('edit_pdf').innerText = "No PDF";
        document.getElementById('edit_pdf').removeAttribute("href");
        document.getElementById('pdfFrame').src = "";
        document.getElementById('pdfPreview').style.display = 'none';
    }

    document.getElementById('manageModal').style.display = 'block';
}
function closeManageModal() {
    document.getElementById('manageModal').style.display = 'none';
}
</script>

</body>
</html>
