@extends('layouts.app')

@section('content')

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<style>
    /* Make the overall form wider and centered */

body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #f2f2f2;
    padding: 20px;
}

.container, .row {
    width: 100%;
    display: flex;
    justify-content: center;
}

/* Prevent layout break if using layout wrappers */
.form-container {
    width: 900px;          /* fixed width for all steps */
    max-width: 100%;       /* prevents overflow on smaller screens */
    margin: 40px auto;     /* centers horizontally and adds top spacing */
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    min-height: 600px;     /* optional: keep same height for all steps */
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}
    .form-title {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 20px;
        text-align: center;
    }

    #fullcalendar {
        width: 100% !important;
        height: auto !important;
        min-height: 600px; /* ⬅️ give it more vertical room */
        overflow: visible !important;
    }

    .fc-scroller {
        overflow: visible !important; /* Fix internal clipping inside FullCalendar */
    }

    .fc-daygrid-day.selected {
        background-color: #4caf50 !important;
        color: white !important;
    }

    .fc-daygrid-day:hover {
        cursor: pointer;
        background-color: #a5d6a7 !important;
    }

    /* Two-column grid */
    .form-grid {
        display: flex;
        gap: 30px; /* ⬅️ a bit more spacing */
        flex-wrap: wrap;
    }

    .form-left {
        flex: 1;
        min-width: 500px; /* wider column */
        overflow: visible !important;
    }

    .form-right {
        flex: 1;
        min-width: 500px; /* wider column */
        overflow: visible !important;
    }

    .form-row {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }

    .time-row input {
        width: 45%;
    }

    .radio-group label, .checkbox-group label {
        display: block;
        margin: 5px 0;
    }

    .btn-secondary {
        padding: 5px 10px;
        margin-top: 5px;
        cursor: pointer;
        background-color: #2196f3;
        color: white;
        border: none;
        border-radius: 4px;
    }

    .btn-secondary:hover {
        background-color: #1976d2;
    }

    #scheduled-entries div {
        margin-bottom: 5px;
        font-size: 14px;
    }

    .radio-group input[type="radio"] {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #2196f3;
        border-radius: 50%;
        outline: none;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-right: 8px;
        background-color: white;
    }

    .radio-group input[type="radio"]:checked {
        background-color: #2196f3;
        border-color: #2196f3;
    }

    .radio-group label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 500;
        cursor: pointer;
    }

    #scheduled-entries table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 14px;
    }

    #scheduled-entries th, #scheduled-entries td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
    }

    #scheduled-entries th {
        background-color: #2196f3;
        color: white;
    }

    #scheduled-entries tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    #scheduled-entries tr:hover {
        background-color: #e3f2fd;
    }

    .form-submit {
        text-align: center;
        margin-top: 20px;
    }

    .btn-submit {
        background-color: #4caf50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
    }

    .btn-submit:hover {
        background-color: #43a047;
    }

    .task-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 14px;
    }

    .task-table th {
        background-color: #003DA5;
        color: white;
        padding: 8px;
        text-align: left;
    }

    .task-table td {
        border: 1px solid #ddd;
        padding: 8px;
        vertical-align: top;
    }

    .task-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .task-table tr:hover {
        background-color: #e3f2fd;
    }

#step-1 label:first-of-type {
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}

#step-1 input[name="title"] {
    margin-bottom: 20px;
}

/* Group Level + Room nicely */
.location-group label {
    margin-top: 10px;
    font-weight: 600;
    display: block;
}

.location-group select,
.location-group input {
    margin-bottom: 12px;
}

.two-column {
    display: flex;
    gap: 20px;
    margin-top: 15px;
}

.left-side {
    flex: 1;
}

.right-side {
    flex: 1;
    background: #f8f8f8;
    padding: 12px;
    border-radius: 8px;
    max-height: 600px;
    overflow-y: auto;
}

