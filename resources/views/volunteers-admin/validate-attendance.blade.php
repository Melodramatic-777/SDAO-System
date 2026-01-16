@extends('volunteers-admin.layout')

@php
    use Carbon\Carbon;
@endphp



@section('content')

<main class="page-container">

    <h4 class="mb-4 fw-bold">
        <i class="bi bi-clock-history me-2"></i>
        Validate Volunteer Time In / Time Out
    </h4>

    {{-- 🔍 FILTER BAR --}}
    <form id="filterForm"
          method="GET"
          action="{{ route('volunteers-admin.validate-attendance') }}"
          class="flex-wrap gap-2 p-3 mb-3 bg-white border rounded shadow-sm filter-bar d-flex align-items-center">

        <select name="school" class="form-select" style="width:180px;">
            <option value="">All Schools</option>
            @foreach($schools as $school)
                <option value="{{ $school }}" {{ request('school') == $school ? 'selected' : '' }}>
                    {{ $school }}
                </option>
            @endforeach
        </select>

        <select name="program" class="form-select" style="width:220px;">
            <option value="">All Programs</option>
            @foreach($programs as $program)
                <option value="{{ $program }}" {{ request('program') == $program ? 'selected' : '' }}>
                    {{ $program }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-primary ms-2">
            <i class="bi bi-funnel me-1"></i> Apply
        </button>

        <button type="button"
                class="btn btn-secondary ms-2"
                onclick="window.location.href='{{ route('volunteers-admin.validate-attendance') }}'">
            <i class="bi bi-arrow-clockwise me-1"></i> Reset
        </button>

    </form>

    {{-- 📋 TABLE --}}
    <div class="shadow-sm card">
        <div class="card-body table-responsive">

            <table class="table align-middle table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Opportunity Title</th>
                        <th>Task</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Total Hours</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
@forelse ($rows->where('status', '!=', 'Complete') as $row)

                    @php
                        $status = $row['status']
                            ?? (!empty($row['time_in']) && empty($row['time_out']) ? 'Ongoing' : 'Pending');
                    @endphp

                    <tr>
                        <td>{{ $row['student_id'] }}</td>
                        <td>{{ $row['name'] }}</td>
                        <td>{{ $row['opportunity_name'] }}</td>
<td>{{ $row['task_name'] }}</td>


                        
<td>
    {{ $row['date'] !== '—'
        ? Carbon::parse($row['date'])->format('F j, Y')
        : '—'
    }}
</td>
<td>
    {{ $row['time_in'] !== '—'
        ? Carbon::createFromFormat('H:i:s', $row['time_in'])->format('g:i A')
        : '—'
    }}
</td>
<td>
    {{ $row['time_out'] !== '—'
        ? Carbon::createFromFormat('H:i:s', $row['time_out'])->format('g:i A')
        : '—'
    }}
</td>

<td>{{ $row['total_hours'] }}</td>

                        {{-- ✅ STATUS COLUMN --}}
<td class="text-center">
    @if($status === 'Pending')
        <span class="badge bg-warning text-dark">Pending</span>
    @elseif($status === 'Ongoing')
        <span class="badge bg-primary">Ongoing</span>
    @elseif($status === 'Approved')
        <span class="badge bg-success">Approved</span>
    @elseif($status === 'Rejected')
        <span class="badge bg-danger">Rejected</span>
    @elseif($status === 'Complete')
        <span class="badge bg-secondary">Complete</span>
    @else
        <span class="badge bg-secondary">{{ $status }}</span>
    @endif
</td>


                        {{-- 🎯 ACTIONS COLUMN --}}
                        <td class="text-center">

                            @if($status === 'Pending')

                                {{-- APPROVE --}}
                                <form method="POST"
                                      action="{{ route('volunteers-admin.validate-log') }}"
                                      class="d-inline">
                                    @csrf
                                    <input type="hidden" name="log_key" value="{{ $row['log_key'] }}">
                                    <input type="hidden" name="action" value="approve">
                                    <button class="btn btn-sm btn-success me-1"
                                            title="Approve"
                                            onclick="return confirm('Approve this time log?')">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>

                                {{-- REJECT --}}
                                <form method="POST"
                                      action="{{ route('volunteers-admin.validate-log') }}"
                                      class="d-inline">
                                    @csrf
                                    <input type="hidden" name="log_key" value="{{ $row['log_key'] }}">
                                    <input type="hidden" name="action" value="reject">
                                    <button class="btn btn-sm btn-danger"
                                            title="Reject"
                                            onclick="return confirm('Reject this time log?')">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>

                            @elseif($status === 'Ongoing')

                                <button class="btn btn-sm btn-success me-1 disabled"
                                        style="opacity:.4; pointer-events:none;">
                                    <i class="bi bi-check-lg"></i>
                                </button>

                                <button class="btn btn-sm btn-danger disabled"
                                        style="opacity:.4; pointer-events:none;">
                                    <i class="bi bi-x-lg"></i>
                                </button>

                            @elseif($status === 'Approved')

<button
    class="btn btn-sm btn-info"
    data-bs-toggle="modal"
    data-bs-target="#feedbackModal"
    data-logkey="{{ $row['log_key'] }}"
    data-student="{{ $row['name'] }}"
>
    Submit Feedback
</button>


                            @else
                                <span class="text-muted">—</span>
                            @endif

                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="8" class="py-4 text-center text-muted">
                            No fingerprint logs found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

        </div>
    </div>



    {{-- 📝 FEEDBACK MODAL --}}
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form method="POST" action="{{ route('volunteers-admin.submit-feedback') }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-chat-left-text me-2"></i>
                        Submit Feedback
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="log_key" id="feedbackLogKey">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Volunteer</label>
                        <input type="text" id="feedbackStudent" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Feedback Message</label>
                        <textarea
                            name="feedback"
                            class="form-control"
                            rows="4"
                            required
                            placeholder="Write feedback about the volunteer's performance..."
                        ></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Submit Feedback
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>


{{-- ✅ FEEDBACK SUCCESS MODAL --}}
<div class="modal fade" id="feedbackSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="border-0 shadow modal-content">

            <div class="text-white modal-header bg-success">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle me-2"></i>
                    Feedback Submitted
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="py-4 text-center modal-body">
                <h5 class="mb-2 fw-semibold">
                    This record has been moved to history
                </h5>
                <p class="mb-0 text-muted">
                    The volunteer log is now archived and no longer editable.
                </p>
            </div>

            <div class="modal-footer justify-content-center">
                <button type="button" class="px-4 btn btn-success" data-bs-dismiss="modal">
                    OK
                </button>
            </div>

        </div>
    </div>
</div>


</main>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('feedbackModal');

    modal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        document.getElementById('feedbackLogKey').value =
            button.getAttribute('data-logkey');

        document.getElementById('feedbackStudent').value =
            button.getAttribute('data-student');
    });
});
</script>

@if(session('feedback_moved'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const successModal = new bootstrap.Modal(
        document.getElementById('feedbackSuccessModal')
    );
    successModal.show();
});
</script>
@endif


@endsection
