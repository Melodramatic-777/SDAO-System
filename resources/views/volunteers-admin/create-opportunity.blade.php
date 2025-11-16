@extends('layouts.app')

@section('content')

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<style>
    /* Make the overall form wider and centered */
    .form-container {
        max-width: 1300px; /* ⬅️ wider form */
        margin: 0 auto;
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
</style>


<div class="form-container">
    <h2 class="form-title">Request Volunteer Form</h2>
    <form action="{{ route('volunteers-admin.store-opportunity') }}" method="POST">
        @csrf
        <div class="form-grid">

            <!-- Left Column -->
<div class="form-left">
    <!-- ✅ Urgent field on top of Title -->

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

    <!-- 🟩 Moved Food Provided to here -->
    <label>Food Provided</label>
    <div class="radio-group" style="margin-top: 5px; margin-bottom: 10px;">
        <label><input type="radio" name="food_provided" value="yes"> Yes</label>
        <label><input type="radio" name="food_provided" value="no"> No</label>
    </div>

    <button type="button" id="add-calendar-schedule" class="btn-secondary">Add Schedule</button>
</div>
               
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
                    <h5 id="scheduled-header" style="display:none;">Scheduled Entries</h5>
                    <div id="scheduled-entries"></div>

                <label>Description</label>
                <textarea name="description" placeholder="Enter activity description..." required></textarea>

                    
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
            foodProvidedBottom.style.display = "none";
            document.querySelectorAll("#regular-schedule input").forEach(i => i.value = "");

            // 👇 Force FullCalendar to render once visible
            setTimeout(() => {
                calendar.render();

                const timeInputs = document.querySelectorAll('#calendar-start-time, #calendar-end-time');
timeInputs.forEach(input => {
    input.addEventListener('focus', () => {
        document.getElementById('time-details').scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    });
});

                calendar.today(); // show the active/current month
            }, 200);

        } else {
            intermittentDetails.style.display = "none";
            regularSchedule.style.display = "block";
            foodProvidedBottom.style.display = "block";
            document.querySelectorAll(".scheduled-entry-input").forEach(i => i.remove());
            scheduledEntries.innerHTML = "";
        }
    });
});

document.addEventListener("input", function(e) {
    if (e.target.matches('input[type="number"][min="1"]')) {
        let value = parseInt(e.target.value);

        // ✅ Only enforce minimum of 1
        if (isNaN(value) || value < 1) {
            alert('Volunteers needed must be at least 1.');
            e.target.value = 1;
        }
    }
});



    // FullCalendar setup
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

let scheduleIndex = 0; // Track unique schedule inputs

document.getElementById('add-calendar-schedule').addEventListener('click', () => {
    document.getElementById('scheduled-header').style.display = 'block';
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

// Create table if it doesn’t exist yet
let table = scheduledEntries.querySelector('table');
if (!table) {
    table = document.createElement('table');
    const header = document.createElement('thead');
header.innerHTML = `
    <tr>
        <th>#</th>
        <th>Date</th>
        <th>Start Time</th>
        <th>End Time</th>
        <th>Volunteers Needed</th>
        <th>Food Provided</th>
        <th>Edit</th>
    </tr>`;
    table.appendChild(header);
    table.appendChild(document.createElement('tbody'));
    scheduledEntries.appendChild(table);
}

const tbody = table.querySelector('tbody');
const row = document.createElement('tr');
const foodProvided = document.querySelector('input[name="food_provided"]:checked')?.value || 'no';

const formattedDate = new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
});

function toggleBackButton() {
    const backButton = document.querySelector('.fc-customBack-button');
    const currentMonth = new Date().getMonth();
    const currentYear = new Date().getFullYear();

    const calendarDate = calendar.getDate();
    const calendarMonth = calendarDate.getMonth();
    const calendarYear = calendarDate.getFullYear();

    // Show back button only if not in current month
    if (calendarMonth !== currentMonth || calendarYear !== currentYear) {
        backButton.style.display = 'inline-block';
    } else {
        backButton.style.display = 'none';
    }
}
calendar.on('datesSet', toggleBackButton);

