<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scholars GWA Records - NU Fairview SDAO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    @vite('resources/css/admin-activities.css')
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

<div class="container">
<h2 class="mb-3 fw-bold">Encode Scholars' GWA Records</h2>

<!-- ✅ Filters -->
<form method="GET" class="flex-wrap gap-3 mb-3 d-flex">

<select name="academic_year" class="form-select" style="width:200px;">
<option value="">All Academic Years</option>
@foreach($academicYears as $year)
<option value="{{ $year }}" {{ request('academic_year')==$year?'selected':'' }}>{{ $year }}</option>
@endforeach
</select>

<select name="term" class="form-select" style="width:200px;">
<option value="">All Terms</option>
@foreach($terms as $t)
<option value="{{ $t }}" {{ request('term')==$t?'selected':'' }}>{{ $t }} Term</option>
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

<select name="school" class="form-select" style="width:200px;">
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

<button class="btn btn-primary" type="submit">Filter</button>
<a href="{{ route('scholars-admin.scholars') }}" class="btn btn-secondary">Reset</a>
</form>

<!-- ✅ Search -->
<input type="text" id="searchInput" class="mb-3 search-box" placeholder="Search by name or student no...">

<!-- ✅ Save Button -->
<div class="mb-2 text-end">
<button id="saveAllBtn" class="btn-primary">Save All</button>
</div>

<!-- ✅ TABLE -->
<table>
<thead>
<tr>
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
@foreach($gwaRecords as $record)
<tr id="row-{{ $record['student_number'] }}"
    data-year="{{ $record['academic_year'] }}"
    data-term="{{ $record['term'] }}"
    data-type="{{ $record['scholarship_type'] }}"
    data-school="{{ $record['school'] }}"
    data-program="{{ $record['program'] }}"
    data-grade="{{ $record['gradeRequirement'] ?? $record['grade_requirement'] ?? $record['required_gwa'] }}"
    data-cutoff="{{ $record['eligibilityCutoff'] ?? $record['eligibility_cutoff'] ?? $record['cutoff_grade'] }}">

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

<script>
function toggleSchool(yearSlug, typeSlug, schoolSlug, btn) {
    const elem = document.getElementById(`school-${yearSlug}-${typeSlug}-${schoolSlug}`);
    if (elem) {
        elem.classList.toggle('active');
        btn.classList.toggle('active');
    }
}

function openYearTab(yearSlug) {
    document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content[id^="tab-year-"]').forEach(c => c.classList.remove('active'));

    document.querySelector(`.tab[onclick="openYearTab('${yearSlug}')"]`)?.classList.add('active');
    document.getElementById('tab-year-' + yearSlug)?.classList.add('active');

    const firstScholarship = document.querySelector(`#tab-year-${yearSlug} .tabs .tab`);
    firstScholarship?.click();
}

function openScholarshipTab(yearSlug, typeSlug) {
    const yearContainer = document.getElementById(`tab-year-${yearSlug}`);
    if (!yearContainer) return;

    yearContainer.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
    yearContainer.querySelectorAll('.tab-content[id^="tab-scholarship-"]').forEach(c => c.classList.remove('active'));

    const tabBtn = yearContainer.querySelector(`.tab[onclick="openScholarshipTab('${yearSlug}','${typeSlug}')"]`);
    tabBtn?.classList.add('active');
    document.getElementById(`tab-scholarship-${yearSlug}-${typeSlug}`)?.classList.add('active');
}

function openProgramTab(yearSlug, typeSlug, schoolSlug, programSlug) {
    const school = document.getElementById(`school-${yearSlug}-${typeSlug}-${schoolSlug}`);
    school?.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
    school?.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

    const program = document.getElementById(`tab-program-${yearSlug}-${typeSlug}-${schoolSlug}-${programSlug}`);
    program?.classList.add('active');

    const tabBtn = school?.querySelector(`.tab[onclick="openProgramTab('${yearSlug}','${typeSlug}','${schoolSlug}','${programSlug}')"]`);
    tabBtn?.classList.add('active');
}

function evaluateRow(row) {
    const statusCell = row.querySelector('.status');
    const gradeRequirement = parseFloat(row.dataset.grade);
    const eligibilityCutoff = parseFloat(row.dataset.cutoff);

    if (!isFinite(gradeRequirement) || !isFinite(eligibilityCutoff)) {
        statusCell.textContent = '-';
        return;
    }

    let eligibleCount = 0;
    let probationCount = 0;
    let revoked = false;
    let lastTermStatus = null;

    const terms = ['1st', '2nd', '3rd'];

    for (let term of terms) {
        const input = row.querySelector(`input[data-term="${term}"]`);
        if (!input) continue;

        const val = parseFloat(input.value);
        if (!isFinite(val)) continue;

        if (val <= gradeRequirement) {
            eligibleCount++;
            lastTermStatus = 'Eligible';
        } 
        else if (val <= eligibilityCutoff) {
            probationCount++;
            lastTermStatus = 'Probationary';
        } 
        else {
            revoked = true;
            lastTermStatus = 'Revoked';
        }
    }

    let finalStatus = '';

    if (revoked || probationCount >= 2) {
        finalStatus = 'Revoked';
    } else if (lastTermStatus === 'Probationary' || probationCount === 1) {
        finalStatus = 'Probationary';
    } else {
        finalStatus = 'Eligible';
    }

    statusCell.textContent = finalStatus;
}


