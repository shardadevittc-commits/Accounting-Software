@extends('admin.layouts.app')

@section('title', 'User Management & Access Controls - Accounts ERP')

@section('content')
<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom">
    <div>
        <h2 class="fw-extrabold text-dark fs-4 mb-0">User Management</h2>
        <small class="text-muted fs-7"><i class="fa-solid fa-users text-primary me-1"></i> Manage system users, credentials, role assignments & custom permissions</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm rounded-3 px-3 py-2 fw-semibold fs-7 shadow-sm">
            <i class="fa-solid fa-user-plus me-1"></i> Add New User
        </a>
    </div>
</div>

<!-- Alert Notifications -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Summary KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <div class="kpi-title">Total Registered Users</div>
                <div class="kpi-amount">{{ $stats['total_users'] }}</div>
                <div class="kpi-subtext">Active ERP Accounts</div>
            </div>
            <div class="kpi-icon-badge bg-primary-subtle text-primary">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <div class="kpi-title">System Administrators</div>
                <div class="kpi-amount text-purple">{{ $stats['super_admins'] }}</div>
                <div class="kpi-subtext">Unrestricted System Access</div>
            </div>
            <div class="kpi-icon-badge bg-purple-subtle text-purple">
                <i class="fa-solid fa-user-shield"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <div class="kpi-title">Active System Roles</div>
                <div class="kpi-amount text-success">{{ $stats['active_roles'] }}</div>
                <div class="kpi-subtext">Configured Access Roles</div>
            </div>
            <div class="kpi-icon-badge bg-success-subtle text-success">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters & Search Toolbar -->
<div class="erp-card mb-4">
    <form action="{{ route('users.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="search-box-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" class="top-search-input" placeholder="Search by name, username, or email..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-4">
            <select name="role_id" class="form-select form-select-sm rounded-3 border-secondary-subtle fs-7" onchange="this.form.submit()">
                <option value="">-- Filter by Assigned Role --</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm rounded-3 w-100 fw-semibold fs-7">
                <i class="fa-solid fa-filter me-1"></i> Apply Filters
            </button>
            @if(request('search') || request('role_id'))
                <a href="{{ route('users.index') }}" class="btn btn-light border btn-sm rounded-3 fs-7" title="Clear Filters">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Users Table Card -->
<div class="erp-card p-0 overflow-hidden shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-uppercase fs-8 text-secondary border-bottom">
                <tr>
                    <th class="ps-4 py-3">User Details</th>
                    <th class="py-3">Username</th>
                    <th class="py-3">Email Address</th>
                    <th class="py-3">Assigned Role</th>
                    <th class="py-3">Custom Permissions</th>
                    <th class="pe-4 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="border-top-0 fs-7">
                @forelse($users as $u)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $u->avatar_url }}" class="rounded-circle border" width="38" height="38" alt="Avatar">
                                <div>
                                    <div class="fw-bold text-dark">{{ $u->name }}</div>
                                    @if($u->isSuperAdmin())
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2 fs-8">Admin</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3 fw-semibold text-secondary">
                            <code>{{ $u->username ?? '-' }}</code>
                        </td>
                        <td class="py-3 text-secondary">
                            {{ $u->email }}
                        </td>
                        <td class="py-3">
                            @forelse($u->roles as $r)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fs-8">
                                    <i class="fa-solid fa-shield-halved me-1"></i> {{ $r->name }}
                                </span>
                            @empty
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 fs-8">No Role</span>
                            @endforelse
                        </td>
                        <td class="py-3">
                            @if($u->directPermissions->count() > 0)
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fs-8" title="Custom Direct Permissions">
                                    +{{ $u->directPermissions->count() }} Custom Overrides
                                </span>
                            @else
                                <span class="text-muted fs-8">Standard Role Permissions</span>
                            @endif
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('users.edit', $u->id) }}" class="btn btn-sm btn-outline-primary rounded-2 px-2 py-1 fs-8" title="Edit User & Permissions">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </a>
                                @if(!$u->isSuperAdmin() && auth()->id() !== $u->id)
                                    <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete user [{{ $u->name }}]?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-2 px-2 py-1 fs-8" title="Delete User">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-users-slash fs-2 mb-2 text-secondary opacity-50"></i>
                            <p class="mb-0 fw-semibold">No system users found matching criteria.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    @if($users->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center">
            <div class="text-muted fs-7">
                Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
            </div>
            <div>
                {{ $users->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
