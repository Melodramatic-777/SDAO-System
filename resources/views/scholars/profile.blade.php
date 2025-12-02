@extends('scholars.layout')

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
        <p>Welcome, {{ session('scholar_accounts.first_name') }}!</p>
    </div>
</header>


<div class="mx-5 mt-4 row align-items-center">

{{-- LEFT STUDENT INFO --}}
<div class="col-md-4">

    <h4 class="fw-bold">{{ $studentData['last_name'] }}, {{ $studentData['first_name'] }}</h4>
    <p class="text-muted">Program: {{ $studentData['program'] }}</p>

    <div class="mb-2 info-item d-flex align-items-center">
        <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
            <path d="M3 5h18v14H3V5zm2 2v10h14V7H5zm2 2h4v2H7V9zm0 3h6v2H7v-2z"/>
        </svg>
        <span class="info-label">Student ID Number:</span>
        <span class="info-value ms-auto">{{ $studentData['student_no'] }}</span>
    </div>

    <div class="mb-2 info-item d-flex align-items-center">
        <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
            <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
        </svg>
        <span class="info-label">School:</span>
        <span class="info-value ms-auto">{{ $studentData['school'] }}</span>
    </div>

    <div class="mb-2 info-item d-flex align-items-center">
        <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
            <path d="M4 4h16v16H4V4zm8 7l8-5H4l8 5zm8 7V9l-8 5-8-5v9h16z"/>
        </svg>
        <span class="info-label">Email Address:</span>
        <span class="info-value ms-auto">{{ $studentData['email'] }}</span>
    </div>

    <div class="mb-2 info-item d-flex align-items-center">
        <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
            <path d="M12 2l4 7h6l-4.9 4.2L18 22l-6-4-6 4 1.9-8.8L2 9h6l4-7z"/>
        </svg>
        <span class="info-label">Scholarship Type:</span>
        <span class="info-value ms-auto">{{ $studentData['scholarship_type'] }}</span>
    </div>

</div>



</div>

</div>


</div> <!-- END OF ROW -->

<hr class="my-4">

<div class="p-4 container-fluid">

<div class="mb-3 d-flex align-items-center">
  <strong class="me-2">Sort by:</strong>

  <!-- Sort Dropdown -->
  <select class="form-select" id="sort-newest" style="width: 220px; margin-right: 10px;">
      <option value="newest">Newest</option>
      <option value="oldest">Oldest</option>
  </select>

  <!-- Office Dropdown (same exact style as Volunteer Opportunities page) -->
<select class="form-select" id="filter-office" style="width: 220px;">
  <option value="">All Offices</option>
  <option value="SDA Assistant">Student Development and Activities Office</option> <!-- ✅ Added -->
  @foreach($offices as $office)
    <option value="{{ $office['name'] }}">{{ $office['name'] }}</option>
  @endforeach
</select>
</div>





    <div class="table-responsive">
        <table class="table align-middle table-bordered">
            <thead>
                <tr class="table-title-row">
                    <th colspan="8">Volunteer History</th>
                </tr>
                <tr class="table-header-row">
                    <th>Date</th>
                    <th>Office</th>
                    <th>Volunteer Activity</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Total Time</th>
                    <th>Office Feedback</th>
                    <th>Volunteer Feedback to the Office</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td colspan="8" class="py-4 text-center text-muted">
                        No volunteer records yet.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>



@endsection
