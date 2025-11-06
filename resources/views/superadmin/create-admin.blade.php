<!-- create-admin.blade.php -->
<h1>Add New Admin</h1>
<form method="POST" action="{{ route('superadmin.admins.store') }}">
    @csrf
    <label>First Name</label><input name="first_name" required><br>
    <label>Middle Name</label><input name="middle_name" required><br>
    <label>Last Name</label><input name="last_name" required><br>
    <label>Email</label><input name="email" type="email" required><br>
    <label>Role</label>
    <select name="role" required>
        <option value="SDA Coordinator">SDA Coordinator</option>
        <option value="SDA Associate">SDA Associate</option>
        <option value="SDA Assistant">SDA Assistant</option>
    </select><br>
    <button type="submit">Create Admin</button>
</form>
