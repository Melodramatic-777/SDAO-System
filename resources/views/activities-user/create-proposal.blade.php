@extends('layouts.app')

@section('content')

@php
    /** 
     * Make sure $user is always available and has the correct keys.
     * If not, fallback to an empty array so the Blade won’t crash.
     */
    $user = session('activities_user') ?? [];
@endphp

<div class="container">
    <h1>Submit a Proposal</h1>

    <form method="POST" action="{{ route('activities-user.proposals.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Hidden fields are now guarded -->
        <input type="hidden" name="organization_id" value="{{ $user['organization_id'] ?? '' }}">
        <input type="hidden" name="organization_name" value="{{ $user['organization_name'] ?? '' }}">
        <input type="hidden" name="submitted_by" value="{{ $user['email'] ?? '' }}">

        <h2>1. Proposal Information</h2>

        <label>Proposal Title:</label>
        <input type="text" name="title" required>

        <label>Objectives:</label>
        <textarea name="objectives" required></textarea>

        <label>
            <input type="checkbox" id="intermittent-event"> My event happens intermittently.
        </label>

        <div id="date-fields">
            <label>Start Date:</label>
            <input type="date" name="start_date">

            <label>End Date:</label>
            <input type="date" name="end_date">
        </div>

        <div id="schedule-details" style="display: none;">
            <h3>Event Schedule</h3>
            <div class="schedule-group">
                <label>Date:</label>
                <input type="date" name="schedule[0][date]">

                <label>Event Name:</label>
                <input type="text" name="schedule[0][event_name]">

                <label>Start Time:</label>
                <input type="time" name="schedule[0][start_time]">

                <label>End Time:</label>
                <input type="time" name="schedule[0][end_time]">

                <label>Target Participants:</label>
                <input type="text" name="schedule[0][target_participants]">

                <label>Target Number:</label>
                <input type="number" name="schedule[0][target_number]">
            </div>
            <button type="button" id="add-schedule">+ Add Another</button>
        </div>

        <label>Brief Description:</label>
        <textarea name="description" required></textarea>

        <button type="button" id="next-step-1">Next</button>

        <!-- Step 2: Upload -->
        <div id="step-2" style="display: none;">
            <h2>2. Upload File</h2>
            <input type="file" name="pdf_file" accept="application/pdf" required>
            <p>Upload 1 supported file: PDF. Max 100 MB.</p>
            <button type="button" id="next-step-2">Next</button>
        </div>

        <!-- Step 3: Review -->
        <div id="step-3" style="display: none;">
            <h2>3. Review</h2>

            <h3>Proposal Information</h3>
            <p><strong>Title:</strong> <span id="review-title"></span></p>
            <p><strong>Objectives:</strong> <span id="review-objectives"></span></p>
            <p><strong>Start Date:</strong> <span id="review-start-date"></span></p>
            <p><strong>End Date:</strong> <span id="review-end-date"></span></p>
            <p><strong>Description:</strong> <span id="review-description"></span></p>

            <div id="review-schedule" style="display: none;">
                <h3>Event Schedule</h3>
                <div id="review-schedule-list"></div>
            </div>

            <h3>File</h3>
            <p><strong>Uploaded File:</strong> <span id="review-file-name"></span></p>

            <button type="submit">Submit Proposal</button>
        </div>
    </form>
</div>

<script>
    let scheduleCount = 1;

    document.getElementById('intermittent-event').addEventListener('change', function () {
        document.getElementById('schedule-details').style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById('add-schedule').addEventListener('click', function () {
        const scheduleDetails = document.getElementById('schedule-details');
        const firstGroup = scheduleDetails.querySelector('.schedule-group');
        const newGroup = firstGroup.cloneNode(true);

        newGroup.querySelectorAll('input').forEach(function (input) {
            let name = input.name;
            name = name.replace(/\[\d+\]/, `[${scheduleCount}]`);
            input.name = name;
            input.value = '';
        });

        scheduleDetails.insertBefore(newGroup, this);

        scheduleCount++;
    });

    document.getElementById('next-step-1').addEventListener('click', function () {
        document.getElementById('step-2').style.display = 'block';
    });

    document.getElementById('next-step-2').addEventListener('click', function () {
        document.getElementById('review-title').innerText = document.querySelector('input[name="title"]').value;
        document.getElementById('review-objectives').innerText = document.querySelector('textarea[name="objectives"]').value;
        document.getElementById('review-start-date').innerText = document.querySelector('input[name="start_date"]').value;
        document.getElementById('review-end-date').innerText = document.querySelector('input[name="end_date"]').value;
        document.getElementById('review-description').innerText = document.querySelector('textarea[name="description"]').value;

        const fileInput = document.querySelector('input[name="pdf_file"]');
        document.getElementById('review-file-name').innerText = fileInput.files.length > 0 ? fileInput.files[0].name : 'No file selected';

        const scheduleDetails = document.getElementById('schedule-details');
        const scheduleGroups = scheduleDetails.querySelectorAll('.schedule-group');
        const reviewScheduleList = document.getElementById('review-schedule-list');

        reviewScheduleList.innerHTML = '';

        if (scheduleGroups.length > 0 && document.getElementById('intermittent-event').checked) {
            document.getElementById('review-schedule').style.display = 'block';
            scheduleGroups.forEach(function (group, index) {
                const date = group.querySelector(`input[name^="schedule"][name$="[date]"]`).value;
                const eventName = group.querySelector(`input[name^="schedule"][name$="[event_name]"]`).value;
                const startTime = group.querySelector(`input[name^="schedule"][name$="[start_time]"]`).value;
                const endTime = group.querySelector(`input[name^="schedule"][name$="[end_time]"]`).value;
                const participants = group.querySelector(`input[name^="schedule"][name$="[target_participants]"]`).value;
                const number = group.querySelector(`input[name^="schedule"][name$="[target_number]"]`).value;

                const item = document.createElement('div');
                item.innerHTML = `
                    <p><strong>Schedule ${index + 1}:</strong></p>
                    <ul>
                        <li><strong>Date:</strong> ${date}</li>
                        <li><strong>Event Name:</strong> ${eventName}</li>
                        <li><strong>Start Time:</strong> ${startTime}</li>
                        <li><strong>End Time:</strong> ${endTime}</li>
                        <li><strong>Participants:</strong> ${participants}</li>
                        <li><strong>Target Number:</strong> ${number}</li>
                    </ul>
                `;
                reviewScheduleList.appendChild(item);
            });
        } else {
            document.getElementById('review-schedule').style.display = 'none';
        }

        document.getElementById('step-3').style.display = 'block';
    });
</script>
@endsection
