<!-- list-admins.blade.php -->
<h1>All Admins</h1>
<a href="{{ route('superadmin.admins.create') }}">Add New Admin</a>
<table>
    <thead>
        <tr>
            <th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Email</th><th>Role</th><th>Created At</th>
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
            </tr>
        @endforeach
    </tbody>
</table>
