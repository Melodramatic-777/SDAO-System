<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Accounts - NU Fairview SDAO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  @vite('resources/css/admin-activities.css')
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
            <p>Welcome, {{ session('activities_admin.first_name') }}!</p>
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
        <a href="{{ route('activities-admin.home') }}">Home</a>
        <a href="{{ route('activities-admin.activities-calendar') }}">Activities Calendar</a>
        <a href="{{ route('activities-admin.documents') }}">Documents</a>
        <a href="{{ route('activities-admin.profile') }}">Profile</a>
        <a href="{{ route('activities-admin.accounts') }}">Accounts</a>
        <a href="{{ route('activities-admin.inquiries') }}">Inquiries</a>
    </nav>
    </header>

<!-- ✅ Main Tabs -->
<div class="tabs">
    <button class="tablink active" data-tab="Organizations">Organizations</button>
    <button class="tablink" data-tab="Deans">Deans</button>
    <button class="tablink" data-tab="ProgramChairs">Program Chairs</button>
    <button class="tablink" data-tab="Departments">Departments</button> 
    <button class="tablink" data-tab="Facilities">Facilities</button>
    <button class="tablink" data-tab="ItSystems">IT Systems</button>
    <button class="tablink" data-tab="Comex">COMEX</button> 
    <button class="tablink" data-tab="Marketing">Marketing</button>
    <button class="tablink" data-tab="Executives">Executives</button> 
</div>

<!-- ✅ Organizations Tab Content -->
<div id="Organizations" class="tab-content active">
  <h2>Organizations</h2>
  <div class="tabs" style="border-bottom: 1px solid #003DA5;">
    <button class="subtablink active" data-subtab="OrgsList">Organizations List</button>
    <button class="subtablink" data-subtab="OrgUsers">Organization Users</button>
</div>
  <!-- ✅ Manage Organizations Modal -->
<div id="manageOrgs" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Manage Organizations</h3>
      <span class="close" onclick="document.getElementById('manageOrgs').style.display='none'">&times;</span>
    </div>

    <form method="POST" action="{{ route('activities-admin.store-organization') }}">
      @csrf

  <label>Type</label><br/>
  <label>
    <input type="radio" name="type" value="Academic" onclick="toggleAcademicFields()"> Academic
  </label>
  <label>
    <input type="radio" name="type" value="Non-Academic" onclick="toggleAcademicFields()"> Non-Academic
  </label>
  <br/><br/>


      <label>Organization Name</label>
      <input type="text" name="org_name" required><br/>

      <div id="academic-fields" style="display:none;">
        <label>School</label>
        <select name="school_id" id="school-select">
          <option value="">-- Select School --</option>
          @foreach ($schools as $school)
            <option value="{{ $school['id'] }}">{{ $school['name'] }}</option>
          @endforeach
        </select><br/>

        <label>Program</label>
        <select name="program" id="program-select">
          <option value="">-- Select Program --</option>
          @foreach ($schools as $school)
            @foreach ($school['programs'] as $program)
              <option value="{{ $program }}" data-school="{{ $school['id'] }}">{{ $program }}</option>
            @endforeach
          @endforeach
        </select><br/>
      </div>

      <button type="submit" class="btn">Add Organization</button>
    </form>
  </div>
</div>


<!-- ✅ Orgs List Subtab with filter -->
<div id="OrgsList" class="subtab-content active">
  <button class="btn" onclick="document.getElementById('manageOrgs').style.display='block'">Manage Organizations</button>

  <br>
  <br>
  <!-- Subtabs -->
<div class="subtabs">
  <button class="org-filter-btn active" onclick="showOrgSubtab('Academic')">Academic</button>
  <button class="org-filter-btn" onclick="showOrgSubtab('NonAcademic')">Non-Academic</button>
</div>

  <!-- Academic Orgs -->
