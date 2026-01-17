@extends('layouts.app')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<style>
  :root{
    --primary: #003DA5;
    --primary-2: #0b54c1;
    --accent: #10b981;
    --muted: #6b7280;
    --card-bg: #ffffff;
    --soft-bg: #f8fafc;
    --radius: 14px;
  }

  body {
    font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    background: linear-gradient(135deg, #e9f0ff 0%, #f6f9ff 100%);
    min-height: 100vh;
    -webkit-font-smoothing:antialiased;
  }

.page-wrap {
  max-width: 1100px;
  margin: 0 auto;           /* center horizontally */
  display: flex;             /* flex container */
  justify-content: center;   /* center horizontally */
  align-items: center;       /* center vertically */
  min-height: 100vh;         /* full viewport height for vertical centering */
}

.card-wrap {
  background: var(--card-bg);
  border-radius: var(--radius);
  padding: 28px;
  box-shadow: 0 14px 40px rgba(2,6,23,0.08);
  width: 100%;               /* fill parent max-width */
  max-width: 900px;          /* limit max width */
}


  header.form-header { text-align:center; margin-bottom:18px; }

.steps {
  display: flex;
  align-items: center;
  justify-content: center;  /* center all steps */
  gap: 12px;                /* space between step-items */
  margin: 18px 0 24px;
  width: 100%;
}


.step-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.step-indicator {
    width:46px; height:46px; border-radius:50%;
    background:#E6EEF9;        /* default grey */
    display:flex; align-items:center; justify-content:center;
    color:var(--muted);
    font-weight:700;
    transition: transform .18s ease, background .18s ease;
}  .step-indicator.active { transform: scale(1.06); background: linear-gradient(135deg, var(--primary), var(--primary-2)); color:#fff; }

.step-line {
  width: 100px;               /* fixed width for lines */
  height: 3px;
  background: #eef2ff;
  border-radius: 6px;
}

.step-item:last-child .step-line {
  display: none;          /* hide line after last step */
}


  #stepTitle { font-size:18px; font-weight:600; color:var(--primary); text-align:center; margin-bottom:16px; }

.step-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.step-content {
    display: flex;
    flex-direction: column;   /* stack number and label vertically */
    align-items: center;      /* center horizontally */
    gap: 6px;                 /* space between number and label */
}

.step-label {
    font-size: 13px;
    color: var(--muted);
    font-weight: 600;
    text-align: center;
}


.radio-group label {
    font-weight: normal;
}

.step-indicator.completed {
    background: #10b981; /* grey for completed */
    color: white;         /* white text */
}

.step-indicator.active {
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    color: white;
}


  .left-panel { min-width:0; }
  .right-panel { background: var(--soft-bg); border-radius:12px; padding:14px; max-height:720px; overflow:auto; }

  .form-section { margin-bottom:18px; }
  label { display:block; font-weight:600; color:#172554; margin-bottom:6px; }

  input[type="text"], input[type="date"], input[type="time"], input[type="number"], textarea, select {
    width:100%; padding:10px 12px; border-radius:10px; border:1px solid #e6eef9; background:#fff; outline:none;
    transition: box-shadow .12s ease, border-color .12s ease;
  }
  input:focus, textarea:focus, select:focus { border-color: var(--primary); box-shadow: 0 6px 18px rgba(3,61,165,0.08); }

  .btn-primary { background: linear-gradient(135deg, var(--primary), var(--primary-2)); border:none; color:white; padding:12px 16px; border-radius:10px; font-weight:600; }
  .btn-outline { background:white; border:1px solid var(--primary); color:var(--primary); padding:10px 14px; border-radius:10px; font-weight:600; }
  .btn-secondary { background:var(--primary); color:white; border:none; padding:7px 10px; border-radius:8px; }

  /* FullCalendar tweaks */
 /* ====================== */
/* CANVA CALENDAR THEME   */
/* ====================== */

.fc {
    --fc-palette-primary: #003DA5 !important;
    --fc-palette-primary-dark: #0b54c1 !important;
}

/* Calendar container */
#fullcalendar {
    border-radius: 16px;
    padding: 20px;
    background: #ffffff;
    border: 2px solid #e5e7eb;
}


/* FIX: Force FullCalendar header to NOT inherit yellow link color */
/* FIX: remove yellow and force FullCalendar header to blue */
.fc .fc-toolbar-title,
.fc .fc-col-header-cell-cushion,
.fc .fc-button,
.fc .fc-button:disabled,
.fc a,
.fc a:hover {
    color: var(--primary) !important;   /* Blue from your variable */
    text-decoration: none !important;
}



/* Header Title */
.fc .fc-toolbar-title {
    font-size: 20px;
    font-weight: 700;
    color: #003DA5;
}

/* Header Buttons */
.fc .fc-button {
    background: #f3f4f6 !important;
    border: none !important;
    color: #003DA5 !important;
    font-weight: 600;
    padding: 6px 12px !important;
    border-radius: 8px !important;
}

.fc .fc-button:hover {
    background: #e0e7ff !important;
}

/* Day Cell */
.fc .fc-daygrid-day-frame {
    border-radius: 10px !important;
    transition: 0.2s;
}

/* Hover */
.fc .fc-daygrid-day:hover {
    background: #eef2ff !important;
}

/* Selected Day (matches Canva purple) */
.fc .fc-daygrid-day.selected,
.fc .fc-daygrid-day.fc-day-today.selected {
    background: #003DA5 !important;
    color: #fff !important;
    border-radius: 10px;
}

/* Today's date highlight (light purple ring) */
.fc .fc-daygrid-day.fc-day-today {
    background: #f5f8ff !important;
    border: 2px solid #c7d2fe !important;
    border-radius: 10px;
}

/* Disable past dates */
.fc-day-disabled {
    background: #f3f4f6 !important;
    color: #9ca3af !important;
    cursor: not-allowed !important;
}

/* Numbers inside days */
.fc .fc-daygrid-day-number {
    font-size: 14px;
    font-weight: 600;
    color: #003DA5;
}

/* Selected number text */
.fc .selected .fc-daygrid-day-number {
    color: white !important;
}

/* Week headers */
.fc .fc-col-header-cell-cushion {
    color: #003DA5 !important;
    font-weight: 600;
    padding: 8px 0;
}


.fc-daygrid-day-number {
    text-decoration: none !important;
}

/* Remove underline from all FullCalendar links */
.fc a {
    text-decoration: none !important;
}

/* Remove underline on hover */
.fc a:hover {
    text-decoration: none !important;
}

/* Specifically remove underline from month title */
.fc-toolbar-title {
    text-decoration: none !important;
}

/* Remove underline from weekday headers (Sun–Sat) */
.fc-col-header-cell-cushion {
    text-decoration: none !important;
}

/* Remove underline from day numbers */
.fc-daygrid-day-number {
    text-decoration: none !important;
}


  /* Scheduled entries table inside right panel */
  #scheduled-entries table { width:100%; border-collapse:collapse; font-size:13px; }
  #scheduled-entries th { background:var(--primary); color:white; padding:8px; text-align:left; border-radius:6px 6px 0 0; }
  #scheduled-entries td { padding:8px; border-bottom:1px solid #eef2ff; vertical-align:middle; }

  /* Summary card grid (two-column cards) */
  .summary-grid { display:grid; grid-template-columns: 1fr 1fr; gap:12px; }
  @media (max-width:600px){ .summary-grid { grid-template-columns: 1fr; } }

  .summary-card { background: white; padding:12px; border-radius:10px; box-shadow: 0 6px 18px rgba(2,6,23,0.03); border:1px solid #f1f7ff; }
  .summary-card h6 { margin:0 0 8px; color:var(--primary); font-weight:700; font-size:13px; }
  .summary-card p { margin:0; color:#0f172a; font-size:14px; }

  .muted { color:var(--muted); font-size:13px; }
  .pill { display:inline-block; padding:6px 10px; border-radius:999px; background:#eef2ff; color:var(--primary); font-weight:600; font-size:13px; }


.form-navigation-between {
    display: flex;
    justify-content: space-between;
    gap: 12px;
}
/* REMOVE ALL YELLOW FROM FULLCALENDAR HEADER + DAYGRID */
.fc a,
.fc a:hover,
.fc a:focus,
.fc .fc-scrollgrid-sync-inner a,
.fc .fc-daygrid-day-number,
.fc .fc-col-header-cell-cushion,
.fc .fc-toolbar-title {
    color: var(--primary) !important;   /* Your NU Blue */
    text-decoration: none !important;
}

/* Also remove yellow background from clicked/active buttons */
.fc .fc-button:active,
.fc .fc-button-primary:not(:disabled).fc-button-active,
.fc .fc-button-primary:not(:disabled):active {
    background: #e0e7ff !important;
    border: none !important;
    color: var(--primary) !important;
}

/* REMOVE ALL YELLOW from FullCalendar */

/* Remove yellow background used by TODAY */
.fc-daygrid-day.fc-day-today,
.fc .fc-day-today {
    background: #f5f8ff !important;   /* soft blue */
    border: 2px solid #c7d2fe !important;
}

/* Remove yellow highlight when selecting or dragging */
.fc-highlight {
    background: #e0e7ff !important;  /* soft blue highlight */
}

/* Remove yellow background on active buttons */
.fc .fc-button-primary:not(:disabled).fc-button-active,
.fc .fc-button-primary:not(:disabled):active,
.fc .fc-button:focus,
.fc .fc-button-primary:focus {
    background: #e0e7ff !important;
    color: var(--primary) !important;
    box-shadow: none !important;
    outline: none !important;
    border: none !important;
}

/* Remove yellow selection overlay */
.fc .fc-daygrid-bg-harness .fc-daygrid-event-harness,
.fc .fc-bg-event {
    background: transparent !important;
}

/* Remove any yellow anchor/link color */
.fc a,
.fc a:hover,
.fc a:focus,
.fc .fc-scrollgrid-sync-inner a {
    color: var(--primary) !important;
    text-decoration: none !important;
}

/* ====================================== */
/* TASK SELECTION CONTAINER — CANVA STYLE */
/* ====================================== */

#taskSelectionContainer {
    background: #ffffff;
    border-radius: 18px;
    border: 1.5px solid #e4e9f5;
    padding: 22px;
    box-shadow: 0 10px 32px rgba(2, 6, 23, 0.06);
    max-height: 520px;
    overflow-y: auto;
    margin-top: 12px;
}

/* Task card */
.task-card {
    background: #f8faff;
    border: 1.5px solid #e3e8f4;
    border-radius: 14px;
    padding: 16px;
    transition: 0.2s ease;
}

/* Hover effect like Canva */
.task-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(3,61,165,0.08);
    border-color: #cdd8f0;
}

/* Title */
.task-card p.font-medium {
    font-size: 16px;
    font-weight: 700;
    color: #003DA5;
}

/* Descriptions */
.task-card p.text-sm {
    font-size: 13px;
    line-height: 1.4;
}

/* Checkbox */
.task-card input[type="checkbox"] {
    width: 20px;
    height: 20px;
    accent-color: #003DA5;
}

/* Number input for volunteers */
.task-card .task-number input {
    width: 70px !important;
    border-radius: 10px;
    border: 1.5px solid #d2d9e8;
    padding: 6px;
}

.task-card .task-number input:focus {
    border-color: #003DA5;
    box-shadow: 0 0 0 3px rgba(0, 61, 165, 0.12);
    outline: none;
}



.task-step-layout {
  display: flex;
  gap: 20px;
  align-items: flex-start;
}

.task-left {
  flex: 2;
}

.task-right {
  flex: 1;
  background: #f9fafb;
  padding: 15px;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  position: sticky;
  top: 20px;
}

@media (max-width: 768px) {
  .task-step-layout {
    flex-direction: column;
  }
  .task-right {
    position: static;
  }
}

.volunteer-heading {
    font-size: 12px;
    font-weight: 600;
    color: #6b7280; /* muted gray */
    letter-spacing: 0.6px;
}

</style>

<div class="page-wrap">
  <div class="card-wrap">

<!-- Header + Steps (Fully Centered with Lines) -->
<div style="text-align:center; margin-bottom: 25px; display:flex; flex-direction:column; align-items:center;">

    <!-- Title + Subtitle -->
<div>
    <h1 id="formTitle" style="color: var(--primary); font-weight: 700; font-size: 26px; margin-bottom: 5px;">
        Volunteer Request Form
    </h1>
    <p style="color: var(--muted); font-size: 14px; margin: 0;">
        Request volunteers for your activity or event
    </p>
</div>


<div class="steps">
    <div class="step-item">
        <div class="step-content">
            <div id="stepIndicator1" class="step-indicator active">1</div>
            <div class="step-label">Basic Information</div>
        </div>
        <div class="step-line"></div>
    </div>
    <div class="step-item">
        <div class="step-content">
            <div id="stepIndicator2" class="step-indicator">2</div>
            <div class="step-label">Date & Time</div>
        </div>
        <div class="step-line"></div>
    </div>
    <div class="step-item">
        <div class="step-content">
            <div id="stepIndicator3" class="step-indicator">3</div>
            <div class="step-label">Tasking</div>
        </div>
        <div class="step-line"></div>
    </div>

        <div class="step-item">
        <div class="step-content">
            <div id="stepIndicator4" class="step-indicator">4</div>
            <div class="step-label">Summary</div>
        </div>
        <div class="step-line"></div>
    </div>
</div>


</div>


    <div id="stepTitle">Basic Information</div>

    <main>
      <!-- LEFT: main form area (keeps your internal form inputs + calendar) -->
      <div class="left-panel">
        <form action="{{ route('volunteers-admin.store-opportunity') }}" method="POST" id="opportunity-form">
          @csrf

          <!-- STEP 1 -->
          <div class="form-step" id="step-1" >
            <div class="form-section">
              <label>Activity Title <span class="text-danger">*</span></label>
              <input type="text" name="title" placeholder="Enter title proposal" required>
            </div>

            <div class="form-section">
              <label>Description <span class="text-danger">*</span></label>
              <textarea name="description" placeholder="Enter activity description..." required rows="4"></textarea>
            </div>

            <div class="form-section">
              <label>Location<span class="text-danger">*</span></label>
              <div style="display:grid;grid-template-columns:1fr 1fr; gap:12px;">
                <div>
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
                </div>
                <div>
                  <select id="room" required disabled>
                      <option value="" selected disabled>Select Room / Area</option>
                  </select>
                  <input type="text" id="room-other-input" placeholder="Enter custom room/area..." style="display:none; margin-top:8px;">
                </div>
              </div>

              <input type="hidden" name="location" id="location" />
            </div>

<div class="form-section" style="display:flex; justify-content:flex-end; gap:12px;">
    
    <!-- Cancel (go back) -->
    <button
        type="button"
        class="btn btn-danger"
        onclick="window.history.back();"
    >
        Cancel
    </button>

    <!-- Next -->
    <button
        type="button"
        class="btn btn-primary next-step"
        id="next-step-1"
    >
        Next
    </button>

</div>



          </div>

        <div class="form-step" id="step-2" style="display:none;" class="mb-6 space-y-4" style="max-height: 500px; overflow-y: auto; padding-right: 6px;">

            <label>Does your event happen intermittently?<span class="text-danger">*</span></label>
            <div class="radio-group">
                <label><input type="radio" name="intermittent" value="0" checked> No</label>
                <label><input type="radio" name="intermittent" value="1"> Yes, multiple dates</label>
            </div>

            <div id="intermittent-layout" style="display:none;" class="two-column">
                <div class="left-side">
                    <div id="intermittent-details">
                        <div id="fullcalendar"></div>
                        <div id="time-details" style="margin-top:10px;">
                            <label>Start Time<span class="text-danger">*</span></label>
                            <input type="time" id="calendar-start-time">

                            <label>End Time<span class="text-danger">*</span></label>
                            <input type="time" id="calendar-end-time">

                  
                            <label>Food Provided<span class="text-danger">*</span></label>
                            <div class="radio-group" style="margin-top: 5px; margin-bottom: 10px;">
                                <label><input type="radio" name="food_provided" value="yes"> Yes</label>
                                <label><input type="radio" name="food_provided" value="no"> No</label>
                            </div>

                            <button type="button" id="add-calendar-schedule" class="btn-secondary">Add Schedule</button>
                        </div>
                    </div>
                </div>

                <div class="right-side">
                    <div id="scheduled-entries"></div>
                </div>
            </div>

<div id="regular-schedule">
<div class="form-section" style="margin-top: 16px;">
    <label>Date <span class="text-danger">*</span></label>
    <input type="date" name="start_date">
</div>


    <div class="form-section" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; align-items: center;">
        <div>
            <label>Start Time <span class="text-danger">*</span></label>
            <input type="time" name="schedule[0][start_time]">
            
        </div>
        <div>
            <label>End Time <span class="text-danger">*</span></label>
            <input type="time" name="schedule[0][end_time]">
        </div>
    </div>

    <div class="form-section">
        <label>Food Provided <span class="text-danger">*</span></label>
        <div class="radio-group" style="margin-top:5px;">
            <label><input type="radio" name="food_provided" value="yes"> Yes</label>
            <label><input type="radio" name="food_provided" value="no"> No</label>
        </div>
    </div>
</div>

    <input type="hidden" id="regular-date-hidden" name="schedule[0][date]">

<div class="form-navigation-between">
    <button type="button" class="btn-outline prev-step">Back</button>
    <button type="button" class="btn-primary next-step">Next</button>
</div>


        </div>




        <!-- STEP 3 (Tasks) -->
<div class="form-step" id="step-3" style="display:none;">

  <p class="mb-6 text-gray-600">
    Select the tasks you need volunteers for and specify how many for each task.
  </p>

  <!-- TWO COLUMN LAYOUT -->
  <div class="task-step-layout">

    <!-- ================= LEFT COLUMN ================= -->
    <div class="task-left">

      <!-- DATE SELECTOR -->
      <div class="mb-4 form-section">
        <label>
          Select Date for Tasks <span class="text-danger">*</span>
        </label>
        <select id="task-date-selector"
                class="form-control"
                style="padding:10px; border-radius:10px;">
          <option value="" disabled selected>Loading dates...</option>
        </select>

        <div id="task-save-row" style="margin-top:10px;">
          <button type="button" id="save-date-tasks" class="btn btn-primary">
            Save Tasks for This Date
          </button>
        </div>
      </div>


      
      <!-- TASK CARDS CONTAINER -->
      <div id="taskSelectionContainer"
           class="mb-6 space-y-4"
           style="max-height:500px; overflow-y:auto; padding-right:6px;">

   <!-- Marshalling -->
  <div class="p-4 border rounded-lg task-card">
    <div class="flex items-start gap-3">
      <input type="checkbox" data-task-name="Marshalling" name="tasks[Marshalling][active]" class="form-checkbox task-toggle">
      <div>
        <p class="font-medium">Marshalling</p>
        <p class="text-sm text-gray-500"><strong>Skills:</strong> Crowd management, clear communication, alert, patient</p>
        <p class="text-sm text-gray-600">
          Helps manage crowd flow, guide participants, and keep pathways clear; recommended for confident and alert volunteers.
        </p>
      </div>
    </div>

    <div class="hidden mt-4 task-number">
      <label class="block mb-1 text-sm font-semibold text-gray-700">Number of Volunteers</label>
      <input type="number" name="tasks[Marshalling][needed]" min="1" placeholder="0" class="w-24 p-2 border rounded-lg">
      
    </div>
  </div>

  <!-- Registration -->
  <div class="p-4 border rounded-lg task-card">
    <div class="flex items-start gap-3">
      <input type="checkbox" data-task-name="Registration" name="tasks[Registration][active]" class="form-checkbox task-toggle">
      <div>
        <p class="font-medium">Registration</p>
        <p class="text-sm text-gray-500"><strong>Skills:</strong>Organized, detail-oriented, friendly</p>
        <p class="text-sm text-gray-600">
          Handles participant check-in and attendance; ideal for organized, patient volunteers.
        </p>
      </div>
    </div>

    <div class="hidden mt-4 task-number">
      <label class="block mb-1 text-sm font-semibold text-gray-700">Number of Volunteers</label>
      <input type="number" name="tasks[Registration][needed]" min="1" placeholder="0" class="w-24 p-2 border rounded-lg">
    </div>
  </div>

  <!-- Ushering -->
  <div class="p-4 border rounded-lg task-card">
    <div class="flex items-start gap-3">
      <input type="checkbox" data-task-name="Ushering" name="tasks[Ushering][active]" class="form-checkbox task-toggle">
      <div>
        <p class="font-medium">Ushering</p>
        <p class="text-sm text-gray-500"><strong>Skills:</strong>Approachable, good communication, presentable</p>
        <p class="text-sm text-gray-600">
          Guides attendees to seats and assists with inquiries; perfect for friendly, patient volunteers.
        </p>
      </div>
    </div>

    <div class="hidden mt-4 task-number">
      <label class="block mb-1 text-sm font-semibold text-gray-700">Number of Volunteers</label>
      <input type="number" name="tasks[Ushering][needed]" min="1" placeholder="0" class="w-24 p-2 border rounded-lg">
    </div>
  </div>

  <!-- Photography -->
  <div class="p-4 border rounded-lg task-card">
    <div class="flex items-start gap-3">
      <input type="checkbox" data-task-name="Photography" name="tasks[Photography][active]" class="form-checkbox task-toggle">
      <div>
        <p class="font-medium">Photography</p>
        <p class="text-sm text-gray-500"><strong>Skills:</strong>Basic photography skills</p>
        <p class="text-sm text-gray-600">
          Captures event moments; ideal for those familiar with camera/phone photography.
        </p>
      </div>
    </div>

    <div class="hidden mt-4 task-number">
      <label class="block mb-1 text-sm font-semibold text-gray-700">Number of Volunteers</label>
      <input type="number" name="tasks[Photography][needed]" min="1" placeholder="0" class="w-24 p-2 border rounded-lg">
    </div>
  </div>

  <!-- Videography -->
  <div class="p-4 border rounded-lg task-card">
    <div class="flex items-start gap-3">
      <input type="checkbox" data-task-name="Videography" name="tasks[Videography][active]" class="form-checkbox task-toggle">
      <div>
        <p class="font-medium">Videography</p>
        <p class="text-sm text-gray-500"><strong>Skills:</strong>Basic video recording skills</p>
        <p class="text-sm text-gray-600">
          Records event highlights; great for those with steady hands and timing awareness.
        </p>
      </div>
    </div>

    <div class="hidden mt-4 task-number">
      <label class="block mb-1 text-sm font-semibold text-gray-700">Number of Volunteers</label>
      <input type="number" name="tasks[Videography][needed]" min="1" placeholder="0" class="w-24 p-2 border rounded-lg">
    </div>
  </div>

  <!-- Technical Support -->
  <div class="p-4 border rounded-lg task-card">
    <div class="flex items-start gap-3">
      <input type="checkbox" data-task-name="Technical Support" name="tasks[Technical Support][active]" class="form-checkbox task-toggle">
      <div>
        <p class="font-medium">Technical Support</p>
        <p class="text-sm text-gray-500"><strong>Skills:</strong>Basic IT skills, focused</p>
        <p class="text-sm text-gray-600">
          Assists with microphones, speakers, projectors; ideal for tech-savvy volunteers.
        </p>
      </div>
    </div>

    <div class="hidden mt-4 task-number">
      <label class="block mb-1 text-sm font-semibold text-gray-700">Number of Volunteers</label>
      <input type="number" name="tasks[Technical Support][needed]" min="1" placeholder="0" class="w-24 p-2 border rounded-lg">
    </div>
  </div>

  <!-- Creatives / Design -->
  <div class="p-4 border rounded-lg task-card">
    <div class="flex items-start gap-3">
      <input type="checkbox" data-task-name="Creatives/Design" name="tasks[Creatives/Design][active]" class="form-checkbox task-toggle">
      <div>
        <p class="font-medium">Creatives / Design</p>
        <p class="text-sm text-gray-500"><strong>Skills:</strong>Basic design sense</p>
        <p class="text-sm text-gray-600">
          Helps create layouts, tarpaulins, and visual materials; ideal for creative volunteers.
        </p>
      </div>
    </div>

    <div class="hidden mt-4 task-number">
      <label class="block mb-1 text-sm font-semibold text-gray-700">Number of Volunteers</label>
      <input type="number" name="tasks[Creatives/Design][needed]" min="1" placeholder="0" class="w-24 p-2 border rounded-lg">
    </div>
  </div>

  <!-- Hosting -->
  <div class="p-4 border rounded-lg task-card">
    <div class="flex items-start gap-3">
      <input type="checkbox" data-task-name="Hosting" name="tasks[Hosting][active]" class="form-checkbox task-toggle">
      <div>
        <p class="font-medium">Hosting</p>
        <p class="text-sm text-gray-500"><strong>Skills:</strong>Confident, articulate, energetic</p>
        <p class="text-sm text-gray-600">
          Hosts the program and keeps the flow smooth; suited for confident speakers.
        </p>
      </div>
    </div>

    <div class="hidden mt-4 task-number">
      <label class="block mb-1 text-sm font-semibold text-gray-700">Number of Volunteers</label>
      <input type="number" name="tasks[Hosting][needed]" min="1" placeholder="0" class="w-24 p-2 border rounded-lg">
    </div>
  </div>

  <!-- Logistics -->
  <div class="p-4 border rounded-lg task-card">
    <div class="flex items-start gap-3">
      <input type="checkbox" data-task-name="Logistics" name="tasks[Logistics][active]" class="form-checkbox task-toggle">
      <div>
        <p class="font-medium">Logistics</p>
        <p class="text-sm text-gray-500"><strong>Skills:</strong>Physically capable, organized</p>
        <p class="text-sm text-gray-600">
          Moves equipment/materials; ideal for physically able volunteers.
        </p>
      </div>
    </div>

    <div class="hidden mt-4 task-number">
      <label class="block mb-1 text-sm font-semibold text-gray-700">Number of Volunteers</label>
      <input type="number" name="tasks[Logistics][needed]" min="1" placeholder="0" class="w-24 p-2 border rounded-lg">
    </div>
  </div>

  <!-- Admin Support -->
  <div class="p-4 border rounded-lg task-card">
    <div class="flex items-start gap-3">
      <input type="checkbox" data-task-name="Admin Support" name="tasks[Admin Support][active]" class="form-checkbox task-toggle">
      <div>
        <p class="font-medium">Admin Support</p>
        <p class="text-sm text-gray-500"><strong>Skills:</strong>Detail-oriented, encoding</p>
        <p class="text-sm text-gray-600">
          Prepares documents, certificates, and encodes data.
        </p>
      </div>
    </div>

    <div class="hidden mt-4 task-number">
      <label class="block mb-1 text-sm font-semibold text-gray-700">Number of Volunteers</label>
      <input type="number" name="tasks[Admin Support][needed]" min="1" placeholder="0" class="w-24 p-2 border rounded-lg">
    </div>
  </div>

  <!-- Others -->
  <div class="p-4 border rounded-lg task-card">
    <div class="flex items-start gap-3">
      <input type="checkbox" data-task-name="Others" name="tasks[Others][active]" class="form-checkbox task-toggle">
      <div>
        <p class="font-medium">Others</p>
        <p class="text-sm text-gray-500">Optional</p>
        <p class="text-sm text-gray-600">
          For tasks not listed above.
        </p>
      </div>
    </div>

    <div class="hidden mt-4 task-number">
      <label class="block mb-1 text-sm font-semibold text-gray-700">Number of Volunteers</label>
      <input type="number" name="tasks[Others][needed]" min="1" placeholder="0" class="w-24 p-2 border rounded-lg">
    </div>
  </div>

</div>

<!-- Multiple dates task assignment -->
<div id="taskByDateSection" class="hidden mt-6">
  <div class="p-4 mb-4 border-2 border-blue-200 rounded-lg bg-blue-50">
    <p class="mb-2 font-medium text-blue-800">📅 Task Assignment by Date</p>
    <p class="text-sm text-blue-700">Specify which tasks are needed for each selected date.</p>
  </div>
  <div id="taskByDateContainer" class="space-y-4"></div>
</div>


      <div id="intermittentSummaryInputs"></div>

    </div>

    <!-- ================= RIGHT COLUMN ================= -->
    <div class="task-right">

      <h6 style="color:#003DA5; font-weight:700; margin-bottom:10px;">
        Saved Task Assignments
      </h6>

      <table id="task-summary-table"
             class="table table-bordered"
             style="background:white; border-radius:10px; overflow:hidden; display:none;">
        <thead style="background:#003DA5; color:white;">
          <tr>
            <th>Date</th>
            <th>Task</th>
            <th>Needed Volunteers</th>
          </tr>
        </thead>
        <tbody id="task-summary-body"></tbody>
      </table>

    </div>

  </div>

<div class="form-navigation-between">
    <button type="button" class="btn-outline prev-step">Back</button>
    <button type="button" class="btn-primary next-step">Next</button>
</div>

</div>



<!-- ================= STEP 4 : SUMMARY ================= -->
<div class="form-step" id="step-4" style="display:none;">

  <h5 style="color:#003DA5; font-weight:700; margin-bottom:14px;">Review Your Submission</h5>

  <div class="summary-grid">

    <div class="summary-card">
      <h6>Title</h6>
      <p id="summary-title"></p>
    </div>

    <div class="summary-card">
      <h6>Location</h6>
      <p id="summary-location"></p>
    </div>

    <div class="summary-card" style="grid-column:1/-1;">
      <h6>Description</h6>
      <p id="summary-description"></p>
    </div>

    <div class="summary-card" style="grid-column:1/-1;">
      <h6>Task Schedule</h6>
<div id="summary-task-assignments"></div>
    </div>

  </div>

  <!-- ✅ NAVIGATION MOVED HERE -->
<div class="form-navigation-between" style="display:flex; justify-content:space-between; margin-top:20px;">
  <button type="button" class="btn-outline prev-step">Back</button>
  <button type="submit" class="btn-primary">Submit Opportunity</button>
</div>


</div>




        </form>
      </div>

    
    </main>

  </div>
</div>
 

<!-- GLOBAL MODAL -->
<div class="modal fade" id="globalModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      
      <!-- Header without close button -->
      <div class="modal-header">
        <h5 class="modal-title" id="globalModalTitle" style="font-weight: bold; color: var(--primary, #003DA5);">
          Notice
        </h5>
      </div>
      
      <div class="modal-body" id="globalModalBody">
        ...
      </div>
      
      <!-- Footer with centered OK button -->
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-primary" id="modalConfirmBtn" data-bs-dismiss="modal">
          OK
        </button>
      </div>
      
    </div>
  </div>
</div>




<script>


let tasksByDate = {};

document.addEventListener("DOMContentLoaded", function() {
  // ---------- form steps
  const steps = Array.from(document.querySelectorAll(".form-step"));
  function validateStep(stepIndex) {

  // STEP 1 — Activity Details
  if (stepIndex === 0) {
    const title = document.querySelector('input[name="title"]')?.value.trim();
    const desc  = document.querySelector('textarea[name="description"]')?.value.trim();
    const loc   = document.getElementById('location')?.value.trim();

    if (!title || !desc || !loc) {
      showAlertModal("Please complete Title, Description, and Location.");
      return false;
    }
  }


  document.querySelectorAll("input[name='intermittent']").forEach(radio => {
  radio.addEventListener("change", () => {
    const isIntermittent = radio.value === "1";

    // Toggle visibility
    document.getElementById("regularSchedule").style.display =
      isIntermittent ? "none" : "block";

    document.getElementById("intermittentSchedule").style.display =
      isIntermittent ? "block" : "none";

    /* =========================
       🔴 DISABLE UNUSED INPUTS
       ========================= */

    // Regular inputs
    document
      .querySelectorAll("#regularSchedule input, #regularSchedule select")
      .forEach(el => {
        el.disabled = isIntermittent;
      });

    // Intermittent inputs
    document
      .querySelectorAll("#intermittentSchedule input, #intermittentSchedule select")
      .forEach(el => {
        el.disabled = !isIntermittent;
      });
  });
});


// STEP 2 — Schedule
// STEP 2 — Schedule
if (stepIndex === 1) {

  const selectedSchedule = document.querySelector(
    "input[name='intermittent']:checked"
  );

  // ❌ No schedule type selected
  if (!selectedSchedule) {
    showAlertModal("Please select a schedule type.");
    return false;
  }

  /* =========================
     INTERMITTENT (value = "1")
     ========================= */
  if (selectedSchedule.value === "1") {

    const table = document.getElementById("scheduled-table");
    const rows = table
      ? table.querySelectorAll("tbody tr")
      : [];

    // ❌ Must have at least TWO dates
    if (rows.length < 2) {
      showAlertModal("Please add at least 2 schedule date.");
      return false;
    }

    // ✅ Intermittent valid
    return true;
  }

  /* =========================
     REGULAR (value = "0")
     ========================= */
  if (selectedSchedule.value === "0") {

    const date      = document.querySelector('input[name="start_date"]')?.value;
    const startTime = document.querySelector('input[name="schedule[0][start_time]"]')?.value;
    const endTime   = document.querySelector('input[name="schedule[0][end_time]"]')?.value;

    if (!date || !startTime || !endTime) {
      showAlertModal("Please complete the schedule details.");
      return false;
    }

    return true;
  }
}





  // STEP 3 — Tasking
  if (stepIndex === 2) {
    if (!tasksByDate || Object.keys(tasksByDate).length === 0) {
      showAlertModal("Please assign at least one task before continuing.");
      return false;
    }

    let hasAtLeastOneTask = false;
    Object.values(tasksByDate).forEach(tasks => {
      if (Object.keys(tasks).length > 0) hasAtLeastOneTask = true;
    });

    if (!hasAtLeastOneTask) {
      showAlertModal("Please assign at least one task with volunteers.");
      return false;
    }
  }

  return true; // ✅ allow navigation
}



  let currentStep = 0;

function showStep(i) {
    steps.forEach((s, idx) => s.style.display = idx === i ? "block" : "none");
    currentStep = i;

    if (i === 2) {
        setTimeout(refreshTaskDateDropdown, 150); // <-- IMPORTANT
    }

    document.getElementById('stepTitle').textContent = ['Basic Information','Date & Time','Tasking','Summary'][i] || '';
    updateIndicators(i+1);
    if (currentStep === 3) generateSummary();
}


function updateIndicators(stepNumber) {
    for (let i = 1; i <= 4; i++) {
        const el = document.getElementById('stepIndicator' + i);
        if (!el) continue;
        el.classList.remove('active', 'completed');

        if (i < stepNumber) {
            el.classList.add('completed'); // previous steps
        } else if (i === stepNumber) {
            el.classList.add('active');    // current step
        }
        // future steps keep default style
    }
}

  
document.querySelectorAll(".next-step").forEach(btn => {
  btn.addEventListener("click", () => {
    if (!validateStep(currentStep)) return;
    if (currentStep < steps.length - 1) {
      showStep(currentStep + 1);
    }
  });
});

document.querySelectorAll(".prev-step").forEach(btn => {
  btn.addEventListener("click", () => {
    if (currentStep > 0) {
      showStep(currentStep - 1);
    }
  });
});



document.getElementById("opportunity-form").addEventListener("submit", function(e) {
  if (!validateStep(0) || !validateStep(1) || !validateStep(2)) {
    e.preventDefault();
    return;
  }
});


  showStep(0);

  // ---------- Location room logic (unchanged)
  const rooms = {
    "Level 1":[ "Sky Garden","LRC","Function Hall","Accounting Office","Registrar's Office","Level 1" ],
    "Level 2":[ "Room 204 (SDAO)","Room 210 (GCO)","Room 211 (Community Extension and NSTP Office)","Room 224-225" ],
    "Level 3":[ "Room 304 (Faculty Room)","Room 320 (Chapel)" ],
    "Level 4":[ "Room 411 (ITSO)","AVR" ],
    "Level 5":[ "Function Hall" ]
  };
  const levelSelect = document.getElementById("level");
  const roomSelect = document.getElementById("room");
  const locationInput = document.getElementById("location");
  const levelOtherInput = document.getElementById("level-other-input");
  const roomOtherInput = document.getElementById("room-other-input");

  levelSelect.addEventListener("change", function () {
    const selectedLevel = this.value;
    roomSelect.innerHTML = `<option value="" disabled selected>Select Room / Area</option>`;
    roomSelect.disabled = false;
    levelOtherInput.style.display = "none";
    roomOtherInput.style.display = "none";
    if (selectedLevel === "Others") {
      levelOtherInput.style.display = "block";
      roomSelect.innerHTML = `<option value="" disabled selected>Select Room / Area</option><option value="Others">Others</option>`;
      roomSelect.disabled = false;
      locationInput.value = "";
      return;
    }
    (rooms[selectedLevel] || []).forEach(r=>{
      const opt = document.createElement("option"); opt.value = r; opt.textContent = r; roomSelect.appendChild(opt);
    });
    const opt = document.createElement("option"); opt.value="Others"; opt.textContent="Others"; roomSelect.appendChild(opt);
    locationInput.value = "";
  });

  roomSelect.addEventListener("change", function () {
    const selectedRoom = this.value;
    roomOtherInput.style.display = "none";
    if (selectedRoom === "Others") { roomOtherInput.style.display = "block"; locationInput.value = ""; return; }
    if (levelSelect.value === "Others") { locationInput.value = (levelOtherInput.value.trim() || '') + " - " + selectedRoom; return; }
    locationInput.value = levelSelect.value + " - " + selectedRoom;
  });

  levelOtherInput.addEventListener("input", function(){ if (roomSelect.value && roomSelect.value !== "Others") locationInput.value = this.value.trim() + " - " + roomSelect.value; });
  roomOtherInput.addEventListener("input", function(){ const lvl = (levelSelect.value === "Others") ? levelOtherInput.value.trim() : levelSelect.value; locationInput.value = lvl + " - " + this.value.trim(); });

  // quick validation next from step1
  document.getElementById('next-step-1').addEventListener('click', () => {
    const title = document.querySelector('input[name="title"]').value.trim();
    const desc  = document.querySelector('textarea[name="description"]').value.trim();
    const loc   = document.getElementById('location').value.trim();
    if (!title || !desc || !loc) { showAlertModal('Please fill title, description and location before continuing.'); return; }
    showStep(1);
  });

  // ---------- FullCalendar & schedule management ----------
  const calendarEl = document.getElementById('fullcalendar');
  const todayStr = new Date().toISOString().split('T')[0];
  let selectedDates = new Set();
  let scheduleIndex = 0;
  let indexMap = {};
  const form = document.getElementById('opportunity-form');
  const scheduledEntries = document.getElementById('scheduled-entries');

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    selectable: true,
    validRange: { start: todayStr },
    headerToolbar: { left: 'prev', center: 'title', right: 'next today' },
    dateClick: function(info) {
      const dateStr = info.dateStr;
      if (dateStr < todayStr) return;
      if (selectedDates.has(dateStr)) { selectedDates.delete(dateStr); info.dayEl.classList.remove('selected'); }
      else { selectedDates.add(dateStr); info.dayEl.classList.add('selected'); }
    },
    dayCellDidMount: function(info) {
      const dateStr = info.date.toISOString().split('T')[0];
      if (dateStr < todayStr) { info.el.classList.add('fc-day-disabled'); info.el.style.backgroundColor = '#f5f5f5'; info.el.style.cursor = 'not-allowed'; return; }
      if (selectedDates.has(dateStr)) info.el.classList.add('selected'); else info.el.classList.remove('selected');
    }
  });
  calendar.render();

  function formatTimeForDisplay(timeStr) {
    if (!timeStr) return '';
    const [hour, minute] = timeStr.split(':');
    const d = new Date(); d.setHours(parseInt(hour,10)); d.setMinutes(parseInt(minute,10));
    return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
  }

  function createHiddenInput(name, value) {
    const input = document.createElement('input'); input.type = 'hidden'; input.name = name; input.value = value; input.classList.add('scheduled-entry-input'); return input;
  }

  function ensureScheduledTable(){
    let table = scheduledEntries.querySelector('table');
    if (!table) {
      table = document.createElement('table'); table.id = 'scheduled-table'; table.style.width = '100%';
      table.innerHTML = `<thead><tr><th>Date</th><th>Start</th><th>End</th><th>Food</th><th style="width:120px">Actions</th></tr></thead><tbody></tbody>`;
      scheduledEntries.appendChild(table);
    }
    return table;
  }

  document.getElementById('add-calendar-schedule').addEventListener('click', function(){
    if (selectedDates.size === 0) { showAlertModal('Please select at least one date.'); return; }
    const startTime = document.getElementById('calendar-start-time').value;
    const endTime = document.getElementById('calendar-end-time').value;
    const targetNumber = document.getElementById('calendar-target-number').value || 1;
    const foodProvided = document.querySelector('input[name="food_provided_interm"]:checked')?.value || 'no';
    if (!startTime || !endTime) { showAlertModal('Please fill start and end times.'); return; }

    const table = ensureScheduledTable();
    const tbody = table.querySelector('tbody');

    selectedDates.forEach(dateStr => {
      const existingDateInputs = Array.from(document.querySelectorAll('.scheduled-entry-input[name$="[date]"]') || []);
      const exists = existingDateInputs.some(i => i.value === dateStr);
      if (exists) return;

      const row = document.createElement('tr');
      row.dataset.rowId = scheduleIndex;
      indexMap[scheduleIndex] = scheduleIndex;

      const formattedDate = new Date(dateStr).toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' });

row.innerHTML = `
  <td class="col-date">${formattedDate}</td>
  <td class="col-start">${formatTimeForDisplay(startTime)}</td>
  <td class="col-end">${formatTimeForDisplay(endTime)}</td>
  <td class="col-food">${foodProvided.charAt(0).toUpperCase() + foodProvided.slice(1)}</td>
  <td class="col-actions">
    <div class="dropdown">
      <button class="btn-secondary dropdown-toggle" type="button" data-index="${scheduleIndex}">Actions</button>
      <ul class="dropdown-menu" style="min-width:120px; padding:0; margin:0; list-style:none; border:1px solid #e6eef9; border-radius:8px; overflow:hidden; position:absolute; display:none; background:#fff; z-index:100;">
        <li><button type="button" class="dropdown-edit" data-index="${scheduleIndex}" style="width:100%; padding:6px 12px; border:none; background:none; text-align:left;">Edit</button></li>
        <li><button type="button" class="dropdown-delete" data-index="${scheduleIndex}" style="width:100%; padding:6px 12px; border:none; background:none; text-align:left; color:#b71c1c;">Delete</button></li>
      </ul>
    </div>
  </td>
`;


      tbody.appendChild(row);

      // hidden inputs for server
      form.appendChild(createHiddenInput(`schedule[${scheduleIndex}][date]`, dateStr));
      form.appendChild(createHiddenInput(`schedule[${scheduleIndex}][start_time]`, startTime));
      form.appendChild(createHiddenInput(`schedule[${scheduleIndex}][end_time]`, endTime));
      form.appendChild(createHiddenInput(`schedule[${scheduleIndex}][food_provided]`, foodProvided));

      scheduleIndex++;
    });

    // cleanup selection UI
    document.querySelectorAll('.fc-daygrid-day.selected').forEach(el=>el.classList.remove('selected'));
    selectedDates.clear();
    document.getElementById('calendar-start-time').value=''; document.getElementById('calendar-end-time').value=''; document.getElementById('calendar-target-number').value='';
    reindexScheduledTable();
  });

  function reindexScheduledTable(){
    const rows = document.querySelectorAll('#scheduled-table tbody tr');
    rows.forEach((r, idx) => {
      const colIndex = r.querySelector('.col-index');
      if (colIndex) colIndex.textContent = idx+1;
    });
  }

 // ---------- Edit / Save / Cancel logic including dropdowns ----------
// ---------- Edit / Save / Cancel logic including dropdowns ----------
document.addEventListener('click', function(e){

  // --- TOGGLE dropdown
  if (e.target.classList.contains('dropdown-toggle')) {
    const ul = e.target.nextElementSibling;
    const allDropdowns = document.querySelectorAll('.dropdown-menu');
    allDropdowns.forEach(d => { if(d !== ul) d.style.display='none'; }); // close others
    ul.style.display = ul.style.display === 'block' ? 'none' : 'block';
  } else {
    // close all dropdowns if clicked outside
    document.querySelectorAll('.dropdown-menu').forEach(d => d.style.display='none');
  }

  // --- DELETE row (inline or dropdown)
if (
  e.target.classList.contains('delete-btn') ||
  e.target.classList.contains('dropdown-delete')
) {
  const idx = e.target.dataset.index;
  const row = e.target.closest('tr');

  showConfirmModal(
    "Remove this scheduled entry?",
    () => {
      if (row) row.remove();
      removeHiddenInputsByIndex(idx);
      reindexScheduledTable();
    },
    "Delete Schedule"
  );
}
  // --- EDIT row (inline or dropdown)
  if (e.target.classList.contains('edit-btn') || e.target.classList.contains('dropdown-edit')) {
    const idx = e.target.dataset.index;
    const row = e.target.closest('tr');
    if (!row) return;

    editRow(row, idx);
  }

  // --- SAVE edited row
  if (e.target.classList.contains('save-edit')) {
    const idx = e.target.dataset.index;
    const row = e.target.closest('tr');

    const newDate   = document.getElementById(`edit-date-${idx}`).value;
    const newStart  = document.getElementById(`edit-start-${idx}`).value;
    const newEnd    = document.getElementById(`edit-end-${idx}`).value;
    const newFood   = document.getElementById(`edit-food-${idx}`).value;

    if (!newDate || !newStart || !newEnd ) {
      showAlertModal('Please complete all fields before saving.');
      return;
    }

    // Update hidden inputs
    updateOrCreateHiddenInput(`schedule[${idx}][date]`, newDate);
    updateOrCreateHiddenInput(`schedule[${idx}][start_time]`, newStart);
    updateOrCreateHiddenInput(`schedule[${idx}][end_time]`, newEnd);
    updateOrCreateHiddenInput(`schedule[${idx}][food_provided]`, newFood);

renderRowDisplay(row, idx, newDate, newStart, newEnd, newFood);
    reindexScheduledTable();
  }

  // --- CANCEL edit
if (e.target.classList.contains('cancel-edit')) {
    const idx = e.target.dataset.index;
    const row = e.target.closest('tr');

    const dateVal  = document.querySelector(`input[name="schedule[${idx}][date]"]`)?.value || '';
    const startVal = document.querySelector(`input[name="schedule[${idx}][start_time]"]`)?.value || '';
    const endVal   = document.querySelector(`input[name="schedule[${idx}][end_time]"]`)?.value || '';
    const foodVal  = document.querySelector(`input[name="schedule[${idx}][food_provided]"]`)?.value || 'no';

    renderRowDisplay(row, idx, dateVal, startVal, endVal, foodVal); // target = 1 for intermittent
}


});

// ---------- Helper functions ----------

// Reusable function to populate a row for editing
function editRow(row, idx) {
  const dateVal   = document.querySelector(`input[name="schedule[${idx}][date]"]`)?.value || '';
  const startVal  = document.querySelector(`input[name="schedule[${idx}][start_time]"]`)?.value || '';
  const endVal    = document.querySelector(`input[name="schedule[${idx}][end_time]"]`)?.value || '';
  const foodVal   = document.querySelector(`input[name="schedule[${idx}][food_provided]"]`)?.value || 'no';

  const displayIdx = row.querySelector('.col-index')?.textContent || '';
  row.innerHTML = `
    <td><input type="date" id="edit-date-${idx}" value="${dateVal}" style="width:150px"></td>
    <td><input type="time" id="edit-start-${idx}" value="${startVal}" style="width:110px"></td>
    <td><input type="time" id="edit-end-${idx}" value="${endVal}" style="width:110px"></td>
    <td>
      <select id="edit-food-${idx}">
        <option value="yes" ${foodVal==='yes' ? 'selected' : ''}>Yes</option>
        <option value="no" ${foodVal==='no' ? 'selected' : ''}>No</option>
      </select>
    </td>
    <td>
      <button type="button" class="save-edit" data-index="${idx}" style="padding:6px 10px;background:#16a34a;color:#fff;border:none;border-radius:8px;margin-right:6px">Save</button>
      <button type="button" class="cancel-edit" data-index="${idx}" style="padding:6px 10px;background:#b71c1c;color:#fff;border:none;border-radius:8px">Cancel</button>
    </td>
  `;
}

// Reusable function to render a row back to display mode
function renderRowDisplay(row, idx, date, start, end, target, food) {
  const displayIdx = row.querySelector('.col-index')?.textContent || '';
  row.innerHTML = `
    <td class="col-date">${new Date(date).toLocaleDateString()}</td>
    <td class="col-start">${formatTimeForDisplay(start)}</td>
    <td class="col-end">${formatTimeForDisplay(end)}</td>
    <td class="col-food">${food.charAt(0).toUpperCase() + food.slice(1)}</td>
<div class="dropdown">
      <button class="btn-secondary dropdown-toggle" type="button" data-index="${idx}">Actions</button>
      <ul class="dropdown-menu" style="min-width:120px; padding:0; margin:0; list-style:none; border:1px solid #e6eef9; border-radius:8px; overflow:hidden; position:absolute; display:none; background:#fff; z-index:100;">
        <li><button type="button" class="dropdown-edit" data-index="${idx}" style="width:100%; padding:6px 12px; border:none; background:none; text-align:left;">Edit</button></li>
        <li><button type="button" class="dropdown-delete" data-index="${idx}" style="width:100%; padding:6px 12px; border:none; background:none; text-align:left; color:#b71c1c;">Delete</button></li>
      </ul>
    </div>
  `;
}



  function updateOrCreateHiddenInput(name, value) {
    const existing = document.querySelector(`input[name="${name}"]`);
    if (existing) { existing.value = value; } else { form.appendChild(createHiddenInput(name, value)); }
  }
  function removeHiddenInputsByIndex(idx) {
const names = [
    `schedule[${idx}][date]`,
    `schedule[${idx}][start_time]`,
    `schedule[${idx}][end_time]`,
    `schedule[${idx}][target_number]`,
    `schedule[${idx}][food_provided]`
];
    names.forEach(n => { const el = document.querySelector(`input[name="${n}"]`); if (el) el.remove();});
  }

  // intermittent toggle logic
  // Intermittent toggle logic
document.querySelectorAll("input[name='intermittent']").forEach(radio=>{
  radio.addEventListener('change', ()=>{
    const intermittentLayout = document.getElementById("intermittent-layout");
    const regularSchedule = document.getElementById("regular-schedule");
    if (radio.value === "1") {
      intermittentLayout.style.display = "flex"; 
      regularSchedule.style.display = "none";
      setTimeout(()=>{ calendar.render(); calendar.today(); }, 200);
    } else {
      intermittentLayout.style.display = "none"; 
      regularSchedule.style.display = "block";

      // Clear intermittent schedules
      document.querySelectorAll(".scheduled-entry-input").forEach(i=>i.remove());
      document.getElementById('scheduled-entries').innerHTML = ""; 
      scheduleIndex = 0; 
      selectedDates.clear(); 
      indexMap = {};

      // CLEAR intermittent tasks
      tasksByDate = {};

      // Refresh task summary table for REGULAR schedule
      refreshTaskSummaryTable();
    }
  });
});


// Fix adding intermittent schedule
document.getElementById('add-calendar-schedule').addEventListener('click', function(){
  if (selectedDates.size === 0) { showAlertModal('Please select at least one date.'); return; }
  const startTime = document.getElementById('calendar-start-time').value;
  const endTime = document.getElementById('calendar-end-time').value;
  const foodProvided = document.querySelector('input[name="food_provided"]:checked')?.value || 'no';
  if (!startTime || !endTime) { showAlertModal('Please fill start and end times.'); return; }

  const table = ensureScheduledTable();
  const tbody = table.querySelector('tbody');

  selectedDates.forEach(dateStr => {
    const exists = Array.from(document.querySelectorAll('.scheduled-entry-input[name$="[date]"]'))
                        .some(i => i.value === dateStr);
    if (exists) return;

    const row = document.createElement('tr');
    row.dataset.rowId = scheduleIndex;
    indexMap[scheduleIndex] = scheduleIndex;

    const formattedDate = new Date(dateStr).toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' });

    row.innerHTML = `
      <td class="col-date">${formattedDate}</td>
      <td class="col-start">${formatTimeForDisplay(startTime)}</td>
      <td class="col-end">${formatTimeForDisplay(endTime)}</td>
      <td class="col-food">${foodProvided.charAt(0).toUpperCase() + foodProvided.slice(1)}</td>
    <td class="col-actions">
  <div class="dropdown">
    <button class="btn-secondary dropdown-toggle" type="button" data-index="${scheduleIndex}">
      Actions
    </button>
    <ul class="dropdown-menu" style="min-width:120px; padding:0; margin:0; list-style:none; border:1px solid #e6eef9; border-radius:8px; overflow:hidden; position:absolute; display:none; background:#fff; z-index:100;">
      <li><button type="button" class="dropdown-edit" data-index="${scheduleIndex}" style="width:100%; padding:6px 12px; border:none; background:none; text-align:left;">Edit</button></li>
      <li><button type="button" class="dropdown-delete" data-index="${scheduleIndex}" style="width:100%; padding:6px 12px; border:none; background:none; text-align:left; color:#b71c1c;">Delete</button></li>
    </ul>
  </div>
</td>

    `;

    tbody.appendChild(row);

    // hidden inputs for server
    form.appendChild(createHiddenInput(`schedule[${scheduleIndex}][date]`, dateStr));
    form.appendChild(createHiddenInput(`schedule[${scheduleIndex}][start_time]`, startTime));
    form.appendChild(createHiddenInput(`schedule[${scheduleIndex}][end_time]`, endTime));
    form.appendChild(createHiddenInput(`schedule[${scheduleIndex}][target_number]`, 1));
    form.appendChild(createHiddenInput(`schedule[${scheduleIndex}][food_provided]`, foodProvided));

    scheduleIndex++;
  });

  // cleanup selection UI
  document.querySelectorAll('.fc-daygrid-day.selected').forEach(el=>el.classList.remove('selected'));
  selectedDates.clear();
  document.getElementById('calendar-start-time').value=''; 
  document.getElementById('calendar-end-time').value='';
  reindexScheduledTable();
});

 

});
document.querySelectorAll('.task-card').forEach(card => {
  const checkbox = card.querySelector('input[type="checkbox"]');
  const numberInputWrapper = card.querySelector('.task-number');

  checkbox.addEventListener('change', () => {
    if (checkbox.checked) {
      numberInputWrapper.style.display = 'block';
    } else {
      numberInputWrapper.style.display = 'none';
      numberInputWrapper.querySelector('input').value = ''; // optional: clear value
    }
  });
});



document.addEventListener("DOMContentLoaded", function () {

    // ---------------------------------
    // CORE ELEMENTS
    // ---------------------------------
    const dateSelector = document.getElementById("task-date-selector");
    const saveBtn = document.getElementById("save-date-tasks");
    const form = document.getElementById("opportunity-form");
    const startDateInput = document.querySelector('input[name="start_date"]');

    // ---------------------------------
    // REGULAR SCHEDULE HANDLER
    // ---------------------------------
    function handleRegularScheduleLoad() {
        const isIntermittent =
            document.querySelector("input[name='intermittent']:checked")?.value === "1";

        if (isIntermittent) return;
        if (!startDateInput?.value) return;

        ensureRegularTasksByDate();
        loadTasksForDate(startDateInput.value);
        refreshTaskSummaryTable();
    }

    // Run once on load
    handleRegularScheduleLoad();

    // Re-run when start date changes
    if (startDateInput) {
        startDateInput.addEventListener("change", () => {
            ensureRegularTasksByDate();
            refreshTaskDateDropdown();
            handleRegularScheduleLoad();
        });
    }

    // ---------------------------------
    // INTERMITTENT DATE SELECTION
    // ---------------------------------
    if (dateSelector) {
        dateSelector.addEventListener("change", function () {
            if (!this.value) return;
            document.getElementById("task-save-row").style.display = "block";
            loadTasksForDate(this.value);
        });
    }

    // ---------------------------------
    // SAVE TASKS (REGULAR + INTERMITTENT)
    // ---------------------------------
    if (saveBtn) {
        saveBtn.addEventListener("click", function () {

            const isIntermittent =
                document.querySelector("input[name='intermittent']:checked")?.value === "1";

            let selectedDate;

            if (isIntermittent) {
                selectedDate = dateSelector?.value;
                if (!selectedDate) return showAlertModal("Select a date");
            } else {
                selectedDate = startDateInput?.value;
                if (!selectedDate) return showAlertModal("Select a start date");
            }

            if (!tasksByDate[selectedDate]) {
                tasksByDate[selectedDate] = {};
            }

            document.querySelectorAll(".task-card").forEach(card => {
                const checkbox = card.querySelector("input[type='checkbox']");
                const numInput = card.querySelector("input[type='number']");
                const taskName = checkbox.dataset.taskName;

                if (checkbox.checked) {
                    tasksByDate[selectedDate][taskName] = parseInt(numInput.value) || 1;
                } else {
                    delete tasksByDate[selectedDate][taskName];
                }
            });

            refreshTaskSummaryTable();
            rebuildTaskingHiddenInputs();

        });
    }

    // ---------------------------------
    // SCHEDULE TYPE SWITCH (REGULAR / INTERMITTENT)
    // ---------------------------------
    document.querySelectorAll("input[name='intermittent']").forEach(radio => {
        radio.addEventListener("change", () => {
            refreshTaskDateDropdown();
            handleRegularScheduleLoad();
        });
    });




    // Create hidden inputs for Laravel
// --------------------------------------------------------------
// FINAL WORKING VERSION – BUILD tasks_by_date hidden inputs
// --------------------------------------------------------------
// --------------------------------------------------------------
// SINGLE OFFICIAL HIDDEN INPUT BUILDER FOR TASKING
// --------------------------------------------------------------
function rebuildTaskingHiddenInputs() {
    const form = document.getElementById("opportunity-form");

    // Remove all old inputs
    document.querySelectorAll(".task-by-date-input").forEach(i => i.remove());

    // Get summary rows
    const rows = document.querySelectorAll("#task-summary-body tr");

    rows.forEach(row => {
        const dateText = row.children[0].textContent.trim();
        const taskName = row.children[1].textContent.trim();
        const needed = row.children[2].textContent.trim();

        if (!dateText || !taskName) return;

        // Convert display date → ISO (Y-m-d)
const iso = dateText;
        // Build correct input name
        const input = document.createElement("input");
        input.type = "hidden";
        input.classList.add("task-by-date-input");
        input.name = `tasks_by_date[${iso}][${taskName}]`;
        input.value = needed;

        form.appendChild(input);
    });
}



});

function loadTasksForDate(date) {
    if (!tasksByDate[date]) return;

    document.querySelectorAll(".task-card").forEach(card => {
        const checkbox = card.querySelector("input[type='checkbox']");
        const numInput = card.querySelector("input[type='number']");
        const taskName = checkbox.dataset.taskName;

        if (tasksByDate[date][taskName] !== undefined) {
            checkbox.checked = true;
            numInput.value = tasksByDate[date][taskName];
        } else {
            checkbox.checked = false;
            numInput.value = 1;
        }
    });
}




document.addEventListener('click', function(e) {
    // DELETE button
    if (e.target.classList.contains('delete-btn')) {
        const idx = e.target.getAttribute('data-index');
        if (!confirm('Remove this scheduled entry?')) return;
        const row = e.target.closest('tr');
        if (row) row.remove();
        removeHiddenInputsByIndex(idx);
        reindexScheduledTable();
    }

    // EDIT button
    if (e.target.classList.contains('edit-btn')) {
        const idx = e.target.getAttribute('data-index');
        const row = e.target.closest('tr');
        if (!row) return;

        const dateInput = document.querySelector(`input[name="schedule[${idx}][date]"]`);
        const startInput = document.querySelector(`input[name="schedule[${idx}][start_time]"]`);
        const endInput = document.querySelector(`input[name="schedule[${idx}][end_time]"]`);
        const foodInput = document.querySelector(`input[name="schedule[${idx}][food_provided]"]`);

        const curDate = dateInput.value;
        const curStart = startInput.value;
        const curEnd = endInput.value;
        const curFood = foodInput.value;

        row.innerHTML = `
            <td><input type="date" id="edit-date-${idx}" value="${curDate}" style="width:150px"></td>
            <td><input type="time" id="edit-start-${idx}" value="${curStart}" style="width:110px"></td>
            <td><input type="time" id="edit-end-${idx}" value="${curEnd}" style="width:110px"></td>
            <td>
              <select id="edit-food-${idx}">
                <option value="yes" ${curFood==='yes' ? 'selected' : ''}>Yes</option>
                <option value="no" ${curFood==='no' ? 'selected' : ''}>No</option>
              </select>
            </td>
            <td>
              <button type="button" class="save-edit" data-index="${idx}" style="padding:6px 10px;background:#16a34a;color:#fff;border:none;border-radius:8px;margin-right:6px">Save</button>
              <button type="button" class="cancel-edit" data-index="${idx}" style="padding:6px 10px;background:#b71c1c;color:#fff;border:none;border-radius:8px">Cancel</button>
            </td>
        `;
    }

    // SAVE EDIT button
    if (e.target.classList.contains('save-edit')) {
        const idx = e.target.getAttribute('data-index');
        const newDate = document.getElementById(`edit-date-${idx}`).value;
        const newStart = document.getElementById(`edit-start-${idx}`).value;
        const newEnd = document.getElementById(`edit-end-${idx}`).value;
        const newFood = document.getElementById(`edit-food-${idx}`).value;

        if (!newDate || !newStart || !newEnd ) {
            showAlertModal('Please complete all fields before saving.');
            return;
        }

        updateOrCreateHiddenInput(`schedule[${idx}][date]`, newDate);
        updateOrCreateHiddenInput(`schedule[${idx}][start_time]`, newStart);
        updateOrCreateHiddenInput(`schedule[${idx}][end_time]`, newEnd);
        updateOrCreateHiddenInput(`schedule[${idx}][food_provided]`, newFood);

        const row = e.target.closest('tr');
        const displayIdx = row.querySelector('td:first-child')?.textContent || '';
        row.innerHTML = `
            <td class="col-date">${new Date(newDate).toLocaleDateString()}</td>
            <td class="col-start">${formatTimeForDisplay(newStart)}</td>
            <td class="col-end">${formatTimeForDisplay(newEnd)}</td>
            <td class="col-food">${newFood.charAt(0).toUpperCase() + newFood.slice(1)}</td>
          <div class="dropdown">
      <button class="btn-secondary dropdown-toggle" type="button" data-index="${idx}">Actions</button>
      <ul class="dropdown-menu" style="min-width:120px; padding:0; margin:0; list-style:none; border:1px solid #e6eef9; border-radius:8px; overflow:hidden; position:absolute; display:none; background:#fff; z-index:100;">
        <li><button type="button" class="dropdown-edit" data-index="${idx}" style="width:100%; padding:6px 12px; border:none; background:none; text-align:left;">Edit</button></li>
        <li><button type="button" class="dropdown-delete" data-index="${idx}" style="width:100%; padding:6px 12px; border:none; background:none; text-align:left; color:#b71c1c;">Delete</button></li>
      </ul>
    </div>
        `;
        reindexScheduledTable();
    }

    // CANCEL EDIT button
    if (e.target.classList.contains('cancel-edit')) {
        const idx = e.target.getAttribute('data-index');
        const dateInput = document.querySelector(`input[name="schedule[${idx}][date]"]`);
        const startInput = document.querySelector(`input[name="schedule[${idx}][start_time]"]`);
        const endInput = document.querySelector(`input[name="schedule[${idx}][end_time]"]`);
        const foodInput = document.querySelector(`input[name="schedule[${idx}][food_provided]"]`);
        const row = e.target.closest('tr');

        const displayIdx = row.querySelector('td:first-child')?.textContent || '';
        const curDate = dateInput ? new Date(dateInput.value).toLocaleDateString() : '';
        const curStart = startInput ? formatTimeForDisplay(startInput.value) : '';
        const curEnd = endInput ? formatTimeForDisplay(endInput.value) : '';
        const curFood = foodInput ? foodInput.value.charAt(0).toUpperCase() + foodInput.value.slice(1) : '';

row.innerHTML = `
  <td class="col-date">${curDate}</td>
  <td class="col-start">${curStart}</td>
  <td class="col-end">${curEnd}</td>
  <td class="col-food">${curFood}</td>
  <td class="col-actions">
    <div class="dropdown">
      <button class="btn-secondary dropdown-toggle" type="button" data-index="${idx}">Actions</button>
      <ul class="dropdown-menu">
        <li><button type="button" class="dropdown-edit" data-index="${idx}">Edit</button></li>
        <li><button type="button" class="dropdown-delete" data-index="${idx}">Delete</button></li>
      </ul>
    </div>
  </td>
`;


    }
});



function ensureRegularTasksByDate() {
    const isIntermittent =
        document.querySelector("input[name='intermittent']:checked")?.value === "1";

    if (isIntermittent) return;

    const startDate = document.querySelector('input[name="start_date"]').value;
    if (!startDate) return;

    if (!tasksByDate[startDate]) {
        tasksByDate[startDate] = {};
    }
}


// Helper: format Date object → ISO YYYY-MM-DD
function formatDateToISO(date) {
    if (!(date instanceof Date)) return "";
    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, "0");
    const dd = String(date.getDate()).padStart(2, "0");
    return `${yyyy}-${mm}-${dd}`;
}