document.getElementById('saveAllBtn').addEventListener('click', function () {
    const scholars = [];

    document.querySelectorAll('tr[id^="row-"]').forEach(row => {
        const studentNumber = row.querySelector('td:first-child').textContent.trim();
        const academicYear = row.dataset.year;
        const scholarshipType = row.dataset.type;

const scholar = {
    student_number: studentNumber,
    academic_year: academicYear,
    term: row.dataset.term,           // ✅ Add term
    scholarship_type: scholarshipType,

            gwa_1st: row.querySelector('input[data-term="1st"]').value || null,
            gwa_2nd: row.querySelector('input[data-term="2nd"]').value || null,
            gwa_3rd: row.querySelector('input[data-term="3rd"]').value || null,
            status: row.querySelector('.status').textContent.trim()
        };

        scholars.push(scholar);
    });

    fetch("{{ route('scholars-admin.saveAllGwa') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ scholars })
    })
    .then(res => {
        if (!res.ok) throw new Error("HTTP " + res.status);
        return res.json();
    })
    .then(data => {
        if (data.success) {
            alert("✅ All GWA records saved successfully!");
        } else {
            alert("❌ Failed to save records.");
        }
    })
    .catch(err => {
        console.error("⚠ Save error:", err);
        alert("⚠ Error saving records.");
    });
});

document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.gwa-input').forEach(input => {

        // Run when typing
        input.addEventListener('keyup', function() {
            evaluateRow(this.closest('tr'));
        });

        // Run when value changes (mouse scroll / arrow / paste)
        input.addEventListener('input', function() {
            evaluateRow(this.closest('tr'));
        });

        // Run when leaving field
        input.addEventListener('change', function() {
            evaluateRow(this.closest('tr'));
        });
    });

    // ✅ Evaluate all rows on page load
    document.querySelectorAll('tr[id^="row-"]').forEach(row => {
        evaluateRow(row);
    });
});

document.getElementById("searchInput").addEventListener("keyup", function () {
    const filter = this.value.toLowerCase().trim();

    // Hide all rows first
    document.querySelectorAll("tr[id^='row-']").forEach(row => {
        const studentNumber = row.querySelector("td:nth-child(2)")?.textContent.toLowerCase() || "";
        const scholarName = row.querySelector("td:nth-child(3)")?.textContent.toLowerCase() || "";
        row.style.display = (studentNumber.includes(filter) || scholarName.includes(filter)) ? "" : "none";
    });

    // Hide/show tables depending on visible rows
    document.querySelectorAll("table").forEach(table => {
        const hasVisibleRows = Array.from(table.querySelectorAll("tbody tr")).some(row => row.style.display !== "none");
        table.style.display = hasVisibleRows ? "" : "none";
    });

    if (!filter) return; // If search is empty, do nothing

    // Reset all tabs and school-program sections
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.school-programs').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.school-btn').forEach(b => b.classList.remove('active'));

    // Expand tabs for first matching row
    const firstMatch = Array.from(document.querySelectorAll("tr[id^='row-']")).find(row => row.style.display !== "none");
    if (!firstMatch) return;

    const yearSlug = firstMatch.dataset.year ? firstMatch.dataset.year.toLowerCase().replace(/\s+/g,'-') : null;
    const typeSlug = firstMatch.dataset.type ? firstMatch.dataset.type.toLowerCase().replace(/\s+/g,'-') : null;
    const studentNameSlug = firstMatch.querySelector('td:nth-child(3)').textContent.toLowerCase().replace(/\s+/g,'-') || null;

    if (!yearSlug || !typeSlug) return;

    // Open Academic Year tab
    openYearTab(yearSlug);

    // Open Scholarship tab
    openScholarshipTab(yearSlug, typeSlug);

    // Find school & program for this student
    const schoolDiv = firstMatch.closest(".school-programs");
    if (schoolDiv) {
        const schoolBtn = schoolDiv.previousElementSibling;
        if (schoolBtn && !schoolDiv.classList.contains("active")) {
            schoolBtn.click(); // Open school programs
        }

        // Open program tab
        const programTabs = Array.from(schoolDiv.querySelectorAll(".tabs .tab"));
        const programTab = programTabs.find(tab => {
            const tabSlug = tab.textContent.toLowerCase().replace(/\s+/g,'-');
            return firstMatch.dataset.program ? tabSlug === firstMatch.dataset.program.toLowerCase().replace(/\s+/g,'-') : true;
        });

        if (programTab) {
            programTab.click();
        }
    }

    // Scroll to the matching row
    firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center' });
});



</script>

</body>
</html>