<div id="AcademicOrgs" class="orgsubtab-content" style="display: block;">
    <br/><h4>Academic Organizations</h4>
    <table>
      <thead>
        <tr>
          <th>Organization Name</th>
          <th>Type</th>
          <th>School</th>
          <th>Program</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($organizations as $org)
          @if ($org['type'] === 'Academic')
            <tr>
              <td>{{ $org['name'] }}</td>
              <td>{{ $org['type'] }}</td>
              <td>{{ $org['school_name'] ?? 'N/A' }}</td>
              <td>{{ $org['program'] ?? '' }}</td>
            </tr>
          @endif
        @empty
          <tr><td colspan="4">No Academic Organizations found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Non-Academic Orgs -->
<div id="NonAcademicOrgs" class="orgsubtab-content" style="display: none;">
    <br/><h4>Non-Academic Organizations</h4>
    <table>
      <thead>
        <tr>
          <th>Organization Name</th>
          <th>Type</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($organizations as $org)
          @if ($org['type'] === 'Non-Academic')
            <tr>
              <td>{{ $org['name'] }}</td>
              <td>{{ $org['type'] }}</td>
            </tr>
          @endif
        @empty
          <tr><td colspan="2">No Non-Academic Organizations found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- ✅ Create Organization Account Modal -->
<div id="createOrgAccount" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Organization Accounts</h3>
      <span class="close" onclick="document.getElementById('createOrgAccount').style.display='none'">&times;</span>
    </div>

<form method="POST" action="{{ route('activities-admin.organizations.create-account') }}">
  @csrf

  <label>Organization</label>
  <select name="organization_id" required>
    @foreach ($organizations as $org)
      <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
    @endforeach
  </select>
  <br/><br/>

  <!-- Radio -->
  <label>
    <input type="radio" name="account_type" value="President" onclick="toggleOrgAccountFields()" checked> President
  </label>
  <label>
    <input type="radio" name="account_type" value="Adviser" onclick="toggleOrgAccountFields()"> Adviser
  </label>

  <br/><br/>

  <div id="president-fields">
    <h4>President</h4>
    <label>First Name</label><input type="text" name="pres_first"><br/>
    <label>Middle Name</label><input type="text" name="pres_middle"><br/>
    <label>Last Name</label><input type="text" name="pres_last"><br/>
    <label>Suffix</label><input type="text" name="pres_suffix"><br/>
    <label>Email</label><input type="email" name="pres_email"><br/>
  </div>

  <div id="adviser-fields" style="display: none;">
    <h4>Adviser</h4>
    <label>First Name</label><input type="text" name="adv_first"><br/>
    <label>Middle Name</label><input type="text" name="adv_middle"><br/>
    <label>Last Name</label><input type="text" name="adv_last"><br/>
    <label>Suffix</label><input type="text" name="adv_suffix"><br/>
    <label>Email</label><input type="email" name="adv_email"><br/>
  </div>

  <br/>
  <button type="submit" class="btn">Create Account</button>
</form>

  </div>
