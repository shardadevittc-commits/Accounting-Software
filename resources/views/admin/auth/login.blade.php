<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Accounts ERP</title>

    <!-- Favicon & App Icons -->
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome 6 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Custom Dashboard & Login CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
</head>
<body>
    <div class="login-page-wrapper">
        <div class="login-card-container">
            <!-- Brand Header -->
            <div class="login-brand-header text-center">
                <div class="mb-3 d-flex justify-content-center">
                    <div class="brand-logo-badge" style="height: 64px; padding: 6px 18px; border-radius: 14px;">
                        <img src="{{ asset('assets/images/dbs-logo.png') }}" alt="Divine Bright Steels" style="height: 52px; width: auto; object-fit: contain;">
                    </div>
                </div>
                <h4 class="fw-bold text-white mb-1">DIVINE BRIGHT STEELS</h4>
                <p class="login-subtitle">Accounts &amp; Financial ERP Login</p>
            </div>
            <!-- Session Notifications / Validation Errors -->
            @include('admin.layouts.partials.alerts')

            <!-- Login Form -->
            <form action="{{ route('login') }}" method="POST" class="login-form">
                @csrf

                <!-- Email Address -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="custom-input-group">
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="form-control custom-dark-input @error('email') is-invalid @enderror" 
                            placeholder="admin@gmail.com" 
                            value="{{ old('email', 'admin@gmail.com') }}" 
                            required 
                            autofocus>
                        <i class="fa-regular fa-envelope"></i>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="password" class="form-label mb-0">Password</label>
                        <!-- <a href="#" class="text-white-50 fs-8 text-decoration-none">Forgot?</a> -->
                    </div>
                    <div class="custom-input-group">
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-control custom-dark-input @error('password') is-invalid @enderror" 
                            placeholder="••••••••" 
                            value="password"
                            required
                        >
                        <i class="fa-solid fa-lock"></i>
                        <button type="button" class="toggle-password-btn" id="togglePasswordBtn" title="Toggle password visibility" tabindex="-1">
                            <i class="fa-solid fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check m-0">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input custom-dark-check" checked>
                        <label for="remember" class="form-check-label custom-checkbox-label">Keep me logged in</label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="gradient-submit-btn">
                    Sign In to Dashboard <i class="fa-solid fa-arrow-right ms-2 fs-7"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Password Visibility Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePasswordBtn = document.getElementById('togglePasswordBtn');
            const passwordInput = document.getElementById('password');
            const togglePasswordIcon = document.getElementById('togglePasswordIcon');

            if (togglePasswordBtn && passwordInput && togglePasswordIcon) {
                togglePasswordBtn.addEventListener('click', function () {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    
                    if (isPassword) {
                        togglePasswordIcon.classList.remove('fa-eye');
                        togglePasswordIcon.classList.add('fa-eye-slash');
                        togglePasswordBtn.style.color = '#818cf8';
                    } else {
                        togglePasswordIcon.classList.remove('fa-eye-slash');
                        togglePasswordIcon.classList.add('fa-eye');
                        togglePasswordBtn.style.color = '';
                    }
                });
            }
        });
    </script>
</body>
</html>
