/**
 * Tixx Accounts ERP - Fully Dynamic Single-Primary Theme Harmonizer Engine
 */

(function () {
    'use strict';

    // Color Math Utility Functions
    function hexToRgb(hex) {
        var cleanHex = hex.replace('#', '').trim();
        if (cleanHex.length === 3) {
            cleanHex = cleanHex.split('').map(function (c) { return c + c; }).join('');
        }
        var num = parseInt(cleanHex, 16);
        return {
            r: (num >> 16) & 255,
            g: (num >> 8) & 255,
            b: num & 255
        };
    }

    function rgbToHex(r, g, b) {
        return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase();
    }

    function adjustColorShade(hex, percent) {
        var rgb = hexToRgb(hex);
        var r = Math.min(255, Math.max(0, Math.round(rgb.r * (1 + percent / 100))));
        var g = Math.min(255, Math.max(0, Math.round(rgb.g * (1 + percent / 100))));
        var b = Math.min(255, Math.max(0, Math.round(rgb.b * (1 + percent / 100))));
        return rgbToHex(r, g, b);
    }

    function calculateLuminance(rgb) {
        var a = [rgb.r, rgb.g, rgb.b].map(function (v) {
            v /= 255;
            return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
        });
        return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
    }

    // Debounce DB saving to prevent hammering server
    var saveDbTimer = null;
    function saveThemeToDatabase(hexColor) {
        if (saveDbTimer) clearTimeout(saveDbTimer);
        saveDbTimer = setTimeout(function () {
            var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

            if (!csrfToken) {
                var tokenInput = document.querySelector('input[name="_token"]');
                if (tokenInput) csrfToken = tokenInput.value;
            }

            if (!csrfToken) return;

            fetch('/theme/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ theme_color: hexColor })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    console.log('Theme color persisted to user account:', data.theme_color);
                }
            })
            .catch(function (err) {
                console.error('Error saving theme color:', err);
            });
        }, 400);
    }

    // Adaptively toggle Page Background Swatches based on Header Tone (Dark vs Light)
    window.updateAdaptivePageBgOptions = function (headerHex) {
        if (!headerHex || !/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(headerHex)) return;
        
        var headerRgb = hexToRgb(headerHex);
        var headerLum = calculateLuminance(headerRgb);
        var isHeaderDark = headerLum < 0.45;

        var lightOptionsContainer = document.getElementById('pageLightBgContainer');
        var darkOptionsContainer = document.getElementById('pageDarkBgContainer');
        var bgSectionLabel = document.getElementById('pageBgSectionLabel');

        if (isHeaderDark) {
            // Header is DARK -> Show LIGHT Page Background Options
            if (lightOptionsContainer) lightOptionsContainer.style.display = 'block';
            if (darkOptionsContainer) darkOptionsContainer.style.display = 'none';
            if (bgSectionLabel) {
                bgSectionLabel.innerHTML = '<i class="fa-solid fa-sun text-warning me-2"></i> Page Light Background Options <span class="badge bg-warning-subtle text-warning-emphasis border ms-2 fs-9">Header is Dark</span>';
            }
        } else {
            // Header is LIGHT -> Show DARK Page Background Options
            if (lightOptionsContainer) lightOptionsContainer.style.display = 'none';
            if (darkOptionsContainer) darkOptionsContainer.style.display = 'block';
            if (bgSectionLabel) {
                bgSectionLabel.innerHTML = '<i class="fa-solid fa-moon text-primary me-2"></i> Page Dark Background Options <span class="badge bg-primary-subtle text-primary border ms-2 fs-9">Header is Light</span>';
            }
        }
    };

    // Apply Header Background
    window.applyHeaderBackground = function (headerBgHex, bgName, showToast) {
        if (!headerBgHex || !/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(headerBgHex)) return;

        var cleanHeaderHex = headerBgHex.trim().toUpperCase();
        var headerRgb = hexToRgb(cleanHeaderHex);
        var headerLum = calculateLuminance(headerRgb);
        var dropdownBgHex = headerLum < 0.45 ? adjustColorShade(cleanHeaderHex, 10) : adjustColorShade(cleanHeaderHex, -8);
        var topbarText = headerLum < 0.45 ? '#a0a5ba' : '#475569';
        var topbarTextActive = headerLum < 0.45 ? '#ffffff' : '#0f172a';

        var root = document.documentElement;
        root.style.setProperty('--frame-bg', cleanHeaderHex);
        root.style.setProperty('--topbar-bg', cleanHeaderHex);
        root.style.setProperty('--topbar-dropdown-bg', dropdownBgHex);
        root.style.setProperty('--topbar-text', topbarText);
        root.style.setProperty('--topbar-text-active', topbarTextActive);

        try {
            localStorage.setItem('tixx_erp_frame_bg', cleanHeaderHex);
            localStorage.setItem('tixx_erp_header_bg', cleanHeaderHex);
            if (bgName) localStorage.setItem('tixx_erp_header_name', bgName);
        } catch (e) {}

        var customHeaderPicker = document.getElementById('customHeaderColorPicker');
        if (customHeaderPicker) customHeaderPicker.value = cleanHeaderHex;

        // Dynamically update Page Background Swatch Visibility (Light vs Dark)
        window.updateAdaptivePageBgOptions(cleanHeaderHex);

        if (showToast && typeof window.showThemeToast === 'function') {
            window.showThemeToast('Header Color Updated: [' + (bgName || cleanHeaderHex) + ']');
        }
    };

    // Apply Page Body Background
    window.applyPageBackground = function (pageBgHex, bgName, showToast) {
        if (!pageBgHex || !/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(pageBgHex)) return;

        var cleanPageHex = pageBgHex.trim().toUpperCase();
        var pageRgb = hexToRgb(cleanPageHex);
        var pageLum = calculateLuminance(pageRgb);

        var root = document.documentElement;
        root.style.setProperty('--main-body-bg', cleanPageHex);
        root.style.setProperty('--app-bg', cleanPageHex);

        if (pageLum < 0.45) {
            // Dark Page Mode
            root.style.setProperty('--card-bg', '#151828');
            root.style.setProperty('--border-color', 'rgba(255, 255, 255, 0.12)');
            root.style.setProperty('--text-primary', '#F8FAFC');
            root.style.setProperty('--text-muted', '#94A3B8');
        } else {
            // Light Page Mode
            root.style.setProperty('--card-bg', '#FFFFFF');
            root.style.setProperty('--border-color', '#E2E8F0');
            root.style.setProperty('--text-primary', '#1E2538');
            root.style.setProperty('--text-muted', '#8C93A6');
        }

        try {
            localStorage.setItem('tixx_erp_app_bg', cleanPageHex);
            localStorage.setItem('tixx_erp_page_bg', cleanPageHex);
            if (bgName) localStorage.setItem('tixx_erp_page_bg_name', bgName);
        } catch (e) {}

        var customPagePicker = document.getElementById('customPageBgColorPicker');
        if (customPagePicker) customPagePicker.value = cleanPageHex;

        if (showToast && typeof window.showThemeToast === 'function') {
            window.showThemeToast('Page Background Updated: [' + (bgName || cleanPageHex) + ']');
        }
    };

    // Backward compatibility helper
    window.applyAppBackground = function (frameBgHex, appBgHex, bgName, showToast) {
        window.applyHeaderBackground(frameBgHex, bgName, false);
        if (appBgHex) {
            window.applyPageBackground(appBgHex, bgName, showToast);
        }
    };

    // Apply Primary Accent Theme Color
    window.applyThemeColor = function (hexColor, themeName, showToast, saveToDb) {
        if (!hexColor || !/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(hexColor)) return;

        var cleanHex = hexColor.trim().toUpperCase();
        var rgb = hexToRgb(cleanHex);
        var luminance = calculateLuminance(rgb);

        var contrast = luminance < 0.45 ? '#FFFFFF' : '#0F172A';
        var contrastRgb = contrast === '#FFFFFF' ? '255, 255, 255' : '15, 23, 42';

        var hoverShade = adjustColorShade(cleanHex, -15);
        var activeShade = adjustColorShade(cleanHex, -25);
        var lightShade = adjustColorShade(cleanHex, 25);
        var lighterShade = adjustColorShade(cleanHex, 45);
        var darkShade = adjustColorShade(cleanHex, -20);

        var root = document.documentElement;

        root.style.setProperty('--theme-primary', cleanHex);
        root.style.setProperty('--theme-primary-rgb', rgb.r + ', ' + rgb.g + ', ' + rgb.b);
        root.style.setProperty('--theme-primary-hover', hoverShade);
        root.style.setProperty('--theme-primary-active', activeShade);
        root.style.setProperty('--theme-primary-light', lightShade);
        root.style.setProperty('--theme-primary-lighter', lighterShade);
        root.style.setProperty('--theme-primary-dark', darkShade);
        root.style.setProperty('--theme-primary-soft', 'rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', 0.08)');
        root.style.setProperty('--theme-primary-soft-hover', 'rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', 0.15)');
        root.style.setProperty('--theme-primary-alpha', 'rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', 0.25)');
        root.style.setProperty('--theme-primary-focus-ring', 'rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', 0.35)');
        root.style.setProperty('--theme-text-on-primary', contrast);
        root.style.setProperty('--theme-text-on-primary-rgb', contrastRgb);

        root.style.setProperty('--primary', cleanHex);
        root.style.setProperty('--primary-rgb', rgb.r + ', ' + rgb.g + ', ' + rgb.b);
        root.style.setProperty('--primary-hover', hoverShade);
        root.style.setProperty('--primary-active', activeShade);
        root.style.setProperty('--primary-contrast', contrast);
        root.style.setProperty('--primary-contrast-rgb', contrastRgb);
        root.style.setProperty('--primary-border', 'rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', 0.25)');
        root.style.setProperty('--primary-ring', 'rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', 0.35)');
        root.style.setProperty('--primary-subtle', 'rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', 0.12)');
        root.style.setProperty('--primary-blue', cleanHex);
        root.style.setProperty('--gold-active', cleanHex);

        try {
            localStorage.setItem('tixx_erp_theme_color', cleanHex);
            if (themeName) localStorage.setItem('tixx_erp_theme_name', themeName);
        } catch (e) {}

        if (saveToDb !== false) {
            saveThemeToDatabase(cleanHex);
        }

        var customPicker = document.getElementById('customThemeColorPicker');
        if (customPicker) customPicker.value = cleanHex;

        var colorTextSpan = document.getElementById('selectedColorHexText');
        if (colorTextSpan) colorTextSpan.textContent = cleanHex;

        if (showToast && typeof window.showThemeToast === 'function') {
            window.showThemeToast('Theme Accent Updated: [' + (themeName || cleanHex) + ']');
        }
    };

    // Hydrate initial saved theme immediately
    var initialColor = (window.USER_THEME_COLOR && /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(window.USER_THEME_COLOR))
        ? window.USER_THEME_COLOR
        : (localStorage.getItem('tixx_erp_theme_color') || '#2563EB');

    var savedThemeName = localStorage.getItem('tixx_erp_theme_name') || 'Primary Theme';
    window.applyThemeColor(initialColor, savedThemeName, false, false);

    var savedHeaderBg = localStorage.getItem('tixx_erp_header_bg') || localStorage.getItem('tixx_erp_frame_bg') || '#151828';
    var savedPageBg = localStorage.getItem('tixx_erp_page_bg') || localStorage.getItem('tixx_erp_app_bg') || '#F8FAFC';

    window.applyHeaderBackground(savedHeaderBg, null, false);
    window.applyPageBackground(savedPageBg, null, false);

    // DOM Ready Event Binding
    document.addEventListener('DOMContentLoaded', function () {
        // Initial adaptive swatch check
        window.updateAdaptivePageBgOptions(savedHeaderBg);

        // 1. Accent Swatches
        document.querySelectorAll('.theme-swatch-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var color = this.getAttribute('data-color');
                var name = this.getAttribute('data-name');
                window.applyThemeColor(color, name, true, true);

                document.querySelectorAll('.theme-swatch-btn').forEach(function (b) {
                    b.classList.remove('active-swatch');
                });
                this.classList.add('active-swatch');
            });
        });

        // 2. Header Color Swatches (both dark and light header buttons)
        document.querySelectorAll('.header-swatch-btn, .bg-swatch-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var headerBg = this.getAttribute('data-header-bg') || this.getAttribute('data-frame-bg');
                var name = this.getAttribute('data-name') || this.getAttribute('data-bg-name');
                window.applyHeaderBackground(headerBg, name, true);

                document.querySelectorAll('.header-swatch-btn, .bg-swatch-btn').forEach(function (b) {
                    b.classList.remove('active-swatch');
                });
                this.classList.add('active-swatch');
            });
        });

        // 3. Page Background Swatches
        document.querySelectorAll('.page-bg-swatch-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var pageBg = this.getAttribute('data-page-bg');
                var name = this.getAttribute('data-name');
                window.applyPageBackground(pageBg, name, true);

                document.querySelectorAll('.page-bg-swatch-btn').forEach(function (b) {
                    b.classList.remove('active-swatch');
                });
                this.classList.add('active-swatch');
            });
        });

        // 4. Live Custom Primary Color Picker Input
        var customPicker = document.getElementById('customThemeColorPicker');
        if (customPicker) {
            customPicker.addEventListener('input', function () {
                window.applyThemeColor(this.value, 'Custom (' + this.value.toUpperCase() + ')', false, true);
            });
            customPicker.addEventListener('change', function () {
                window.applyThemeColor(this.value, 'Custom (' + this.value.toUpperCase() + ')', true, true);
            });
        }

        // 5. Custom Header Color Picker Wheel
        var customHeaderPicker = document.getElementById('customHeaderColorPicker') || document.getElementById('customBgColorPicker');
        if (customHeaderPicker) {
            customHeaderPicker.addEventListener('input', function () {
                window.applyHeaderBackground(this.value, 'Custom Header (' + this.value.toUpperCase() + ')', false);
            });
            customHeaderPicker.addEventListener('change', function () {
                window.applyHeaderBackground(this.value, 'Custom Header (' + this.value.toUpperCase() + ')', true);
            });
        }

        // 6. Custom Page Background Color Picker Wheel
        var customPagePicker = document.getElementById('customPageBgColorPicker');
        if (customPagePicker) {
            customPagePicker.addEventListener('input', function () {
                window.applyPageBackground(this.value, 'Custom Page (' + this.value.toUpperCase() + ')', false);
            });
            customPagePicker.addEventListener('change', function () {
                window.applyPageBackground(this.value, 'Custom Page (' + this.value.toUpperCase() + ')', true);
            });
        }

        // 7. Reset All Theme Settings Button
        var resetBtn = document.getElementById('resetThemeBtn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                window.applyThemeColor('#2563EB', 'Electric Cobalt (Default)', false, true);
                window.applyHeaderBackground('#151828', 'Midnight Dark Header', false);
                window.applyPageBackground('#F8FAFC', 'Clean Slate Light Page', true);
            });
        }
    });
})();