</style>

    
<div class="form-container">
    <h2 class="form-title">Request Volunteer Form</h2>

    <form action="{{ route('volunteers-admin.store-opportunity') }}" method="POST" id="opportunity-form">
        @csrf

        <!-- ---------------------------- -->
        <!-- 🔵 STEP 1 — TITLE + LOCATION -->
        <!-- ---------------------------- -->
      <div class="form-step" id="step-1">
    <h3>Step 1: Activity Title & Location</h3>

    <!-- TITLE FIRST -->
    <label>Title of activity</label>
    <input type="text" name="title" placeholder="Enter title proposal" required>

    <!-- GROUP LOCATION UI -->
    <div class="location-group">

        <label>Location</label>

        <!-- LEVEL -->
        <select id="level" required>
            <option value="" selected disabled>Select Level</option>
            <option value="Level 1">Level 1</option>
            <option value="Level 2">Level 2</option>
            <option value="Level 3">Level 3</option>
            <option value="Level 4">Level 4</option>
            <option value="Level 5">Level 5</option>
            <option value="Others">Others</option>
        </select>

        <input type="text" id="level-other-input" placeholder="Enter custom level..." style="display:none; margin-top:8px;">

        <!-- ROOM -->
        <select id="room" required disabled>
            <option value="" selected disabled>Select Room / Area</option>
        </select>

        <input type="text" id="room-other-input" placeholder="Enter custom room/area..." style="display:none; margin-top:8px;">

    </div>

    <input type="hidden" name="location" id="location">

    <button type="button" class="btn-submit next-step">Next</button>
</div>


        <!-- --------------------------------------------- -->
        <!-- 🔵 STEP 2 — INTERMITTENT OR REGULAR SCHEDULE -->
        <!-- --------------------------------------------- -->
      <div class="form-step" id="step-2" style="display:none;">
    <h3>Step 2: Schedule Details</h3>

    <!-- Intermittent Option -->
    <label>My event happens intermittently?</label>
    <div class="radio-group">
        <label><input type="radio" name="intermittent" value="0" checked> No</label>
        <label><input type="radio" name="intermittent" value="1"> Yes</label>
    </div>

    <!-- TWO-COLUMN WRAPPER (Shows only when intermittent = yes) -->
    <div id="intermittent-layout" style="display:none;" class="two-column">
        
        <!-- LEFT SIDE → FORM FIELDS -->
        <div class="left-side">

            <!-- Intermittent Schedule -->
            <div id="intermittent-details">
                <h4>Custom Schedule (Month Calendar)</h4>
                <div id="fullcalendar"></div>

                <div id="time-details" style="margin-top:10px;">
                    <label>Start Time</label>
                    <input type="time" id="calendar-start-time">

                    <label>End Time</label>
                    <input type="time" id="calendar-end-time">

                    <label>Volunteers Needed</label>
                    <input type="number" id="calendar-target-number" min="1">

                    <label>Food Provided</label>
                    <div class="radio-group" style="margin-top: 5px; margin-bottom: 10px;">
                        <label><input type="radio" name="food_provided" value="yes"> Yes</label>
                        <label><input type="radio" name="food_provided" value="no"> No</label>
                    </div>

                    <button type="button" id="add-calendar-schedule" class="btn-secondary">Add Schedule</button>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE → SCHEDULED ENTRIES -->
        <div class="right-side">
            <h5 id="scheduled-header">Scheduled Entries</h5>
            <div id="scheduled-entries"></div>
        </div>

    </div>

    <!-- Regular Schedule (VISIBLE when intermittent = no) -->
    <div id="regular-schedule">
        <div class="form-row">
            <div>
                <label>Start Date</label>
                <input type="date" name="start_date">
            </div>
        </div>

        <label>Time of activity</label>
        <div class="form-row time-row">
            <input type="time" name="schedule[0][start_time]">
            <span>to</span>
            <input type="time" name="schedule[0][end_time]">
        </div>

        <label>Number of volunteers needed</label>
        <input type="number" name="schedule[0][target_number]" min="1">
    </div>

    <!-- Bottom Food Option (only for regular schedule) -->
    <div id="food-provided-bottom">
        <label>Food Provided</label>
        <div class="radio-group">
            <label><input type="radio" name="food_provided" value="yes"> Yes</label>
            <label><input type="radio" name="food_provided" value="no"> No</label>
        </div>
    </div>

    <button type="button" class="btn-submit prev-step">Back</button>
    <button type="button" class="btn-submit next-step">Next</button>
