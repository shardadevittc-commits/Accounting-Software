<!-- Reusable Alert Notifications & Validation Error Messages Partial -->

<!-- Success Flash Messages -->
@if(session('success'))
    <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success rounded-3 py-2 px-3 fs-7 mb-4 shadow-sm">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
    </div>
@endif

@if(session('profile_success'))
    <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success rounded-3 py-2 px-3 fs-7 mb-4 shadow-sm">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('profile_success') }}
    </div>
@endif

@if(session('password_success'))
    <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success rounded-3 py-2 px-3 fs-7 mb-4 shadow-sm">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('password_success') }}
    </div>
@endif

<!-- Info Flash Messages -->
@if(session('info'))
    <div class="alert alert-info border-0 text-info bg-info bg-opacity-10 rounded-3 py-2 px-3 fs-7 mb-4 shadow-sm">
        <i class="fa-solid fa-circle-info me-2"></i> {{ session('info') }}
    </div>
@endif

<!-- Warning Flash Messages -->
@if(session('warning'))
    <div class="alert alert-warning border-0 text-warning bg-warning bg-opacity-10 rounded-3 py-2 px-3 fs-7 mb-4 shadow-sm">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('warning') }}
    </div>
@endif

<!-- Validation Errors -->
@if($errors->any())
    <div class="alert alert-danger border-0 text-danger bg-danger bg-opacity-10 rounded-3 py-2 px-3 fs-7 mb-4 shadow-sm">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ $errors->first() }}
    </div>
@endif
