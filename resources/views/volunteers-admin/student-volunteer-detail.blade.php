@extends('volunteers-admin.layout')

@section('content')

<header class="p-3 custom-header d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
        <!-- SIDE BUTTON -->
        <button type="submit" class="side-button header-btn me-3">
            <img src="{{ asset('images/Side.png') }}" alt="Side" height="30">
        </button>

        <div class="logo d-flex align-items-center">
            <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30" class="me-2">
            <span>Student Developments and Activities Office</span>
        </div>
    </div>

    <div class="header-right-group">
        <p>Welcome, {{ session('volunteers_admin.first_name') }}!</p>
    </div>
</header>

<div class="p-4 container-fluid">
    <!-- Back link -->
    <a href="{{ route('volunteers-admin.student-volunteers') }}" class="text-decoration-none">
        ← Back to List of Student Volunteers
    </a>

    <!-- Main row -->
    <div class="mt-4 row align-items-center">

        {{-- LEFT STUDENT INFO --}}
        <div class="col-md-4">
            <h4 class="fw-bold">{{ $studentData['last_name'] }}, {{ $studentData['first_name'] }}</h4>
            <p class="text-muted">Program: {{ $studentData['program'] }}</p>

            {{-- Student ID --}}
            <div class="mb-2 info-item d-flex align-items-center">
                <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
                    <path d="M3 5h18v14H3V5zm2 2v10h14V7H5zm2 2h4v2H7V9zm0 3h6v2H7v-2z"/>
                </svg>
                <span class="info-label">Student ID Number:</span>
                <span class="info-value ms-auto">{{ $studentData['student_no'] }}</span>
            </div>

            {{-- School --}}
            <div class="mb-2 info-item d-flex align-items-center">
                <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
                    <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                </svg>
                <span class="info-label">School:</span>
                <span class="info-value ms-auto">{{ $studentData['school'] }}</span>
            </div>

            {{-- Email --}}
            <div class="mb-2 info-item d-flex align-items-center">
                <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
                    <path d="M4 4h16v16H4V4zm8 7l8-5H4l8 5zm8 7V9l-8 5-8-5v9h16z"/>
                </svg>
                <span class="info-label">Email Address:</span>
                <span class="info-value ms-auto">{{ $studentData['email'] }}</span>
            </div>

            {{-- Scholarship Type --}}
            <div class="mb-2 info-item d-flex align-items-center">
                <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
                    <path d="M12 2l4 7h6l-4.9 4.2L18 22l-6-4-6 4 1.9-8.8L2 9h6l4-7z"/>
                </svg>
                <span class="info-label">Scholarship Type:</span>
                <span class="info-value ms-auto">{{ $studentData['scholarship_type'] }}</span>
            </div>
        </div>

        {{-- MID - PIE + PROGRESS --}}
        <div class="text-center col-md-4">
            @php $hasHours = ($studentData['hours_completed'] ?? 0) > 0; @endphp
            <div class="gap-3 d-flex justify-content-center align-items-center">

                {{-- Pie Chart / No data --}}
                @if(!$hasHours)
                    <div class="d-flex justify-content-center align-items-center" style="width:200px; height:200px; border:1px dashed #ccc; border-radius:8px;">
                        <span class="text-muted" style="font-size:1rem;">No data yet</span>
                    </div>
                @else
                    <div>
                        <canvas id="pieChart" width="200" height="200"></canvas>
                        <small class="mt-1 d-block">Office Volunteer Hours</small>
                    </div>
                @endif

                {{-- Progress Doughnut --}}
                <div style="width:200px; height:200px; position:relative;">
                    <canvas id="progressChart"></canvas>
                    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); font-size:1.7rem; font-weight:bold; color:#004aad;">
                        {{ $hasHours ? floor(($studentData['hours_completed']/10)*100) : 0 }}%
                    </div>
                </div>

            </div>
        </div>

        {{-- RIGHT STAT CARDS --}}
        <div class="col-md-4">
            <div class="gap-3 status-cards d-grid">
                <div class="status-card completed">
                    <h6>Hours Completed</h6>
                    <h3>{{ $studentData['hours_completed'] ?? 0 }}</h3>
                </div>

                <div class="status-card participated">
                    <h6>Activities Participated</h6>
                    <h3>{{ $studentData['activities'] ?? 0 }}</h3>
                </div>

                <div class="status-card month">
                    <h6>Hours This Month</h6>
                    <h3>{{ $studentData['hours_month'] ?? 0 }}</h3>
                </div>

                <div class="status-card remaining">
                    <h6>Remaining Hours</h6>
                    <h3>{{ $studentData['remaining_hours'] ?? 0 }}</h3>
                </div>
            </div>
        </div>

    </div> <!-- END MAIN ROW -->

    <hr class="my-4">

    {{-- Filters --}}
    <div class="flex-wrap gap-2 mb-3 d-flex align-items-center">
        <strong>Sort by:</strong>
        <select class="form-select me-2" id="sort-newest" style="width:220px;">
            <option value="newest">Newest</option>
            <option value="oldest">Oldest</option>
        </select>

        <select class="form-select" id="filter-office" style="width:220px;">
            <option value="">All Offices</option>
            <option value="SDA Assistant">Student Development and Activities Office</option>
            @foreach($offices as $office)
                <option value="{{ $office['name'] }}">{{ $office['name'] }}</option>
            @endforeach
        </select>
    </div>

    {{-- Volunteer History Table --}}
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
                    <td colspan="8" class="py-4 text-center text-muted">No volunteer records yet.</td>
                </tr>
            </tbody>
        </table>
    </div>

</div> <!-- END CONTAINER -->

@endsection