</div>


        <!-- ---------------------------- -->
        <!-- 🔵 STEP 3 — DESCRIPTION -->
        <!-- ---------------------------- -->
        <div class="form-step" id="step-3" style="display:none;">
            <h3>Step 3: Description</h3>

            <label>Description</label>
            <textarea name="description" placeholder="Enter activity description..." required></textarea>

            <button type="button" class="btn-submit prev-step">Back</button>
            <button type="button" class="btn-submit next-step">Next</button>
        </div>

        <!-- ----------------------------- -->
        <!-- 🔵 STEP 4 — TASK SELECTION -->
        <!-- ----------------------------- -->
        <div class="form-step" id="step-4" style="display:none;">
            <h3>Step 4: Task Details</h3>

<table class="task-table"> <thead> <tr> <th>Select</th> <th>Task</th> <th>Skills Recommended</th> <th>Short Description</th> </tr> </thead> <tbody> <tr> <td><input type="checkbox" name="tasks[]" value="Marshalling"></td> <td>Marshalling</td> <td>Crowd management, clear communication, alert, patient</td> <td>Helps manage crowd flow, guide participants, and keep pathways clear.</td> </tr> <tr> <td><input type="checkbox" name="tasks[]" value="Registration"></td> <td>Registration</td> <td>Organized, detail-oriented, friendly, basic data encoding</td> <td>Handles check-in, attendance lists, and assists participants.</td> </tr> <tr> <td><input type="checkbox" name="tasks[]" value="Ushering"></td> <td>Ushering</td> <td>Approachable, good communication, presentable</td> <td>Guides attendees to seats and helps answer questions.</td> </tr> <tr> <td><input type="checkbox" name="tasks[]" value="Photography"></td> <td>Photography</td> <td>Basic photography skills, attention to detail</td> <td>Takes photos of key moments and activities.</td> </tr> <tr> <td><input type="checkbox" name="tasks[]" value="Videography"></td> <td>Videography</td> <td>Basic video recording, timing awareness</td> <td>Records event highlights and important segments.</td> </tr> <tr> <td><input type="checkbox" name="tasks[]" value="Technical Support"></td> <td>Technical Support</td> <td>Basic IT skills, calm under pressure</td> <td>Helps manage audio, projector, and technical systems.</td> </tr> <tr> <td><input type="checkbox" name="tasks[]" value="Creatives/Design"></td> <td>Creatives / Design</td> <td>Basic design sense, creativity</td> <td>Prepares visual materials, layouts, and social media assets.</td> </tr> <tr> <td><input type="checkbox" name="tasks[]" value="Hosting"></td> <td>Hosting</td> <td>Confident speaker, energetic, good diction</td> <td>Leads the program, introduces speakers, engages the audience.</td> </tr> <tr> <td><input type="checkbox" name="tasks[]" value="Logistics"></td> <td>Logistics</td> <td>Physically capable, organized</td> <td>Transfers equipment/materials and supports operations.</td> </tr> <tr> <td><input type="checkbox" name="tasks[]" value="Admin Support"></td> <td>Admin Support</td> <td>Detail-oriented, encoding, filing</td> <td>Prepares documents, attendance, certificates, encodes data.</td> </tr> <!-- Others row --> <tr> <td><input type="checkbox" name="tasks[]" value="Others"></td> <td>Others</td> <!-- Skills Recommended --> <td> <input type="text" name="task_other_skills" placeholder="Enter skills recommended…" style="width:100%;"> </td> <!-- Short Description --> <td> <input type="text" name="task_other_description" placeholder="Enter short description…" style="width:100%;"> </td> </tr> </tbody> </table>

            <button type="button" class="btn-submit prev-step">Back</button>

            <div class="form-submit">
                <button type="submit" class="btn-submit">Submit</button>
            </div>
        </div>

    </form>