function refreshTaskDateDropdown() {
    const dropdown = document.getElementById("task-date-selector");
    if (!dropdown) return;

    dropdown.innerHTML = ""; // Clear all options

    // Get all date cells from the scheduled table
    const dateCells = document.querySelectorAll("#scheduled-table tbody .col-date");

    if (dateCells.length > 0) {
        // Add placeholder
        const placeholder = document.createElement("option");
        placeholder.value = "";
        placeholder.textContent = "Select a date";
        placeholder.disabled = true;
        placeholder.selected = true;
        dropdown.appendChild(placeholder);

        dateCells.forEach(cell => {
            const dateText = cell.textContent.trim();
            if (!dateText) return;

            const parsedDate = new Date(dateText);
            if (isNaN(parsedDate)) return; // skip invalid dates

            const iso = formatDateToISO(parsedDate);

            const opt = document.createElement("option");
            opt.value = iso;
            opt.textContent = parsedDate.toLocaleDateString('en-US'); // consistent display
            dropdown.appendChild(opt);
        });

        updateSaveTaskButton();
        return;
    }

    // Fallback: if no table dates, use start_date input
    const startDateValue = document.querySelector('input[name="start_date"]')?.value;
    if (startDateValue) {
        const opt = document.createElement("option");
        opt.value = startDateValue;
        const parsedDate = new Date(startDateValue);
        opt.textContent = isNaN(parsedDate) ? startDateValue : parsedDate.toLocaleDateString('en-US');
        dropdown.appendChild(opt);
    }

    updateSaveTaskButton();
}


