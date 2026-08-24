<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard | Accounts ERP')</title>

    <!-- Global User Saved Theme Config -->
    <script>
        window.USER_THEME_COLOR = "{{ auth()->check() && auth()->user()->theme_color ? auth()->user()->theme_color : '#2563EB' }}";
    </script>

    <!-- Immediate Theme Restorer (Prevents Flash of Unstyled Content / FOUC) -->
    <script>
        (function() {
            try {
                var root = document.documentElement;

                // 1. Restore Header Theme Background
                var savedHeaderBg = localStorage.getItem('tixx_erp_header_bg') || localStorage.getItem('tixx_erp_frame_bg');
                if (savedHeaderBg && /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(savedHeaderBg)) {
                    var cleanHeaderHex = savedHeaderBg.trim().toUpperCase();
                    root.style.setProperty('--frame-bg', cleanHeaderHex);
                    root.style.setProperty('--topbar-bg', cleanHeaderHex);
                    
                    var hNum = parseInt(cleanHeaderHex.replace('#', ''), 16);
                    var hr = (hNum >> 16) & 255;
                    var hg = (hNum >> 8) & 255;
                    var hb = hNum & 255;
                    var hLum = (hr * 0.2126 + hg * 0.7152 + hb * 0.0722) / 255;
                    
                    root.style.setProperty('--topbar-text', hLum < 0.45 ? '#a0a5ba' : '#475569');
                    root.style.setProperty('--topbar-text-active', hLum < 0.45 ? '#ffffff' : '#0f172a');
                }

                // 2. Restore Page Body Background Theme
                var savedPageBg = localStorage.getItem('tixx_erp_page_bg') || localStorage.getItem('tixx_erp_app_bg');
                if (savedPageBg && /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(savedPageBg)) {
                    var cleanPageHex = savedPageBg.trim().toUpperCase();
                    root.style.setProperty('--main-body-bg', cleanPageHex);
                    root.style.setProperty('--app-bg', cleanPageHex);

                    var pNum = parseInt(cleanPageHex.replace('#', ''), 16);
                    var pr = (pNum >> 16) & 255;
                    var pg = (pNum >> 8) & 255;
                    var pb = pNum & 255;
                    var pLum = (pr * 0.2126 + pg * 0.7152 + pb * 0.0722) / 255;

                    if (pLum < 0.45) {
                        root.style.setProperty('--card-bg', '#151828');
                        root.style.setProperty('--border-color', 'rgba(255, 255, 255, 0.12)');
                        root.style.setProperty('--text-primary', '#F8FAFC');
                        root.style.setProperty('--text-muted', '#94A3B8');
                    } else {
                        root.style.setProperty('--card-bg', '#FFFFFF');
                        root.style.setProperty('--border-color', '#E2E8F0');
                        root.style.setProperty('--text-primary', '#1E2538');
                        root.style.setProperty('--text-muted', '#8C93A6');
                    }
                }

                // 3. Restore Primary Theme Color & Compute Harmonious Palette
                var dbColor = window.USER_THEME_COLOR;
                var savedColor = localStorage.getItem('tixx_erp_theme_color');
                var primaryHex = (dbColor && /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(dbColor)) 
                    ? dbColor 
                    : (savedColor && /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(savedColor) ? savedColor : '#2563EB');

                if (primaryHex) {
                    var cleanHex = primaryHex.trim().toUpperCase();
                    var num = parseInt(cleanHex.replace('#', ''), 16);
                    var r = (num >> 16) & 255;
                    var g = (num >> 8) & 255;
                    var b = num & 255;
                    
                    var lum = (r * 0.2126 + g * 0.7152 + b * 0.0722) / 255;
                    var contrast = lum < 0.45 ? '#FFFFFF' : '#0F172A';

                    root.style.setProperty('--theme-primary', cleanHex);
                    root.style.setProperty('--theme-primary-rgb', r + ', ' + g + ', ' + b);
                    root.style.setProperty('--theme-primary-soft', 'rgba(' + r + ', ' + g + ', ' + b + ', 0.08)');
                    root.style.setProperty('--theme-primary-alpha', 'rgba(' + r + ', ' + g + ', ' + b + ', 0.25)');
                    root.style.setProperty('--theme-primary-focus-ring', 'rgba(' + r + ', ' + g + ', ' + b + ', 0.35)');
                    root.style.setProperty('--theme-text-on-primary', contrast);

                    root.style.setProperty('--primary', cleanHex);
                    root.style.setProperty('--primary-rgb', r + ', ' + g + ', ' + b);
                    root.style.setProperty('--primary-contrast', contrast);
                    root.style.setProperty('--primary-border', 'rgba(' + r + ', ' + g + ', ' + b + ', 0.25)');
                    root.style.setProperty('--primary-ring', 'rgba(' + r + ', ' + g + ', ' + b + ', 0.35)');
                    root.style.setProperty('--primary-subtle', 'rgba(' + r + ', ' + g + ', ' + b + ', 0.12)');
                    root.style.setProperty('--primary-blue', cleanHex);
                    root.style.setProperty('--gold-active', cleanHex);
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
        @include('admin.layouts.partials.header')

        <!-- Main Body Content -->
        <main class="main-dashboard-body">
            @yield('content')
        </main>
    </div>

    <!-- Dynamic Theme Color System Modal -->
    <div class="modal fade" id="themeSelectorModal" tabindex="-1" aria-labelledby="themeSelectorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-dark text-white p-4">
                    <div>
                        <h5 class="modal-title fw-bold fs-5 mb-1" id="themeSelectorModalLabel">
                            <i class="fa-solid fa-palette text-primary me-2"></i> Dynamic Theme Color System
                        </h5>
                        <small class="text-white-50 fs-7">Choose Header Color (Dark/Light). Page background options adaptively match your Header choice!</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <!-- SECTION 1: PRIMARY ACCENT THEME COLOR -->
                    <div class="mb-4 bg-white p-3 rounded-3 border">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom">
                            <div>
                                <h6 class="fw-bold text-dark fs-7 text-uppercase letter-spacing-1 mb-0">
                                    <i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i> Primary Theme Color Picker
                                </h6>
                                <small class="text-muted fs-8">Updates buttons, active icons, badges, charts, and focus rings</small>
                            </div>
                            <div class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-2 fs-7 fw-bold">
                                Selected Color: <span id="selectedColorHexText">{{ auth()->check() && auth()->user()->theme_color ? auth()->user()->theme_color : '#2563EB' }}</span>
                            </div>
                        </div>

                        <!-- LIVE COLOR PICKER & PRESET PALETTES -->
                        <div class="row g-3 align-items-center mb-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-bold text-dark fs-8">Custom Accent Color</div>
                                        <small class="text-muted fs-9">Pick any custom color wheel shade</small>
                                    </div>
                                    <input type="color" id="customThemeColorPicker" class="form-control form-control-color border-0 p-0 cursor-pointer shadow-sm" style="width: 44px; height: 38px; border-radius: 8px;" value="{{ auth()->check() && auth()->user()->theme_color ? auth()->user()->theme_color : '#2563EB' }}" title="Choose Custom Primary Theme Color">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted fs-9">
                                    <i class="fa-solid fa-circle-info text-info me-1"></i> Instantly updates your user profile & application controls live.
                                </div>
                            </div>
                        </div>

                        <!-- PRESET ACCENT SWATCHES -->
                        <div class="mt-3">
                            <label class="fw-bold text-secondary fs-8 mb-2 text-uppercase">Preset Accent Colors:</label>
                            <div class="row g-2">
                                <div class="col-6 col-md-3">
                                    <div class="theme-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100 d-flex align-items-center gap-2" data-color="#2563EB" data-name="Electric Blue">
                                        <div class="rounded-circle shadow-sm flex-shrink-0" style="width: 26px; height: 26px; background-color: #2563EB;"></div>
                                        <div class="fw-bold text-dark fs-9">Blue (#2563EB)</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="theme-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100 d-flex align-items-center gap-2" data-color="#DC2626" data-name="Crimson Red">
                                        <div class="rounded-circle shadow-sm flex-shrink-0" style="width: 26px; height: 26px; background-color: #DC2626;"></div>
                                        <div class="fw-bold text-dark fs-9">Red (#DC2626)</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="theme-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100 d-flex align-items-center gap-2" data-color="#16A34A" data-name="Emerald Green">
                                        <div class="rounded-circle shadow-sm flex-shrink-0" style="width: 26px; height: 26px; background-color: #16A34A;"></div>
                                        <div class="fw-bold text-dark fs-9">Green (#16A34A)</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="theme-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100 d-flex align-items-center gap-2" data-color="#7C3AED" data-name="Imperial Purple">
                                        <div class="rounded-circle shadow-sm flex-shrink-0" style="width: 26px; height: 26px; background-color: #7C3AED;"></div>
                                        <div class="fw-bold text-dark fs-9">Purple (#7C3AED)</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="theme-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100 d-flex align-items-center gap-2" data-color="#EA580C" data-name="Vibrant Orange">
                                        <div class="rounded-circle shadow-sm flex-shrink-0" style="width: 26px; height: 26px; background-color: #EA580C;"></div>
                                        <div class="fw-bold text-dark fs-9">Orange (#EA580C)</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="theme-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100 d-flex align-items-center gap-2" data-color="#0D9488" data-name="Ocean Teal">
                                        <div class="rounded-circle shadow-sm flex-shrink-0" style="width: 26px; height: 26px; background-color: #0D9488;"></div>
                                        <div class="fw-bold text-dark fs-9">Teal (#0D9488)</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="theme-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100 d-flex align-items-center gap-2" data-color="#4F46E5" data-name="Midnight Indigo">
                                        <div class="rounded-circle shadow-sm flex-shrink-0" style="width: 26px; height: 26px; background-color: #4F46E5;"></div>
                                        <div class="fw-bold text-dark fs-9">Indigo (#4F46E5)</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="theme-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100 d-flex align-items-center gap-2" data-color="#DB2777" data-name="Neon Pink">
                                        <div class="rounded-circle shadow-sm flex-shrink-0" style="width: 26px; height: 26px; background-color: #DB2777;"></div>
                                        <div class="fw-bold text-dark fs-9">Pink (#DB2777)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: HEADER THEME COLOR SELECTION -->
                    <div class="mb-4 bg-white p-3 rounded-3 border">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                            <div>
                                <h6 class="fw-bold text-dark fs-7 text-uppercase letter-spacing-1 mb-0">
                                    <i class="fa-solid fa-heading text-primary me-2"></i> Header Theme Selection
                                </h6>
                                <small class="text-muted fs-8">Pick Dark or Light Header color (Page background options will update automatically!)</small>
                            </div>
                        </div>

                        <!-- DARK HEADERS PRESETS -->
                        <div class="mb-3">
                            <label class="fw-bold text-secondary fs-9 mb-2 text-uppercase"><i class="fa-solid fa-moon me-1"></i> Dark Header Colors:</label>
                            <div class="row g-2">
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="header-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-header-bg="#151828" data-name="Midnight Navy Header">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #151828;"></div>
                                        <div class="fw-bold text-dark fs-9">Midnight</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="header-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-header-bg="#14161F" data-name="Obsidian Black Header">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #14161F;"></div>
                                        <div class="fw-bold text-dark fs-9">Obsidian</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="header-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-header-bg="#1E293B" data-name="Deep Slate Header">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #1E293B;"></div>
                                        <div class="fw-bold text-dark fs-9">Deep Slate</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="header-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-header-bg="#1C192D" data-name="Violet Night Header">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #1C192D;"></div>
                                        <div class="fw-bold text-dark fs-9">Violet Night</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="header-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-header-bg="#13231E" data-name="Emerald Night Header">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #13231E;"></div>
                                        <div class="fw-bold text-dark fs-9">Emerald</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="header-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-header-bg="#0F172A" data-name="Corporate Navy Header">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #0F172A;"></div>
                                        <div class="fw-bold text-dark fs-9">Corporate</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- LIGHT HEADERS PRESETS -->
                        <div class="mb-3">
                            <label class="fw-bold text-secondary fs-9 mb-2 text-uppercase"><i class="fa-solid fa-sun me-1"></i> Light Header Colors:</label>
                            <div class="row g-2">
                                <div class="col-6 col-md-3">
                                    <div class="header-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-header-bg="#FFFFFF" data-name="Pure White Header">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #FFFFFF;"></div>
                                        <div class="fw-bold text-dark fs-9">Pure White</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="header-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-header-bg="#F8FAFC" data-name="Clean Slate Header">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #F8FAFC;"></div>
                                        <div class="fw-bold text-dark fs-9">Clean Slate</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="header-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-header-bg="#FAF9F6" data-name="Soft Cream Header">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #FAF9F6;"></div>
                                        <div class="fw-bold text-dark fs-9">Soft Cream</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="header-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-header-bg="#F0F7FF" data-name="Ice Blue Header">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #F0F7FF;"></div>
                                        <div class="fw-bold text-dark fs-9">Ice Blue</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CUSTOM HEADER COLOR PICKER -->
                        <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-bold text-dark fs-8"><i class="fa-solid fa-eye-dropper text-primary me-1"></i> Custom Header Color</div>
                                <small class="text-muted fs-9">Pick any custom hex color for the topbar</small>
                            </div>
                            <input type="color" id="customHeaderColorPicker" class="form-control form-control-color border-0 p-0 cursor-pointer shadow-sm" style="width: 44px; height: 38px; border-radius: 8px;" value="#151828" title="Choose Custom Header Background Color">
                        </div>
                    </div>

                    <!-- SECTION 3: ADAPTIVE PAGE BACKGROUND OPTIONS -->
                    <div class="mb-2 bg-white p-3 rounded-3 border">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                            <div>
                                <h6 class="fw-bold text-dark fs-7 text-uppercase letter-spacing-1 mb-0" id="pageBgSectionLabel">
                                    <i class="fa-solid fa-sun text-warning me-2"></i> Page Light Background Options
                                </h6>
                                <small class="text-muted fs-8">Automated contrast matching based on your Header color</small>
                            </div>
                        </div>

                        <!-- 1. LIGHT PAGE OPTIONS (Shown when Header is DARK) -->
                        <div id="pageLightBgContainer">
                            <div class="row g-2 mb-3">
                                <div class="col-6 col-md-3">
                                    <div class="page-bg-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-page-bg="#F8FAFC" data-name="Clean Slate Light Page">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #F8FAFC;"></div>
                                        <div class="fw-bold text-dark fs-9">Clean Slate</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="page-bg-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-page-bg="#FFFFFF" data-name="Pure Snow White Page">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #FFFFFF;"></div>
                                        <div class="fw-bold text-dark fs-9">Pure White</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="page-bg-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-page-bg="#FAF9F6" data-name="Soft Cream Page">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #FAF9F6;"></div>
                                        <div class="fw-bold text-dark fs-9">Soft Cream</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="page-bg-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-page-bg="#F0F7FF" data-name="Ice Blue Page">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #F0F7FF;"></div>
                                        <div class="fw-bold text-dark fs-9">Ice Blue</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="page-bg-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-page-bg="#F0FDF4" data-name="Soft Mint Page">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #F0FDF4;"></div>
                                        <div class="fw-bold text-dark fs-9">Soft Mint</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="page-bg-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-page-bg="#FFF1F2" data-name="Soft Rose Page">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #FFF1F2;"></div>
                                        <div class="fw-bold text-dark fs-9">Soft Rose</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="page-bg-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-page-bg="#F5F3FF" data-name="Soft Lavender Page">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #F5F3FF;"></div>
                                        <div class="fw-bold text-dark fs-9">Soft Lavender</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="page-bg-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-page-bg="#FFF7ED" data-name="Warm Peach Page">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #FFF7ED;"></div>
                                        <div class="fw-bold text-dark fs-9">Warm Peach</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. DARK PAGE OPTIONS (Shown when Header is LIGHT) -->
                        <div id="pageDarkBgContainer" style="display: none;">
                            <div class="row g-2 mb-3">
                                <div class="col-6 col-md-3">
                                    <div class="page-bg-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-page-bg="#0F111E" data-name="Midnight Dark Page">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #0F111E;"></div>
                                        <div class="fw-bold text-dark fs-9">Midnight Dark</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="page-bg-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-page-bg="#0B0C12" data-name="Obsidian Black Page">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #0B0C12;"></div>
                                        <div class="fw-bold text-dark fs-9">Obsidian Black</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="page-bg-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-page-bg="#0F172A" data-name="Deep Slate Page">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #0F172A;"></div>
                                        <div class="fw-bold text-dark fs-9">Deep Slate</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="page-bg-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-page-bg="#12101E" data-name="Violet Night Page">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #12101E;"></div>
                                        <div class="fw-bold text-dark fs-9">Violet Night</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="page-bg-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-page-bg="#0C1713" data-name="Emerald Night Page">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #0C1713;"></div>
                                        <div class="fw-bold text-dark fs-9">Emerald Night</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="page-bg-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-page-bg="#090D16" data-name="Corporate Navy Page">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #090D16;"></div>
                                        <div class="fw-bold text-dark fs-9">Corporate Navy</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="page-bg-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-page-bg="#111827" data-name="Charcoal Dark Page">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #111827;"></div>
                                        <div class="fw-bold text-dark fs-9">Charcoal Dark</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="page-bg-swatch-btn p-2 bg-white rounded-3 border text-center cursor-pointer h-100" data-page-bg="#021D1C" data-name="Dark Teal Page">
                                        <div class="rounded-3 mx-auto mb-1 border shadow-sm" style="width: 38px; height: 24px; background-color: #021D1C;"></div>
                                        <div class="fw-bold text-dark fs-9">Dark Teal</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CUSTOM PAGE BG PICKER -->
                        <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-bold text-dark fs-8"><i class="fa-solid fa-eye-dropper text-primary me-1"></i> Custom Page Background Color</div>
                                <small class="text-muted fs-9">Pick any custom hex color for main content area</small>
                            </div>
                            <input type="color" id="customPageBgColorPicker" class="form-control form-control-color border-0 p-0 cursor-pointer shadow-sm" style="width: 44px; height: 38px; border-radius: 8px;" value="#F8FAFC" title="Choose Custom Page Background Color">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-white p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-light border btn-sm rounded-3 fs-7" id="resetThemeBtn">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset to Default
                    </button>
                    <button type="button" class="btn btn-primary btn-sm rounded-3 fs-7 fw-bold" data-bs-dismiss="modal">
                        <i class="fa-solid fa-check me-1"></i> Apply & Close
                    </button>
                </div>
            </div>
        </div>
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
