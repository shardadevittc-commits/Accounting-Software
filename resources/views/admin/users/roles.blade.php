@extends('admin.layouts.app')

@section('title', 'User Role Assignment - Accounts ERP')

@section('content')
<div class="user-roles-page-wrapper">
    <!-- Page Header & Action Bar -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-extrabold text-dark fs-4 mb-0">User Role & Access Assignment</h2>
            <small class="text-muted fs-7"><i class="fa-solid fa-user-shield me-1 text-primary"></i> Assign single or multiple roles to system users</small>
        </div>

        <a href="{{ route('roles.index') }}" class="btn btn-light border btn-sm rounded-3 px-3 py-2 fw-semibold text-secondary">
            <i class="fa-solid fa-shield-halved me-1"></i> Manage Roles
        </a>
    </div>

    <!-- Alert Notifications -->
    @include('admin.layouts.partials.alerts')

    <div class="row g-4">
        <!-- COLUMN 1: USER SELECTION LIST -->
        <div class="col-lg-4">
            <div class="profile-card h-100">
                <div class="profile-card-header pb-3">
                    <h5 class="profile-card-title"><i class="fa-solid fa-users text-primary me-2"></i> System Users</h5>
                    <p class="profile-card-subtitle">Select a user to manage assigned roles</p>
                    
                    <form action="{{ route('users.roles') }}" method="GET" class="mt-3">
                        <div class="search-box-wrapper">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" name="search" class="top-search-input py-1.5 fs-7" placeholder="Search user by name or email..." value="{{ $search }}">
                        </div>
                    </form>
                </div>

                <div class="profile-card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 520px; overflow-y: auto;">
                        @forelse($users as $user)
                        @php
                            $isSelected = $selectedUser && $selectedUser->id === $user->id;
                        @endphp
                        <a href="{{ route('users.roles', ['user_id' => $user->id, 'search' => $search]) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-3 py-3 {{ $isSelected ? 'bg-primary-subtle border-start border-4 border-primary fw-bold' : '' }}">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $user->avatar_url }}" class="user-initials-avatar" style="width:36px; height:36px; object-fit:cover;" alt="Avatar">
                                <div>
                                    <div class="text-dark fs-7 mb-0">{{ $user->name }}</div>
                                    <small class="text-muted fs-8 text-break d-block">{{ $user->email }}</small>
                                </div>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 fs-8">
                                {{ $user->roles->count() }} Role(s)
                            </span>
                        </a>
                        @empty
                        <div class="text-center py-4 text-muted fs-7">
                            No users found.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMN 2: ROLE ASSIGNMENT & EFFECTIVE PERMISSIONS FORM -->
        <div class="col-lg-8">
            @if($selectedUser)
            <div class="profile-card mb-4">
                <div class="profile-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $selectedUser->avatar_url }}" class="user-initials-avatar" style="width:46px; height:46px; object-fit:cover;" alt="Selected Avatar">
                        <div>
                            <h5 class="profile-card-title mb-0">{{ $selectedUser->name }}</h5>
                            <span class="text-muted fs-7">{{ $selectedUser->email }}</span>
                        </div>
                    </div>

                    @if($selectedUser->isSuperAdmin())
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fs-7">
                            <i class="fa-solid fa-crown me-1"></i> Admin (Unrestricted Access)
                        </span>
                    @endif
                </div>

                <div class="profile-card-body">
                    <form action="{{ route('users.roles.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">

                        <div class="mb-4">
                            <label class="profile-field-label">SELECT ASSIGNED ROLES</label>
                            <small class="text-muted fs-8 d-block mb-3">Check one or multiple roles to grant user access abilities.</small>

                            <div class="row g-3">
                                @foreach($roles as $role)
                                @php
                                    $isUserRole = $selectedUser->roles->contains('id', $role->id);
                                @endphp
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 bg-light-subtle h-100 d-flex align-items-start justify-content-between hover-border-primary">
                                        <div class="me-2">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <label class="form-check-label fw-bold text-dark fs-7 cursor-pointer" for="userRole_{{ $role->id }}">
                                                    {{ $role->name }}
                                                </label>
                                                @if($role->is_system)
                                                    <span class="badge bg-warning-subtle text-warning fs-8 rounded-pill">System</span>
                                                @endif
                                            </div>
                                            <small class="text-muted fs-8 d-block">{{ $role->description ?? 'No description.' }}</small>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="userRole_{{ $role->id }}" {{ $isUserRole ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-2 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold rounded-3 shadow-sm">
                                <i class="fa-solid fa-check me-2"></i> Update User Roles
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- EFFECTIVE PERMISSIONS BREAKDOWN CARD -->
            <div class="profile-card">
                <div class="profile-card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="profile-card-title"><i class="fa-solid fa-key text-success me-2"></i> Effective Permissions Summary</h5>
                        <p class="profile-card-subtitle">Combined permissions granted to [{{ $selectedUser->name }}] through assigned roles</p>
                    </div>
                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fs-8">
                        {{ $selectedUser->isSuperAdmin() ? 'All Permissions Granted' : $selectedUser->allPermissions()->count() . ' Effective Permissions' }}
                    </span>
                </div>

                <div class="profile-card-body">
                    @forelse($effectivePermissions as $modName => $perms)
                    <div class="p-3 border rounded-3 bg-light-subtle mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold text-dark mb-0 fs-7">
                                <i class="fa-solid fa-layer-group me-1 text-primary"></i> {{ $modName }}
                            </h6>
                            <span class="badge bg-light text-muted border fs-8">{{ count($perms) }} Effective</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1.5">
                            @foreach($perms as $p)
                                <span class="badge bg-white text-dark border px-2.5 py-1.5 fs-8 shadow-xs d-inline-flex align-items-center gap-1">
                                    <i class="fa-solid fa-circle-check text-success fs-8"></i> {{ $p->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted fs-7">
                        <i class="fa-solid fa-user-lock fs-3 mb-2 opacity-50 d-block"></i>
                        No roles or permissions currently assigned to this user.
                    </div>
                    @endforelse
                </div>
            </div>
            @else
            <div class="erp-card text-center py-5 text-muted">
                <i class="fa-solid fa-user-gear fs-2 mb-2 text-secondary opacity-50 d-block"></i>
                Select a user from the left column to view and edit role assignments.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
