@extends('admin.layouts.app')

@section('title', 'Role Details: ' . $role->name . ' - Tixx Accounts ERP')

@section('content')
<div class="role-show-page-wrapper">
    <!-- Page Header & Action Bar -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h2 class="fw-extrabold text-dark fs-4 mb-0">{{ $role->name }}</h2>
                <span class="badge bg-light text-dark font-monospace border px-2 py-1 fs-8">{{ $role->slug }}</span>
                @if($role->is_system)
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fs-8">
                        <i class="fa-solid fa-lock me-1"></i> System Role Protected
                    </span>
                @else
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1 fs-8">Custom Role</span>
                @endif

                @if($role->status === 'active')
                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fs-8">
                        <i class="fa-solid fa-circle-check me-1"></i> Active
                    </span>
                @else
                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1 fs-8">Inactive</span>
                @endif
            </div>
            <p class="text-muted fs-7 mb-0">{{ $role->description ?? 'No description provided for this role.' }}</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('roles.index') }}" class="btn btn-light border btn-sm rounded-3 px-3 py-2 fw-semibold text-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Roles
            </a>

            @permission('roles.edit')
            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary btn-sm rounded-3 px-3 py-2 fw-semibold shadow-sm">
                <i class="fa-solid fa-pen-to-square me-1"></i> Edit Role & Permissions
            </a>
            @endpermission
        </div>
    </div>

    <!-- Alert Notifications -->
    @include('admin.layouts.partials.alerts')

    <!-- KPI Statistics Header Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="kpi-card h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="kpi-title">ASSIGNED USERS</span>
                    <div class="kpi-icon-badge bg-primary-subtle text-primary">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="kpi-amount">{{ $role->users->count() }}</div>
                <div class="kpi-subtext text-secondary mt-1">Users holding this role</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="kpi-card h-100 border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="kpi-title">GRANTED PERMISSIONS</span>
                    <div class="kpi-icon-badge bg-info-subtle text-info">
                        <i class="fa-solid fa-key"></i>
                    </div>
                </div>
                <div class="kpi-amount text-info">{{ $role->permissions->count() }}</div>
                <div class="kpi-subtext text-secondary mt-1">Active permission abilities</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="kpi-card h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="kpi-title">MODULES COVERED</span>
                    <div class="kpi-icon-badge bg-warning-subtle text-warning">
                        <i class="fa-solid fa-cubes"></i>
                    </div>
                </div>
                <div class="kpi-amount text-warning">{{ $groupedPermissions->count() }}</div>
                <div class="kpi-subtext text-secondary mt-1">Functional ERP modules</div>
            </div>
        </div>
    </div>

    <!-- SIDE BY SIDE: ASSIGNED PERMISSIONS & ASSIGNED USERS -->
    <div class="row g-4 mb-4">
        <!-- COLUMN 1: ASSIGNED PERMISSIONS BY MODULE -->
        <div class="col-lg-7">
            <div class="profile-card h-100">
                <div class="profile-card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="profile-card-title"><i class="fa-solid fa-shield-cat text-primary me-2"></i> Granted Module Permissions</h5>
                        <p class="profile-card-subtitle">Permissions associated with role [{{ $role->name }}]</p>
                    </div>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fs-8">
                        {{ $role->permissions->count() }} Active Permissions
                    </span>
                </div>

                <div class="profile-card-body">
                    @forelse($groupedPermissions as $moduleName => $permissions)
                    <div class="p-3 border rounded-3 bg-light-subtle mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold text-dark mb-0 fs-7">
                                <i class="fa-solid fa-layer-group me-1 text-primary"></i> {{ $moduleName }}
                            </h6>
                            <span class="badge bg-light text-muted border fs-8">{{ count($permissions) }} Granted</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1.5">
                            @foreach($permissions as $permission)
                                <span class="badge bg-white text-dark border px-2.5 py-1.5 fs-8 shadow-xs d-inline-flex align-items-center gap-1">
                                    <i class="fa-solid fa-check text-success fs-8"></i> {{ $permission->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted fs-7">
                        <i class="fa-solid fa-lock-open fs-3 mb-2 opacity-50 d-block"></i>
                        No permissions currently assigned to this role.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- COLUMN 2: ASSIGNED USERS LIST -->
        <div class="col-lg-5">
            <div class="profile-card h-100">
                <div class="profile-card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="profile-card-title"><i class="fa-solid fa-users text-success me-2"></i> Users Assigned</h5>
                        <p class="profile-card-subtitle">Users assigned to [{{ $role->name }}]</p>
                    </div>
                    @permission('users.view')
                    <a href="{{ route('users.roles') }}" class="fs-8 text-primary fw-semibold text-decoration-none">
                        Manage Users <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    @endpermission
                </div>

                <div class="profile-card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($role->users as $user)
                        <div class="list-group-item d-flex align-items-center justify-content-between px-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $user->avatar_url }}" class="user-initials-avatar" style="width:38px; height:38px; object-fit:cover;" alt="User Avatar">
                                <div>
                                    <div class="fw-bold text-dark fs-7">{{ $user->name }}</div>
                                    <small class="text-muted fs-8">{{ $user->email }}</small>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success rounded-pill px-2 fs-8">Assigned</span>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted fs-7">
                            <i class="fa-solid fa-user-slash fs-3 mb-2 opacity-50 d-block"></i>
                            No users currently hold this role.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
