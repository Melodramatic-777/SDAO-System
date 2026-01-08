@extends('scholars.layout')

@section('content')

<div class="mx-5 mt-4 row">

    {{-- STUDENT INFO --}}
    <div class="col-12 col-md-6">


        {{-- STUDENT INFO --}}
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


    </div> {{-- END STUDENT INFO COLUMN --}}


</div>

<hr class="my-4">

{{-- VOLUNTEER HISTORY --}}
<div class="mx-5 mt-4 row">
    <div class="col-12">

        {{-- SORT & FILTER --}}
        <div class="flex-wrap mb-3 d-flex align-items-center">
            <strong class="me-2">Sort by:</strong>

            <select class="mb-2 form-select me-2" style="width: 220px;">
                <option value="newest">Newest</option>
                <option value="oldest">Oldest</option>
            </select>

            <select class="mb-2 form-select" style="width: 220px;">
                <option value="">All Offices</option>
                @foreach($offices as $office)
                    <option value="{{ $office['name'] }}">{{ $office['name'] }}</option>
                @endforeach
            </select>
        </div>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table align-middle table-bordered">
                <thead>
                    <tr class="table-title-row">
                        <th colspan="8">Volunteer History</th>
                    </tr>
                    <tr class="table-header-row">
                        <th>Date</th>
                        <th>Office</th>
                        <th>Activity</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Total Time</th>
                        <th>Office Feedback</th>
                        <th>Your Feedback</th>
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
</div>

<hr class="my-4">

{{-- SECURITY SECTION (BOTTOM) --}}
<div class="mx-5 mb-5 row">
    <div class="col-12 col-md-6">
        <div class="border-0 shadow-sm card">
            <div class="card-body">
                <h6 class="mb-2 fw-bold text-primary">
                    <i class="bi bi-shield-lock"></i> Security
                </h6>

                <button class="btn btn-outline-warning"
                        data-bs-toggle="modal"
                        data-bs-target="#changePasswordModal">
                    <i class="bi bi-key"></i> Change Password
                </button>
            </div>
        </div>
    </div>
</div>

{{-- CHANGE PASSWORD MODAL (WIDER) --}}
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4">

            <form method="POST" action="{{ route('scholars.profile.change-password') }}">
                @csrf

                {{-- HEADER --}}
                <div class="px-5 pt-4 border-0 modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-shield-lock text-warning"></i>
                        Change Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                {{-- BODY --}}
                <div class="px-5 pb-4 modal-body">

                    {{-- ALERTS --}}
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row g-4">

                        {{-- LEFT COLUMN : SECURITY INFO --}}
                        <div class="col-md-5 border-end">
                            <h6 class="mb-3 fw-bold text-primary">
                                <i class="bi bi-info-circle"></i> Password Guidelines
                            </h6>

                            <ul class="small text-muted ps-3">
                                <li>Use at least <strong>8 characters</strong></li>
                                <li>Include uppercase and lowercase letters</li>
                                <li>Add numbers or symbols for stronger security</li>
                                <li>Do not reuse old passwords</li>
                            </ul>

                            <div class="p-3 mt-4 rounded bg-light small text-muted">
                                <i class="bi bi-shield-check text-success"></i>
                                Changing your password will help keep your account secure.
                            </div>
                        </div>

                        {{-- RIGHT COLUMN : FORM --}}
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password"
                                       name="current_password"
                                       class="form-control"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password"
                                       name="new_password"
                                       class="form-control"
                                       required>
                                <small class="text-muted">
                                    Minimum of 8 characters
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password"
                                       name="new_password_confirmation"
                                       class="form-control"
                                       required>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="px-5 pb-4 border-0 modal-footer">
                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit"
                            class="btn btn-primary">
                        Update Password
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>


@endsection