</div>



  <!-- ✅ Org Users Subtab -->
  <div id="OrgUsers" class="subtab-content">
    <button class="btn" onclick="document.getElementById('createOrgAccount').style.display='block'">Create Account</button>
    <table>
      <thead>
        <tr><th>Full Name</th><th>Role</th><th>Organization</th><th>Email</th><th>Created At</th></tr>
      </thead>
      <tbody>
        @foreach ($orgUsers as $user)
          <tr>
            <td>{{ $user['first_name'] }} {{ $user['middle_name'] ?? '' }} {{ $user['last_name'] }} {{ $user['suffix'] ?? '' }}</td>
            <td>{{ $user['role'] }}</td>
            <td>{{ $user['organization_name'] ?? 'N/A' }}</td>
            <td>{{ $user['email'] }}</td>
            <td>{{ $user['created_at'] }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- ✅ Deans Tab Content -->
<div id="Deans" class="tab-content">
  <h2>Deans</h2>
  <div class="tabs" style="border-bottom: 1px solid #003DA5;">
    <button class="subtablink active" data-subtab="SchoolsList">Schools List</button>
    <button class="subtablink" data-subtab="DeanUsers">Dean Users</button>
  </div>

<!-- ✅ Manage Schools Modal -->
<!-- ✅ Manage Schools Modal -->
<div id="manageSchools" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Manage Schools</h3>
      <span class="close" onclick="document.getElementById('manageSchools').style.display='none'">&times;</span>
    </div>
    <form method="POST" action="{{ route('activities-admin.schools.store') }}">
      @csrf
      <input type="hidden" name="tab" value="Deans">
      <input type="hidden" name="subtab" value="SchoolsList">
      <label>School Name</label>
      <input type="text" name="school_name" required><br/>

      <label>Programs</label>
      <div id="programs-container">
        <input type="text" name="programs[]" required><br/>
      </div>
      <button type="button" class="btn" onclick="addProgramField()">+ Add Another Program</button><br/><br/>

      <button type="submit" class="btn">Add School</button>
    </form>
  </div>
</div>



  <!-- ✅ Schools List Subtab -->
  <div id="SchoolsList" class="subtab-content active">
    <button class="btn" onclick="document.getElementById('manageSchools').style.display='block'">Manage Schools</button>
    <table>
      <thead>
        <tr><th>School Name</th><th>Programs</th></tr>
      </thead>
      <tbody>
        @foreach ($schools as $school)
          <tr>
            <td>{{ $school['name'] }}</td>
            <td>
              @if (!empty($school['programs']))
                <ul style="padding-left: 1rem; margin: 0;">
                  @foreach ($school['programs'] as $program)
                    <li>{{ $program }}</li>
                  @endforeach
                </ul>
              @else
                <em>No programs listed</em>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>


  <!-- ✅ Create Dean Account Modal -->
<div id="createDeanAccount" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Dean Account</h3>
      <span class="close" onclick="document.getElementById('createDeanAccount').style.display='none'">&times;</span>
    </div>
    <form method="POST" action="{{ route('activities-admin.schools.create-dean-account') }}">
      @csrf
      <label>School</label>
      <select name="school_id" required>
        @foreach ($schools as $school)
          <option value="{{ $school['id'] }}">{{ $school['name'] }}</option>
        @endforeach
      </select><br/>
      <label>First Name</label><input type="text" name="first_name" required><br/>
      <label>Middle Name</label><input type="text" name="middle_name"><br/>
      <label>Last Name</label><input type="text" name="last_name" required><br/>
      <label>Suffix</label><input type="text" name="suffix"><br/>
      <label>Email</label><input type="email" name="email" required><br/>
      <button type="submit" class="btn">Create Dean</button>
    </form>
  </div>
</div>

  <!-- ✅ Dean Users Subtab -->
  <div id="DeanUsers" class="subtab-content">
    <button class="btn" onclick="document.getElementById('createDeanAccount').style.display='block'">Create Dean Account</button>
    <table>
      <thead>
        <tr><th>School</th><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Suffix</th><th>Email</th></tr>
      </thead>
      <tbody>
        @foreach ($deans as $dean)
          <tr>
            <td>{{ $dean['school_name'] }}</td>
            <td>{{ $dean['first_name'] }}</td>
            <td>{{ $dean['middle_name'] ?? '' }}</td>
            <td>{{ $dean['last_name'] }}</td>
            <td>{{ $dean['suffix'] ?? '' }}</td>
            <td>{{ $dean['email'] }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- PROGRAM CHAIRS -->

<div id="ProgramChairs" class="tab-content">
  <h2>Program Chairs</h2></br></br>
  <button class="btn" onclick="document.getElementById('createProgramChairAccount').style.display='block'">Create Program Chair Account</button>
  <table>
    <thead>
      <tr>
        <th>School</th>
        <th>Program</th>
        <th>First Name</th>
        <th>Middle Name</th>
        <th>Last Name</th>
        <th>Suffix</th>
        <th>Email</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($programChairs as $chair)
        <tr>
          <td>{{ $chair['school_name'] }}</td>
          <td>{{ $chair['program'] }}</td>
          <td>{{ $chair['first_name'] }}</td>
          <td>{{ $chair['middle_name'] ?? '' }}</td>
          <td>{{ $chair['last_name'] }}</td>
          <td>{{ $chair['suffix'] ?? '' }}</td>
          <td>{{ $chair['email'] }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>



<div id="createProgramChairAccount" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Program Chair Account</h3>
      <span class="close" onclick="document.getElementById('createProgramChairAccount').style.display='none'">&times;</span>
    </div>
    <form method="POST" action="{{ route('activities-admin.schools.create-program-chair-account') }}">
      @csrf
      <input type="hidden" name="tab" value="ProgramChairs">

      <label>School</label>
      <select name="school_id" id="school-select" required>
        <option value="">-- Select School --</option>
        @foreach ($schools as $school)
          <option value="{{ $school['id'] }}">{{ $school['name'] }}</option>
        @endforeach
      </select><br/>

      <label>Program</label>
      <select name="program" id="program-select" required>
        <option value="">-- Select Program --</option>
        @foreach ($schools as $school)
          @foreach ($school['programs'] as $program)
            <option value="{{ $program }}" data-school="{{ $school['id'] }}">{{ $program }}</option>
          @endforeach
        @endforeach
      </select><br/>

      <label>First Name</label><input type="text" name="first_name" required><br/>
      <label>Middle Name</label><input type="text" name="middle_name"><br/>
      <label>Last Name</label><input type="text" name="last_name" required><br/>
      <label>Suffix</label><input type="text" name="suffix"><br/>
      <label>Email</label><input type="email" name="email" required><br/>
      <button class="btn" type="submit">Create</button>
    </form>
  </div>
</div>


<!-- DEPARTMENTS -->

<div id="Departments" class="tab-content">
  <h2>Departments</h2></br></br>



  <!-- Button to open modal -->
  <button class="btn" onclick="document.getElementById('createDepartmentAccount').style.display='block'">
    Create Department Account
  </button>

  <!-- Accounts table -->
  <table>
    <thead>
      <tr>
        <th>School</th>
        <th>Program</th>
        <th>First Name</th>
        <th>Middle Name</th>
        <th>Last Name</th>
        <th>Suffix</th>
        <th>Email</th>
        <th>Created At</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($departments as $department)
        <tr>
          <td>{{ $department['school_name'] }}</td>
          <td>{{ $department['program'] }}</td>
          <td>{{ $department['first_name'] }}</td>
          <td>{{ $department['middle_name'] }}</td>
          <td>{{ $department['last_name'] }}</td>
          <td>{{ $department['suffix'] }}</td>
          <td>{{ $department['email'] }}</td>
          <td>{{ $department['created_at'] }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="8">No Department accounts found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<!-- Create Department Account Modal -->
<div id="createDepartmentAccount" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Department Account</h3>
      <span class="close" onclick="document.getElementById('createDepartmentAccount').style.display='none'">&times;</span>
    </div>

    <form method="POST" action="{{ route('activities-admin.create-department-account') }}">
      @csrf
      <input type="hidden" name="tab" value="Departments">

      <label>School</label>
      <select name="school_id" id="school-select" required>
        <option value="">-- Select School --</option>
        @foreach ($schools as $school)
          <option value="{{ $school['id'] }}">{{ $school['name'] }}</option>
        @endforeach
      </select><br/>

      <label>Program</label>
      <select name="program" id="program-select" required>
        <option value="">-- Select Program --</option>
        @foreach ($schools as $school)
          @foreach ($school['programs'] as $program)
            <option value="{{ $program }}" data-school="{{ $school['id'] }}">{{ $program }}</option>
          @endforeach
        @endforeach
      </select><br/>
      
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


<!-- FACILITIES -->

<div id="Facilities" class="tab-content">
  <h2>Facilities</h2></br></br>



  <!-- Button to open modal -->
  <button class="btn" onclick="document.getElementById('createFacilitiesAccount').style.display='block'">
    Create Facilities Account
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
      @forelse ($facilities as $facility)
        <tr>
          <td>{{ $facility['first_name'] }}</td>
          <td>{{ $facility['middle_name'] }}</td>
          <td>{{ $facility['last_name'] }}</td>
          <td>{{ $facility['suffix'] }}</td>
          <td>{{ $facility['email'] }}</td>
          <td>{{ $facility['created_at'] }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="6">No Facilities accounts found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<!-- Create Facilities Account Modal -->
<div id="createFacilitiesAccount" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Facilities Account</h3>
      <span class="close" onclick="document.getElementById('createFacilitiesAccount').style.display='none'">&times;</span>
    </div>

    <form method="POST" action="{{ route('activities-admin.create-facilities-account') }}">
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


<!-- IT Systems -->

<div id="ItSystems" class="tab-content">
  <h2>IT Systems Accounts</h2></br></br>

  <button class="btn" onclick="document.getElementById('createItSystemsAccount').style.display='block'">
    Create IT Systems Account
  </button>

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
      @foreach ($itSystems as $account)
        <tr>
          <td>{{ $account['first_name'] }}</td>
          <td>{{ $account['middle_name'] ?? '' }}</td>
          <td>{{ $account['last_name'] }}</td>
          <td>{{ $account['suffix'] ?? '' }}</td>
          <td>{{ $account['email'] }}</td>
          <td>{{ $account['created_at'] ?? '' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

<!-- Modal -->
<div id="createItSystemsAccount" class="modal" style="display:none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create IT Systems Account</h3>
      <span class="close" onclick="document.getElementById('createItSystemsAccount').style.display='none'">&times;</span>
    </div>

    <form method="POST" action="{{ route('activities-admin.create-it-systems-account') }}">
      @csrf
      <input type="hidden" name="tab" value="ItSystems">

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


<!-- ✅ COMEX Tab Content -->
<div id="Comex" class="tab-content">
  <h2>COMEX</h2></br></br>
  <button class="btn" onclick="document.getElementById('createComexAccount').style.display='block'">Create COMEX Account</button>
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
      @foreach ($comex as $c)
      <tr>
        <td>{{ $c['first_name'] }}</td>
        <td>{{ $c['middle_name'] ?? '' }}</td>
        <td>{{ $c['last_name'] }}</td>
        <td>{{ $c['suffix'] ?? '' }}</td>
        <td>{{ $c['email'] }}</td>
        <td>{{ $c['created_at'] }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<!-- ✅ COMEX Create Modal -->
<div id="createComexAccount" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create COMEX Account</h3>
      <span class="close" onclick="document.getElementById('createComexAccount').style.display='none'">&times;</span>
    </div>
    <form method="POST" action="{{ route('activities-admin.create-comex-account') }}">
      @csrf
      <label>First Name</label><input type="text" name="first_name" required><br/>
      <label>Middle Name</label><input type="text" name="middle_name"><br/>
      <label>Last Name</label><input type="text" name="last_name" required><br/>
      <label>Suffix</label><input type="text" name="suffix"><br/>
      <label>Email</label><input type="email" name="email" required><br/>
      <button class="btn" type="submit">Create</button>
    </form>
  </div>
</div>

<!-- ✅ Marketing Tab Content -->
<div id="Marketing" class="tab-content">
  <h2>Marketing</h2></br></br>
  <button class="btn" onclick="document.getElementById('createMarketingAccount').style.display='block'">Create Marketing Account</button>
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
      @foreach ($marketing as $m)
      <tr>
        <td>{{ $m['first_name'] }}</td>
        <td>{{ $m['middle_name'] ?? '' }}</td>
        <td>{{ $m['last_name'] }}</td>
        <td>{{ $m['suffix'] ?? '' }}</td>
        <td>{{ $m['email'] }}</td>
        <td>{{ $m['created_at'] }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<!-- ✅ Marketing Create Modal -->
<div id="createMarketingAccount" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Marketing Account</h3>
      <span class="close" onclick="document.getElementById('createMarketingAccount').style.display='none'">&times;</span>
    </div>
    <form method="POST" action="{{ route('activities-admin.create-marketing-account') }}">
      @csrf
      <label>First Name</label><input type="text" name="first_name" required><br/>
      <label>Middle Name</label><input type="text" name="middle_name"><br/>
      <label>Last Name</label><input type="text" name="last_name" required><br/>
      <label>Suffix</label><input type="text" name="suffix"><br/>
      <label>Email</label><input type="email" name="email" required><br/>
      <button class="btn" type="submit">Create</button>
    </form>
  </div>
</div>




<!-- ✅ Executives Tab Content -->
<div id="Executives" class="tab-content">
  <h2>Executives</h2>

  <!-- Subtabs -->
  <div class="tabs" style="border-bottom: 1px solid #003DA5;">
    <button class="subtablink active" data-subtab="DirectorTypes">Directors</button>
    <button class="subtablink" data-subtab="DirectorUsers">Director Users</button>
  </div>

  <!-- ✅ Add Director Type Modal -->
  <div id="addDirectorTypeModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Add Director Type</h3>
        <span class="close" onclick="document.getElementById('addDirectorTypeModal').style.display='none'">&times;</span>
      </div>
      <form method="POST" action="{{ route('activities-admin.create-director') }}">
        @csrf
        <label>Director Type</label>
        <input type="text" name="type" required><br/>
        <button type="submit" class="btn">Add Director Type</button>
      </form>
    </div>
  </div>

  <!-- ✅ Add Director User Modal -->
  <div id="addDirectorUserModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Add Director User</h3>
        <span class="close" onclick="document.getElementById('addDirectorUserModal').style.display='none'">&times;</span>
      </div>
      <form method="POST" action="{{ route('activities-admin.create-director-user') }}">
        @csrf
        <label>Director Type</label>
        <select name="director_id" required>
          @foreach ($directors as $director)
            <option value="{{ $director['id'] }}">{{ $director['type'] }}</option>
          @endforeach
        </select><br/>
        <label>First Name</label><input type="text" name="first_name" required><br/>
        <label>Middle Name</label><input type="text" name="middle_name"><br/>
        <label>Last Name</label><input type="text" name="last_name" required><br/>
        <label>Suffix</label><input type="text" name="suffix"><br/>
        <label>Email</label><input type="email" name="email" required><br/>
        <button type="submit" class="btn">Add Director User</button>
      </form>
    </div>
  </div>

  <!-- ✅ Directors Subtab -->
  <div id="DirectorTypes" class="subtab-content active">
    <button class="btn" onclick="document.getElementById('addDirectorTypeModal').style.display='block'">Add Director Type</button>
    <table>
      <thead>
        <tr><th>Type</th><th>Created At</th></tr>
      </thead>
      <tbody>
        @foreach ($directors as $director)
          <tr>
            <td>{{ $director['type'] }}</td>
            <td>{{ $director['created_at'] ?? 'N/A' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- ✅ Director Users Subtab -->
  <div id="DirectorUsers" class="subtab-content">
    <button class="btn" onclick="document.getElementById('addDirectorUserModal').style.display='block'">Add Director User</button>
    <table>
      <thead>
        <tr>
          <th>Director Type</th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Created At</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($directorUsers as $user)
          <tr>
            <td>{{ $user['director_type'] }}</td>
            <td>{{ $user['first_name'] }} {{ $user['middle_name'] ?? '' }} {{ $user['last_name'] }} {{ $user['suffix'] ?? '' }}</td>
            <td>{{ $user['email'] }}</td>
            <td>{{ $user['created_at'] ?? 'N/A' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
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

    // Save active main tab to localStorage
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

    // Save active subtab to localStorage
    localStorage.setItem('activeSubtab', subtab.dataset.subtab);
  });
});

// On page load, restore active tabs from localStorage
document.addEventListener('DOMContentLoaded', () => {
  const savedTab = localStorage.getItem('activeTab');
  if (savedTab) {
    const tabToActivate = document.querySelector(`.tablink[data-tab="${savedTab}"]`);
    if (tabToActivate) {
      tabToActivate.click();
    } else {
      tabs[0].click();
    }
  } else {
    tabs[0].click();
  }

  const savedSubtab = localStorage.getItem('activeSubtab');
  if (savedSubtab) {
    const subtabToActivate = document.querySelector(`.subtablink[data-subtab="${savedSubtab}"]`);
    if (subtabToActivate) {
      subtabToActivate.click();
    } else if (subtabs.length > 0) {
      subtabs[0].click();
    }
  } else if (subtabs.length > 0) {
    subtabs[0].click();
  }
});


  document.getElementById('school-select').addEventListener('change', function () {
    const selectedSchool = this.value;
    const programOptions = document.querySelectorAll('#program-select option');

    programOptions.forEach(option => {
      if (option.value === "") {
        option.style.display = "block"; 
      } else if (option.dataset.school === selectedSchool) {
        option.style.display = "block";
      } else {
        option.style.display = "none";
      }
    });

    document.getElementById('program-select').value = "";
  });


  function addProgramField() {
  const container = document.getElementById('programs-container');
  const newField = document.createElement('div');
  newField.innerHTML = '<input type="text" name="programs[]" required> <button type="button" onclick="this.parentNode.remove()">Remove</button><br/>';
  container.appendChild(newField);
}

document.getElementById('department-school').addEventListener('change', function () {
  const selected = this.value;
  const options = document.querySelectorAll('#department-program option');
  options.forEach(opt => {
    if (opt.value === "") { opt.style.display = "block"; }
    else if (opt.dataset.school === selected) { opt.style.display = "block"; }
    else { opt.style.display = "none"; }
  });
  document.getElementById('department-program').value = "";
});

document.getElementById('dep-school').addEventListener('change', function () {
  const selected = this.value;
  const options = document.querySelectorAll('#dep-program option');
  options.forEach(opt => {
    if (opt.value === "") { opt.style.display = "block"; }
    else if (opt.dataset.school === selected) { opt.style.display = "block"; }
    else { opt.style.display = "none"; }
  });
  document.getElementById('dep-program').value = "";
});

  const execSubtabLinks = document.querySelectorAll('#Executives .subtablink');
  const execSubtabContents = document.querySelectorAll('#Executives .subtab-content');

  execSubtabLinks.forEach(btn => {
    btn.addEventListener('click', function() {
      execSubtabLinks.forEach(b => b.classList.remove('active'));
      execSubtabContents.forEach(c => c.classList.remove('active'));

      this.classList.add('active');
      const target = this.getAttribute('data-subtab');
      document.getElementById(target).classList.add('active');
    });
  });

function toggleAcademicFields() {
  // get which is checked
  const selected = document.querySelector('input[name="type"]:checked').value;

  if (selected === 'Academic') {
    document.getElementById('academic-fields').style.display = 'block';
  } else {
    document.getElementById('academic-fields').style.display = 'none';
  }
}

function showOrgSubtab(type) {
  // hide both
  document.getElementById('AcademicOrgs').style.display = 'none';
  document.getElementById('NonAcademicOrgs').style.display = 'none';

  // show selected
  if (type === 'Academic') {
    document.getElementById('AcademicOrgs').style.display = 'block';
  } else {
    document.getElementById('NonAcademicOrgs').style.display = 'block';
  }

  // update active class
  document.querySelectorAll('.org-filter-btn').forEach(btn => btn.classList.remove('active'));
  document.querySelectorAll('.org-filter-btn').forEach(btn => {
    if (btn.textContent.trim() === 'Academic' && type === 'Academic') {
      btn.classList.add('active');
    }
    if (btn.textContent.trim() === 'Non-Academic' && type === 'NonAcademic') {
      btn.classList.add('active');
    }
  });
}

function toggleOrgAccountFields() {
  const selected = document.querySelector('input[name="account_type"]:checked').value;

  if (selected === 'President') {
    document.getElementById('president-fields').style.display = 'block';
    document.getElementById('adviser-fields').style.display = 'none';
  } else {
    document.getElementById('president-fields').style.display = 'none';
    document.getElementById('adviser-fields').style.display = 'block';
  }
}


</script>

</body>
</html>
