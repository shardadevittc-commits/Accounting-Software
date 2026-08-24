@extends('admin.layouts.app')

@section('title', 'Role & Permission Management - Tixx Accounts ERP')

@section('content')
<div class="roles-page-wrapper">
    <!-- Page Header & Action Bar -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-extrabold text-dark fs-4 mb-0">Roles & Permissions Management</h2>
            <small class="text-muted fs-7"><i class="fa-solid fa-user-shield me-1 text-primary"></i> Enterprise Role-Based Access Control (RBAC)</small>
        </div>

        @permission('roles.create')
        <a href="{{ route('roles.create') }}" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold fs-7 shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Create New Role
        </a>
        @endpermission
    </div>

    <!-- Alert Notifications -->
    @include('admin.layouts.partials.alerts')

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="kpi-title">TOTAL ROLES</span>
                    <div class="kpi-icon-badge bg-primary-subtle text-primary">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>
                <div class="kpi-amount">{{ $stats['total_roles'] }}</div>
                <div class="kpi-subtext text-secondary mt-1">Configured in system</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="kpi-title">ACTIVE ROLES</span>
                    <div class="kpi-icon-badge bg-success-subtle text-success">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <div class="kpi-amount text-success">{{ $stats['active_roles'] }}</div>
                <div class="kpi-subtext text-secondary mt-1">Available for assignment</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="kpi-title">SYSTEM ROLES</span>
                    <div class="kpi-icon-badge bg-warning-subtle text-warning">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                </div>
                <div class="kpi-amount text-warning">{{ $stats['system_roles'] }}</div>
                <div class="kpi-subtext text-secondary mt-1">Protected default roles</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card h-100 border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="kpi-title">CUSTOM ROLES</span>
                    <div class="kpi-icon-badge bg-info-subtle text-info">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                </div>
                <div class="kpi-amount text-info">{{ $stats['custom_roles'] }}</div>
                <div class="kpi-subtext text-secondary mt-1">User-created roles</div>
            </div>
        </div>
    </div>

    <!-- Filters & Search Bar Card -->
    <div class="erp-card mb-4">
        <form action="{{ route('roles.index') }}" method="GET" class="row g-3 align-items-center">
            <!-- Search Box -->
            <div class="col-lg-5">
                <div class="search-box-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" class="top-search-input py-2" placeholder="Search by role name, slug, or description..." value="{{ $search }}">
                </div>
            </div>

            <!-- Status Filter -->
            <div class="col-lg-3">
                <select name="status" class="form-select profile-custom-input py-2 fs-7">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Module Filter -->
            <div class="col-lg-3">
                <select name="module" class="form-select profile-custom-input py-2 fs-7">
                    <option value="">All Permission Modules</option>
                    @foreach($modules as $moduleName)
                        <option value="{{ $moduleName }}" {{ $module === $moduleName ? 'selected' : '' }}>{{ $moduleName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Submit & Reset Buttons -->
            <div class="col-lg-1 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm py-2 px-3 rounded-3" title="Filter Results">
                    <i class="fa-solid fa-filter"></i>
                </button>
                @if($search || $status || $module)
                    <a href="{{ route('roles.index') }}" class="btn btn-light border btn-sm py-2 px-3 rounded-3 text-secondary" title="Clear Filters">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Roles Grid / Table Card -->
    <div class="erp-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-7">
                <thead class="table-light">
                    <tr>
                        <th>Role Name & Identifier</th>
                        <th>Description</th>
                        <th>Users</th>
                        <th>Permissions</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="badge-icon p-2 rounded-3 {{ $role->is_system ? 'bg-warning-subtle text-warning' : 'bg-primary-subtle text-primary' }}">
                                    <i class="fa-solid {{ $role->is_system ? 'fa-shield-halved' : 'fa-user-gear' }}"></i>
                                </div>
                                <div>
                                    <a href="{{ route('roles.show', $role->id) }}" class="fw-bold text-dark text-decoration-none fs-7 hover-primary">
                                        {{ $role->name }}
                                    </a>
                                    <small class="d-block text-muted font-monospace fs-8">{{ $role->slug }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-secondary fs-8 d-inline-block text-truncate" style="max-width: 280px;">
                                {{ $role->description ?? 'No description provided.' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1">
                                <i class="fa-solid fa-users me-1 fs-8"></i> {{ $role->users_count }} Users
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info rounded-pill px-2.5 py-1">
                                <i class="fa-solid fa-key me-1 fs-8"></i> {{ $role->permissions_count }} Permissions
                            </span>
                        </td>
                        <td>
                            @if($role->is_system)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2">
                                    <i class="fa-solid fa-lock me-1"></i> System
                                </span>
                            @else
                                <span class="badge bg-light text-dark border rounded-pill px-2">
                                    Custom
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($role->status === 'active')
                                <span class="badge bg-success-subtle text-success rounded-pill px-2">
                                    <i class="fa-solid fa-circle-check me-1"></i> Active
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                <!-- View Details -->
                                <a href="{{ route('roles.show', $role->id) }}" class="btn btn-sm btn-light border text-secondary" title="View Details">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                <!-- Edit Role -->
                                @permission('roles.edit')
                                <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-light border text-primary" title="Edit Role & Permissions">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                @endpermission

                                <!-- Delete Role -->
                                @permission('roles.delete')
                                @if(!$role->is_system)
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete role [{{ $role->name }}]?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete Role">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endif
                                @endpermission
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fs-2 mb-2 text-secondary opacity-50 d-block"></i>
                            No roles found matching your search criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($roles->hasPages())
        <div class="pt-3 mt-2 border-top">
            {{ $roles->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection
