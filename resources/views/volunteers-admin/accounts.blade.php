<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Accounts - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  @vite('resources/css/app.css')
<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
body { min-height: 100vh; display: flex; flex-direction: column; }
header { background-color: #003DA5; color: white; display: flex; justify-content: space-between; align-items: center; padding: 1rem 2rem; }
header .logo { font-weight: bold; display: flex; align-items: center; gap: 0.5rem; }
header nav a { color: white; margin: 0 0.75rem; text-decoration: none; font-weight: 500; font-size: 0.9rem; }
.login-container { background-color: white; max-width: 400px; margin: 4rem auto; padding: 2rem; border: 2px solid #003DA5; border-radius: 25px; text-align: center; }
.login-container h2 { color: #003DA5; font-size: 2rem; margin-bottom: 1.5rem; font-weight: 800; font-style: italic; }
.login-container label { display: block; text-align: left; margin-bottom: 0.25rem; font-size: 0.9rem; }
.login-container input { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #003DA5; border-radius: 5px; }
.login-container p { font-size: 0.85rem; color: #555; margin-bottom: 1rem; }
.login-container button { background-color: #003DA5; color: white; padding: 0.75rem 2rem; border: none; border-radius: 25px; font-weight: bold; cursor: pointer; }
.custom-header { background-color: white; display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 2rem; border-bottom: 2px solid #003DA5; }
.custom-header .logo { display: flex; align-items: center; gap: 0.75rem; font-weight: bold; color: #003DA5; }
.custom-header .logo img { height: 40px; width: auto; }
.custom-header .header-right p { color: #003DA5; font-weight: 100; font-size: 1rem; margin: 0; }
.side-button { background: none; border: none; padding: 0; cursor: pointer; }
.side-button img { height: 30px; width: auto; }
.header-right-group { display: flex; align-items: center; gap: 5.00rem; }
.header-right-group p { color: #003DA5; font-weight: 100; font-size: 1rem; margin: 0; }
.tabs { display: flex; border-bottom: 2px solid #003DA5; margin: 1rem 2rem; flex-wrap: wrap; }
.tabs button { background: none; border: none; padding: 1rem; cursor: pointer; font-weight: bold; color: #003DA5; }
.tabs button.active { border-bottom: 3px solid #003DA5; }
.tab-content { display: none; padding: 2rem; background: #fff; margin: 0 2rem; border: 1px solid #ccc; }
.tab-content.active { display: block; }
.subtab-content { display: none; }
.subtab-content.active { display: block; }
table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
table, th, td { border: 1px solid #003DA5; }
th, td { padding: 0.75rem; text-align: left; }
.btn { background: #003DA5; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; margin-right: 0.5rem; }
.modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background: rgba(0,0,0,0.5); }
.modal-content { background: #fff; margin: 5% auto; padding: 2rem; width: 50%; border-radius: 8px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; }
.close { cursor: pointer; font-size: 1.5rem; }
.org-filter-btn { background: none; border: 2px solid #003DA5; color: #003DA5; padding: 0.5rem 1rem; margin-right: 0.5rem; border-radius: 5px; cursor: pointer; font-weight: bold; transition: background 0.3s, color 0.3s; }
.org-filter-btn.active, .org-filter-btn:hover { background: #003DA5; color: #fff; }
</style>

</head>
<body>

<header class="custom-header">
        <div class="header-left">
            <div class="logo">
                <img src="{{ asset('images/NU_Logo.png') }}" alt="NU Logo" height="30">
                <span>Student Developments and Activities Office - Activities</span>
            </div>
        </div>
        <div class="header-right-group">
            <p>Welcome, {{ session('volunteers_admin.first_name') }}!</p>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="side-button">
                    <img src="{{ asset('images/Side.png') }}" alt="Side" height="30">
                </button>
            </form>
        </div>
    </header>

<!-- ✅ Navigation -->
<header>
    <nav>
        <a href="{{ route('volunteers-admin.leader-board') }}">Leader board</a>
        <a href="{{ route('volunteers-admin.office') }}">Office</a>
        <a href="{{ route('volunteers-admin.student-volunteers') }}">Student Volunteers</a>
        <a href="{{ route('volunteers-admin.accounts') }}">Accounts</a>
        <a href="{{ route('volunteers-admin.inquiries') }}">Inquiries</a>
    </nav>
    </header>

<!-- ✅ Main Tabs -->
<div class="tabs">
    <button class="tablink" data-tab="Scholars">Scholars</button>
    <button class="tablink" data-tab="AspiringScholars">Aspiring Scholars</button>
    <button class="tablink" data-tab="Offices">Offices</button>

</div>

<!-- ✅ Aspiring Scholars Tab Content -->
<div id="AspiringScholars" class="tab-content">
  <h2>Aspiring Scholars</h2><br><br>

  <!-- Button to open modal -->
  <button class="btn" onclick="document.getElementById('createAspiringScholarAccount').style.display='block'">
    Create Aspiring Scholar Account
  </button>

  <!-- Accounts table -->
  <table>
    <thead>
      <tr>
        <th>First Name</th>
        <th>Middle Name</th>
        <th>Last Name</th>
        <th>Suffix</th>
        <th>Email</th>
        <th>Created At</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($aspiringScholars as $scholar)
        <tr>
          <td>{{ $scholar['first_name'] }}</td>
          <td>{{ $scholar['middle_name'] }}</td>
          <td>{{ $scholar['last_name'] }}</td>
          <td>{{ $scholar['suffix'] }}</td>
          <td>{{ $scholar['email'] }}</td>
          <td>{{ $scholar['created_at'] }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="6">No Aspiring Scholar accounts found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  {{-- Pagination links --}}
{{ $aspiringScholars->links() }}
</div>

<!-- ✅ Create Aspiring Scholar Account Modal -->
<div id="createAspiringScholarAccount" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Aspiring Scholar Account</h3>
      <span class="close" onclick="document.getElementById('createAspiringScholarAccount').style.display='none'">&times;</span>
    </div>

    <form method="POST" action="{{ route('volunteers-admin.create-aspiring-scholar-account') }}">
      @csrf

      <label>First Name</label>
      <input type="text" name="first_name" required><br/>

      <label>Middle Name</label>
      <input type="text" name="middle_name"><br/>

      <label>Last Name</label>
      <input type="text" name="last_name" required><br/>

      <label>Suffix</label>
      <input type="text" name="suffix"><br/>

      <label>Email</label>
      <input type="email" name="email" required><br/>

      <button class="btn" type="submit">Create</button>
    </form>
  </div>
</div>



<div id="Scholars" class="tab-content">
  <h2>Scholars</h2><br><br>

  <!-- Button to open import modal -->
  <button class="btn" onclick="document.getElementById('importScholarsModal').style.display='block'">
    Import Scholars CSV
  </button>

  <!-- Scholars Table -->
  <table>
    <thead>
      <tr>
        <th>Student No.</th>
        <th>First Name</th>
        <th>Middle Name</th>
        <th>Last Name</th>
        <th>Birthday</th>
        <th>School</th>
        <th>Program</th>
        <th>Scholarship Type</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($scholars as $scholar)
        <tr>
          <td>{{ $scholar['student_no'] }}</td>
          <td>{{ $scholar['first_name'] }}</td>
          <td>{{ $scholar['middle_name'] }}</td>
          <td>{{ $scholar['last_name'] }}</td> 
          <td>{{ $scholar['birthday'] }}</td>
          <td>{{ $scholar['school'] }}</td>
          <td>{{ $scholar['program'] }}</td>
          <td>{{ $scholar['scholarship_type'] }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="4">No Scholars found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
  {{-- Scholars --}}
{{ $scholars->links() }}
</div>


<!-- Import Scholars Modal -->
<div id="importScholarsModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Import Scholars CSV</h3>
      <span class="close" onclick="document.getElementById('importScholarsModal').style.display='none'">&times;</span>
    </div>

    <form method="POST" action="{{ route('volunteers-admin.import-scholars') }}" enctype="multipart/form-data">
      @csrf

      <label>Select CSV or Excel File</label>
      <input type="file" name="scholars_file" accept=".csv, .xlsx" required><br/><br/>

      <button class="btn" type="submit">Import</button>
    </form>
  </div>
</div>

<!-- Offices Tab with Subtabs -->
<div id="Offices" class="tab-content active">
  <h2>Offices</h2>

  <!-- Offices Subtabs -->
  <div class="tabs" style="border-bottom: 1px solid #003DA5;">
    <button class="subtablink active" data-subtab="OfficeTypes">Offices</button>
    <button class="subtablink" data-subtab="OfficeUsers">Users</button>
  </div>

  <!-- Offices Subtab -->
  <div id="OfficeTypes" class="subtab-content active">
    <button class="btn" onclick="document.getElementById('addOfficeModal').style.display='block'">Add Office</button>
    <table>
      <thead>
        <tr><th>Office Name</th><th>Created At</th></tr>
      </thead>
      <tbody>
        @foreach ($offices as $office)
          <tr>
            <td>{{ $office['name'] }}</td>
            <td>{{ $office['created_at'] ?? 'N/A' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    
{{-- Offices --}}
{{ $offices->links() }}
  </div>

  <!-- Office Users Subtab -->
  <div id="OfficeUsers" class="subtab-content">
    <button class="btn" onclick="document.getElementById('addOfficeUserModal').style.display='block'">Add Office User</button>
    <table>
      <thead>
        <tr><th>Office</th><th>Full Name</th><th>Email</th><th>Created At</th></tr>
      </thead>
      <tbody>
        @foreach ($officeAccounts as $user)
          <tr>
            <td>{{ $user['office_name'] ?? 'N/A' }}</td>
            <td>{{ $user['first_name'] }} {{ $user['middle_name'] ?? '' }} {{ $user['last_name'] }} {{ $user['suffix'] ?? '' }}</td>
            <td>{{ $user['email'] }}</td>
            <td>{{ $user['created_at'] ?? 'N/A' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    
{{-- Offices --}}
{{ $offices->links() }}
  </div>
</div>

<!-- Add Office Modal -->
<div id="addOfficeModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Add Office</h3>
      <span class="close" onclick="document.getElementById('addOfficeModal').style.display='none'">&times;</span>
    </div>

    <form method="POST" action="{{ route('volunteers-admin.create-office') }}">
      @csrf
      <label>Office Name</label>
      <input type="text" name="office_name" required />
      <button class="btn" type="submit">Add Office</button>
    </form>
  </div>
</div>

<!-- Add Office User Modal -->
<div id="addOfficeUserModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Add Office User</h3>
      <span class="close" onclick="document.getElementById('addOfficeUserModal').style.display='none'">&times;</span>
    </div>

    <form method="POST" action="{{ route('volunteers-admin.create-office-account') }}">
      @csrf
      <label>Office</label>
      <select name="office_id" required>
        @foreach ($offices as $office)
          <option value="{{ $office['id'] }}">{{ $office['name'] }}</option>
        @endforeach
      </select>

      <label>First Name</label>
      <input type="text" name="first_name" required />

      <label>Middle Name</label>
      <input type="text" name="middle_name" />

      <label>Last Name</label>
      <input type="text" name="last_name" required />

      <label>Suffix</label>
      <input type="text" name="suffix" />

      <label>Email</label>
      <input type="email" name="email" required />

      <button class="btn" type="submit">Add Office User</button>
    </form>
  </div>
</div>

<!-- ✅ JS Logic -->
<script>
const tabs = document.querySelectorAll('.tablink');
const contents = document.querySelectorAll('.tab-content');

tabs.forEach(tab => {
  tab.addEventListener('click', () => {
    tabs.forEach(t => t.classList.remove('active'));
    contents.forEach(c => c.classList.remove('active'));
    tab.classList.add('active');
    document.getElementById(tab.dataset.tab).classList.add('active');

    // Save active tab in localStorage
    localStorage.setItem('activeTab', tab.dataset.tab);
  });
});

const subtabs = document.querySelectorAll('.subtablink');
const subcontents = document.querySelectorAll('.subtab-content');

subtabs.forEach(subtab => {
  subtab.addEventListener('click', () => {
    subtabs.forEach(st => st.classList.remove('active'));
    subcontents.forEach(sc => sc.classList.remove('active'));
    subtab.classList.add('active');
    document.getElementById(subtab.dataset.subtab).classList.add('active');

    // Save active subtab in localStorage
    localStorage.setItem('activeSubtab', subtab.dataset.subtab);
  });
});

// This block appears redundant due to above event listeners but keeping for group structure
document.querySelectorAll('#Offices .tabs').forEach(tabGroup => {
  const subtabs = tabGroup.querySelectorAll('.subtablink');
  subtabs.forEach(subtab => subtab.addEventListener('click', () => {
    subtabs.forEach(st => st.classList.remove('active'));
    document.querySelectorAll('#Offices .subtab-content').forEach(sc => sc.classList.remove('active'));
    subtab.classList.add('active');
    document.getElementById(subtab.dataset.subtab).classList.add('active');
  }));
});

document.addEventListener('DOMContentLoaded', () => {
  // Restore active main tab
  const activeTab = localStorage.getItem('activeTab');
  if (activeTab) {
    const tabButton = document.querySelector(`.tablink[data-tab="${activeTab}"]`);
    if (tabButton) {
      tabButton.click();
    }
  } else {
    // Default activate first tab if none saved
    tabs[0].click();
  }

  // Restore active subtab (only if Offices tab is active)
  const activeSubtab = localStorage.getItem('activeSubtab');
  if (activeSubtab) {
    const subtabButton = document.querySelector(`.subtablink[data-subtab="${activeSubtab}"]`);
    if (subtabButton) {
      subtabButton.click();
    }
  } else {
    // Default activate first subtab
    subtabs[0].click();
  }
});
</script>


</body>
</html>