// Format time like "1:00 PM"
function formatTime(timeStr) {
    const [hour, minute] = timeStr.split(':');
    const dateObj = new Date();
    dateObj.setHours(hour);
    dateObj.setMinutes(minute);
    return dateObj.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

row.innerHTML = `
    <td>${scheduleIndex + 1}</td>
    <td>${formattedDate}</td>
    <td>${formatTime(startTime)}</td>
    <td>${formatTime(endTime)}</td>
    <td>${targetNumber}</td>
    <td>${foodProvided.charAt(0).toUpperCase() + foodProvided.slice(1).toLowerCase()}</td>
    <td>
        <button type="button" class="edit-btn" data-index="${scheduleIndex}" style="
            padding:4px 8px; 
            background:#003DA5;
            color:white;
            border:none;
            border-radius:4px;
            cursor:pointer;
        ">Edit</button>
    </td>
`;


// Add hidden input for food_provided per schedule
const foodInput = document.createElement('input');
foodInput.type = 'hidden';
foodInput.name = `schedule[${scheduleIndex}][food_provided]`;
foodInput.value = foodProvided;
foodInput.classList.add('scheduled-entry-input');
document.querySelector('form').appendChild(foodInput);
tbody.appendChild(row);


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


document.addEventListener("click", function(e) {
    if (e.target.classList.contains("edit-btn")) {
        const index = e.target.getAttribute("data-index");
        const row = e.target.closest("tr");

        const cells = row.querySelectorAll("td");

        const original = {
            number: cells[0].innerText,
            date: cells[1].innerText,
            start: cells[2].innerText,
            end: cells[3].innerText,
            volunteers: cells[4].innerText,
            food: cells[5].innerText,
        };

        row.innerHTML = `
            <td>${original.number}</td>
            <td><input type="date" id="edit-date-${index}" value="${new Date(original.date).toISOString().split('T')[0]}"></td>
            <td><input type="time" id="edit-start-${index}"></td>
            <td><input type="time" id="edit-end-${index}"></td>
            <td><input type="number" min="1" id="edit-volunteers-${index}" value="${original.volunteers}"></td>
            <td>
                <select id="edit-food-${index}">
                    <option value="Yes" ${original.food === "Yes" ? "selected" : ""}>Yes</option>
                    <option value="No" ${original.food === "No" ? "selected" : ""}>No</option>
                </select>
            </td>
            <td>
                <button type="button" class="save-edit" data-index="${index}" style="
                    padding:4px 8px;
                    background:#4caf50;
                    color:white;
                    border:none;
                    border-radius:4px;
                    cursor:pointer;
                ">Save</button>

                <button type="button" class="cancel-edit" data-index="${index}" style="
                    padding:4px 8px;
                    background:#b71c1c;
                    color:white;
                    border:none;
                    border-radius:4px;
                    cursor:pointer;
                    margin-left:5px;
                ">Cancel</button>
            </td>
        `;
    }

    // SAVE EDIT
    if (e.target.classList.contains("save-edit")) {
        const index = e.target.getAttribute("data-index");

        const newDate = document.getElementById(`edit-date-${index}`).value;
        const newStart = document.getElementById(`edit-start-${index}`).value;
        const newEnd = document.getElementById(`edit-end-${index}`).value;
        const newVol = document.getElementById(`edit-volunteers-${index}`).value;
        const newFood = document.getElementById(`edit-food-${index}`).value;

        if (!newDate || !newStart || !newEnd || !newVol) {
            return alert("Please complete all fields before saving.");
        }

        const row = e.target.closest("tr");

        row.innerHTML = `
            <td>${parseInt(index) + 1}</td>
            <td>${new Date(newDate).toLocaleDateString()}</td>
            <td>${formatTime(newStart)}</td>
            <td>${formatTime(newEnd)}</td>
            <td>${newVol}</td>
            <td>${newFood}</td>
            <td>
                <button type="button" class="edit-btn" data-index="${index}" style="
                    padding:4px 8px; 
                    background:#003DA5;
                    color:white;
                    border:none;
                    border-radius:4px;
                    cursor:pointer;
                ">Edit</button>
            </td>
        `;

        // Update hidden input fields
        document.querySelector(`input[name="schedule[${index}][date]"]`).value = newDate;
        document.querySelector(`input[name="schedule[${index}][start_time]"]`).value = newStart;
        document.querySelector(`input[name="schedule[${index}][end_time]"]`).value = newEnd;
        document.querySelector(`input[name="schedule[${index}][target_number]"]`).value = newVol;
        document.querySelector(`input[name="schedule[${index}][food_provided]"]`).value = newFood;
    }

    // CANCEL EDIT → reload table
    if (e.target.classList.contains("cancel-edit")) {
        location.reload(); // quickest revert
    }
});


</script>

@endsection
