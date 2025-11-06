<!DOCTYPE html>
<html>
<head>
    <title>Scholarships Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    @vite('resources/css/admin-activities.css')


<style>

    .scholarship-container {
    margin: 20px 0;
}

.scholarship-container .add-btn {
    background-color: #003DA5;       /* Primary color */
    color: #fff;                     /* White text */
    border: none;
    padding: 10px 18px;              /* Slightly bigger padding */
    border-radius: 8px;              /* Rounded corners */
    cursor: pointer;
    font-size: 15px;                 /* Slightly larger font */
    font-weight: 600;                /* Bold text */
    box-shadow: 0 3px 6px rgba(0,0,0,0.1); /* Subtle shadow */
    transition: all 0.2s ease-in-out; /* Smooth hover transition */
    margin-bottom: 15px;
}

/* Add Scholarship Button */
.scholarship-container .add-btn:hover {
    background-color: #00297d;       /* Darker blue on hover */
    transform: translateY(-2px);     /* Slight lift effect */
    box-shadow: 0 5px 10px rgba(0,0,0,0.15); /* Stronger shadow on hover */
}

/* Optional: Active click effect */
.scholarship-container .add-btn:active {
    transform: translateY(0);        /* Press down effect */
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
}
.scholarship-container .add-btn:hover {
    background-color: #00297d;
}

/* Scholarships Table */
.scholarship-table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.scholarship-table th, 
.scholarship-table td {
    padding: 12px 15px;
    border: 1px solid #ddd;
    text-align: left;
}

.scholarship-table th {
    background-color: #003DA5;
    color: #fff;
}

.scholarship-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

/* Center the Action buttons */
.scholarship-table td:last-child {
    text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
    .scholarship-table, .scholarship-table th, .scholarship-table td {
        font-size: 14px;
    }
    .scholarship-container .add-btn {
        width: 100%;
        text-align: center;
    }
}

.scholarship-table td button {
    background-color: #0056b3;         /* Blue button */
    color: #fff;
    border: none;
    padding: 6px 12px;                 /* Smaller than add-btn */
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease-in-out;
}

/* Hover effect */
.scholarship-table td button:hover {
    background-color: #0056b3;         /* Darker blue */
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Active click effect */
.scholarship-table td button:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

<div class="scholarship-container">
    <button type="button" class="add-btn" onclick="openAddModal()">
        + Add Scholarship Type
    </button>
    <table class="scholarship-table">
        <thead>
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
                <td>{{ $type['grade_requirement'] }}≥</td>
                <td>{{ $type['eligibility_cutoff'] }}<</td>
                <td>
                    @if(isset($type['requirements_pdf']))
                        <a href="{{ $type['requirements_pdf'] }}" target="_blank">View PDF</a>
                    @else
                        No PDF
                    @endif
                </td>
                <td>
                    <button type="button" onclick='openManageModal("{{ $id }}", @json($type))'>
                        Manage
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
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
