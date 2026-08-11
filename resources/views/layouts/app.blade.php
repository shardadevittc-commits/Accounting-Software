<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Super Admin Accounts ERP Dashboard')</title>

    <!-- Immediate Theme Restorer (Prevents Flash of Default Theme) -->
    <script>
        (function() {
            try {
                var savedColor = localStorage.getItem('tixx_erp_theme_color');
                if (savedColor && /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(savedColor)) {
                    var cleanHex = savedColor.trim().toUpperCase();
                    var num = parseInt(cleanHex.replace('#', ''), 16);
                    var r = (num >> 16) & 255;
                    var g = (num >> 8) & 255;
                    var b = num & 255;
                    
                    var lum = (r * 0.2126 + g * 0.7152 + b * 0.0722) / 255;
                    var contrast = lum < 0.45 ? '#FFFFFF' : '#0F172A';
                    var contrastRgb = contrast === '#FFFFFF' ? '255, 255, 255' : '15, 23, 42';

                    var root = document.documentElement;
                    root.style.setProperty('--primary', cleanHex);
                    root.style.setProperty('--primary-rgb', r + ', ' + g + ', ' + b);
                    root.style.setProperty('--primary-contrast', contrast);
                    root.style.setProperty('--primary-contrast-rgb', contrastRgb);
                    root.style.setProperty('--primary-border', 'rgba(' + r + ', ' + g + ', ' + b + ', 0.25)');
                    root.style.setProperty('--primary-ring', 'rgba(' + r + ', ' + g + ', ' + b + ', 0.35)');
                    root.style.setProperty('--primary-subtle', 'rgba(' + r + ', ' + g + ', ' + b + ', 0.1)');
                    root.style.setProperty('--primary-blue', cleanHex);
                }
            } catch (e) {}
        })();
    </script>

    <!-- Google Fonts (Outfit & Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome 6 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Custom Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
    @stack('styles')
</head>
<body>
    <div class="app-frame">
        <!-- Top Horizontal Navigation Bar (Menu Uper se) -->
        @include('layouts.partials.header')

        <!-- Main Body Content -->
        <main class="main-dashboard-body">
            @yield('content')
        </main>
    </div>

    <!-- Theme Notification Toast -->
    <div id="themeToast" class="theme-toast">
        <i class="fa-solid fa-palette text-warning"></i>
        <span id="themeToastMessage">Theme settings updated!</span>
    </div>

    <!-- Bootstrap 5 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Dynamic Theme System JS -->
    <script src="{{ asset('assets/js/theme.js') }}"></script>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom Dashboard JS -->
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>

    <script>
        window.showThemeToast = function(msg) {
            var toast = document.getElementById('themeToast');
            var toastMsg = document.getElementById('themeToastMessage');
            if (toast && toastMsg) {
                toastMsg.textContent = msg;
                toast.classList.add('show');
                setTimeout(function() {
                    toast.classList.remove('show');
                }, 3000);
            }
        };
    </script>
    @stack('scripts')
</body>
</html>

