@extends('volunteers-offices.layout')

@section('content')



<div class="container">

  <!-- Main Tabs -->
  <div class="tabs" style="border-bottom: 2px solid #003DA5; margin-bottom: 1rem;">
    <button class="tablink active" data-tab="Admins">Admins</button>
    <button class="tablink" data-tab="Offices">Offices</button>
  </div>

  <!-- ======================= ADMINS TAB ======================= -->
  <div id="Admins" class="tab-content active">
    <h2>Admins</h2>

    <!-- Add Admin Form -->

    <div class="mb-3 d-flex justify-content-end">
  <button class="btn btn-primary" onclick="document.getElementById('addAdminModal').style.display='block'">
    Add New Admin
  </button>
</div>

    <!-- Admins Table -->
        <div class="mb-3 d-flex justify-content-end">
      <input type="text" id="searchAdmins" class="form-control w-50" placeholder="Search...">
    </div>

<table id="tableAdmins" class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>First Name</th>
      <th>Middle Name</th>
      <th>Last Name</th>
      <th>Email</th>
      <th>Role</th>
      <th>Created At</th>
      <th>Actions</th> <!-- New column -->
    </tr>
  </thead>
  <tbody>
    @foreach ($admins as $admin)
      <tr>
        <td>{{ $admin['first_name'] }}</td>
        <td>{{ $admin['middle_name'] }}</td>
        <td>{{ $admin['last_name'] }}</td>
        <td>{{ $admin['email'] }}</td>
        <td>{{ $admin['role'] }}</td>
        <td>{{ $admin['created_at'] }}</td>
        <td>
          <form method="POST" action="{{ route('volunteers-offices.revoke-office-user', $admin['email']) }}" style="display:inline;">
            @csrf
            @method('DELETE')
<button type="button" class="btn btn-sm btn-danger" onclick="confirmRevoke('{{ route('volunteers-offices.revoke-office-user', $admin['email']) }}')">Revoke</button>
          </form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
  </div>


  <!-- Add Admin Modal -->
<div id="addAdminModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Add New Admin</h3>
      <span class="close" onclick="document.getElementById('addAdminModal').style.display='none'">&times;</span>
    </div>
    <form method="POST" action="{{ route('volunteers-offices.store-admin') }}">
      @csrf
      <div class="mb-2">
        <label>First Name</label>
        <input name="first_name" class="form-control" required>
      </div>
      <div class="mb-2">
        <label>Middle Name</label>
        <input name="middle_name" class="form-control" required>
      </div>
      <div class="mb-2">
        <label>Last Name</label>
        <input name="last_name" class="form-control" required>
      </div>
      <div class="mb-2">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-2">
        <label>Role</label>
        <select name="role" class="form-control" required>
          <option value="SDA Coordinator">SDA Coordinator</option>
          <option value="SDA Associate">SDA Associate</option>
          <option value="SDA Assistant">SDA Assistant</option>
        </select>
      </div>
      <button class="mt-2 btn btn-primary" type="submit">Create Admin</button>
    </form>
  </div>
</div>


<div id="revokeModal" class="modal" style="display:none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Confirm Revoke</h3>
      <span class="close" onclick="closeRevokeModal()">&times;</span>
    </div>
    <div class="modal-body">
      <p>Are you sure you want to revoke this account?</p>
    </div>
    <div class="modal-footer">
      <form id="revokeForm" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Yes, Revoke</button>
        <button type="button" class="btn btn-secondary" onclick="closeRevokeModal()">Cancel</button>
      </form>
    </div>
  </div>
</div>


  <!-- ======================= OFFICES TAB ======================= -->
  <div id="Offices" class="tab-content">
    <h2>Offices</h2>

    <!-- Offices Subtabs -->
    <div class="tabs" style="border-bottom: 1px solid #003DA5; margin-bottom: 1rem;">
      <button class="subtablink active" data-subtab="OfficeTypes">Offices</button>
      <button class="subtablink" data-subtab="OfficeUsers">Users</button>
    </div>

    <!-- Office Types -->
    <div id="OfficeTypes" class="subtab-content active">
      <div class="mb-3 d-flex justify-content-end">
  <button class="btn btn-primary" onclick="document.getElementById('addOfficeModal').style.display='block'">
    Add Office
  </button>
</div>


      <div class="mb-3 d-flex justify-content-end">
        <input type="text" id="searchOffices" class="form-control w-50" placeholder="Search...">
      </div>

      <table id="tableOffices" class="table table-bordered table-striped">
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

      {{ $offices->links() }}
    </div>

    <!-- Office Users -->
    <div id="OfficeUsers" class="subtab-content">
      <div class="mb-3 d-flex justify-content-end">
  <button class="btn btn-primary" onclick="document.getElementById('addOfficeUserModal').style.display='block'">
    Add Office User
  </button>
</div>


      <div class="mb-3 d-flex justify-content-end">
        <input type="text" id="searchOfficeUsers" class="form-control w-50" placeholder="Search...">
      </div>

