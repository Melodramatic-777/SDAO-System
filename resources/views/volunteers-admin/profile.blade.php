@extends('volunteers-admin.layout')

@section('content')

<div class="mx-5 mt-4 row">

<div class="container py-4">

    <div class="col-12 col-md-6">
        <h4 class="fw-bold">
            {{ $admin['last_name'] }}, {{ $admin['first_name'] }}
            @if(!empty($admin['middle_name'])) {{ $admin['middle_name'] }} @endif
            @if(!empty($admin['suffix'])) {{ $admin['suffix'] }} @endif
        </h4>

        <p class="text-muted">Role: {{ $admin['role'] }}</p>

        <div class="mb-2 info-item d-flex align-items-center">
            <svg width="22" height="22" fill="#004aad" class="me-2" viewBox="0 0 24 24">
                <path d="M4 4h16v16H4V4zm8 7l8-5H4l8 5zm8 7V9l-8 5-8-5v9h16z"/>
            </svg>
            <span class="info-label">Email Address:</span>
            <span class="info-value ms-auto">{{ $admin['email'] }}</span>
        </div>
    </div>


    <!-- Change Password Button -->
<!-- Change Password Button -->
<!-- Change Password Button -->
<div class="mt-3">
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
        Change Password
    </button>
</div>

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



</div>


</div>
@endsection
