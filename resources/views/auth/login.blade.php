<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin Login - Tixx Accounting</title>

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
            <div class="login-brand-header">
                <a href="#" class="brand-logo mb-2">
                    <div class="brand-icon-gems">
                        <div class="brand-gem bg-gem-1"></div>
                        <div class="brand-gem bg-gem-2"></div>
                        <div class="brand-gem bg-gem-3"></div>
                        <div class="brand-gem bg-gem-4"></div>
                    </div>
                    <span>Tixx</span>
                </a>
                <h4 class="fw-bold text-white mb-1">Super Admin Login</h4>
                <p class="login-subtitle">Sign in to manage your accounting dashboard</p>
            </div>

            <!-- Session Notifications / Validation Errors -->
            @include('layouts.partials.alerts')

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
                            placeholder="superadmin@gmail.com" 
                            value="{{ old('email', 'superadmin@gmail.com') }}" 
                            required 
                            autofocus
                        >
                        <i class="fa-regular fa-envelope"></i>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="password" class="form-label mb-0">Password</label>
                        <a href="#" class="text-white-50 fs-8 text-decoration-none">Forgot?</a>
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
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check m-0">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input custom-dark-check" checked>
                        <label for="remember" class="form-check-label custom-checkbox-label">Keep me logged in</label>
                    </div>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 fs-8 fw-semibold">Super Admin</span>
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
</body>
</html>