<table id="tableOfficeUsers" class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Office</th>
      <th>Full Name</th>
      <th>Email</th>
      <th>Created At</th>
      <th>Actions</th> <!-- New column -->
    </tr>
  </thead>
  <tbody>
    @foreach ($officeAccounts as $user)
      <tr>
        <td>{{ $user['office_name'] ?? 'N/A' }}</td>
        <td>{{ $user['last_name'] }}, {{ $user['first_name'] }} {{ $user['middle_name'] ?? '' }} {{ $user['suffix'] ?? '' }}</td>
        <td>{{ $user['email'] }}</td>
        <td>{{ $user['created_at'] ?? 'N/A' }}</td>
        <td>
          <form method="POST" action="{{ route('volunteers-offices.revoke-office-user', $user['email']) }}" style="display:inline;">
            @csrf
            @method('DELETE')
<button type="button" class="btn btn-sm btn-danger" onclick="confirmRevoke('{{ route('volunteers-offices.revoke-office-user', $user['email']) }}')">Revoke</button>
          </form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

      {{ $officeAccounts->links() }}
    </div>
  </div>
</div>

<!-- ======================= MODALS ======================= -->
<!-- Add Office Modal -->
<div id="addOfficeModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Add Office</h3>
      <span class="close" onclick="document.getElementById('addOfficeModal').style.display='none'">&times;</span>
    </div>
    <form method="POST" action="{{ route('volunteers-offices.create-office') }}">
      @csrf
      <label>Office Name</label>
      <input type="text" name="office_name" class="form-control" required />
      <button class="mt-2 btn btn-primary" type="submit">Add Office</button>
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
    <form method="POST" action="{{ route('volunteers-offices.create-office-account') }}">
      @csrf
      <label>Office</label>
      <select name="office_id" class="form-control" required>
        @foreach ($offices as $office)
          <option value="{{ $office['id'] }}">{{ $office['name'] }}</option>
        @endforeach
      </select>

      <label>First Name</label>
      <input type="text" name="first_name" class="form-control" required />
      <label>Middle Name</label>
      <input type="text" name="middle_name" class="form-control" />
      <label>Last Name</label>
      <input type="text" name="last_name" class="form-control" required />
      <label>Suffix</label>
      <input type="text" name="suffix" class="form-control" />
      <label>Email</label>
      <input type="email" name="email" class="form-control" required />
      <button class="mt-2 btn btn-primary" type="submit">Add Office User</button>
    </form>
  </div>
</div>

<!-- ======================= TAB SCRIPT ======================= -->
<script>
  // Main Tabs
  const tabs = document.querySelectorAll('.tablink');
  const contents = document.querySelectorAll('.tab-content');
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      contents.forEach(c => c.classList.remove('active'));
      tab.classList.add('active');
      document.getElementById(tab.dataset.tab).classList.add('active');
      localStorage.setItem('activeTab', tab.dataset.tab);
    });
  });
  const savedTab = localStorage.getItem('activeTab');
  if (savedTab) {
    tabs.forEach(t => t.classList.remove('active'));
    contents.forEach(c => c.classList.remove('active'));
    document.querySelector(`.tablink[data-tab="${savedTab}"]`).classList.add('active');
    document.getElementById(savedTab).classList.add('active');
  }

  // Subtabs
  const subtabs = document.querySelectorAll('.subtablink');
  const subcontents = document.querySelectorAll('.subtab-content');
  subtabs.forEach(subtab => {
    subtab.addEventListener('click', () => {
      subtabs.forEach(s => s.classList.remove('active'));
      subcontents.forEach(sc => sc.classList.remove('active'));
      subtab.classList.add('active');
      document.getElementById(subtab.dataset.subtab).classList.add('active');
      localStorage.setItem('activeSubtab', subtab.dataset.subtab);
    });
  });
  const savedSubtab = localStorage.getItem('activeSubtab');
  if (savedSubtab) {
    subtabs.forEach(s => s.classList.remove('active'));
    subcontents.forEach(sc => sc.classList.remove('active'));
    document.querySelector(`.subtablink[data-subtab="${savedSubtab}"]`).classList.add('active');
    document.getElementById(savedSubtab).classList.add('active');
  }

  // Office User Modal
  function openUserModal(userId) {
    const users = @json($officeAccounts);
    const user = users.find(u => u.id === userId);
    if (!user) return;
    document.getElementById('userName').textContent = `Name: ${user.first_name} ${user.middle_name ?? ''} ${user.last_name} ${user.suffix ?? ''}`;
    document.getElementById('userEmail').textContent = `Email: ${user.email}`;
    document.getElementById('userOffice').textContent = `Office: ${user.office_name}`;
    document.getElementById('viewUserModal').style.display = 'block';
  }



  function confirmRevoke(url) {
  const form = document.getElementById('revokeForm');
  form.action = url; // Set form action dynamically
  document.getElementById('revokeModal').style.display = 'block';
}

function closeRevokeModal() {
  document.getElementById('revokeModal').style.display = 'none';
}

</script>

@endsection

