@extends('scholars.layout')

@section('content')




<div class="container mt-4 text-center">


  <div id="invitesTable" class="mt-3">
    <table class="table mx-auto text-center align-middle table-bordered" style="max-width: 900px;">
      <thead>
        <tr class="table-title-row">
          <th colspan="5" class="text-center fs-5">List of Volunteer Invites</th>
        </tr>
        <tr class="table-header-row">
          <th>Office</th>
          <th>Opportunity Name</th>
          <th>Invited At</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($invites as $invite)
          <tr>
            <td>{{ $invite['office'] }}</td>
            <td>{{ $invite['title'] }}</td>
            <td>{{ $invite['invited_at'] }}</td>
            <td>
              <span class="badge 
                @if($invite['status'] === 'Accepted') bg-success 
                @elseif($invite['status'] === 'Declined') bg-danger 
                @else bg-warning text-dark 
                @endif">
                {{ $invite['status'] }}
              </span>
            </td>
            <td>
<button  
    class="p-0 icon-btn btn btn-sm"
    style="background: none; border: none;"
    data-bs-toggle="modal"
    data-bs-target="#detailsModal{{ $invite['opportunity_id'] }}"
    title="View Opportunity">
    <i class="bi bi-eye fs-5" style="color: #0d6efd;"></i>
</button>


            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-muted">No invites found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- ✅ Moved all modals outside the table to fix the overlay issue -->
@foreach ($invites as $invite)
<div class="modal fade" id="detailsModal{{ $invite['opportunity_id'] }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content custom-modal-content">

            <div class="modal-header">
                <h5 class="modal-title">{{ $invite['title'] }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-start">
                <p><strong>Office:</strong> {{ $invite['office'] }}</p>
                <p><strong>Invited At:</strong> {{ $invite['invited_at'] }}</p>
                <p><strong>Status:</strong> {{ $invite['status'] }}</p>

                <hr>

                <h6><strong>Invited Tasks</strong></h6>

                {{-- REGULAR SCHEDULE --}}
                @if(!$invite['is_intermittent'])
                    @if(isset($invite['tasks']['regular']))
                        <ul>
                            @foreach($invite['tasks']['regular'] as $task)
                                <li>{{ $task }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No tasks assigned.</p>
                    @endif
                @endif

                {{-- INTERMITTENT SCHEDULE --}}
             {{-- INTERMITTENT SCHEDULE --}}
@if($invite['is_intermittent'])
    <form action="{{ route('scholars.invites.accept') }}" method="POST" id="acceptForm{{ $invite['opportunity_id'] }}">
        @csrf
        <input type="hidden" name="opportunity_id" value="{{ $invite['opportunity_id'] }}">
        <input type="hidden" name="participant_key" value="{{ $invite['participant_key'] }}">

        <p><strong>Select Dates You Want to Join:</strong></p>

        @foreach($invite['tasks'] as $date => $tasks)
            <div class="p-2 mb-2 border rounded">

                <label class="fw-bold">
                    <input type="checkbox"
                           name="selected_dates[]"
                           value="{{ $date }}"
                           class="form-check-input me-2">
                    {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                </label>

                <ul class="mt-2 mb-0">
                    @foreach($tasks as $task)
                        <li>{{ $task }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </form>
@endif

            </div>

       <div class="modal-footer">

    @if($invite['status'] === 'Pending Invite' || $invite['status'] === 'Pending')

        {{-- For intermittent: submit selected dates --}}
        @if($invite['is_intermittent'])
            <button type="submit" form="acceptForm{{ $invite['opportunity_id'] }}"
                    class="btn btn-success btn-sm">
                Accept Selected Dates
            </button>
        @else
            {{-- REGULAR --}}
            <form action="{{ route('scholars.invites.accept') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="opportunity_id" value="{{ $invite['opportunity_id'] }}">
                <input type="hidden" name="participant_key" value="{{ $invite['participant_key'] }}">
                <button class="btn btn-success btn-sm">Accept</button>
            </form>
        @endif

        {{-- DECLINE BUTTON --}}
        <form action="{{ route('scholars.invites.decline') }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="opportunity_id" value="{{ $invite['opportunity_id'] }}">
            <input type="hidden" name="participant_key" value="{{ $invite['participant_key'] }}">
            <button class="btn btn-danger btn-sm">Decline</button>
        </form>

    @else
        <button class="btn btn-secondary btn-sm" disabled>
            {{ $invite['status'] }}
        </button>
    @endif

</div>


        </div>
    </div>
</div>
@endforeach



@endsection
