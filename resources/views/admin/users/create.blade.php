@extends('admin.layouts.app')

@section('title', 'Add New User & Configure Permissions - Accounts ERP')

@section('content')
<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom">
    <div>
        <h2 class="fw-extrabold text-dark fs-4 mb-0">Add New System User</h2>
        <small class="text-muted fs-7"><i class="fa-solid fa-user-plus text-primary me-1"></i> Create user credentials, assign role, and customize access permissions</small>
    </div>
    <div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3 py-2 fw-semibold fs-7">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Users List
        </a>
    </div>
</div>

<!-- Validation Error Messages -->
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <div class="fw-bold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> Please correct the following errors:</div>
        <ul class="mb-0 ps-3 fs-7">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('users.store') }}" method="POST" id="createUserForm">
    @csrf

    <div class="row g-4">
        <!-- Left Column: User Credentials & Role Selection -->
        <div class="col-lg-4">
            <!-- Account Information Card -->
            <div class="erp-card mb-4">
                <h5 class="fw-bold text-dark fs-6 mb-3 border-bottom pb-2">
                    <i class="fa-solid fa-id-card text-primary me-2"></i> Account Information
                </h5>

                <!-- Full Name -->
                <div class="mb-3">
                    <label class="form-label fw-bold fs-7 text-uppercase letter-spacing-1 text-secondary">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control rounded-3 fs-7" placeholder="e.g. Rahul Sharma" value="{{ old('name') }}" required>
                </div>

                <!-- Username -->
                <div class="mb-3">
                    <label class="form-label fw-bold fs-7 text-uppercase letter-spacing-1 text-secondary">Username <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light fs-7">@</span>
                        <input type="text" name="username" class="form-control rounded-3 fs-7" placeholder="e.g. rahul_sharma" value="{{ old('username') }}" required>
                    </div>
                </div>

                <!-- Email Address -->
                <div class="mb-3">
                    <label class="form-label fw-bold fs-7 text-uppercase letter-spacing-1 text-secondary">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control rounded-3 fs-7" placeholder="e.g. rahul@company.com" value="{{ old('email') }}" required>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label fw-bold fs-7 text-uppercase letter-spacing-1 text-secondary">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control rounded-3 fs-7" placeholder="Minimum 6 characters" required>
                </div>

                <!-- Password Confirmation -->
                <div class="mb-3">
                    <label class="form-label fw-bold fs-7 text-uppercase letter-spacing-1 text-secondary">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control rounded-3 fs-7" placeholder="Re-type password" required>
                </div>
            </div>

            <!-- Role Assignment Card -->
            <div class="erp-card">
                <h5 class="fw-bold text-dark fs-6 mb-3 border-bottom pb-2">
                    <i class="fa-solid fa-shield-halved text-warning me-2"></i> Role Assignment
                </h5>

                <div class="mb-3">
                    <label class="form-label fw-bold fs-7 text-uppercase letter-spacing-1 text-secondary">Select Primary Role <span class="text-danger">*</span></label>
                    <select name="role_id" id="roleSelect" class="form-select rounded-3 fs-7 fw-semibold border-primary-subtle" required>
                        <option value="">-- Choose Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }} {{ $role->is_system ? '(System Role)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted fs-8 mt-1 d-block">Selecting a role will automatically pre-check the default permissions in the grid on the right.</small>
                </div>

                <!-- Role Info Box -->
                <div id="roleInfoBox" class="alert alert-info border-0 rounded-3 p-3 mb-0 fs-8 d-none">
                    <div class="fw-bold mb-1" id="roleInfoName">Selected Role</div>
                    <div class="text-secondary" id="roleInfoDesc">Role description will appear here.</div>
                </div>
            </div>
        </div>

        <!-- Right Column: Interactive Permissions Grid -->
        <div class="col-lg-8">
            <div class="erp-card">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 border-bottom pb-2">
                    <div>
                        <h5 class="fw-bold text-dark fs-6 mb-0">
                            <i class="fa-solid fa-sliders text-success me-2"></i> Access Permissions Matrix
                        </h5>
                        <small class="text-muted fs-8">Role pre-selects default permissions. You can check or uncheck individual boxes to customize user access.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-light border rounded-3 fs-8 fw-semibold" id="btnSelectAll">
                            <i class="fa-solid fa-check-double text-success me-1"></i> Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-light border rounded-3 fs-8 fw-semibold" id="btnUnselectAll">
                            <i class="fa-solid fa-xmark text-danger me-1"></i> Clear All
                        </button>
                    </div>
                </div>

                <!-- Grouped Permissions Grid -->
                <div class="permissions-matrix-wrapper">
                    @foreach($groupedPermissions as $module => $modulePermissions)
                        <div class="module-permission-card border rounded-3 p-3 mb-3 bg-light-subtle">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                <div class="fw-bold text-dark fs-7 text-uppercase letter-spacing-1">
                                    <i class="fa-solid fa-folder-closed text-primary me-2"></i> {{ $module }}
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input type="checkbox" class="form-check-input module-select-all cursor-pointer me-1" role="switch" id="mod_{{ Str::slug($module) }}" data-module="{{ Str::slug($module) }}">
                                    <label class="form-check-label fs-8 text-muted cursor-pointer" for="mod_{{ Str::slug($module) }}">Select Module</label>
                                </div>
                            </div>
                            <div class="row g-2">
                                @foreach($modulePermissions as $perm)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="permission-item-card p-3 bg-white d-flex align-items-center justify-content-between gap-2 h-100">
                                            <div>
                                                <label class="form-check-label fs-8 text-dark fw-bold mb-0 cursor-pointer d-block" for="perm_{{ $perm->id }}">
                                                    {{ $perm->name }}
                                                </label>
                                                <small class="text-muted fs-9 d-block leading-snug">{{ $perm->description }}</small>
                                            </div>
                                            <div class="form-check form-switch m-0 p-0 fs-5 flex-shrink-0">
                                                <input class="form-check-input perm-checkbox module-{{ Str::slug($module) }} cursor-pointer m-0" 
                                                       type="checkbox" 
                                                       role="switch"
                                                       name="permissions[]" 
                                                       value="{{ $perm->id }}" 
                                                       id="perm_{{ $perm->id }}"
                                                       {{ (is_array(old('permissions')) && in_array($perm->id, old('permissions'))) ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Submit Action Footer -->
                <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('users.index') }}" class="btn btn-light border px-4 py-2 rounded-3 fs-7 fw-semibold">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fs-7 fw-bold shadow-sm">
                        <i class="fa-solid fa-check me-1"></i> Create User & Save Permissions
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var roleSelect = document.getElementById('roleSelect');
    var roleInfoBox = document.getElementById('roleInfoBox');
    var roleInfoName = document.getElementById('roleInfoName');
    var roleInfoDesc = document.getElementById('roleInfoDesc');

    // Role Data map
    var rolesData = {
        @foreach($roles as $r)
            "{{ $r->id }}": {
                name: @json($r->name),
                description: @json($r->description),
                permissions: @json($r->permissions->pluck('id'))
            },
        @endforeach
    };

    // Auto Check Permissions when Role is Selected
    roleSelect.addEventListener('change', function() {
        var selectedRoleId = this.value;
        
        // Uncheck all checkboxes first
        document.querySelectorAll('.perm-checkbox').forEach(function(cb) {
            cb.checked = false;
        });

        if (selectedRoleId && rolesData[selectedRoleId]) {
            var roleInfo = rolesData[selectedRoleId];
            
            // Show Role Info Box
            roleInfoName.textContent = roleInfo.name;
            roleInfoDesc.textContent = roleInfo.description || 'Custom role permissions set.';
            roleInfoBox.classList.remove('d-none');

            // Pre-check permissions of this role
            roleInfo.permissions.forEach(function(permId) {
                var cb = document.getElementById('perm_' + permId);
                if (cb) {
                    cb.checked = true;
                }
            });
        } else {
            roleInfoBox.classList.add('d-none');
        }

        updateModuleSelectAllStates();
    });

    // Select All Global
    document.getElementById('btnSelectAll').addEventListener('click', function() {
        document.querySelectorAll('.perm-checkbox').forEach(function(cb) {
            cb.checked = true;
        });
        updateModuleSelectAllStates();
    });

    // Unselect All Global
    document.getElementById('btnUnselectAll').addEventListener('click', function() {
        document.querySelectorAll('.perm-checkbox').forEach(function(cb) {
            cb.checked = false;
        });
        updateModuleSelectAllStates();
    });

    // Select All per Module
    document.querySelectorAll('.module-select-all').forEach(function(modCb) {
        modCb.addEventListener('change', function() {
            var slug = this.getAttribute('data-module');
            var isChecked = this.checked;
            document.querySelectorAll('.module-' + slug).forEach(function(cb) {
                cb.checked = isChecked;
            });
        });
    });

    function updateModuleSelectAllStates() {
        document.querySelectorAll('.module-select-all').forEach(function(modCb) {
            var slug = modCb.getAttribute('data-module');
            var moduleCbs = document.querySelectorAll('.module-' + slug);
            var checkedCount = document.querySelectorAll('.module-' + slug + ':checked').length;
            modCb.checked = (moduleCbs.length > 0 && checkedCount === moduleCbs.length);
        });
    }

    // Trigger role select change on load if old role selected
    if (roleSelect.value) {
        roleSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection
