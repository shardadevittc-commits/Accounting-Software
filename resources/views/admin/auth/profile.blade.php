@extends('admin.layouts.app')

@section('title', 'Profile & Preferences - Tixx Accounts ERP')

@section('content')
<div class="profile-page-wrapper">
    <!-- Page Header Title -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark fs-3 mb-1">Profile & preferences</h2>
        <p class="text-muted fs-6 mb-0">Manage your account details, language, and password.</p>
    </div>

    <!-- Reusable Alert Notifications & Error Messages -->
    @include('admin.layouts.partials.alerts')

    <!-- SIDE-BY-SIDE 2-COLUMN GRID ROW -->
    <div class="row g-4">
        <!-- COLUMN 1: PROFILE DETAILS CARD -->
        <div class="col-lg-6">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-header">
                    <h5 class="profile-card-title">Profile</h5>
                    <p class="profile-card-subtitle">Your name, contact details, and photo.</p>
                </div>

                <div class="profile-card-body">
                    <!-- Profile Form (Supports Multipart Avatar Upload) -->
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Photo Upload Section -->
                        <div class="profile-photo-section mb-4 pb-3 border-bottom border-light-subtle">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="profile-photo-box">
                                    <img src="{{ $user->avatar_url }}" alt="Profile Photo" id="profileAvatarPreview">
                                </div>
                                <div class="profile-photo-info">
                                    <h6 class="fw-bold text-dark fs-6 mb-1">Profile photo</h6>
                                    <p class="text-muted fs-7 mb-3">Square images look best. Max 8 MB.</p>
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="avatarUploadInput" class="btn btn-theme-light mb-0 cursor-pointer">
                                            <i class="fa-solid fa-upload me-1"></i> Replace
                                        </label>
                                        <input type="file" name="avatar" id="avatarUploadInput" class="d-none" accept="image/*">
                                        
                                        @if($user->avatar)
                                            <button type="button" class="btn btn-theme-danger-text" onclick="submitRemoveAvatarForm()">
                                                <i class="fa-solid fa-trash-can me-1"></i> Remove
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FULL NAME -->
                        <div class="mb-4">
                            <label for="name" class="profile-field-label">
                                FULL NAME <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="name" 
                                id="name" 
                                class="form-control profile-custom-input @error('name') is-invalid @enderror" 
                                value="{{ old('name', $user->name) }}" 
                                required
                            >
                        </div>

                        <!-- EMAIL -->
                        <div class="mb-4">
                            <label for="email" class="profile-field-label">
                                EMAIL <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                class="form-control profile-custom-input @error('email') is-invalid @enderror" 
                                value="{{ old('email', $user->email) }}" 
                                required
                            >
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="btn btn-theme-dark">
                                <i class="fa-solid fa-check me-2"></i> Save Profile Details
                            </button>
                        </div>
                    </form>

                    <!-- Separate Hidden Form for Removing Avatar -->
                    <form id="removeAvatarForm" action="{{ route('profile.avatar.remove') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>

        <!-- COLUMN 2: PASSWORD CHANGE CARD -->
        <div class="col-lg-6">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-header">
                    <h5 class="profile-card-title">Password</h5>
                    <p class="profile-card-subtitle">Change the password you sign in with.</p>
                </div>

                <div class="profile-card-body">
                    <form action="{{ route('profile.updatePassword') }}" method="POST">
                        @csrf

                        <!-- CURRENT PASSWORD -->
                        <div class="mb-4">
                            <label for="current_password" class="profile-field-label">
                                CURRENT PASSWORD <span class="text-danger">*</span>
                            </label>
                            <div class="password-input-group">
                                <input 
                                    type="password" 
                                    name="current_password" 
                                    id="current_password" 
                                    class="form-control profile-custom-input @error('current_password') is-invalid @enderror" 
                                    required
                                >
                                <i class="fa-regular fa-eye toggle-pwd-eye" onclick="togglePasswordVisibility('current_password', this)"></i>
                            </div>
                        </div>

                        <!-- NEW PASSWORD -->
                        <div class="mb-4">
                            <label for="new_password" class="profile-field-label">
                                NEW PASSWORD <span class="text-danger">*</span>
                            </label>
                            <div class="password-input-group">
                                <input 
                                    type="password" 
                                    name="new_password" 
                                    id="new_password" 
                                    class="form-control profile-custom-input @error('new_password') is-invalid @enderror" 
                                    required
                                >
                                <i class="fa-regular fa-eye toggle-pwd-eye" onclick="togglePasswordVisibility('new_password', this)"></i>
                            </div>
                        </div>

                        <!-- CONFIRM NEW PASSWORD -->
                        <div class="mb-4">
                            <label for="new_password_confirmation" class="profile-field-label">
                                CONFIRM NEW PASSWORD <span class="text-danger">*</span>
                            </label>
                            <div class="password-input-group">
                                <input 
                                    type="password" 
                                    name="new_password_confirmation" 
                                    id="new_password_confirmation" 
                                    class="form-control profile-custom-input" 
                                    required
                                >
                                <i class="fa-regular fa-eye toggle-pwd-eye" onclick="togglePasswordVisibility('new_password_confirmation', this)"></i>
                            </div>
                        </div>

                        <!-- Change Password Button -->
                        <div class="pt-2">
                            <button type="submit" class="btn btn-theme-dark">
                                <i class="fa-solid fa-lock me-2"></i> Change password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePasswordVisibility(inputId, eyeIcon) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }

    // Avatar Instant Preview & Upload
    const avatarInput = document.getElementById('avatarUploadInput');
    const avatarPreview = document.getElementById('profileAvatarPreview');

    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    avatarPreview.src = evt.target.result;
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }

    function submitRemoveAvatarForm() {
        if (confirm('Are you sure you want to remove your profile photo?')) {
            document.getElementById('removeAvatarForm').submit();
        }
    }
</script>
@endpush
