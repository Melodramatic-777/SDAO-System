@extends('scholars.layout')

@section('content')




<div class="container mt-4 text-center">
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @elseif (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

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
                class="btn btn-sm btn-primary" 
                data-bs-toggle="modal"
                data-bs-target="#detailsModal{{ $invite['opportunity_id'] }}">
                View
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
        </div>
        <div class="modal-footer">
          @if($invite['status'] === 'Pending')
            <form action="{{ route('scholars.invites.respond', $invite['opportunity_id']) }}" method="POST" class="d-inline">
              @csrf
              <input type="hidden" name="response" value="Accepted">
              <button class="btn btn-success btn-sm">Accept</button>
            </form>
            <form action="{{ route('scholars.invites.respond', $invite['opportunity_id']) }}" method="POST" class="d-inline">
              @csrf
              <input type="hidden" name="response" value="Declined">
              <button class="btn btn-danger btn-sm">Decline</button>
            </form>
          @else
            <button class="btn btn-secondary btn-sm" disabled>{{ $invite['status'] }}</button>
          @endif
        </div>
      </div>
    </div>
  </div>
@endforeach

@endsection
