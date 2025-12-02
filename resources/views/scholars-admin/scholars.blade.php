@extends('scholars-admin.layout')

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
        <p>Welcome, {{ session('scholars_admin.first_name') }}!</p>
    </div>
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
    

<input type="text" id="searchInput" class="form-control search-input" placeholder="Search by name or student no..." style="width: 250px;">
<div class="mb-2 d-flex justify-content-end">
    <button id="saveAllBtn" class="btn btn-success">
        <i class="bi bi-save me-1"></i> Save All GWA
    </button>
</div>
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
<td>
    @php
        $parts = explode(' ', $record['scholar_name']);
        $last = array_pop($parts);       // last word
        $first = $parts[0] ?? '';        // first word
        $middle = implode(' ', array_slice($parts, 1)); // remaining middle
    @endphp

    {{ $last }}, {{ $first }} {{ $middle }}
</td>                <td>{{ $record['school'] }}</td>
<td class="text-center">{{ $record['program'] }}</td>
<td class="text-center">{{ $record['scholarship_type'] }}</td>


                <td><input type="text" class="gwa-input" data-term="1st" value="{{ $record['gwa_1st'] }}"></td>
                <td><input type="text"  class="gwa-input" data-term="2nd" value="{{ $record['gwa_2nd'] }}"></td>
                <td><input type="text"  class="gwa-input" data-term="3rd" value="{{ $record['gwa_3rd'] }}"></td>

<td class="text-center status status-cell"></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    //---------------------------------------------------------
    //  LIVE AUTO-FORMATTING: Add decimal automatically
    //---------------------------------------------------------
function formatLive(input) {
    let val = input.value.replace(/\s+/g, "");

    // Block more than one decimal
    let parts = val.split(".");
    if (parts.length > 2) {
        val = parts[0] + "." + parts[1];
    }

    // If only digits → auto add decimal
    if (/^\d+$/.test(val)) {
        input.value = val + ".";
        return;
    }

    // If digits + dot + decimals → allow only 2 decimals
    if (/^\d+\.\d*$/.test(val)) {

        // prevent typing more than 2 decimal places
        if (parts[1] && parts[1].length > 2) {
            parts[1] = parts[1].slice(0, 2);
            input.value = parts.join(".");
            return;
        }

        input.value = val;
        return;
    }

    // Remove invalid characters
    input.value = val.replace(/[^0-9.]/g, "");
}


// ----------------------------
// FINAL FORMAT (force 0.00)
// ----------------------------
function finalizeDecimal(input) {
    let val = input.value;

    if (val.endsWith(".")) {
        val = val.slice(0, -1);
    }

    if (!isNaN(val) && val !== "") {
        let num = parseFloat(val).toFixed(2);

        // STILL ensure only 2 decimals
        input.value = num.replace(/(\.\d{2})\d+/, "$1");
    } else {
        input.value = "";
    }
}

    //---------------------------------------------------------
    //  APPLY FORMATTERS TO ALL GWA INPUTS
    //---------------------------------------------------------
    document.querySelectorAll('.gwa-input').forEach(input => {

        // live formatting
        input.addEventListener('input', function () {
            formatLive(this);
        });

        // finalize on blur
        input.addEventListener('blur', function () {
            finalizeDecimal(this);
            evaluateRow(this.closest('tr'));
        });

        // format on page load
        finalizeDecimal(input);
    });


    //---------------------------------------------------------
    //  STATUS EVALUATION
    //---------------------------------------------------------
    document.querySelectorAll('tr[data-grade]').forEach(evaluateRow);

    function evaluateRow(row) {
        const statusCell = row.querySelector('.status');
        const gradeRequirement = parseFloat(row.dataset.grade);
        const gradeCutoff = parseFloat(row.dataset.cutoff);

        const terms = ['1st', '2nd', '3rd'];
        let failedCount = 0;
        let hasValue = false;

        for (let term of terms) {
            const input = row.querySelector(`input[data-term="${term}"]`);
            if (!input) continue;

            const gwa = parseFloat(input.value);
            if (!isFinite(gwa)) continue;

            hasValue = true;

            // Check fail conditions
            if (isFinite(gradeCutoff) && gwa < gradeCutoff) failedCount++;
            else if (gwa < gradeRequirement) failedCount++;
        }

        let finalStatus = "-";
        if (hasValue) {
            if (failedCount === 0) finalStatus = "Eligible";
            else if (failedCount === 1) finalStatus = "Probationary";
            else finalStatus = "Revoked";
        }

        // Render badge
        let badgeClass = {
            "Eligible": "status-eligible",
            "Probationary": "status-probationary",
            "Revoked": "status-revoked",
            "-": "status-na"
        }[finalStatus];

        statusCell.innerHTML = `<span class="status-badge ${badgeClass}">${finalStatus}</span>`;
    }


    //---------------------------------------------------------
    //  SAVE ALL BUTTON
    //---------------------------------------------------------
    document.getElementById('saveAllBtn').addEventListener('click', function () {

        let rows = document.querySelectorAll('tbody tr');
        let updates = [];

        rows.forEach(row => {
            updates.push({
                firebase_id: row.dataset.firebase,
                academic_year: row.dataset.year,
                term: row.dataset.term,
                scholarship_type: row.dataset.type,
                gwa_1st: row.querySelector('input[data-term="1st"]').value || null,
                gwa_2nd: row.querySelector('input[data-term="2nd"]').value || null,
                gwa_3rd: row.querySelector('input[data-term="3rd"]').value || null,
                status: row.querySelector('.status').textContent
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
        .then(data => alert("GWA Records saved successfully!"))
        .catch(err => {
            alert("Error saving GWA");
            console.error(err);
        });
    });

});
</script>

@endsection