</div>


<script>
document.addEventListener("DOMContentLoaded", function() {
    // Controls and containers
    const radios = document.querySelectorAll("input[name='intermittent']");
    const regularSchedule = document.getElementById("regular-schedule");
    const intermittentDetails = document.getElementById("intermittent-details");
    const scheduledEntries = document.getElementById("scheduled-entries");
    const foodProvidedBottom = document.getElementById("food-provided-bottom");
    const form = document.getElementById('opportunity-form');

    // calendar selection sets
    let selectedDates = new Set(); // dates toggled by user on calendar
    let scheduleIndex = 0; // global index for schedule[*] inputs
    let indexMap = {}; // maps visual row id -> scheduleIndex (useful on delete/edit)

    // Toggle between intermittent YES/NO
    radios.forEach(radio => {
        radio.addEventListener("change", () => {
        if (radio.value === "1") {

    // Show calendar layout
    document.getElementById("intermittent-layout").style.display = "flex";

    regularSchedule.style.display = "none";
    foodProvidedBottom.style.display = "none";

    // Cleanup old schedule inputs
    document.querySelectorAll(".scheduled-entry-input").forEach(i => i.remove());
    scheduledEntries.innerHTML = "";
    scheduleIndex = 0;
    selectedDates.clear();

    // Render calendar AFTER display
    setTimeout(() => {
        calendar.render();
        calendar.today();
    }, 200);
}
else {
document.getElementById("intermittent-layout").style.display = "none";
                regularSchedule.style.display = "block";
                foodProvidedBottom.style.display = "block";
                // remove previously added intermittent hidden inputs and UI
                document.querySelectorAll(".scheduled-entry-input").forEach(i => i.remove());
                scheduledEntries.innerHTML = "";
                scheduleIndex = 0;
                indexMap = {};
                selectedDates.clear();
            }
        });
    });

    // Enforce minimum for volunteer number inputs
    document.addEventListener("input", function(e) {
        if (e.target.matches('input[type="number"][min="1"]')) {
            let value = parseInt(e.target.value);
            if (isNaN(value) || value < 1) {
                e.target.value = 1;
            }
        }
    });

    // ---------------- FullCalendar Setup ----------------
    const calendarEl = document.getElementById('fullcalendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        selectable: true,
        validRange: {
            start: new Date().toISOString().split('T')[0] // disable all days before today
        },
        headerToolbar: {
            left: 'prev',
            center: 'title',
            right: 'next today'
        },
        dateClick: function(info) {
            const today = new Date().toISOString().split('T')[0];
            const dateStr = info.dateStr;
            const dayEl = info.dayEl;

            // Prevent selecting past dates
            if (dateStr < today) return;

            if (selectedDates.has(dateStr)) {
                selectedDates.delete(dateStr);
                dayEl.classList.remove('selected');
            } else {
                selectedDates.add(dateStr);
                dayEl.classList.add('selected');
            }
        },
        dayCellDidMount: function(info) {
            const dateStr = info.date.toISOString().split('T')[0];
            const today = new Date().toISOString().split('T')[0];

            // Disable past days visually
            if (dateStr < today) {
                info.el.classList.add('fc-day-disabled');
                info.el.style.backgroundColor = '#f5f5f5';
                info.el.style.cursor = 'not-allowed';
                return;
            }

            // Reapply selected style if applicable
            if (selectedDates.has(dateStr)) {
                info.el.classList.add('selected');
            } else {
                info.el.classList.remove('selected');
            }
        }
    });
    calendar.render();

    // Helper to format time like "1:00 PM" for display
    function formatTimeForDisplay(timeStr) {
        if (!timeStr) return '';
        const [hour, minute] = timeStr.split(':');
        const d = new Date();
        d.setHours(parseInt(hour, 10));
        d.setMinutes(parseInt(minute, 10));
        return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }

    // Helper to create hidden inputs (all created with class "scheduled-entry-input")
    function createHiddenInput(name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        input.classList.add('scheduled-entry-input');
        return input;
    }

    // Render header & tbody for table if not present
    function ensureScheduledTable() {
        let table = scheduledEntries.querySelector('table');
        if (!table) {
            table = document.createElement('table');
            table.id = 'scheduled-table';
            table.innerHTML = `
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Volunteers Needed</th>
                        <th>Food Provided</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
            scheduledEntries.appendChild(table);
        }
        return table;
    }

    // Add schedule entries when user clicks Add Schedule
    document.getElementById('add-calendar-schedule').addEventListener('click', function () {
        // validations
        if (selectedDates.size === 0) {
            alert('Please select at least one date.');
            return;
        }
        const startTime = document.getElementById('calendar-start-time').value;
        const endTime = document.getElementById('calendar-end-time').value;
        const targetNumber = document.getElementById('calendar-target-number').value;
        const foodProvided = document.querySelector('#intermittent-details input[name="food_provided"]:checked')?.value
            || document.querySelector('input[name="food_provided"]:checked')?.value || 'no';

        if (!startTime || !endTime || !targetNumber) {
            alert('Please fill all schedule details (start, end, volunteers).');
            return;
        }

        // ensure table exists
        const table = ensureScheduledTable();
        const tbody = table.querySelector('tbody');

        // For each selected date, add a row and hidden inputs
        selectedDates.forEach(dateStr => {
            // Prevent duplicate date for same scheduleIndex set
            // we check existing hidden inputs for date values
            const existingDateInputs = Array.from(document.querySelectorAll('.scheduled-entry-input[name$="[date]"]'));
            const exists = existingDateInputs.some(i => i.value === dateStr);
            if (exists) return; // skip duplicates

            // add visible row
            const row = document.createElement('tr');
            const displayNumber = tbody.children.length + 1;
            row.dataset.rowId = scheduleIndex; // tie to scheduleIndex
            indexMap[scheduleIndex] = scheduleIndex;

            const formattedDate = new Date(dateStr).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            row.innerHTML = `
                <td class="col-index">${displayNumber}</td>
                <td class="col-date">${formattedDate}</td>
                <td class="col-start">${formatTimeForDisplay(startTime)}</td>
                <td class="col-end">${formatTimeForDisplay(endTime)}</td>
                <td class="col-target">${targetNumber}</td>
                <td class="col-food">${(foodProvided || 'no').charAt(0).toUpperCase() + (foodProvided || 'no').slice(1)}</td>
                <td class="col-actions">
                    <button type="button" class="edit-btn" data-index="${scheduleIndex}" style="padding:4px 8px;background:#003DA5;color:white;border:none;border-radius:4px;cursor:pointer;margin-right:4px">Edit</button>
                    <button type="button" class="delete-btn" data-index="${scheduleIndex}" style="padding:4px 8px;background:#b71c1c;color:white;border:none;border-radius:4px;cursor:pointer">Delete</button>
                </td>
            `;

            tbody.appendChild(row);

            // add hidden inputs to the form
            form.appendChild(createHiddenInput(`schedule[${scheduleIndex}][date]`, dateStr));
            form.appendChild(createHiddenInput(`schedule[${scheduleIndex}][start_time]`, startTime));
            form.appendChild(createHiddenInput(`schedule[${scheduleIndex}][end_time]`, endTime));
            form.appendChild(createHiddenInput(`schedule[${scheduleIndex}][target_number]`, targetNumber));
            form.appendChild(createHiddenInput(`schedule[${scheduleIndex}][food_provided]`, foodProvided));

            scheduleIndex++;
        });

        // show header
        document.getElementById('scheduled-header').style.display = 'block';

        // Reset selection UI and fields
        document.querySelectorAll('.fc-daygrid-day.selected').forEach(el => el.classList.remove('selected'));
        selectedDates.clear();
        document.getElementById('calendar-start-time').value = '';
        document.getElementById('calendar-end-time').value = '';
        document.getElementById('calendar-target-number').value = '';

        // Re-number visible indexes
        reindexScheduledTable();
    });

    // Reindex function for visible rows
    function reindexScheduledTable() {
        const rows = document.querySelectorAll('#scheduled-table tbody tr');
        rows.forEach((r, idx) => {
            const colIndex = r.querySelector('.col-index');
            if (colIndex) colIndex.textContent = idx + 1;
        });
    }

    // Click handlers for edit & delete (event delegation)
    document.addEventListener('click', function(e) {
        // Delete
        if (e.target.classList.contains('delete-btn')) {
            const idx = e.target.getAttribute('data-index');
            if (!confirm('Remove this scheduled entry?')) return;

            // remove visible row
            const row = e.target.closest('tr');
            if (row) row.remove();

            // remove associated hidden inputs
            removeHiddenInputsByIndex(idx);

            // reindex visuals
            reindexScheduledTable();
        }

        // Edit -> convert row into editable inputs
        if (e.target.classList.contains('edit-btn')) {
            const idx = e.target.getAttribute('data-index');
            const row = e.target.closest('tr');
            if (!row) return;

            const dateInputName = `schedule[${idx}][date]`;
            const startInputName = `schedule[${idx}][start_time]`;
            const endInputName = `schedule[${idx}][end_time]`;
            const targetInputName = `schedule[${idx}][target_number]`;
            const foodInputName = `schedule[${idx}][food_provided]`;

            const hiddenDate = document.querySelector(`input[name="${dateInputName}"]`);
            const hiddenStart = document.querySelector(`input[name="${startInputName}"]`);
            const hiddenEnd = document.querySelector(`input[name="${endInputName}"]`);
            const hiddenTarget = document.querySelector(`input[name="${targetInputName}"]`);
            const hiddenFood = document.querySelector(`input[name="${foodInputName}"]`);

            // current values (fallback if hidden inputs missing)
            const curDate = hiddenDate ? hiddenDate.value : '';
            const curStart = hiddenStart ? hiddenStart.value : '';
            const curEnd = hiddenEnd ? hiddenEnd.value : '';
            const curTarget = hiddenTarget ? hiddenTarget.value : '';
            const curFood = hiddenFood ? hiddenFood.value : 'no';

            // Replace row innerHTML with inputs
            row.innerHTML = `
                <td>${row.querySelector('.col-index').textContent}</td>
                <td><input type="date" id="edit-date-${idx}" value="${curDate}" style="width:150px"></td>
                <td><input type="time" id="edit-start-${idx}" value="${curStart}" style="width:110px"></td>
                <td><input type="time" id="edit-end-${idx}" value="${curEnd}" style="width:110px"></td>
                <td><input type="number" min="1" id="edit-target-${idx}" value="${curTarget}" style="width:80px"></td>
                <td>
                    <select id="edit-food-${idx}">
                        <option value="yes" ${curFood === 'yes' ? 'selected' : ''}>Yes</option>
                        <option value="no" ${curFood === 'no' ? 'selected' : ''}>No</option>
                    </select>
                </td>
                <td>
                    <button type="button" class="save-edit" data-index="${idx}" style="padding:4px 8px;background:#4caf50;color:white;border:none;border-radius:4px;cursor:pointer;margin-right:4px">Save</button>
                    <button type="button" class="cancel-edit" data-index="${idx}" style="padding:4px 8px;background:#b71c1c;color:white;border:none;border-radius:4px;cursor:pointer">Cancel</button>
                </td>
            `;
        }

        // Save edit
        if (e.target.classList.contains('save-edit')) {
            const idx = e.target.getAttribute('data-index');

            const newDate = document.getElementById(`edit-date-${idx}`).value;
            const newStart = document.getElementById(`edit-start-${idx}`).value;
            const newEnd = document.getElementById(`edit-end-${idx}`).value;
            const newTarget = document.getElementById(`edit-target-${idx}`).value;
            const newFood = document.getElementById(`edit-food-${idx}`).value;

            if (!newDate || !newStart || !newEnd || !newTarget) {
                alert('Please complete all fields before saving.');
                return;
            }

            // Update hidden inputs (if exist) or create them if missing
            updateOrCreateHiddenInput(`schedule[${idx}][date]`, newDate);
            updateOrCreateHiddenInput(`schedule[${idx}][start_time]`, newStart);
            updateOrCreateHiddenInput(`schedule[${idx}][end_time]`, newEnd);
            updateOrCreateHiddenInput(`schedule[${idx}][target_number]`, newTarget);
            updateOrCreateHiddenInput(`schedule[${idx}][food_provided]`, newFood);

            // Update visible row
            const row = e.target.closest('tr');
            const displayIdx = row.querySelector('td:first-child')?.textContent || '';
            row.innerHTML = `
                <td class="col-index">${displayIdx}</td>
                <td class="col-date">${new Date(newDate).toLocaleDateString()}</td>
                <td class="col-start">${formatTimeForDisplay(newStart)}</td>
                <td class="col-end">${formatTimeForDisplay(newEnd)}</td>
                <td class="col-target">${newTarget}</td>
                <td class="col-food">${newFood.charAt(0).toUpperCase() + newFood.slice(1)}</td>
                <td class="col-actions">
                    <button type="button" class="edit-btn" data-index="${idx}" style="padding:4px 8px;background:#003DA5;color:white;border:none;border-radius:4px;cursor:pointer;margin-right:4px">Edit</button>
                    <button type="button" class="delete-btn" data-index="${idx}" style="padding:4px 8px;background:#b71c1c;color:white;border:none;border-radius:4px;cursor:pointer">Delete</button>
                </td>
            `;
            reindexScheduledTable();
        }

        // Cancel edit -> revert by reading hidden inputs
        if (e.target.classList.contains('cancel-edit')) {
            const idx = e.target.getAttribute('data-index');
            const dateInput = document.querySelector(`input[name="schedule[${idx}][date]"]`);
            const startInput = document.querySelector(`input[name="schedule[${idx}][start_time]"]`);
            const endInput = document.querySelector(`input[name="schedule[${idx}][end_time]"]`);
            const tarInput = document.querySelector(`input[name="schedule[${idx}][target_number]"]`);
            const foodInput = document.querySelector(`input[name="schedule[${idx}][food_provided]"]`);

            const row = e.target.closest('tr');
            const displayIdx = row.querySelector('td:first-child')?.textContent || '';

            const curDate = dateInput ? new Date(dateInput.value).toLocaleDateString() : '';
            const curStart = startInput ? formatTimeForDisplay(startInput.value) : '';
            const curEnd = endInput ? formatTimeForDisplay(endInput.value) : '';
            const curTarget = tarInput ? tarInput.value : '';
            const curFood = foodInput ? (foodInput.value.charAt(0).toUpperCase() + foodInput.value.slice(1)) : '';

            row.innerHTML = `
                <td class="col-index">${displayIdx}</td>
                <td class="col-date">${curDate}</td>
                <td class="col-start">${curStart}</td>
                <td class="col-end">${curEnd}</td>
                <td class="col-target">${curTarget}</td>
                <td class="col-food">${curFood}</td>
                <td class="col-actions">
                    <button type="button" class="edit-btn" data-index="${idx}" style="padding:4px 8px;background:#003DA5;color:white;border:none;border-radius:4px;cursor:pointer;margin-right:4px">Edit</button>
                    <button type="button" class="delete-btn" data-index="${idx}" style="padding:4px 8px;background:#b71c1c;color:white;border:none;border-radius:4px;cursor:pointer">Delete</button>
                </td>
            `;
        }
    });

    // helper to update or create hidden input by name
    function updateOrCreateHiddenInput(name, value) {
        const existing = document.querySelector(`input[name="${name}"]`);
        if (existing) {
            existing.value = value;
        } else {
            form.appendChild(createHiddenInput(name, value));
        }
    }

    // helper to remove hidden inputs by schedule index
    function removeHiddenInputsByIndex(idx) {
        const names = [
            `schedule[${idx}][date]`,
            `schedule[${idx}][start_time]`,
            `schedule[${idx}][end_time]`,
            `schedule[${idx}][target_number]`,
            `schedule[${idx}][food_provided]`
        ];
        names.forEach(n => {
            const el = document.querySelector(`input[name="${n}"]`);
            if (el) el.remove();
        });
    }

    // Rooms/Level handling (unchanged behavior)
    const rooms = {
        "Level 1": [
            "Sky Garden",
            "LRC",
            "Function Hall",
            "Accounting Office",
            "Registrar's Office",
            "Level 1"
        ],
        "Level 2": [
            "Room 204 (SDAO)",
            "Room 210 (GCO)",
            "Room 211 (Community Extension and NSTP Office)",
            "Room 224-225"
        ],
        "Level 3": [
            "Room 304 (Faculty Room)",
            "Room 320 (Chapel)"
        ],
        "Level 4": [
            "Room 411 (ITSO)",
            "AVR"
        ],
        "Level 5": [
            "Function Hall"
        ]
    };

    const levelSelect = document.getElementById("level");
    const roomSelect = document.getElementById("room");
    const locationInput = document.getElementById("location");
    const levelOtherInput = document.getElementById("level-other-input");
    const roomOtherInput = document.getElementById("room-other-input");

    // WHEN LEVEL CHANGES
    levelSelect.addEventListener("change", function () {
        const selectedLevel = this.value;
        roomSelect.innerHTML = `<option value="" disabled selected>Select Room / Area</option>`;
        roomSelect.disabled = false;

        // hide custom inputs
        levelOtherInput.style.display = "none";
        roomOtherInput.style.display = "none";

        if (selectedLevel === "Others") {
            levelOtherInput.style.display = "block";
            roomSelect.innerHTML = `
                <option value="" disabled selected>Select Room / Area</option>
                <option value="Others">Others</option>
            `;
            roomSelect.disabled = false;
            locationInput.value = "";
            return;
        }

        // Normal level → load rooms
        (rooms[selectedLevel] || []).forEach(r => {
            const opt = document.createElement("option");
            opt.value = r;
            opt.textContent = r;
            roomSelect.appendChild(opt);
        });

        // Add room Others
        const opt = document.createElement("option");
        opt.value = "Others";
        opt.textContent = "Others";
        roomSelect.appendChild(opt);

        locationInput.value = "";
    });

    // WHEN ROOM CHANGES
    roomSelect.addEventListener("change", function () {
        const selectedRoom = this.value;
        roomOtherInput.style.display = "none";

        if (selectedRoom === "Others") {
            roomOtherInput.style.display = "block";
            locationInput.value = "";
            return;
        }

        if (levelSelect.value === "Others") {
            const lvlText = levelOtherInput.value.trim();
            locationInput.value = lvlText + " - " + selectedRoom;
            return;
        }

        locationInput.value = levelSelect.value + " - " + selectedRoom;
    });

    // Custom LEVEL typing updates final value
    levelOtherInput.addEventListener("input", function () {
        if (roomSelect.value && roomSelect.value !== "Others") {
            locationInput.value = this.value.trim() + " - " + roomSelect.value;
        }
    });

    // Custom ROOM typing updates final value
    roomOtherInput.addEventListener("input", function () {
        const lvl = (levelSelect.value === "Others")
            ? levelOtherInput.value.trim()
            : levelSelect.value;

        locationInput.value = lvl + " - " + this.value.trim();
    });

}); // DOMContentLoaded

const steps = document.querySelectorAll(".form-step");
let currentStep = 0;

function showStep(index) {
    steps.forEach((step, i) => {
        step.style.display = i === index ? "block" : "none";
    });
}

document.querySelectorAll(".next-step").forEach(btn => {
    btn.addEventListener("click", () => {
        if (currentStep < steps.length - 1) {
            currentStep++;
            showStep(currentStep);
            window.scrollTo({ top: 0, behavior: "smooth" });
        }
    });
});

document.querySelectorAll(".prev-step").forEach(btn => {
    btn.addEventListener("click", () => {
        if (currentStep > 0) {
            currentStep--;
            showStep(currentStep);
            window.scrollTo({ top: 0, behavior: "smooth" });
        }
    });
});

// show first step on load
showStep(0);
</script>

@endsection
