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

<!-- LEVEL DROPDOWN -->
<select id="level" required>
    <option value="" selected disabled>Select Level</option>
    <option value="Level 1">Level 1</option>
    <option value="Level 2">Level 2</option>
    <option value="Level 3">Level 3</option>
    <option value="Level 4">Level 4</option>
    <option value="Level 5">Level 5</option>
    <option value="Others">Others</option>
</select>

<!-- Custom LEVEL input - hidden -->
<input type="text" id="level-other-input" placeholder="Enter custom level..."
       style="display:none; margin-top:8px;">

<!-- ROOM DROPDOWN -->
<select id="room" required disabled>
    <option value="" selected disabled>Select Room / Area</option>
</select>

<!-- Custom ROOM input - hidden -->
<input type="text" id="room-other-input" placeholder="Enter custom room/area..."
       style="display:none; margin-top:8px;">


<!-- Hidden final field that is submitted -->
<input type="hidden" name="location" id="location">


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

                    
                <label>Task Selection</label>

<table class="task-table">
    <thead>
        <tr>
            <th>Select</th>
            <th>Task</th>
            <th>Skills Recommended</th>
            <th>Short Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><input type="checkbox" name="tasks[]" value="Marshalling"></td>
            <td>Marshalling</td>
            <td>Crowd management, clear communication, alert, patient</td>
            <td>Helps manage crowd flow, guide participants, and keep pathways clear.</td>
        </tr>
        <tr>
            <td><input type="checkbox" name="tasks[]" value="Registration"></td>
            <td>Registration</td>
            <td>Organized, detail-oriented, friendly, basic data encoding</td>
            <td>Handles check-in, attendance lists, and assists participants.</td>
        </tr>
        <tr>
            <td><input type="checkbox" name="tasks[]" value="Ushering"></td>
            <td>Ushering</td>
            <td>Approachable, good communication, presentable</td>
            <td>Guides attendees to seats and helps answer questions.</td>
        </tr>
        <tr>
            <td><input type="checkbox" name="tasks[]" value="Photography"></td>
            <td>Photography</td>
            <td>Basic photography skills, attention to detail</td>
            <td>Takes photos of key moments and activities.</td>
        </tr>
        <tr>
            <td><input type="checkbox" name="tasks[]" value="Videography"></td>
            <td>Videography</td>
            <td>Basic video recording, timing awareness</td>
            <td>Records event highlights and important segments.</td>
        </tr>
        <tr>
            <td><input type="checkbox" name="tasks[]" value="Technical Support"></td>
            <td>Technical Support</td>
            <td>Basic IT skills, calm under pressure</td>
            <td>Helps manage audio, projector, and technical systems.</td>
        </tr>
        <tr>
            <td><input type="checkbox" name="tasks[]" value="Creatives/Design"></td>
            <td>Creatives / Design</td>
            <td>Basic design sense, creativity</td>
            <td>Prepares visual materials, layouts, and social media assets.</td>
        </tr>
        <tr>
            <td><input type="checkbox" name="tasks[]" value="Hosting"></td>
            <td>Hosting</td>
            <td>Confident speaker, energetic, good diction</td>
            <td>Leads the program, introduces speakers, engages the audience.</td>
        </tr>
        <tr>
            <td><input type="checkbox" name="tasks[]" value="Logistics"></td>
            <td>Logistics</td>
            <td>Physically capable, organized</td>
            <td>Transfers equipment/materials and supports operations.</td>
        </tr>
        <tr>
            <td><input type="checkbox" name="tasks[]" value="Admin Support"></td>
            <td>Admin Support</td>
            <td>Detail-oriented, encoding, filing</td>
            <td>Prepares documents, attendance, certificates, encodes data.</td>
        </tr>

        <!-- Others row -->
        <tr>
            <td><input type="checkbox" name="tasks[]" value="Others"></td>
   <td>Others</td>

    <!-- Skills Recommended -->
    <td>
        <input type="text" name="task_other_skills" placeholder="Enter skills recommended…" style="width:100%;">
    </td>

    <!-- Short Description -->
    <td>
        <input type="text" name="task_other_description" placeholder="Enter short description…" style="width:100%;">
    </td>
        </tr>
    </tbody>
</table>
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


// WHEN LEVEL CHANGES -----------------------------------
levelSelect.addEventListener("change", function () {
    const selectedLevel = this.value;

    roomSelect.innerHTML = `<option value="" disabled selected>Select Room / Area</option>`;
    roomSelect.disabled = false;

    // hide custom inputs
    levelOtherInput.style.display = "none";
    roomOtherInput.style.display = "none";

    // If LEVEL = OTHERS
    if (selectedLevel === "Others") {
        levelOtherInput.style.display = "block";

        // Show room dropdown BUT only "Others"
        roomSelect.innerHTML = `
            <option value="" disabled selected>Select Room / Area</option>
            <option value="Others">Others</option>
        `;
        roomSelect.disabled = false;

        locationInput.value = "";
        return;
    }

    // Normal level → load rooms
    rooms[selectedLevel].forEach(r => {
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


// WHEN ROOM CHANGES -------------------------------------
roomSelect.addEventListener("change", function () {
    const selectedRoom = this.value;

    roomOtherInput.style.display = "none"; // hide first

    // If room = Others → show custom room input
    if (selectedRoom === "Others") {
        roomOtherInput.style.display = "block";
        locationInput.value = "";
        return;
    }

    // If level is custom
    if (levelSelect.value === "Others") {
        const lvlText = levelOtherInput.value.trim();
        locationInput.value = lvlText + " - " + selectedRoom;
        return;
    }

    // Normal level + room
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

</script>

@endsection 