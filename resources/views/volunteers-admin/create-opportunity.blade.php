@extends('layouts.app')

@section('content')

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<style>
    #fullcalendar {
        max-width: 100%;
        margin: 10px 0;
        min-height: 400px; /* Ensure calendar is visible */
    }
    .fc-daygrid-day.selected {
        background-color: #4caf50 !important;
        color: white !important;
    }
    .fc-daygrid-day:hover {
        cursor: pointer;
        background-color: #a5d6a7 !important;
    }
    .form-grid {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    .form-left, .form-right {
        flex: 1;
        min-width: 300px;
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
</style>

<div class="form-container">
    <h2 class="form-title">Request Volunteer Form</h2>
    <form action="{{ route('volunteers-admin.store-opportunity') }}" method="POST">
        @csrf
        <div class="form-grid">

            <!-- Left Column -->
<div class="form-left">
    <!-- ✅ Urgent field on top of Title -->
    <label><strong>Is this opportunity urgent?</strong></label>
    <div class="radio-group" style="margin-bottom: 10px;">
        <label><input type="radio" name="is_urgent" value="yes"> Yes, urgent</label>
        <label><input type="radio" name="is_urgent" value="no" checked> No</label>
    </div>

    <label>Title of activity</label>
    <input type="text" name="title" placeholder="Enter title proposal" required>

                <label>Location</label>
                <input type="text" name="location" placeholder="Enter Location" required>

                <!-- Intermittent Option -->
                <label>My event happens intermittently?</label>
                <div class="radio-group">
                    <label><input type="radio" name="is_intermittent" value="0" checked> No</label>
                    <label><input type="radio" name="is_intermittent" value="1"> Yes</label>
                </div>

                <!-- Regular Schedule (shown if NO) -->
                <div id="regular-schedule">
                    <div class="form-row">
                        <div>
                            <label>Start Date</label>
                            <input type="date" name="start_date">
                        </div>
                        <div>
                            <label>End Date</label>
                            <input type="date" name="end_date">
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

                <!-- FullCalendar (shown if YES) -->
                <div id="intermittent-details" style="display:none; margin-top:10px;">
                    <h4>Custom Schedule (Month Calendar)</h4>
                    <div id="fullcalendar"></div>

                    <div id="time-details" style="margin-top:10px;">
                        <label>Start Time</label>
                        <input type="time" id="calendar-start-time">
                        <label>End Time</label>
                        <input type="time" id="calendar-end-time">
                        <label>Volunteers Needed</label>
                        <input type="number" id="calendar-target-number" min="1">
                        <button type="button" id="add-calendar-schedule" class="btn-secondary">Add Schedule</button>
                    </div>

                               <label>Food Provided</label>
                <div class="radio-group">
                    <label><input type="radio" name="food_provided" value="yes"> Yes</label>
                    <label><input type="radio" name="food_provided" value="no"> No</label>
                </div>
                    <h5>Scheduled Entries</h5>
                    <div id="scheduled-entries"></div>
                </div>

                <div id="food-provided-bottom">
    <label>Food Provided</label>
    <div class="radio-group">
        <label><input type="radio" name="food_provided" value="yes"> Yes</label>
        <label><input type="radio" name="food_provided" value="no"> No</label>
    </div>
</div>

            </div>

            

            <!-- Right Column -->
            <div class="form-right">
                <label>Task Details</label>
                <textarea name="task_details" placeholder="Enter description...." required></textarea>

                <label>Skills Needed</label>
                <textarea name="skills_needed" placeholder="Enter description...." required></textarea>
            </div>

        </div>

        <div class="form-submit">
            <button type="submit" class="btn-submit">Submit</button>
        </div>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const radios = document.querySelectorAll("input[name='is_intermittent']");
    const regularSchedule = document.getElementById("regular-schedule");
    const intermittentDetails = document.getElementById("intermittent-details");
    const scheduledEntries = document.getElementById("scheduled-entries");
    const foodProvidedBottom = document.getElementById("food-provided-bottom");

    let selectedDates = new Set();
    let addedDates = new Set();

    // Toggle between intermittent YES/NO
    radios.forEach(radio => {
        radio.addEventListener("change", () => {
            if (radio.value === "1") {
                intermittentDetails.style.display = "block";
                regularSchedule.style.display = "none";
                foodProvidedBottom.style.display = "none"; // hide duplicate
                document.querySelectorAll("#regular-schedule input").forEach(i => i.value = "");
            } else {
                intermittentDetails.style.display = "none";
                regularSchedule.style.display = "block";
                foodProvidedBottom.style.display = "block"; // show again
                document.querySelectorAll(".scheduled-entry-input").forEach(i => i.remove());
                scheduledEntries.innerHTML = "";
            }
        });
    });

    // FullCalendar setup
    const calendarEl = document.getElementById('fullcalendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        selectable: true,
        headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
        dateClick: function(info) {
            const dateStr = info.dateStr;
            const dayEl = info.dayEl;
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
            if (selectedDates.has(dateStr)) info.el.classList.add('selected');
            else info.el.classList.remove('selected');
        }
    });
    calendar.render();

let scheduleIndex = 0; // Track unique schedule inputs

document.getElementById('add-calendar-schedule').addEventListener('click', () => {
    const startTime = document.getElementById('calendar-start-time').value;
    const endTime = document.getElementById('calendar-end-time').value;
    const targetNumber = document.getElementById('calendar-target-number').value;

    if (selectedDates.size === 0) return alert('Please select at least one date.');
    if (!startTime || !endTime || !targetNumber) return alert('Please fill all schedule details.');

    selectedDates.forEach((date) => {
        if (addedDates.has(date)) return; // avoid duplicates
        addedDates.add(date);

        const inputs = [
            { name: `schedule[${scheduleIndex}][date]`, value: date },
            { name: `schedule[${scheduleIndex}][start_time]`, value: startTime },
            { name: `schedule[${scheduleIndex}][end_time]`, value: endTime },
            { name: `schedule[${scheduleIndex}][target_number]`, value: targetNumber }
        ];

        inputs.forEach(f => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = f.name;
            input.value = f.value;
            input.classList.add('scheduled-entry-input');
            document.querySelector('form').appendChild(input);
        });

        const entryDiv = document.createElement('div');
        entryDiv.textContent = `${date}: ${startTime} - ${endTime}, Volunteers Needed: ${targetNumber}`;
        scheduledEntries.appendChild(entryDiv);

        scheduleIndex++; // increase only after adding a full set
    });

    // Reset after adding
    document.querySelectorAll('.fc-daygrid-day.selected').forEach(el => el.classList.remove('selected'));
    selectedDates.clear();
    document.getElementById('calendar-start-time').value = '';
    document.getElementById('calendar-end-time').value = '';
    document.getElementById('calendar-target-number').value = '';
});
});

</script>

@endsection
