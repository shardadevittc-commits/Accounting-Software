@extends('admin.layouts.app')

@section('title', 'Edit Role: ' . $role->name . ' - Tixx Accounts ERP')

@section('content')
<div class="role-form-page-wrapper">
    <!-- Page Header & Action Bar -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-extrabold text-dark fs-4 mb-0">
                Edit Role: {{ $role->name }}
            </h2>
            <small class="text-muted fs-7">
                <i class="fa-solid fa-shield-halved me-1 text-primary"></i> 
                Modify role details and permission matrix assignments
            </small>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('roles.show', $role->id) }}" class="btn btn-light border btn-sm rounded-3 px-3 py-2 fw-semibold text-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> View Details
            </a>
            <button type="submit" form="roleEditForm" class="btn btn-primary btn-sm rounded-3 px-3 py-2 fw-semibold shadow-sm">
                <i class="fa-solid fa-check me-1"></i> Update Role
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @include('admin.layouts.partials.alerts')

    <!-- Unsaved Changes Warning -->
    <div id="unsavedChangesWarning" class="alert alert-warning d-none align-items-center justify-content-between rounded-3 mb-4 py-2 px-3">
        <span><i class="fa-solid fa-triangle-exclamation me-2"></i> You have unsaved changes in permission assignments.</span>
        <button type="submit" form="roleEditForm" class="btn btn-warning btn-sm fw-bold">Update Role Now</button>
    </div>

    <form id="roleEditForm" action="{{ route('roles.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- ROLE INFORMATION CARD -->
        <div class="profile-card mb-4">
            <div class="profile-card-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="profile-card-title"><i class="fa-solid fa-id-card text-primary me-2"></i> Role Basic Details</h5>
                    <p class="profile-card-subtitle">Set role name, identifier slug, and description.</p>
                </div>
                @if($role->is_system)
                    <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-1 border border-warning-subtle fs-8">
                        <i class="fa-solid fa-lock me-1"></i> System Role Protected
                    </span>
                @endif
            </div>

            <div class="profile-card-body">
                <div class="row g-3">
                    <!-- Role Name -->
                    <div class="col-md-6">
                        <label for="name" class="profile-field-label">ROLE NAME <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            name="name" 
                            id="roleNameInput" 
                            class="form-control profile-custom-input @error('name') is-invalid @enderror" 
                            value="{{ old('name', $role->name) }}" 
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Role Slug -->
                    <div class="col-md-6">
                        <label for="slug" class="profile-field-label">ROLE SLUG / IDENTIFIER</label>
                        <input 
                            type="text" 
                            name="slug" 
                            id="roleSlugInput" 
                            class="form-control profile-custom-input @error('slug') is-invalid @enderror font-monospace" 
                            value="{{ old('slug', $role->slug) }}" 
                            {{ $role->is_system ? 'readonly' : '' }}
                        >
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-md-9">
                        <label for="description" class="profile-field-label">DESCRIPTION</label>
                        <input 
                            type="text" 
                            name="description" 
                            id="description" 
                            class="form-control profile-custom-input @error('description') is-invalid @enderror" 
                            value="{{ old('description', $role->description) }}" 
                            placeholder="Briefly describe responsibilities of this role..."
                        >
                    </div>

                    <!-- Status -->
                    <div class="col-md-3">
                        <label for="status" class="profile-field-label">STATUS <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select profile-custom-input" {{ $role->is_system ? 'disabled' : '' }}>
                            <option value="active" {{ old('status', $role->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $role->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @if($role->is_system)
                            <input type="hidden" name="status" value="active">
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- DYNAMIC PERMISSION ASSIGNMENT MATRIX CARD -->
        <div class="profile-card mb-4">
            <div class="profile-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="profile-card-title"><i class="fa-solid fa-key text-warning me-2"></i> Dynamic Permission Assignment Matrix</h5>
                    <p class="profile-card-subtitle">Select individual permissions or toggle module-level access.</p>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fs-7">
                        <i class="fa-solid fa-check-double me-1"></i>
                        <span id="selectedCountBadge">0</span> / {{ $groupedPermissions->flatten()->count() }} Selected
                    </span>
                </div>
            </div>

            <!-- Global Matrix Search & Select All Toolbar -->
            <div class="px-4 py-3 bg-light border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="selectAllGlobalToggle">
                        <label class="form-check-label fw-bold text-dark fs-7 cursor-pointer" for="selectAllGlobalToggle">
                            Select All Permissions Across Modules
                        </label>
                    </div>
                </div>

                <div class="search-box-wrapper" style="max-width: 320px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchPermissionsInput" class="top-search-input py-1 fs-7" placeholder="Filter permissions live...">
                </div>
            </div>

            <!-- MODULE PERMISSION CARDS MATRIX -->
            <div class="profile-card-body p-4">
                <div class="row g-4" id="permissionModulesContainer">
                    @foreach($groupedPermissions as $moduleName => $permissions)
                    @php
                        $moduleSlug = Str::slug($moduleName);
                        $moduleIconMap = [
                            'Dashboard' => 'fa-gauge-high text-primary',
                            'Customers' => 'fa-users text-success',
                            'Vendors' => 'fa-truck-field text-warning',
                            'Products' => 'fa-boxes-stacked text-info',
                            'Sales' => 'fa-file-invoice-dollar text-success',
                            'Purchase' => 'fa-receipt text-warning',
                            'Payments' => 'fa-hand-holding-dollar text-primary',
                            'Expenses' => 'fa-wallet text-danger',
                            'Reports' => 'fa-chart-line text-purple',
                            'Users' => 'fa-user-shield text-info',
                            'Roles' => 'fa-lock text-warning',
                            'Settings' => 'fa-gear text-secondary',
                        ];
                        $iconClass = $moduleIconMap[$moduleName] ?? 'fa-cube text-primary';
                    @endphp
                    <div class="col-xl-6 col-lg-12 permission-module-card-col" data-module="{{ strtolower($moduleName) }}">
                        <div class="erp-card h-100 border shadow-sm permission-module-card">
                            <!-- Module Header -->
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid {{ $iconClass }} fs-5"></i>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0 fs-7">{{ $moduleName }}</h6>
                                        <small class="text-muted fs-8">{{ count($permissions) }} permissions available</small>
                                    </div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input module-select-all-toggle" type="checkbox" id="modToggle_{{ $moduleSlug }}" data-module="{{ $moduleSlug }}">
                                    <label class="form-check-label fs-8 text-muted fw-bold ms-1" for="modToggle_{{ $moduleSlug }}">Select All</label>
                                </div>
                            </div>

                            <!-- Permissions Switches Grid -->
                            <div class="row g-2">
                                @foreach($permissions as $permission)
                                @php
                                    $isPreChecked = in_array($permission->id, $assignedPermissionIds);
                                @endphp
                                <div class="col-sm-6 permission-item-wrapper" data-perm-name="{{ strtolower($permission->name) }}" data-perm-slug="{{ strtolower($permission->slug) }}">
                                    <div class="p-2 rounded-3 border bg-light-subtle h-100 d-flex align-items-center justify-content-between hover-border-primary">
                                        <div>
                                            <label class="form-check-label fw-bold text-dark fs-8 d-block cursor-pointer" for="perm_{{ $permission->id }}">
                                                {{ $permission->name }}
                                            </label>
                                            <small class="text-muted font-monospace fs-8 d-block">{{ $permission->slug }}</small>
                                        </div>
                                        <div class="form-check form-switch mb-0 ms-2">
                                            <input 
                                                class="form-check-input permission-checkbox module-perm-{{ $moduleSlug }}" 
                                                type="checkbox" 
                                                name="permissions[]" 
                                                value="{{ $permission->id }}" 
                                                id="perm_{{ $permission->id }}"
                                                {{ $isPreChecked ? 'checked' : '' }}
                                            >
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- FORM BOTTOM ACTIONS -->
        <div class="d-flex align-items-center justify-content-end gap-2 mb-5">
            <a href="{{ route('roles.show', $role->id) }}" class="btn btn-light border px-4 py-2 fw-semibold text-secondary rounded-3">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold rounded-3 shadow-sm">
                <i class="fa-solid fa-check me-2"></i> Update Role & Permissions
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const permCheckboxes = document.querySelectorAll('.permission-checkbox');
    const moduleToggles = document.querySelectorAll('.module-select-all-toggle');
    const globalToggle = document.getElementById('selectAllGlobalToggle');
    const searchInput = document.getElementById('searchPermissionsInput');
    const countBadge = document.getElementById('selectedCountBadge');
    const unsavedWarning = document.getElementById('unsavedChangesWarning');

    let initialCheckedCount = 0;

    // Update Counter & Module Checkbox States
    function updateMatrixStats() {
        let checkedCount = 0;

        permCheckboxes.forEach(cb => {
            if (cb.checked) checkedCount++;
        });

        if (countBadge) countBadge.textContent = checkedCount;

        // Update Module Select All Toggles
        moduleToggles.forEach(modToggle => {
            const modSlug = modToggle.dataset.module;
            const moduleCbs = document.querySelectorAll(`.module-perm-${modSlug}`);
            const checkedModuleCbs = document.querySelectorAll(`.module-perm-${modSlug}:checked`);

            if (moduleCbs.length > 0) {
                modToggle.checked = (moduleCbs.length === checkedModuleCbs.length);
                modToggle.indeterminate = (checkedModuleCbs.length > 0 && checkedModuleCbs.length < moduleCbs.length);
            }
        });

        // Update Global Select All Toggle
        if (globalToggle) {
            globalToggle.checked = (permCheckboxes.length > 0 && checkedCount === permCheckboxes.length);
            globalToggle.indeterminate = (checkedCount > 0 && checkedCount < permCheckboxes.length);
        }

        // Show Unsaved Changes Warning if modified from initial count
        if (unsavedWarning && initialCheckedCount !== checkedCount) {
            unsavedWarning.classList.remove('d-none');
            unsavedWarning.classList.add('d-flex');
        }
    }

    // Initialize Checkbox Listeners
    permCheckboxes.forEach(cb => {
        if (cb.checked) initialCheckedCount++;
        cb.addEventListener('change', updateMatrixStats);
    });

    // Module Select All Listener
    moduleToggles.forEach(modToggle => {
        modToggle.addEventListener('change', function () {
            const modSlug = this.dataset.module;
            const moduleCbs = document.querySelectorAll(`.module-perm-${modSlug}`);
            moduleCbs.forEach(cb => {
                cb.checked = this.checked;
            });
            updateMatrixStats();
        });
    });

    // Global Select All Listener
    if (globalToggle) {
        globalToggle.addEventListener('change', function () {
            permCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateMatrixStats();
        });
    }

    // Live Search Filter for Permissions Matrix
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const query = this.value.toLowerCase().trim();
            const moduleCards = document.querySelectorAll('.permission-module-card-col');

            moduleCards.forEach(cardCol => {
                const permItems = cardCol.querySelectorAll('.permission-item-wrapper');
                let matchInModule = false;

                permItems.forEach(item => {
                    const name = item.dataset.permName || '';
                    const slug = item.dataset.permSlug || '';

                    if (!query || name.includes(query) || slug.includes(query)) {
                        item.style.display = '';
                        matchInModule = true;
                    } else {
                        item.style.display = 'none';
                    }
                });

                if (matchInModule) {
                    cardCol.style.display = '';
                } else {
                    cardCol.style.display = 'none';
                }
            });
        });
    }

    // Initial Calculation
    updateMatrixStats();
});
</script>
@endpush
