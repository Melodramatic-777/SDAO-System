@extends('scholars.layout')

@section('content')

<div class="mx-5 mt-4">

    <!-- PROFILE CARD -->
    <div class="p-4 mb-4 shadow-sm card">
        <h4 class="mb-1 fw-bold">{{ $studentData['first_name'] }} {{ $studentData['last_name'] }}</h4>
        <p class="mb-3 text-muted">Program: {{ $studentData['program'] }}</p>

        <div class="gap-3 d-flex flex-column">

            <!-- Student ID -->
            <div class="p-2 border rounded d-flex align-items-center">
                <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
                    <path d="M3 5h18v14H3V5zm2 2v10h14V7H5zm2 2h4v2H7V9zm0 3h6v2H7v-2z"/>
                </svg>
                <div>
                    <div class="small text-muted">Student ID Number</div>
                    <div class="fw-semibold">{{ $studentData['student_no'] }}</div>
                </div>
            </div>

            <!-- School -->
            <div class="p-2 border rounded d-flex align-items-center">
                <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
                    <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                </svg>
                <div>
                    <div class="small text-muted">School</div>
                    <div class="fw-semibold">{{ $studentData['school'] }}</div>
                </div>
            </div>

            <!-- Email -->
            <div class="p-2 border rounded d-flex align-items-center">
                <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
                    <path d="M4 4h16v16H4V4zm8 7l8-5H4l8 5zm8 7V9l-8 5-8-5v9h16z"/>
                </svg>
                <div>
                    <div class="small text-muted">Email Address</div>
                    <div class="fw-semibold">{{ $studentData['email'] }}</div>
                </div>
            </div>

            <!-- Scholarship Type -->
            <div class="p-2 border rounded d-flex align-items-center">
                <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
                    <path d="M12 2l4 7h6l-4.9 4.2L18 22l-6-4-6 4 1.9-8.8L2 9h6l4-7z"/>
                </svg>
                <div>
                    <div class="small text-muted">Scholarship Type</div>
                    <div class="fw-semibold">{{ $studentData['scholarship_type'] }}</div>
                </div>
            </div>

        </div>
    </div>

    <!-- SECURITY CARD -->
    <div class="p-4 shadow-sm card">
        <h6 class="mb-3 fw-bold text-primary">
            <i class="bi bi-shield-lock"></i> Security
        </h6>

        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted">Password: <strong>********</strong></span>
            <button class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#changePasswordModal">
                <i class="bi bi-key"></i> Change Password
            </button>
        </div>
    </div>

</div>

<!-- ORIGINAL CHANGE PASSWORD MODAL -->
<!-- CHANGE PASSWORD MODAL -->
<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <form method="POST" action="{{ route('volunteers-admin.profile.change-password') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <!-- Display errors -->
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Success message -->
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" id="current_password" required>
                        @error('current_password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" id="new_password" required>
                        @error('new_password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" name="new_password_confirmation" id="new_password_confirmation" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection