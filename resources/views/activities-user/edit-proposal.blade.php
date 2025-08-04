@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Proposal</h2>

    <form method="POST" action="{{ route('activities-user.proposals.update', $proposal['proposal_id']) }}" enctype="multipart/form-data">
        @csrf

        <div>
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title', $proposal['title']) }}" required>
        </div>

        <div>
            <label>Objectives</label>
            <textarea name="objectives" required>{{ old('objectives', $proposal['objectives']) }}</textarea>
        </div>

        <div>
            <label>Start Date</label>
            <input type="date" name="start_date" value="{{ old('start_date', $proposal['start_date']) }}" required>
        </div>

        <div>
            <label>End Date</label>
            <input type="date" name="end_date" value="{{ old('end_date', $proposal['end_date']) }}" required>
        </div>

        <div>
            <label>Description</label>
            <textarea name="description" required>{{ old('description', $proposal['description']) }}</textarea>
        </div>

        <div>
            <label>Replace PDF (optional)</label>
            <input type="file" name="pdf_file" accept="application/pdf">
            <p>Current PDF: <a href="{{ asset('storage/' . $proposal['pdf_file_path']) }}" target="_blank">View</a></p>
        </div>

        <button type="submit" class="btn btn-primary">Update Proposal</button>
    </form>
</div>
@endsection