document.querySelectorAll('.task-toggle').forEach(cb => {
    cb.addEventListener('change', () => {
const selectedDate = dateSelector.value;
        if (!selectedDate) return;

        unsavedTasks[selectedDate] = Array.from(document.querySelectorAll('.task-toggle:checked'))
            .map(c => c.dataset.taskname);
    });
});
function refreshTaskSummaryTable() {
    const table = document.getElementById("task-summary-table");
    const tbody = document.getElementById("task-summary-body");
    tbody.innerHTML = "";

    let hasData = false;

    Object.keys(tasksByDate).forEach(date => {
        Object.keys(tasksByDate[date]).forEach(taskName => {
            hasData = true;
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${new Date(date).toLocaleDateString()}</td>
                <td>${taskName}</td>
                <td>${tasksByDate[date][taskName]}</td>
            `;
            tbody.appendChild(tr);
        });
    });

    table.style.display = hasData ? "table" : "none";
}



// --------------------------------------------------------------
// BUILD tasks_by_date HIDDEN INPUTS FROM SUMMARY TABLE
// --------------------------------------------------------------
function buildTasksByDateFromSummary() {

    // remove old hidden inputs
    document.querySelectorAll(".task-by-date-input").forEach(i => i.remove());

    const rows = document.querySelectorAll("#task-summary-body tr");
    const form = document.getElementById("opportunity-form");

    rows.forEach(row => {
        const dateText = row.children[0].textContent.trim();
        const taskName = row.children[1].textContent.trim();
        const needed    = row.children[2].textContent.trim();

        if (!dateText || !taskName) return;

        // Convert displayed date back to ISO
const iso = dateText;
        const input = document.createElement("input");
        input.type = "hidden";
        input.classList.add("task-by-date-input");
input.name = `tasks_by_date[${date}][${taskName}]`;
        input.value = needed;

        form.appendChild(input);
    });
}

function buildTasksByDateBeforeSubmit() {
    const form = document.getElementById("opportunity-form");
    tasksByDate = {}; // reset

    const isIntermittent = document.querySelector("input[name='intermittent']:checked")?.value === "1";

    if (isIntermittent) {
        // Build from summary table
        const rows = document.querySelectorAll("#task-summary-body tr");
        rows.forEach(row => {
            const date = new Date(row.children[0].textContent.trim()).toISOString().split("T")[0];
            const taskName = row.children[1].textContent.trim();
            const needed = parseInt(row.children[2].textContent.trim()) || 1;
            if (!tasksByDate[date]) tasksByDate[date] = {};
            tasksByDate[date][taskName] = needed;
        });
    } else {
        // Regular schedule: build from task cards using start_date
        const date = document.querySelector("input[name='start_date']").value;
        if (!date) return;
        tasksByDate[date] = {};
        document.querySelectorAll(".task-card").forEach(card => {
            const checkbox = card.querySelector("input[type='checkbox']");
            if (!checkbox.checked) return;
            const taskName = checkbox.dataset.taskName;
            const numInput = card.querySelector("input[type='number']");
            tasksByDate[date][taskName] = parseInt(numInput.value) || 1;
        });
    }

    // Remove old hidden inputs
    document.querySelectorAll(".task-by-date-input").forEach(i => i.remove());

    // Rebuild hidden inputs
    Object.keys(tasksByDate).forEach(date => {
        Object.keys(tasksByDate[date]).forEach(taskName => {
            const input = document.createElement("input");
            input.type = "hidden";
            input.classList.add("task-by-date-input");
input.name = `tasks_by_date[${date}][${taskName}]`;
            input.value = tasksByDate[date][taskName];
            form.appendChild(input);
        });
    });
}

function buildIntermittentSummaryInputs() {

    // Remove old inputs
    document.querySelectorAll(".intermittent-summary-input").forEach(e => e.remove());

    const rows = document.querySelectorAll("#task-summary-body tr");
    const form = document.getElementById("opportunity-form");

    rows.forEach(row => {
        const dateText = row.children[0].textContent.trim();
        const task = row.children[1].textContent.trim();
        const needed = row.children[2].textContent.trim();

        if (!dateText || !task) return;

        // Convert displayed date → ISO
const iso = dateText;
        // Create hidden input using the structure PHP requires
        const input = document.createElement("input");
        input.type = "hidden";
        input.classList.add("intermittent-summary-input");
        input.name = `intermittent_summary[${iso}][${task}]`;
        input.value = needed;

        form.appendChild(input);
    });
}

document.getElementById("opportunity-form").addEventListener("submit", function(e) {
    buildTasksByDateBeforeSubmit();
    buildIntermittentSummaryInputs(); 

    // Optional: prevent submit if no tasks
    const hasTasks = Object.keys(tasksByDate).length > 0;
    if (!hasTasks) {
        e.preventDefault();
        showAlertModal("Please add at least one task.");
        return;
    }
});

saveBtn.addEventListener("click", function () {
    const isIntermittent =
        document.querySelector("input[name='intermittent']:checked")?.value === "1";

    let selectedDate;

    if (isIntermittent) {
        selectedDate = dateSelector.value;
        if (!selectedDate) return showAlertModal("Select a date");
    } else {
        selectedDate = document.querySelector('input[name="start_date"]').value;
        if (!selectedDate) return showAlertModal("Select a start date");
    }

    if (!tasksByDate[selectedDate]) tasksByDate[selectedDate] = {};

    document.querySelectorAll(".task-card").forEach(card => {
        const checkbox = card.querySelector("input[type='checkbox']");
        const numInput = card.querySelector("input[type='number']");
        const taskName = checkbox.dataset.taskName;

        if (checkbox.checked) {
            tasksByDate[selectedDate][taskName] = parseInt(numInput.value) || 1;
        } else {
            delete tasksByDate[selectedDate][taskName];
        }
    });

    refreshTaskSummaryTable();
    rebuildTaskingHiddenInputs();

});





function generateSummary() {

  /* ======================================================
     BASIC INFO
  ====================================================== */
  document.getElementById('summary-title').textContent =
    document.querySelector('input[name="title"]')?.value || '—';

  document.getElementById('summary-location').textContent =
    document.querySelector('input[name="location"]')?.value || '—';

  document.getElementById('summary-description').textContent =
    document.querySelector('textarea[name="description"]')?.value || '—';


  /* ======================================================
     TASKS & VOLUNTEERS (WITH FOOD PROVIDED)
  ====================================================== */
  const taskBox = document.getElementById('summary-task-assignments');
  taskBox.innerHTML = '';

  const isIntermittent =
    document.querySelector('input[name="intermittent"]:checked')?.value === "1";

  const dates = Object.keys(tasksByDate || {});

  if (!dates.length) {
    taskBox.innerHTML = `<p class="muted">No task assignments added.</p>`;
    return;
  }

  /* ======================================================
     REGULAR SCHEDULE
  ====================================================== */
  if (!isIntermittent) {

    const date = dates[0];
    const tasks = tasksByDate[date];

    const foodProvided =
      document.querySelector('input[name="food_provided"]:checked')?.value || 'no';

    const fullDate = new Date(date).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });

let html = `
  <p><strong>Date:</strong> ${fullDate}</p>
  <p><strong>Food Provided:</strong> ${foodProvided.charAt(0).toUpperCase() + foodProvided.slice(1)}</p>
  <p><strong>Tasks:</strong></p>
  <ul style="padding-left:18px;">
`;

Object.keys(tasks).forEach(taskName => {
  html += `
    <li>
      ${taskName} — <strong>${tasks[taskName]}</strong> volunteer(s)
    </li>
  `;
});


    html += `</ul>`;
    taskBox.innerHTML = html;
    return;
  }

  /* ======================================================
     INTERMITTENT SCHEDULE
  ====================================================== */
  let html = `
    <table class="table table-bordered" style="font-size:13px;">
      <thead>
        <tr>
          <th>Date</th>
          <th>Task</th>
          <th>Volunteers Needed</th>
          <th>Food Provided</th>
        </tr>
      </thead>
      <tbody>
  `;

  dates.forEach(date => {

    const fullDate = new Date(date).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });

    // get food value for this date from scheduled table
    const foodCell = Array.from(document.querySelectorAll('#scheduled-table tbody tr'))
      .find(row => {
        const cellDate = row.querySelector('.col-date')?.textContent;
        return cellDate &&
          new Date(cellDate).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          }) === fullDate;
      })
      ?.querySelector('.col-food')?.textContent || '—';

    Object.keys(tasksByDate[date]).forEach(taskName => {
      html += `
        <tr>
          <td>${fullDate}</td>
          <td>${taskName}</td>
          <td>${tasksByDate[date][taskName]}</td>
          <td>${foodCell}</td>
        </tr>
      `;
    });
  });

  html += `
      </tbody>
    </table>
  `;

  taskBox.innerHTML = html;
}



let modalConfirmCallback = null;

function showAlertModal(message, title = "Notice") {
  const modalEl = document.getElementById("globalModal");
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

  document.getElementById("globalModalTitle").textContent = title;
  document.getElementById("globalModalBody").textContent = message;

  const okBtn = document.getElementById("modalConfirmBtn");

  okBtn.onclick = () => {
    modal.hide(); // 🔥 THIS REMOVES THE BACKDROP
  };

  modal.show();
}

function showConfirmModal(message, onConfirm, title = "Confirm Action") {
  const modal = new bootstrap.Modal(document.getElementById("globalModal"));

  document.getElementById("globalModalTitle").textContent = title;
  document.getElementById("globalModalBody").textContent = message;

  const cancelBtn = document.getElementById("modalCancelBtn");
  const confirmBtn = document.getElementById("modalConfirmBtn");

  cancelBtn.classList.remove("d-none");
  confirmBtn.textContent = "Confirm";

  confirmBtn.onclick = () => {
    modal.hide();
    if (typeof onConfirm === "function") onConfirm();
  };

  modal.show();
}

</script>



@endsection
