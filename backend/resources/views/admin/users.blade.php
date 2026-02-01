@extends('layouts.admin')

@section('page-title', 'Users')

@section('content')
    @if(session('success'))
        <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <ul style="margin: 0; padding-left: 1rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3>User Management</h3>
            <button type="button" class="btn btn-primary" onclick="openModal('addUserModal')">
                <i data-feather="user-plus" style="width: 16px; height: 16px;"></i>
                Add User
            </button>
        </div>
        <div class="card-body" style="padding: 0;">
            @if($users->isEmpty())
                <div style="text-align: center; padding: 3rem; color: #6b7280;">
                    <i data-feather="users" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>No users found</p>
                </div>
            @else
                <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <table class="table" style="width: 100%; min-width: 600px;">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div
                                                style="width: 40px; height: 40px; background: linear-gradient(135deg, #6366f1, #8b5cf6); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 500;">{{ $user->name ?? 'No Name' }}</div>
                                                <div style="font-size: 0.75rem; color: #6b7280;">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->username ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $user->role === 'admin' ? 'badge-approved' : 'badge-pending' }}">
                                            {{ ucfirst($user->role ?? 'user') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $user->is_active ? 'badge-approved' : 'badge-rejected' }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <button type="button" class="btn btn-ghost" style="padding: 0.5rem;"
                                                onclick="editUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->username }}', '{{ $user->role ?? 'user' }}', {{ $user->is_active ? 'true' : 'false' }})"
                                                title="Edit">
                                                <i data-feather="edit" style="width: 16px; height: 16px;"></i>
                                            </button>
                                            @if($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                                                    style="display: inline;"
                                                    onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-ghost" style="padding: 0.5rem; color: #dc2626;"
                                                        title="Delete">
                                                        <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div style="padding: 1rem; border-top: 1px solid #e5e7eb;">
                        {{ $users->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection

@section('modals')
    <!-- Add User Modal -->
    <div id="addUserModal" class="modal" style="display: none;">
        <div class="modal-overlay" onclick="closeModal('addUserModal')"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New User</h3>
                <button type="button" onclick="closeModal('addUserModal')" class="btn btn-ghost">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label
                            style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Name</label>
                        <input type="text" name="name" class="form-input" required placeholder="Full Name">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label
                            style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Email</label>
                        <input type="email" name="email" class="form-input" required placeholder="user@example.com">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label
                            style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Username</label>
                        <input type="text" name="username" class="form-input" required placeholder="username">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label
                            style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Password</label>
                        <input type="password" name="password" class="form-input" required minlength="6"
                            placeholder="Minimum 6 characters">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label
                            style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Role</label>
                        <select name="role" class="form-input">
                            <option value="viewer">Viewer</option>
                            <option value="editor">Editor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_active" id="addActive" value="1" checked>
                        <label for="addActive" style="font-size: 0.875rem; font-weight: 500;">Active Account</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('addUserModal')" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal" style="display: none;">
        <div class="modal-overlay" onclick="closeModal('editUserModal')"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit User</h3>
                <button type="button" onclick="closeModal('editUserModal')" class="btn btn-ghost">
                    <i data-feather="x"></i>
                </button>
            </div>
            <form method="POST" id="editUserForm" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label
                            style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Name</label>
                        <input type="text" name="name" id="editName" class="form-input" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label
                            style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Email</label>
                        <input type="email" name="email" id="editEmail" class="form-input" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label
                            style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Username</label>
                        <input type="text" name="username" id="editUsername" class="form-input" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">New
                            Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="form-input" minlength="6" placeholder="New password">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label
                            style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Role</label>
                        <select name="role" id="editRole" class="form-input">
                            <option value="viewer">Viewer</option>
                            <option value="editor">Editor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_active" id="editActive" value="1">
                        <label for="editActive" style="font-size: 0.875rem; font-weight: 500;">Active Account</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('editUserModal')" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('styles')
    <!-- Global Styles Used -->
@endsection

@section('scripts')
    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'block';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function editUser(id, name, email, username, role, isActive) {
            document.getElementById('editUserForm').action = "{{ url('admin/users') }}/" + id;
            document.getElementById('editName').value = name;
            document.getElementById('editEmail').value = email;
            document.getElementById('editUsername').value = username;
            document.getElementById('editRole').value = role;
            document.getElementById('editActive').checked = isActive;
            openModal('editUserModal');
        }
    </script>
@endsection