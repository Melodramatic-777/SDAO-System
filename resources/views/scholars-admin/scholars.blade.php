<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scholars GWA Records - NU Fairview SDAO</title>
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



<!-- ✅ Filter Controls -->
<!-- ✅ Filter Controls -->
<div class="flex-wrap p-3 mb-3 bg-white border rounded shadow-sm filter-bar d-flex align-items-center justify-content-between">
  
  <!-- Left Section: Filters -->
  <div class="flex-wrap gap-2 d-flex align-items-center">
    <select name="academic_year" class="form-select" style="width:180px;">
      <option value="">All Academic Years</option>
      @foreach($academicYears as $year)
        <option value="{{ $year }}" {{ request('academic_year')==$year?'selected':'' }}>{{ $year }}</option>
      @endforeach
    </select>

    <select name="term" class="form-select" style="width:150px;">
      <option value="">All Terms</option>
      @foreach($terms as $t)
<option value="{{ $t }}" {{ request('term')==$t?'selected':'' }}>{{ $t }}</option>
      @endforeach
    </select>

    <select name="scholarship_type" class="form-select" style="width:200px;">
      <option value="">All Scholarship Types</option>
      @foreach($scholarshipTypes as $type)
        <option value="{{ $type }}" {{ request('scholarship_type')==$type?'selected':'' }}>
          {{ $type }}
        </option>
      @endforeach
    </select>

    <select name="school" class="form-select" style="width:180px;">
      <option value="">All Schools</option>
      @foreach($schools as $school)
        <option value="{{ $school }}" {{ request('school')==$school?'selected':'' }}>{{ $school }}</option>
      @endforeach
    </select>

    <select name="program" class="form-select" style="width:200px;">
      <option value="">All Programs</option>
      @foreach($programs as $program)
        <option value="{{ $program }}" {{ request('program')==$program?'selected':'' }}>{{ $program }}</option>
      @endforeach
    </select>

        <button type="submit" class="btn btn-primary ms-2">
      <i class="bi bi-funnel me-1"></i> Filter
    </button>
    
    <button  type="button"  class="btn btn-secondary ms-2"onclick="window.location.href='{{ route('scholars-admin.scholars') }}'">
      <i class="bi bi-arrow-clockwise me-1"></i> Reset
    </button>
    

<input type="text" id="searchInput" class="form-control search-input" placeholder="Search by name or student no..." style="width: 220px;">
<button id="saveAllBtn" class="btn btn-success ms-2">
    <i class="bi bi-save me-1"></i> Save All GWA
</button>
  </div>
</div>

<div id="Scholars" class="px-5 py-4 container-fluid">
    <!-- ✅ TABLE -->
    <table>
        <thead>

      <tr class="table-title-row">
        <th colspan="10" class="py-3 text-center fw-bold fs-5">
          Scholars A.Y. {{ $latestAcademicYear }} — {{ $latestTerm }} Term
        </th>
      </tr>

            <!-- Existing Columns -->
            <tr>
                <th>#</th>
                <th>Student No.</th>
                <th>Scholar Name</th>
                <th>School</th>
                <th>Program</th>
                <th>Scholarship Type</th>
                <th>1st</th>
                <th>2nd</th>
                <th>3rd</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @foreach($gwaRecords as $index => $record)
              <tr id="row-{{ $record['student_number'] }}"
                data-firebase="{{ $record['firebase_id'] }}"               
                data-year="{{ $record['academic_year'] }}"
                data-term="{{ $record['term'] }}"
                data-type="{{ $record['scholarship_type'] }}"
                data-school="{{ $record['school'] }}"
                data-program="{{ $record['program'] }}"
                data-grade="{{ $record['grade_requirement'] }}"
                data-cutoff="{{ $record['eligibility_cutoff'] }}">

                <td style="text-align:center; width:50px;">{{ $loop->iteration }}</td>
                <td>{{ $record['student_number'] }}</td>
                <td>{{ $record['scholar_name'] }}</td>
                <td>{{ $record['school'] }}</td>
                <td>{{ $record['program'] }}</td>
                <td>{{ $record['scholarship_type'] }}</td>

                <td><input type="number" step="0.01" class="gwa-input" data-term="1st" value="{{ $record['gwa_1st'] }}"></td>
                <td><input type="number" step="0.01" class="gwa-input" data-term="2nd" value="{{ $record['gwa_2nd'] }}"></td>
                <td><input type="number" step="0.01" class="gwa-input" data-term="3rd" value="{{ $record['gwa_3rd'] }}"></td>

                <td class="status">{{ $record['status'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ✅ Evaluate all rows when the page loads
    document.querySelectorAll('tr[data-grade]').forEach(evaluateRow);

    // ✅ Attach listeners to all GWA inputs (1st, 2nd, 3rd)
    document.querySelectorAll('.gwa-input').forEach(input => {
        ['input', 'keyup', 'change'].forEach(event => {
            input.addEventListener(event, function() {
                const row = this.closest('tr');
                evaluateRow(row);
            });
        });
    });

    });

function evaluateRow(row) {
    const statusCell = row.querySelector('.status');
    const gradeRequirement = parseFloat(row.dataset.grade);
    const gradeCutoff = parseFloat(row.dataset.cutoff);

    if (!isFinite(gradeRequirement) && !isFinite(gradeCutoff)) {
        statusCell.textContent = '-';
        statusCell.style.color = 'black';
        return;
    }

    const terms = ['1st', '2nd', '3rd'];
    let failedCount = 0;
    let hasValue = false;

    for (let term of terms) {
        const input = row.querySelector(`input[data-term="${term}"]`);
        if (!input) continue;
        const gwa = parseFloat(input.value);
        if (!isFinite(gwa)) continue;
        hasValue = true;

      
        if (isFinite(gradeCutoff) && gwa < gradeCutoff) {
            failedCount++; 
        } else if (gwa < gradeRequirement) {
            failedCount++; 
        }
    }

    let finalStatus = '-';
    if (hasValue) {
        if (failedCount === 0) finalStatus = 'Eligible';
        else if (failedCount === 1) finalStatus = 'Probationary';
        else if (failedCount >= 1) finalStatus = 'Revoked';
    }

    statusCell.textContent = finalStatus;
    statusCell.style.fontWeight = 'bold';
    statusCell.style.color =
        finalStatus === 'Eligible' ? 'green' :
        finalStatus === 'Probationary' ? 'orange' :
        finalStatus === 'Revoked' ? 'red' : 'black';
}


document.getElementById('saveAllBtn').addEventListener('click', function () {

    let rows = document.querySelectorAll('tbody tr');  
    let updates = [];

    rows.forEach(row => {
        const studentId   = row.dataset.firebase; 
        const year        = row.dataset.year;
        const term        = row.dataset.term;
        const type        = row.dataset.type;

        const gwa1 = row.querySelector('input[data-term="1st"]').value || null;
        const gwa2 = row.querySelector('input[data-term="2nd"]').value || null;
        const gwa3 = row.querySelector('input[data-term="3rd"]').value || null;
        const status = row.querySelector('.status').textContent;

        updates.push({
            firebase_id: studentId,
            academic_year: year,
            term: term,
            scholarship_type: type,
            gwa_1st: gwa1,
            gwa_2nd: gwa2,
            gwa_3rd: gwa3,
            status: status
        });
    });

    fetch("{{ route('scholars-admin.scholars.save') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ records: updates })
    })
    .then(res => res.json())
    .then(data => {
        alert("GWA Records saved successfully!");
    })
    .catch(err => {
        alert("Error saving GWA");
        console.error(err);
    });
});


</script>

</body>
</html>