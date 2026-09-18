@extends('admin.layouts.app')

@section('title', 'Accounting Vouchers & Journal Entries | Accounts ERP')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/accounting_dashboard.css') }}">
<!-- Select2 Searchable Dropdown CSS & Bootstrap 5 Theme -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    /* Select2 custom styling in offcanvas drawer */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 42px !important;
        border-color: #cbd5e1 !important;
        border-radius: 8px !important;
        font-size: 0.875rem !important;
        display: flex !important;
        align-items: center !important;
        background-color: #ffffff !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: #0f172a !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        line-height: 38px !important;
        padding-left: 0.75rem !important;
        padding-right: 2rem !important;
        width: 100% !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
        color: #64748b !important;
        font-weight: 400 !important;
    }
    .select2-container--bootstrap-5 .select2-dropdown {
        z-index: 1080 !important;
        border-color: #cbd5e1;
        box-shadow: 0 10px 25px rgba(0,0,0,0.14);
        border-radius: 8px;
    }
    .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
        border-radius: 6px;
        padding: 6px 12px;
    }
    .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .select2-container--bootstrap-5.select2-container--open .select2-selection {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.2) !important;
    }
    #refBillFeedback, .fs-9 {
        font-size: 0.72rem !important;
        line-height: 1.3 !important;
    }
    /* Tactile Interactive Voucher Tabs */
    .voucher-tabs-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        background: transparent;
        padding: 0;
        border: none;
        margin-bottom: 18px;
        overflow-x: visible;
    }
    .voucher-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary, #1e2538);
        background: var(--card-bg, #ffffff);
        border: 1.5px solid var(--border-color, #e2e8f0);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        user-select: none;
        position: relative;
    }
    .voucher-tab-btn .tab-icon-wrapper {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    .voucher-tab-btn:hover {
        color: var(--theme-primary, #2563eb);
        border-color: var(--theme-primary, #2563eb);
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.14);
    }
    .voucher-tab-btn:hover .tab-icon-wrapper {
        transform: scale(1.1);
    }
    .voucher-tab-btn:active {
        transform: translateY(0);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .voucher-tab-btn.active {
        background: var(--theme-primary, #2563eb) !important;
        color: #ffffff !important;
        border-color: var(--theme-primary, #2563eb) !important;
        box-shadow: 0 4px 16px rgba(37, 99, 235, 0.38);
        transform: translateY(-1px);
    }
    .voucher-tab-btn.active .tab-icon-wrapper {
        background: rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
    }
    .voucher-tab-btn.active i {
        color: #ffffff !important;
    }
    .voucher-tab-btn .tab-count {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 20px;
        background: #f1f5f9;
        color: var(--text-muted, #475569);
        border: 1px solid var(--border-color, #cbd5e1);
        min-width: 24px;
        text-align: center;
        transition: all 0.15s ease;
    }
    .voucher-tab-btn.active .tab-count {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.4) !important;
    }

    /* Dark theme overrides for Voucher Tabs */
    [data-theme="dark"] .voucher-tab-btn,
    body[data-page-theme="dark"] .voucher-tab-btn,
    :root[style*="--main-body-bg: #1"] .voucher-tab-btn,
    :root[style*="--main-body-bg: #0"] .voucher-tab-btn {
        background: var(--card-bg, #151828);
        border-color: var(--border-color, rgba(255, 255, 255, 0.14));
        color: var(--text-primary, #f8fafc);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
    }
    [data-theme="dark"] .voucher-tab-btn .tab-count,
    body[data-page-theme="dark"] .voucher-tab-btn .tab-count,
    :root[style*="--main-body-bg: #1"] .voucher-tab-btn .tab-count,
    :root[style*="--main-body-bg: #0"] .voucher-tab-btn .tab-count {
        background: rgba(255, 255, 255, 0.08);
        color: #94a3b8;
        border-color: rgba(255, 255, 255, 0.15);
    }
    [data-theme="dark"] .voucher-tab-btn:hover,
    body[data-page-theme="dark"] .voucher-tab-btn:hover,
    :root[style*="--main-body-bg: #1"] .voucher-tab-btn:hover,
    :root[style*="--main-body-bg: #0"] .voucher-tab-btn:hover {
        background: rgba(255, 255, 255, 0.06);
        border-color: var(--theme-primary, #2563eb);
    }

    /* Badges */
    .badge-general { background: rgba(168, 85, 247, 0.15); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.3); }
    .badge-cash { background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
    .badge-bank { background: rgba(6, 182, 212, 0.15); color: #06b6d4; border: 1px solid rgba(6, 182, 212, 0.3); }

    /* Type Picker Buttons in Drawer */
    .type-picker-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-bottom: 20px;
    }
    @media (max-width: 768px) {
        .type-picker-grid { grid-template-columns: repeat(2, 1fr); }
    }
    .type-pick-card {
        padding: 10px 8px;
        border-radius: 10px;
        border: 1px solid var(--border-color, #e2e8f0);
        background: var(--card-bg, #ffffff);
        cursor: pointer;
        text-align: center;
        transition: all 0.2s ease;
    }
    .type-pick-card:hover {
        transform: translateY(-2px);
        border-color: var(--theme-primary, #2563eb);
    }
    .type-pick-card.active {
        border-color: var(--theme-primary, #2563eb);
        background: var(--theme-primary-soft, rgba(37, 99, 235, 0.08));
        box-shadow: 0 0 0 2px var(--theme-primary, #2563eb);
    }
    .type-pick-icon {
        font-size: 1.25rem;
        margin-bottom: 4px;
    }
    .type-pick-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text-primary, #0f172a);
    }
    .type-pick-code {
        font-size: 0.7rem;
        font-family: monospace;
        color: var(--text-muted, #64748b);
    }

    /* Live Balance Bar */
    .live-balance-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border-radius: 10px;
        background: rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-color, #e2e8f0);
        margin-top: 14px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .balance-badge-ok {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.35);
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.825rem;
    }
    .balance-badge-unbalanced {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.35);
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.825rem;
    }

    /* Offcanvas Slide-over Drawer */
    .voucher-drawer {
        width: 820px !important;
        max-width: 95vw !important;
        background: var(--card-bg, #ffffff) !important;
        color: var(--text-primary, #0f172a) !important;
        border-left: 1px solid var(--border-color, #e2e8f0) !important;
    }
    .voucher-drawer .offcanvas-header {
        border-bottom: 1px solid var(--border-color, #e2e8f0);
        padding: 16px 24px;
    }
    .voucher-drawer .offcanvas-body {
        padding: 24px;
    }

    /* Journal Dynamic Grid */
    .journal-grid-table th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted, #64748b);
        background: rgba(0, 0, 0, 0.03);
        padding: 8px 10px;
        border-bottom: 1px solid var(--border-color, #e2e8f0);
    }
    .journal-grid-table td {
        padding: 6px 6px;
        border-bottom: 1px solid var(--border-color, #e2e8f0);
    }

    /* Segmented Direction Toggle */
    .direction-toggle-group {
        display: flex;
        width: 100%;
        background: #f1f5f9;
        padding: 4px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        margin-bottom: 16px;
        gap: 4px;
    }
    .direction-toggle-btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px;
        border-radius: 6px;
        border: none;
        background: transparent;
        font-size: 0.85rem;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .direction-toggle-btn:hover:not(.active) {
        background: rgba(0, 0, 0, 0.04);
        color: #1e293b;
    }
    .direction-toggle-btn.active.receipt {
        background: #10b981;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
    }
    .direction-toggle-btn.active.payment {
        background: #ef4444;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
    }

    .form-section-title {
        font-size: 0.825rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--theme-primary, #2563eb);
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 14px;
        margin-bottom: 10px;
    }

    /* =========================================================================
       MODERN ACTION ICON BUTTONS (PREMIUM LOOK & FEEL)
       ========================================================================= */
    .table-actions-wrapper {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }
    .action-icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        text-decoration: none !important;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        outline: none;
    }
    .action-icon-btn:hover {
        transform: translateY(-2px);
    }
    .action-icon-btn:active {
        transform: translateY(0) scale(0.94);
    }
    
    /* 1. View Details (Sapphire Blue) */
    .action-icon-btn.btn-view {
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
        border-color: rgba(37, 99, 235, 0.25);
    }
    .action-icon-btn.btn-view:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.38);
    }

    /* 2. Print Voucher (Violet Purple) */
    .action-icon-btn.btn-print {
        background: rgba(139, 92, 246, 0.12);
        color: #8b5cf6;
        border-color: rgba(139, 92, 246, 0.25);
    }
    .action-icon-btn.btn-print:hover {
        background: #8b5cf6;
        color: #ffffff;
        border-color: #8b5cf6;
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.38);
    }

    /* 3. Edit Voucher (Teal / Cyan) */
    .action-icon-btn.btn-edit {
        background: rgba(14, 165, 233, 0.12);
        color: #0284c7;
        border-color: rgba(14, 165, 233, 0.25);
    }
    .action-icon-btn.btn-edit:hover {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.38);
    }

    /* 4. Delete Voucher (Ruby Red) */
    .action-icon-btn.btn-delete {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
        border-color: rgba(239, 68, 68, 0.25);
    }
    .action-icon-btn.btn-delete:hover {
        background: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.38);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3 accounting-app-container">

    <!-- 1. BREADCRUMBS & PAGE HEADER -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-3">
        <div>
            <nav class="page-breadcrumb" aria-label="breadcrumb">
                <a href="{{ route('dashboard') }}"><i class="fa-solid fa-house-chimney me-1"></i> Home</a>
                <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
                <a href="javascript:void(0)">Accounting</a>
                <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
                <span class="breadcrumb-active">Vouchers</span>
            </nav>
            <h1 class="header-title">
                <span class="header-icon-badge"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                 Vouchers
            </h1>
            <p class="header-subtitle">Professional general journal and financial transaction vouchers with strict ledger integrity.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn-header-action" onclick="loadVouchersTable()" id="btnRefreshVouchers" title="Refresh Vouchers List">
                <i class="fa-solid fa-arrows-rotate" id="syncIcon"></i>
                <span>Refresh</span>
            </button>
            <button class="btn btn-primary d-flex align-items-center gap-2 px-3 py-2 fw-bold shadow-sm" onclick="openCreateDrawer('cash')">
                <i class="fa-solid fa-plus"></i>
                <span>Add Voucher</span>
            </button>
        </div>
    </div>

    <!-- 2. KPI SUMMARY ROW -->
    <div class="kpi-row mb-3" id="kpiSummaryRow">
        <!-- KPI 1: Total Vouchers -->
        <div class="kpi-metric-card">
            <div class="kpi-info">
                <span class="kpi-label">Total Vouchers</span>
                <span class="kpi-value" id="kpiTotalCount">{{ number_format($kpi['total_count']) }}</span>
                <span class="kpi-sub"><i class="fa-solid fa-layer-group text-primary me-1"></i> Total transactions</span>
            </div>
            <div class="kpi-icon-box kpi-icon-blue">
                <i class="fa-solid fa-book-journal-whills"></i>
            </div>
        </div>

        <!-- KPI 2: Total Volume (Debit) -->
        <div class="kpi-metric-card">
            <div class="kpi-info">
                <span class="kpi-label">Transaction Volume</span>
                <span class="kpi-value text-success" id="kpiTotalDebit">₹{{ number_format($kpi['total_debit'], 2) }}</span>
                <span class="kpi-sub"><i class="fa-solid fa-shield-halved text-success me-1"></i> Fully balanced posted</span>
            </div>
            <div class="kpi-icon-box kpi-icon-emerald">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
        </div>

        <!-- KPI 3: Cash & Bank Vouchers -->
        <div class="kpi-metric-card">
            <div class="kpi-info">
                <span class="kpi-label">Cash & Banking</span>
                <span class="kpi-value text-success" id="kpiCashBankCount">{{ $kpi['cash_count'] + $kpi['bank_count'] }}</span>
                <span class="kpi-sub">CV: {{ $kpi['cash_count'] }} | BV: {{ $kpi['bank_count'] }}</span>
            </div>
            <div class="kpi-icon-box kpi-icon-amber">
                <i class="fa-solid fa-building-columns"></i>
            </div>
        </div>

        <!-- KPI 4: General Journal Vouchers -->
        <div class="kpi-metric-card">
            <div class="kpi-info">
                <span class="kpi-label">General Journal</span>
                <span class="kpi-value text-purple" id="kpiGeneralCount">{{ $kpi['general_count'] }}</span>
                <span class="kpi-sub"><i class="fa-solid fa-pen-to-square text-purple me-1"></i> GV: {{ $kpi['general_count'] }} entries</span>
            </div>
            <div class="kpi-icon-box kpi-icon-purple">
                <i class="fa-solid fa-pen-to-square"></i>
            </div>
        </div>
    </div>

    <!-- 3. VOUCHER TYPE TABS -->
    <div class="voucher-tabs-wrapper mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="fs-8 fw-bold text-uppercase text-muted letter-spacing-1">
                <i class="fa-solid fa-layer-group text-primary me-1"></i> Filter by Voucher Type
            </span>
        </div>
        <div class="voucher-tabs-bar">
            <button class="voucher-tab-btn active" data-type="all" onclick="switchVoucherTab('all', this)" title="Show all vouchers">
                <span class="tab-icon-wrapper" style="background: rgba(37, 99, 235, 0.12); color: #2563eb;">
                    <i class="fa-solid fa-list-ul"></i>
                </span>
                <span>All Vouchers</span>
                <span class="tab-count" id="tabCountAll">{{ $kpi['total_count'] }}</span>
            </button>
            <button class="voucher-tab-btn" data-type="cash" onclick="switchVoucherTab('cash', this)" title="Filter Cash Vouchers">
                <span class="tab-icon-wrapper" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                    <i class="fa-solid fa-wallet"></i>
                </span>
                <span>1. Cash (CV)</span>
                <span class="tab-count" id="tabCountCash">{{ $kpi['cash_count'] }}</span>
            </button>
            <button class="voucher-tab-btn" data-type="bank" onclick="switchVoucherTab('bank', this)" title="Filter Bank Vouchers">
                <span class="tab-icon-wrapper" style="background: rgba(6, 182, 212, 0.12); color: #06b6d4;">
                    <i class="fa-solid fa-building-columns"></i>
                </span>
                <span>2. Bank (BV)</span>
                <span class="tab-count" id="tabCountBank">{{ $kpi['bank_count'] }}</span>
            </button>
            <button class="voucher-tab-btn" data-type="general" onclick="switchVoucherTab('general', this)" title="Filter General Journal Vouchers">
                <span class="tab-icon-wrapper" style="background: rgba(168, 85, 247, 0.12); color: #a855f7;">
                    <i class="fa-solid fa-pen-to-square"></i>
                </span>
                <span>3. General (GV)</span>
                <span class="tab-count" id="tabCountGeneral">{{ $kpi['general_count'] }}</span>
            </button>
        </div>
    </div>

    <!-- 4. FILTER TOOLBAR -->
    <div class="filter-toolbar mb-3">
        <div class="filter-group-wrapper">
            <!-- Search Box -->
            <div class="filter-search-box" style="min-width: 260px;">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="filterSearch" placeholder="Search voucher no, party, narration..." onkeyup="debounceFilter()">
            </div>

            <!-- Date From / To -->
            <div class="filter-date-group">
                <div class="filter-date-item">
                    <label for="filterDateFrom">Date From</label>
                    <input type="date" id="filterDateFrom" onchange="loadVouchersTable()">
                </div>
                <div class="filter-date-item">
                    <label for="filterDateTo">Date To</label>
                    <input type="date" id="filterDateTo" onchange="loadVouchersTable()">
                </div>
            </div>

            <!-- Status Filter -->
            <div class="filter-select-item">
                <label for="filterStatus">Status</label>
                <select id="filterStatus" onchange="loadVouchersTable()">
                    <option value="all">All Status</option>
                    <option value="posted" selected>Posted</option>
                    <option value="draft">Draft</option>
                </select>
            </div>

            <!-- Reset Button -->
            <button class="btn-filter-reset" onclick="resetFilters()" title="Reset all filters">
                <i class="fa-solid fa-arrow-rotate-left"></i>
                <span>Reset</span>
            </button>
        </div>
    </div>

    <!-- 5. ENTERPRISE VOUCHER TABLE CARD -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="enterprise-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;">S.No.</th>
                        <th style="width: 140px;">Voucher No.</th>
                        <th style="width: 110px;">Date</th>
                        <th style="width: 130px;">Type</th>
                        <th style="min-width: 220px;">Party / Account Name</th>
                        <th style="width: 140px;">Ref / Bill No.</th>
                        <th style="min-width: 180px;">Description / Narration</th>
                        <th class="text-center" style="width: 100px;">DR/CR</th>
                        <th class="text-end" style="width: 140px;">Amount</th>
                        <th class="text-center" style="width: 110px;">Status</th>
                        <th style="width: 130px;">Created By</th>
                        <th class="text-end" style="width: 190px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="vouchersTableBody">
                    <tr class="skeleton-row">
                        <td colspan="12" class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                            Loading accounting vouchers...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer Pagination Bar -->
        <div class="table-footer-bar">
            <div>
                Showing <strong id="paginationShowing">0</strong> of <strong id="paginationTotal">0</strong> vouchers
            </div>
            <div id="paginationContainer" class="d-flex align-items-center gap-1">
                <!-- Pagination buttons generated by JS -->
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
     RIGHT-SIDE DRAWER: ADD / EDIT ACCOUNTING VOUCHER
     ========================================================================== -->
<div class="offcanvas offcanvas-end voucher-drawer" tabindex="-1" id="voucherDrawer" aria-labelledby="voucherDrawerLabel">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title fw-bold d-flex align-items-center gap-2" id="voucherDrawerLabel">
                <i class="fa-solid fa-file-circle-plus text-primary"></i>
                <span id="drawerTitleText">Create Voucher</span>
            </h5>
            <small class="text-muted" id="drawerSubtitleText">Accounting transaction record</small>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        
        <!-- 1. VOUCHER TYPE SELECTOR GRID (Disabled in Edit mode) -->
        <div id="voucherTypeSelectorWrapper">
            <label class="form-label fs-8 fw-bold text-muted text-uppercase letter-spacing-1 mb-2">Select Voucher Type</label>
            <div class="type-picker-grid">
                <div class="type-pick-card active" id="typePick_cash" onclick="selectVoucherType('cash')">
                    <div class="type-pick-icon text-success"><i class="fa-solid fa-wallet"></i></div>
                    <div class="type-pick-title">Cash</div>
                    <div class="type-pick-code">CV-000000</div>
                </div>
                <div class="type-pick-card" id="typePick_bank" onclick="selectVoucherType('bank')">
                    <div class="type-pick-icon text-cyan"><i class="fa-solid fa-building-columns"></i></div>
                    <div class="type-pick-title">Bank</div>
                    <div class="type-pick-code">BV-000000</div>
                </div>
                <div class="type-pick-card" id="typePick_general" onclick="selectVoucherType('general')">
                    <div class="type-pick-icon text-purple"><i class="fa-solid fa-pen-to-square"></i></div>
                    <div class="type-pick-title">General</div>
                    <div class="type-pick-code">GV-000000</div>
                </div>
            </div>
        </div>

        <form id="voucherForm" onsubmit="handleVoucherSubmit(event)" novalidate>
            @csrf
            <input type="hidden" id="v_id" name="id">
            <input type="hidden" id="v_type" name="voucher_type" value="cash">
            <input type="hidden" id="v_party_id" name="party_id">
            <input type="hidden" id="v_invoice_id" name="invoice_id">

            <!-- COMMON HEADER FIELDS: VOUCHER NO., VOUCHER DATE & TRANSACTION IN ONE ROW -->
            <div class="row g-3 mb-3">
                <div class="col-4">
                    <label class="form-label fs-8 fw-bold">VOUCHER NO. <span class="text-danger">*</span></label>
                    <input type="text" class="form-control font-monospace fw-bold bg-light" id="v_voucher_no" name="voucher_no" readonly>
                    <small class="fs-9 text-muted d-block mt-1">Atomic sequential numbering</small>
                </div>
                <div class="col-4">
                    <label class="form-label fs-8 fw-bold">VOUCHER DATE <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="v_voucher_date" name="voucher_date" value="{{ date('Y-m-d') }}" onchange="updateSaveButtonState()">
                </div>
                <div class="col-4">
                    <label class="form-label fs-8 fw-bold">TRANSACTION <span class="text-danger">*</span></label>
                    <div id="txn_slot_cash">
                        <button type="button" class="direction-toggle-btn active receipt w-100" id="cashDirReceiptBtn" onclick="setCashDirection('receipt')" style="height: 38px; cursor: default;">
                            <i class="fa-solid fa-arrow-down-left me-1"></i> Credit
                        </button>
                    </div>
                    <div id="txn_slot_bank" class="d-none">
                        <button type="button" class="direction-toggle-btn active receipt w-100" id="bankDirReceiptBtn" onclick="setBankDirection('receipt')" style="height: 38px; cursor: default;">
                            <i class="fa-solid fa-arrow-down-left me-1"></i> Credit
                        </button>
                    </div>
                    <div id="txn_slot_general" class="d-none">
                        <div class="direction-toggle-group m-0" style="height: 38px; padding: 2px;">
                            <button type="button" class="direction-toggle-btn active receipt" id="generalDirCreditBtn" onclick="setGeneralDirection('receipt')" style="padding: 4px 8px; font-size: 0.8rem;">
                                <i class="fa-solid fa-arrow-down-left me-1"></i> Credit
                            </button>
                            <button type="button" class="direction-toggle-btn payment" id="generalDirDebitBtn" onclick="setGeneralDirection('payment')" style="padding: 4px 8px; font-size: 0.8rem;">
                                <i class="fa-solid fa-arrow-up-right me-1"></i> Debit
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==========================================================
                 TYPE-SPECIFIC SECTION 3: CASH VOUCHER
                 ========================================================== -->
            <div id="section_cash" class="d-none">
                <div class="row g-3 mb-3">
                    {{-- Hidden Cash Account Ledger (Defaults to Cash in Hand) --}}
                    <div style="display: none;">
                        <select class="form-select" id="cash_account_ledger" onchange="rebuildCashEntries()">
                            @foreach($cashAccounts as $cash)
                                <option value="{{ $cash->id }}" selected>{{ $cash->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 1. SELECT PARTY * -->
                    <div class="col-12">
                        <label class="form-label fs-8 fw-bold" id="cashCounterAccountLabel">SELECT PARTY <span class="text-danger">*</span></label>
                        <select class="form-select select2-party-search" id="cash_counter_ledger">
                            <option value=""></option>
                            @foreach($allParties as $party)
                                <option value="{{ $party->id }}" data-party-id="{{ $party->party_id }}" data-name="{{ $party->name }}">{{ $party->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. REFERENCE / BILL NO. (Directly below Party) -->
                    <div class="col-12" id="cash_ref_slot">
                        <div id="ref_bill_component">
                            <label class="form-label fs-8 fw-bold d-flex align-items-center justify-content-between mb-1">
                                <span>REFERENCE / BILL NO.</span>
                                <span id="pendingBillsCountBadge" class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle d-none" style="font-size: 0.68rem;">0 Pending</span>
                            </label>
                            <select class="form-select font-monospace" id="v_bill_select" onchange="onBillSelectChange(this)" style="min-height: 38px;" disabled>
                                <option value="">Select party first</option>
                            </select>
                            <input type="hidden" id="v_reference_no" name="reference_no">
                            <div id="refBillFeedback" class="fs-9 mt-1"></div>
                        </div>
                    </div>

                    <!-- 3. CASH AMOUNT (₹) * -->
                    <div class="col-12">
                        <label class="form-label fs-8 fw-bold">CASH AMOUNT (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control font-monospace fw-bold fs-5 text-success" id="cash_amount" placeholder="0.00" oninput="rebuildCashEntries()">
                    </div>
                </div>
            </div>

            <!-- ==========================================================
                 TYPE-SPECIFIC SECTION 4: BANK VOUCHER
                 ========================================================== -->
            <div id="section_bank" class="d-none">
                <div class="row g-3 mb-3">
                    {{-- Hidden Bank Account Ledger (Defaults to primary bank account) --}}
                    <div style="display: none;">
                        <select class="form-select" id="bank_account_ledger" onchange="rebuildBankEntries()">
                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}" selected>{{ $bank->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 1. SELECT PARTY * -->
                    <div class="col-12">
                        <label class="form-label fs-8 fw-bold" id="bankCounterAccountLabel">SELECT PARTY <span class="text-danger">*</span></label>
                        <select class="form-select select2-party-search" id="bank_counter_ledger">
                            <option value=""></option>
                            @foreach($allParties as $party)
                                <option value="{{ $party->id }}" data-party-id="{{ $party->party_id }}" data-name="{{ $party->name }}">{{ $party->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. REFERENCE / BILL NO. -->
                    <div class="col-12" id="bank_ref_slot"></div>

                    <!-- 3. BANK TRANSACTION DETAILS -->
                    <div class="col-md-4">
                        <label class="form-label fs-8 fw-bold">TRANSACTION MODE</label>
                        <select class="form-select" id="bank_payment_method">
                            <option value="neft" selected>NEFT</option>
                            <option value="rtgs">RTGS</option>
                            <option value="imps">IMPS</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-8 fw-bold">CHEQUE NO. / UTR / TXN ID</label>
                        <input type="text" class="form-control font-monospace" id="bank_instrument_no" placeholder="e.g. 19482049281">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-8 fw-bold">CHEQUE / TXN DATE</label>
                        <input type="date" class="form-control" id="bank_instrument_date" value="{{ date('Y-m-d') }}">
                    </div>

                    <!-- 4. TRANSACTION AMOUNT (₹) * -->
                    <div class="col-12">
                        <label class="form-label fs-8 fw-bold">TRANSACTION AMOUNT (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control font-monospace fw-bold fs-5 text-cyan" id="bank_amount" placeholder="0.00" oninput="rebuildBankEntries()">
                    </div>
                </div>
            </div>

            <!-- ==========================================================
                 TYPE-SPECIFIC SECTION 5: GENERAL VOUCHER
                 ========================================================== -->
            <div id="section_general" class="d-none">
                <div class="row g-3 mb-3">
                    {{-- Hidden Discount Ledger (Defaults to Discount / Rebate A/c) --}}
                    <div style="display: none;">
                        <select class="form-select" id="general_discount_ledger">
                            @if(isset($discountAccount))
                                <option value="{{ $discountAccount->id }}" selected>{{ $discountAccount->name }}</option>
                            @endif
                        </select>
                    </div>

                    <!-- 1. SELECT PARTY * -->
                    <div class="col-12">
                        <label class="form-label fs-8 fw-bold" id="generalCounterAccountLabel">SELECT PARTY <span class="text-danger">*</span></label>
                        <select class="form-select select2-party-search" id="general_counter_ledger">
                            <option value=""></option>
                            @foreach($allParties as $party)
                                <option value="{{ $party->id }}" data-party-id="{{ $party->party_id }}" data-name="{{ $party->name }}">{{ $party->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. REFERENCE / BILL NO. -->
                    <div class="col-12" id="general_ref_slot"></div>

                    <!-- 3. AMOUNT (₹) * -->
                    <div class="col-12">
                        <label class="form-label fs-8 fw-bold" id="generalAmountLabel">CREDIT / DISCOUNT AMOUNT (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control font-monospace fw-bold fs-5 text-primary" id="general_discount" placeholder="0.00" oninput="rebuildGeneralEntries()">
                    </div>
                </div>
            </div>

            <!-- ==========================================================
                 DOUBLE-ENTRY ACCOUNTING GRID (Hidden by user preference;
                 auto-synced in background for financial postings)
                 ========================================================== -->
            <div class="mt-4 d-none" id="section_double_entry_grid" style="display: none !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="form-section-title m-0">
                        <i class="fa-solid fa-scale-balanced"></i> Double-Entry Accounting Rows
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="btnAddGridRow" onclick="addJournalRow()">
                        <i class="fa-solid fa-plus me-1"></i> Add Row
                    </button>
                </div>

                <div class="table-responsive border rounded-3 overflow-hidden">
                    <table class="table journal-grid-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="min-width: 220px;">Account / Ledger</th>
                                <th style="min-width: 150px;">Description</th>
                                <th class="text-end" style="width: 130px;">Debit (₹)</th>
                                <th class="text-end" style="width: 130px;">Credit (₹)</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="journalGridTbody">
                            <!-- Dynamic rows appended by JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Live Balancing Display Bar -->
                <div class="live-balance-bar">
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <span class="fs-8 text-muted text-uppercase fw-bold">Total Debit</span>
                            <div class="fs-6 fw-bold text-dark font-monospace" id="liveTotalDebit">₹0.00</div>
                        </div>
                        <div class="border-start ps-3">
                            <span class="fs-8 text-muted text-uppercase fw-bold">Total Credit</span>
                            <div class="fs-6 fw-bold text-dark font-monospace" id="liveTotalCredit">₹0.00</div>
                        </div>
                        <div class="border-start ps-3">
                            <span class="fs-8 text-muted text-uppercase fw-bold">Difference</span>
                            <div class="fs-6 fw-bold font-monospace" id="liveDifference">₹0.00</div>
                        </div>
                    </div>
                    <div>
                        <span id="balanceStatusBadge" class="balance-badge-ok">
                            <i class="fa-solid fa-circle-check"></i> Balanced
                        </span>
                    </div>
                </div>
            </div>

            <!-- NARRATION & REMARKS -->
            <div class="mt-3">
                <label class="form-label fs-8 fw-bold">NARRATION / REMARKS</label>
                <textarea class="form-control" id="v_narration" name="narration" rows="2" placeholder="Being amount received/paid towards..."></textarea>
            </div>

            <!-- DRAWER ACTION BUTTONS -->
            <div class="d-flex align-items-center justify-content-between mt-4 pt-3 border-top">
                <button type="button" class="btn btn-light border px-3" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="btnSaveVoucher" disabled>
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Voucher
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================================================
     RIGHT-SIDE DRAWER: VIEW VOUCHER DETAILS
     ========================================================================== -->
<div class="offcanvas offcanvas-end voucher-drawer" tabindex="-1" id="viewVoucherDrawer" aria-labelledby="viewVoucherDrawerLabel">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title fw-bold d-flex align-items-center gap-2" id="viewVoucherDrawerLabel">
                <i class="fa-solid fa-receipt text-primary"></i>
                <span id="viewVoucherNo">VOUCHER-DETAILS</span>
            </h5>
            <span id="viewTypeBadge" class="badge badge-general">General Voucher</span>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        
        <!-- Header Info Cards -->
        <div class="row g-2 mb-4 p-3 rounded-3 bg-light border">
            <div class="col-6 col-md-3">
                <div class="fs-9 text-muted text-uppercase fw-bold">Voucher Date</div>
                <div class="fs-7 fw-bold text-dark" id="viewDate">-</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fs-9 text-muted text-uppercase fw-bold">Balance Status</div>
                <div class="fs-7 fw-bold text-success" id="viewBalanceStatus"><i class="fa-solid fa-circle-check"></i> Balanced ✓</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fs-9 text-muted text-uppercase fw-bold">Party / Account</div>
                <div class="fs-7 fw-bold text-dark" id="viewParty">-</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fs-9 text-muted text-uppercase fw-bold">Reference No.</div>
                <div class="fs-7 fw-bold text-dark font-monospace" id="viewReference">-</div>
            </div>
            <div class="col-12 mt-2 pt-2 border-top" id="viewNarrationRow">
                <div class="fs-9 text-muted text-uppercase fw-bold">Narration</div>
                <div class="fs-8 fst-italic text-dark" id="viewNarration">-</div>
            </div>
        </div>

        <!-- Double Entry Lines Table -->
        <div class="mb-4">
            <h6 class="fw-bold fs-7 text-uppercase letter-spacing-1 mb-2">Accounting Entries Breakdown</h6>
            <div class="table-responsive border rounded-3 overflow-hidden">
                <table class="table enterprise-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Account / Ledger</th>
                            <th>Code / Category</th>
                            <th class="text-end" style="width: 140px;">Debit (₹)</th>
                            <th class="text-end" style="width: 140px;">Credit (₹)</th>
                        </tr>
                    </thead>
                    <tbody id="viewEntriesTbody">
                        <!-- Entries inserted by JS -->
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="3" class="text-end">TOTAL:</td>
                            <td class="text-end text-success font-monospace" id="viewTotalDebit">₹0.00</td>
                            <td class="text-end text-success font-monospace" id="viewTotalCredit">₹0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Audit Stamps -->
        <div class="p-3 bg-light rounded-3 border fs-8 text-muted d-flex justify-content-between flex-wrap gap-2 mb-4">
            <div><i class="fa-regular fa-user me-1"></i> Created By: <strong id="viewCreatedBy">-</strong> on <span id="viewCreatedAt">-</span></div>
            <div><i class="fa-regular fa-clock me-1"></i> Updated: <span id="viewUpdatedAt">-</span></div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" data-bs-dismiss="offcanvas">Close</button>
            <a href="javascript:void(0)" id="viewPrintBtn" target="_blank" class="btn btn-outline-primary btn-sm rounded-3 fw-bold">
                <i class="fa-solid fa-print me-1"></i> Print Voucher
            </a>
            <button type="button" id="viewEditBtn" class="btn btn-primary btn-sm rounded-3 fw-bold">
                <i class="fa-solid fa-pen-to-square me-1"></i> Edit Voucher
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Global Available Ledgers and State
    const ALL_LEDGERS = @json($allLedgers);
    let activeVoucherType = 'general';
    let activeTab = 'all';
    let currentVoucherPage = 1;
    let filterDebounceTimer = null;
    let isSubmitting = false;

    document.addEventListener("DOMContentLoaded", function () {
        loadVouchersTable();
        initJournalRowDefaults();
        initPartySelect2();
        updateReferenceNoState();
    });

    // =========================================================================
    // 1. DATA TABLE LOADING & RENDERING
    // =========================================================================
    function loadVouchersTable(page = 1) {
        currentVoucherPage = page;
        const tbody = document.getElementById("vouchersTableBody");
        tbody.innerHTML = `
            <tr class="skeleton-row">
                <td colspan="12" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    Loading accounting vouchers...
                </td>
            </tr>
        `;

        const params = new URLSearchParams({
            page: page,
            type: activeTab,
            search: document.getElementById("filterSearch").value,
            date_from: document.getElementById("filterDateFrom").value,
            date_to: document.getElementById("filterDateTo").value,
            status: document.getElementById("filterStatus").value,
        });

        fetch(`/vouchers/data?${params.toString()}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    renderVouchersTable(res.data.data);
                    renderPagination(res.data);
                } else {
                    tbody.innerHTML = `<tr><td colspan="12" class="text-center py-4 text-danger">${res.message || 'Error loading records'}</td></tr>`;
                }
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="12" class="text-center py-4 text-danger">Failed to connect to server.</td></tr>`;
            });
    }

    function renderVouchersTable(records) {
        const tbody = document.getElementById("vouchersTableBody");
        if (!records || records.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="12" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-folder-open fs-2 mb-2 d-block opacity-50"></i>
                        No vouchers found matching criteria.
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        records.forEach(v => {
            const statusBadge = v.status === 'posted' 
                ? '<span class="badge bg-success-subtle text-success border border-success px-2 py-1 rounded-pill fs-9 fw-bold">POSTED</span>'
                : '<span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1 rounded-pill fs-9 fw-bold">DRAFT</span>';

            // Determine DR/CR column display:
            let drCrHtml = '<span class="text-muted fw-bold fs-7">—</span>';
            if (v.dr_cr === 'Debit') {
                drCrHtml = '<span class="badge bg-danger-subtle text-danger border border-danger px-2.5 py-0.5 rounded-pill fs-8 fw-bold">Debit</span>';
            } else if (v.dr_cr === 'Credit') {
                drCrHtml = '<span class="badge bg-success-subtle text-success border border-success px-2.5 py-0.5 rounded-pill fs-8 fw-bold">Credit</span>';
            }

            const amountValue = (v.amount && v.amount > 0) ? v.amount : (v.total_debit > 0 ? v.total_debit : v.total_credit);

            html += `
                <tr>
                    <td class="text-muted fs-8">${v.s_no}</td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <span class="font-monospace fw-bold text-primary cursor-pointer" onclick="viewVoucherDetails(${v.id})" title="Click to view details">
                                ${v.voucher_no}
                            </span>
                            <button class="btn btn-sm p-0 text-muted" type="button" onclick="navigator.clipboard.writeText('${v.voucher_no}'); Swal.fire({toast:true, position:'top-end', icon:'success', title:'Copied ${v.voucher_no}', timer:1500, showConfirmButton:false});" title="Copy Number">
                                <i class="fa-regular fa-copy fs-9"></i>
                            </button>
                        </div>
                    </td>
                    <td class="fs-8 fw-semibold">${v.voucher_date}</td>
                    <td>
                        <span class="badge ${v.type_badge_class} rounded-pill px-2.5 py-1 fs-8 fw-bold">
                            ${v.type_label}
                        </span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark fs-7">${escapeHtml(v.party_name)}</div>
                    </td>
                    <td>
                        <span class="font-monospace text-secondary fs-8">${escapeHtml(v.reference_no)}</span>
                    </td>
                    <td>
                        <span class="text-muted fs-8 text-truncate d-inline-block" style="max-width: 220px;" title="${escapeHtml(v.description)}">
                            ${escapeHtml(v.description)}
                        </span>
                    </td>
                    <td class="text-center">${drCrHtml}</td>
                    <td class="text-end fw-bold font-monospace text-dark fs-7">₹${formatNumber(amountValue, 2)}</td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="fs-8 text-muted">${escapeHtml(v.created_by_name)}</td>
                    <td class="text-end" onclick="event.stopPropagation()">
                        <div class="table-actions-wrapper">
                            <button type="button" class="action-icon-btn btn-view" onclick="viewVoucherDetails(${v.id})" title="View Details">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <a href="/vouchers/${v.id}/print" target="_blank" class="action-icon-btn btn-print" title="Print Voucher">
                                <i class="fa-solid fa-print"></i>
                            </a>
                            <button type="button" class="action-icon-btn btn-edit" onclick="editVoucher(${v.id})" title="Edit Voucher">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button type="button" class="action-icon-btn btn-delete" onclick="confirmDeleteVoucher(${v.id}, '${v.voucher_no}')" title="Delete Voucher">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    function renderPagination(meta) {
        document.getElementById("paginationShowing").textContent = meta.data ? meta.data.length : 0;
        document.getElementById("paginationTotal").textContent = meta.total || 0;

        const container = document.getElementById("paginationContainer");
        if (meta.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '';
        if (meta.current_page > 1) {
            html += `<button class="btn btn-sm btn-light border p-1 px-2" onclick="loadVouchersTable(${meta.current_page - 1})"><i class="fa-solid fa-chevron-left"></i></button>`;
        }
        for (let p = 1; p <= meta.last_page; p++) {
            if (p === meta.current_page) {
                html += `<button class="btn btn-sm btn-primary p-1 px-2 fw-bold">${p}</button>`;
            } else if (p === 1 || p === meta.last_page || Math.abs(p - meta.current_page) <= 2) {
                html += `<button class="btn btn-sm btn-light border p-1 px-2" onclick="loadVouchersTable(${p})">${p}</button>`;
            }
        }
        if (meta.current_page < meta.last_page) {
            html += `<button class="btn btn-sm btn-light border p-1 px-2" onclick="loadVouchersTable(${meta.current_page + 1})"><i class="fa-solid fa-chevron-right"></i></button>`;
        }
        container.innerHTML = html;
    }

    // =========================================================================
    // 2. TAB SWITCHING & FILTERS
    // =========================================================================
    function switchVoucherTab(type, btn) {
        activeTab = type;
        document.querySelectorAll('.voucher-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        loadVouchersTable(1);
    }

    function debounceFilter() {
        clearTimeout(filterDebounceTimer);
        filterDebounceTimer = setTimeout(() => {
            loadVouchersTable(1);
        }, 300);
    }

    function resetFilters() {
        document.getElementById("filterSearch").value = '';
        document.getElementById("filterDateFrom").value = '';
        document.getElementById("filterDateTo").value = '';
        document.getElementById("filterStatus").value = 'posted';
        loadVouchersTable(1);
    }

    // =========================================================================
    // 3. VOUCHER TYPE PICKER & DYNAMIC FORMS (DRAWER)
    // =========================================================================
    function openCreateDrawer(defaultType = 'cash') {
        document.getElementById("voucherForm").reset();
        document.getElementById("v_id").value = '';
        document.getElementById("v_party_id").value = '';
        document.getElementById("v_invoice_id").value = '';
        document.getElementById("drawerTitleText").textContent = 'Create Voucher';
        document.getElementById("drawerSubtitleText").textContent = 'Accounting transaction record';
        document.getElementById("voucherTypeSelectorWrapper").classList.remove('d-none');
        
        selectVoucherType(defaultType);

        // Fetch Next Voucher Number
        fetchNextVoucherNumber(defaultType);

        updateSaveButtonState();

        // Reset party select dropdowns
        if (window.jQuery && $.fn.select2) {
            $('#cash_counter_ledger').val('').trigger('change');
            $('#bank_counter_ledger').val('').trigger('change');
            $('#general_counter_ledger').val('').trigger('change');
        }

        const billSelect = document.getElementById("v_bill_select");
        if (billSelect) {
            billSelect.innerHTML = '<option value="">Select party first</option>';
            billSelect.disabled = true;
        }
        const badge = document.getElementById("pendingBillsCountBadge");
        if (badge) badge.classList.add('d-none');
        const feedback = document.getElementById("refBillFeedback");
        if (feedback) feedback.innerHTML = '';
        const discInput = document.getElementById("general_discount");
        if (discInput) discInput.value = '';
        setGeneralDirection('receipt');

        // Apply lock state for Reference/Bill No
        updateReferenceNoState();

        // Open offcanvas
        const drawerEl = document.getElementById("voucherDrawer");
        const drawer = bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
        drawer.show();

        setTimeout(() => initPartySelect2(true), 120);
    }

    function selectVoucherType(type, isEdit = false) {
        activeVoucherType = type;
        document.getElementById("v_type").value = type;

        // Highlight picker card
        document.querySelectorAll('.type-pick-card').forEach(c => c.classList.remove('active'));
        const card = document.getElementById(`typePick_${type}`);
        if (card) card.classList.add('active');

        // Hide all type sections and sync transaction slot
        ['cash', 'bank', 'general'].forEach(t => {
            const sec = document.getElementById(`section_${t}`);
            if (sec) sec.classList.add('d-none');
            const slot = document.getElementById(`txn_slot_${t}`);
            if (slot) {
                if (t === type) slot.classList.remove('d-none');
                else slot.classList.add('d-none');
            }
        });

        const gridBtn = document.getElementById("btnAddGridRow");
        if (gridBtn) gridBtn.style.display = 'none';

        // Specialized voucher form active
        const sec = document.getElementById(`section_${type}`);
        if (sec) sec.classList.remove('d-none');

        // Move ref_bill_component directly into the active section slot (below party)
        const refComp = document.getElementById("ref_bill_component");
        const targetSlot = document.getElementById(`${type}_ref_slot`);
        if (refComp && targetSlot && !targetSlot.contains(refComp)) {
            targetSlot.appendChild(refComp);
        }
        
        if (!isEdit) {
            if (type === 'cash') rebuildCashEntries();
            else if (type === 'bank') rebuildBankEntries();
            else if (type === 'general') rebuildGeneralEntries();
        }

        // Manage Reference / Bill No locking behavior
        if (!isEdit) {
            updateReferenceNoState();
            fetchNextVoucherNumber(type);
        }

        if (type === 'cash' || type === 'bank' || type === 'general') {
            setTimeout(() => initPartySelect2(true), 60);
        }
        updateSaveButtonState();
    }

    function fetchNextVoucherNumber(type) {
        fetch(`/vouchers/next-number?type=${type}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    document.getElementById("v_voucher_no").value = res.next_voucher_no;
                }
            })
            .catch(console.error);
    }

    // =========================================================================
    // 6. CASH VOUCHER LOGIC
    // =========================================================================
    let cashDirection = 'receipt';
    function setCashDirection(dir) {
        cashDirection = dir;
        const rBtn = document.getElementById("cashDirReceiptBtn");
        const pBtn = document.getElementById("cashDirPaymentBtn");

        if (dir === 'receipt') {
            if (rBtn) rBtn.classList.add('active');
            if (pBtn) pBtn.classList.remove('active');
        } else {
            if (pBtn) pBtn.classList.add('active');
            if (rBtn) rBtn.classList.remove('active');
        }
        const lbl = document.getElementById("cashCounterAccountLabel");
        if (lbl) lbl.innerHTML = 'SELECT PARTY <span class="text-danger">*</span>';
        rebuildCashEntries();
    }

    function rebuildCashEntries() {
        if (activeVoucherType !== 'cash') return;

        const cashLedgerEl = document.getElementById("cash_account_ledger");
        let cashLedgerId = cashLedgerEl ? cashLedgerEl.value : null;
        if (!cashLedgerId) {
            const cashObj = ALL_LEDGERS.find(l => l.category === 'cash')
                         || ALL_LEDGERS.find(l => l.code === 'CASH-01');
            if (cashObj) cashLedgerId = cashObj.id;
        }
        if (!cashLedgerId && ALL_LEDGERS.length > 0) {
            cashLedgerId = ALL_LEDGERS[0].id;
        }

        const counterSelect = document.getElementById("cash_counter_ledger");
        const counterVal = (window.jQuery ? $('#cash_counter_ledger').val() : null) || counterSelect?.value || null;
        const amount = parseFloat(document.getElementById("cash_amount")?.value) || 0;

        if (!counterVal || amount <= 0) {
            renderDynamicRows([]);
            updateSaveButtonState();
            return;
        }

        const opt = counterSelect ? (counterSelect.querySelector(`option[value="${counterVal}"]`) || counterSelect.options[counterSelect.selectedIndex]) : null;
        const optPartyId = opt ? opt.getAttribute('data-party-id') : null;
        const partyName = opt ? (opt.getAttribute('data-name') || opt.textContent.split(' (')[0].trim()) : 'Party';

        let counterLedgerId = counterVal;
        const matchedLedger = ALL_LEDGERS.find(l => String(l.id) === String(counterVal))
                           || ALL_LEDGERS.find(l => optPartyId && String(l.party_id) === String(optPartyId))
                           || ALL_LEDGERS.find(l => l.name === partyName);
        if (matchedLedger) {
            counterLedgerId = matchedLedger.id;
        }

        const rows = [];
        if (cashDirection === 'receipt') {
            rows.push({ ledger_id: cashLedgerId, description: `Cash received from ${partyName}`, debit: amount, credit: 0 });
            rows.push({ ledger_id: counterLedgerId, description: `Credit to ${partyName}`, debit: 0, credit: amount, party_name: partyName });
        } else {
            rows.push({ ledger_id: counterLedgerId, description: `Debit to ${partyName}`, debit: amount, credit: 0, party_name: partyName });
            rows.push({ ledger_id: cashLedgerId, description: `Cash paid out`, debit: 0, credit: amount });
        }

        renderDynamicRows(rows);
        updateSaveButtonState();
    }

    // =========================================================================
    // 7. BANK VOUCHER LOGIC
    // =========================================================================
    let bankDirection = 'receipt';
    function setBankDirection(dir) {
        bankDirection = dir;
        const rBtn = document.getElementById("bankDirReceiptBtn");
        const pBtn = document.getElementById("bankDirPaymentBtn");

        if (dir === 'receipt') {
            if (rBtn) rBtn.classList.add('active');
            if (pBtn) pBtn.classList.remove('active');
        } else {
            if (pBtn) pBtn.classList.add('active');
            if (rBtn) rBtn.classList.remove('active');
        }
        const lbl = document.getElementById("bankCounterAccountLabel");
        if (lbl) lbl.innerHTML = 'SELECT PARTY <span class="text-danger">*</span>';
        rebuildBankEntries();
    }

    function rebuildBankEntries() {
        if (activeVoucherType !== 'bank') return;

        const bankLedgerEl = document.getElementById("bank_account_ledger");
        let bankLedgerId = bankLedgerEl ? bankLedgerEl.value : null;
        if (!bankLedgerId) {
            const bankObj = ALL_LEDGERS.find(l => l.category === 'bank')
                         || ALL_LEDGERS.find(l => l.code === 'BANK-01');
            if (bankObj) bankLedgerId = bankObj.id;
        }
        if (!bankLedgerId && ALL_LEDGERS.length > 0) {
            bankLedgerId = ALL_LEDGERS[0].id;
        }

        const counterSelect = document.getElementById("bank_counter_ledger");
        const counterVal = (window.jQuery ? $('#bank_counter_ledger').val() : null) || counterSelect?.value || null;
        const amount = parseFloat(document.getElementById("bank_amount")?.value) || 0;

        if (!counterVal || amount <= 0) {
            renderDynamicRows([]);
            updateSaveButtonState();
            return;
        }

        const opt = counterSelect ? (counterSelect.querySelector(`option[value="${counterVal}"]`) || counterSelect.options[counterSelect.selectedIndex]) : null;
        const optPartyId = opt ? opt.getAttribute('data-party-id') : null;
        const partyName = opt ? (opt.getAttribute('data-name') || opt.textContent.split(' (')[0].trim()) : 'Party';

        let counterLedgerId = counterVal;
        const matchedLedger = ALL_LEDGERS.find(l => String(l.id) === String(counterVal))
                           || ALL_LEDGERS.find(l => optPartyId && String(l.party_id) === String(optPartyId))
                           || ALL_LEDGERS.find(l => l.name === partyName);
        if (matchedLedger) {
            counterLedgerId = matchedLedger.id;
        }

        const rows = [];
        if (bankDirection === 'receipt') {
            rows.push({ ledger_id: bankLedgerId, description: `Bank deposit from ${partyName}`, debit: amount, credit: 0 });
            rows.push({ ledger_id: counterLedgerId, description: `Credit to ${partyName}`, debit: 0, credit: amount, party_name: partyName });
        } else {
            rows.push({ ledger_id: counterLedgerId, description: `Debit to ${partyName}`, debit: amount, credit: 0, party_name: partyName });
            rows.push({ ledger_id: bankLedgerId, description: `Bank payout`, debit: 0, credit: amount });
        }

        renderDynamicRows(rows);
        updateSaveButtonState();
    }

    // =========================================================================
    // 7.1. GENERAL VOUCHER LOGIC (CREDIT & DEBIT SUPPORT)
    // =========================================================================
    let generalDirection = 'receipt'; // 'receipt' = Credit Party, 'payment' = Debit Party

    function setGeneralDirection(dir) {
        generalDirection = dir;
        const cBtn = document.getElementById("generalDirCreditBtn");
        const dBtn = document.getElementById("generalDirDebitBtn");

        if (dir === 'receipt') {
            if (cBtn) {
                cBtn.classList.add('active', 'receipt');
            }
            if (dBtn) {
                dBtn.classList.remove('active', 'payment');
            }
        } else {
            if (dBtn) {
                dBtn.classList.add('active', 'payment');
            }
            if (cBtn) {
                cBtn.classList.remove('active', 'receipt');
            }
        }

        const lbl = document.getElementById("generalAmountLabel");
        if (lbl) {
            lbl.innerHTML = dir === 'payment'
                ? 'DEBIT / ADJUSTMENT AMOUNT (₹) <span class="text-danger">*</span>'
                : 'CREDIT / DISCOUNT AMOUNT (₹) <span class="text-danger">*</span>';
        }

        rebuildGeneralEntries();
    }

    function rebuildGeneralEntries() {
        if (activeVoucherType !== 'general') return;

        const discSelect = document.getElementById("general_discount_ledger");
        let discLedgerId = discSelect ? discSelect.value : null;
        if (!discLedgerId) {
            const discObj = ALL_LEDGERS.find(l => l.code === 'DISC-01')
                         || ALL_LEDGERS.find(l => l.name.toLowerCase().includes('discount'));
            if (discObj) discLedgerId = discObj.id;
        }
        if (!discLedgerId && ALL_LEDGERS.length > 1) {
            discLedgerId = ALL_LEDGERS[1].id;
        }

        const counterSelect = document.getElementById("general_counter_ledger");
        const counterVal = (window.jQuery ? $('#general_counter_ledger').val() : null) || counterSelect?.value || null;
        const discountAmt = parseFloat(document.getElementById("general_discount")?.value) || 0;

        if (!counterVal || discountAmt <= 0) {
            renderDynamicRows([]);
            updateSaveButtonState();
            return;
        }

        const opt = counterSelect ? (counterSelect.querySelector(`option[value="${counterVal}"]`) || counterSelect.options[counterSelect.selectedIndex]) : null;
        const optPartyId = opt ? opt.getAttribute('data-party-id') : null;
        const partyName = opt ? (opt.getAttribute('data-name') || opt.textContent.split(' (')[0].trim()) : 'Party';

        let partyLedgerId = counterVal;
        const matchedLedger = ALL_LEDGERS.find(l => String(l.id) === String(counterVal))
                           || ALL_LEDGERS.find(l => optPartyId && String(l.party_id) === String(optPartyId))
                           || ALL_LEDGERS.find(l => l.name === partyName);
        if (matchedLedger) {
            partyLedgerId = matchedLedger.id;
        }

        const refBillNo = document.getElementById("v_reference_no") ? document.getElementById("v_reference_no").value : '';
        const billSuffix = refBillNo ? ` (Ref: ${refBillNo})` : '';

        const rows = [];
        if (generalDirection === 'receipt') {
            // CREDIT to Party: Party balance decreases (Settlement / Rebate)
            rows.push({
                ledger_id: discLedgerId,
                description: `Discount / Rebate Allowed${billSuffix}`,
                debit: discountAmt,
                credit: 0
            });
            rows.push({
                ledger_id: partyLedgerId,
                description: `Credit / Bill Settlement${billSuffix}`,
                debit: 0,
                credit: discountAmt,
                party_name: partyName
            });
        } else {
            // DEBIT to Party: Party balance increases (Debit note / Adjustment)
            rows.push({
                ledger_id: partyLedgerId,
                description: `General Debit / Adjustment${billSuffix}`,
                debit: discountAmt,
                credit: 0,
                party_name: partyName
            });
            rows.push({
                ledger_id: discLedgerId,
                description: `Discount / Rebate Reversal${billSuffix}`,
                debit: 0,
                credit: discountAmt
            });
        }

        renderDynamicRows(rows);
        updateSaveButtonState();
    }

    // =========================================================================
    // PARTY SELECTION & BILL NUMBER AUTOFILL / DROPDOWN LOGIC
    // =========================================================================
    function updateReferenceNoState() {
        const billSelect = document.getElementById("v_bill_select");
        const refInput = document.getElementById("v_reference_no");
        const feedback = document.getElementById("refBillFeedback");
        const badge = document.getElementById("pendingBillsCountBadge");
        if (!billSelect) return;

        let selectId = "cash_counter_ledger";
        if (activeVoucherType === 'bank') selectId = "bank_counter_ledger";
        else if (activeVoucherType === 'general') selectId = "general_counter_ledger";

        const partyVal = (window.jQuery ? $(`#${selectId}`).val() : null) || document.getElementById(selectId)?.value;

        if (!partyVal) {
            billSelect.disabled = true;
            billSelect.innerHTML = '<option value="">Select party first</option>';
            if (refInput) refInput.value = '';
            if (badge) badge.classList.add('d-none');
            if (feedback) {
                feedback.innerHTML = '<span class="text-muted" style="font-size: 0.72rem;"><i class="fa-solid fa-circle-info me-1"></i> Select party first to load pending bills</span>';
            }
        } else {
            billSelect.disabled = false;
        }
    }

    function handlePartySelection(type) {
        let selectId = "cash_counter_ledger";
        if (type === 'bank') selectId = "bank_counter_ledger";
        else if (type === 'general') selectId = "general_counter_ledger";

        const selectEl = document.getElementById(selectId);
        const billSelect = document.getElementById("v_bill_select");
        const refInput = document.getElementById("v_reference_no");
        const feedback = document.getElementById("refBillFeedback");
        const badge = document.getElementById("pendingBillsCountBadge");
        if (!selectEl) return;

        const ledgerId = (window.jQuery ? $(`#${selectId}`).val() : null) || selectEl.value;

        if (!ledgerId) {
            document.getElementById("v_party_id").value = '';
            document.getElementById("v_invoice_id").value = '';
            if (window.jQuery) {
                const $rendered = $(`#${selectId}`).next('.select2-container').find('.select2-selection__rendered');
                if ($rendered.length) {
                    $rendered.html('<span class="select2-selection__placeholder">-- Search or Select Party --</span>');
                }
            }
            updateReferenceNoState();
            if (type === 'cash') rebuildCashEntries();
            else if (type === 'bank') rebuildBankEntries();
            else if (type === 'general') rebuildGeneralEntries();
            return;
        }

        const opt = selectEl.querySelector(`option[value="${ledgerId}"]`);
        const partyId = opt ? opt.getAttribute('data-party-id') : null;
        const partyName = opt ? (opt.getAttribute('data-name') || opt.textContent.split(' (')[0].trim()) : '';

        // Store party_id in form
        document.getElementById("v_party_id").value = partyId || '';

        // Ensure visible Select2 box displays the selected party name cleanly
        if (window.jQuery && partyName) {
            const $rendered = $(`#${selectId}`).next('.select2-container').find('.select2-selection__rendered');
            if ($rendered.length) {
                $rendered.text(partyName).attr('title', partyName);
            }
        }

        // Enable bill select and show searching spinner
        if (billSelect) {
            billSelect.disabled = false;
            billSelect.innerHTML = '<option value="">Loading pending bills...</option>';
        }
        if (badge) badge.classList.add('d-none');
        if (feedback) {
            feedback.innerHTML = '<span class="text-primary fs-9"><i class="fa-solid fa-spinner fa-spin me-1"></i> Searching party pending bills...</span>';
        }

        fetch(`/vouchers/party-bill?party_id=${encodeURIComponent(partyId || '')}&ledger_id=${encodeURIComponent(ledgerId)}&party_name=${encodeURIComponent(partyName)}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    populateBillDropdown(res.bills || [], null, res);
                } else {
                    if (billSelect) billSelect.innerHTML = '<option value="">-- No Bills Found --</option>';
                    if (refInput) refInput.value = '';
                    if (document.getElementById("v_invoice_id")) document.getElementById("v_invoice_id").value = '';
                    if (feedback) feedback.innerHTML = '';
                }
            })
            .catch(err => {
                console.error("Party bill fetch error:", err);
                if (billSelect) billSelect.innerHTML = '<option value="">-- Error loading bills --</option>';
                if (feedback) feedback.innerHTML = '';
            });

        if (type === 'cash') rebuildCashEntries();
        else if (type === 'bank') rebuildBankEntries();
        else if (type === 'general') rebuildGeneralEntries();
    }

    function populateBillDropdown(bills, defaultBillNo, res) {
        const billSelect = document.getElementById("v_bill_select");
        const refInput = document.getElementById("v_reference_no");
        const badge = document.getElementById("pendingBillsCountBadge");
        const feedback = document.getElementById("refBillFeedback");
        if (!billSelect) return;

        billSelect.innerHTML = '';

        // Filter unsettled pending bills
        const pendingBills = (bills || []).filter(b => {
            const pAmt = (b.pending_amount !== undefined && b.pending_amount !== null)
                ? parseFloat(b.pending_amount)
                : Math.max(0, parseFloat(b.amount || 0) - parseFloat(b.paid_amount || 0) - parseFloat(b.advance_adjusted || 0));
            return !b.is_settled && pAmt > 0;
        });

        // Update pending bills count badge
        if (badge) {
            if (pendingBills.length > 0) {
                badge.className = "badge bg-warning-subtle text-warning-emphasis border border-warning-subtle";
                badge.textContent = `${pendingBills.length} Pending`;
                badge.classList.remove('d-none');
            } else if (res && res.all_settled) {
                badge.className = "badge bg-success-subtle text-success border border-success-subtle";
                badge.textContent = `All Settled`;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        }

        if (!bills || bills.length === 0) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = '-- No Bills Found --';
            billSelect.appendChild(opt);
            if (refInput) refInput.value = '';
            document.getElementById("v_invoice_id").value = '';
            if (feedback) {
                feedback.innerHTML = '<span class="text-muted fs-9"><i class="fa-solid fa-circle-info me-1"></i> No bills found for this party.</span>';
            }
            return;
        }

        // Header placeholder option (stays unselected by default when selecting party)
        const placeholderOpt = document.createElement('option');
        placeholderOpt.value = '';
        placeholderOpt.textContent = pendingBills.length > 0
            ? `-- Select Reference / Bill No. (${pendingBills.length} Pending) --`
            : `-- Select Reference / Bill No. --`;
        billSelect.appendChild(placeholderOpt);

        let defaultBillMatch = null;

        // Populate options with Pending Amount clearly shown
        bills.forEach(b => {
            const totalAmt = parseFloat(b.amount || 0);
            const paidAmt = parseFloat(b.paid_amount || 0);
            const advAdj = parseFloat(b.advance_adjusted || 0);
            const pAmt = (b.pending_amount !== undefined && b.pending_amount !== null)
                ? parseFloat(b.pending_amount)
                : Math.max(0, totalAmt - paidAmt - advAdj);

            const opt = document.createElement('option');
            opt.value = b.bill_no;
            opt.setAttribute('data-invoice-id', b.invoice_id || '');
            opt.setAttribute('data-pending', pAmt);
            opt.setAttribute('data-total', totalAmt);
            opt.setAttribute('data-paid', paidAmt);
            opt.setAttribute('data-advance-adj', advAdj);
            opt.setAttribute('data-settled', b.is_settled ? '1' : '0');

            if (b.is_settled || pAmt <= 0) {
                opt.textContent = `${b.bill_no} — Settled (Total: ₹${formatNumber(totalAmt, 2)})`;
                opt.classList.add('text-muted');
            } else {
                opt.textContent = `${b.bill_no} — Pending: ₹${formatNumber(pAmt, 2)} (Total: ₹${formatNumber(totalAmt, 2)})`;
                opt.classList.add('fw-bold');
            }

            if (defaultBillNo && b.bill_no === defaultBillNo) {
                defaultBillMatch = b;
            }

            billSelect.appendChild(opt);
        });

        // DO NOT auto-select pending bill when party is selected.
        // Dropdown stays on placeholder option so user can click to see and choose.
        if (defaultBillNo && defaultBillMatch) {
            billSelect.value = defaultBillMatch.bill_no;
            onBillSelectChange(billSelect);
        } else {
            billSelect.value = '';
            if (refInput) refInput.value = '';
            if (document.getElementById("v_invoice_id")) document.getElementById("v_invoice_id").value = '';
            if (feedback) {
                if (pendingBills.length > 0) {
                    feedback.innerHTML = `<span class="text-muted fs-9"><i class="fa-solid fa-clock-rotate-left text-warning me-1"></i> ${pendingBills.length} pending bill(s) available. Click dropdown to view and select.</span>`;
                } else {
                    feedback.innerHTML = '<span class="text-muted fs-9">No pending bills found for this party.</span>';
                }
            }
        }
    }

    function onBillSelectChange(selectEl) {
        const val = selectEl.value;
        const refInput = document.getElementById("v_reference_no");
        const invIdInput = document.getElementById("v_invoice_id");
        const feedback = document.getElementById("refBillFeedback");

        if (!val) {
            if (refInput) refInput.value = '';
            if (invIdInput) invIdInput.value = '';
            if (feedback) {
                const pendingCount = selectEl.querySelectorAll('option[data-pending]:not([data-settled="1"])').length;
                if (pendingCount > 0) {
                    feedback.innerHTML = `<span class="text-muted fs-9"><i class="fa-solid fa-clock-rotate-left text-warning me-1"></i> ${pendingCount} pending bill(s) available. Click dropdown to view and select.</span>`;
                } else {
                    feedback.innerHTML = '<span class="text-muted fs-9">Please select a pending bill from the dropdown.</span>';
                }
            }
            return;
        }

        const opt = selectEl.options[selectEl.selectedIndex];
        const invoiceId = opt.getAttribute('data-invoice-id') || '';
        const pendingAmt = parseFloat(opt.getAttribute('data-pending') || 0);
        const totalAmt = parseFloat(opt.getAttribute('data-total') || 0);
        const paidAmt = parseFloat(opt.getAttribute('data-paid') || 0);
        const advanceAdj = parseFloat(opt.getAttribute('data-advance-adj') || 0);
        const isSettled = opt.getAttribute('data-settled') === '1';

        if (refInput) refInput.value = val;
        if (invIdInput) invIdInput.value = invoiceId;

        // Render feedback details without auto-filling amount
        if (feedback) {
            if (advanceAdj > 0 && pendingAmt > 0) {
                feedback.innerHTML = `
                    <div class="d-flex align-items-center flex-wrap gap-2 mt-2">
                        <span class="text-success fw-bold me-1" style="font-size: 0.85rem;">
                            <i class="fa-solid fa-circle-check me-1"></i> Bill: <strong>${escapeHtml(val)}</strong>
                        </span>
                        <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.82rem; font-weight: 600;">
                            Total: ₹${formatNumber(totalAmt, 2)}
                        </span>
                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle fw-bold px-2 py-1" style="font-size: 0.82rem;">
                            <i class="fa-solid fa-hand-holding-dollar me-1"></i> Advance: ₹${formatNumber(advanceAdj, 2)}
                        </span>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-bold px-2 py-1" style="font-size: 0.82rem;">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Pending: ₹${formatNumber(pendingAmt, 2)}
                        </span>
                    </div>
                `;
            } else if (pendingAmt > 0) {
                feedback.innerHTML = `
                    <div class="d-flex align-items-center flex-wrap gap-2 mt-2">
                        <span class="text-success fw-bold me-1" style="font-size: 0.85rem;">
                            <i class="fa-solid fa-circle-check me-1"></i> Bill: <strong>${escapeHtml(val)}</strong>
                        </span>
                        <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.82rem; font-weight: 600;">
                            Total: ₹${formatNumber(totalAmt, 2)}
                        </span>
                        ${paidAmt > 0 ? `
                            <span class="badge bg-info-subtle text-primary border border-info-subtle px-2 py-1" style="font-size: 0.82rem; font-weight: 600;">
                                Paid: ₹${formatNumber(paidAmt, 2)}
                            </span>
                        ` : ''}
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold px-2 py-1" style="font-size: 0.82rem;">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Pending: ₹${formatNumber(pendingAmt, 2)}
                        </span>
                    </div>
                `;
            } else {
                feedback.innerHTML = `
                    <div class="d-flex align-items-center flex-wrap gap-2 mt-2">
                        <span class="text-success fw-bold me-1" style="font-size: 0.85rem;">
                            <i class="fa-solid fa-circle-check me-1"></i> Bill: <strong>${escapeHtml(val)}</strong>
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-2 py-1" style="font-size: 0.82rem;">
                            <i class="fa-solid fa-check me-1"></i> Settled (₹${formatNumber(totalAmt, 2)})
                        </span>
                    </div>
                `;
            }
        }

        if (activeVoucherType === 'general') {
            rebuildGeneralEntries();
        }
    }

    function autoFillPendingAmount(amt) {
        const currentType = activeVoucherType || (document.getElementById("v_type") ? document.getElementById("v_type").value : 'cash');
        if (currentType === 'cash') {
            const cashInput = document.getElementById("cash_amount");
            if (cashInput) {
                cashInput.value = parseFloat(amt).toFixed(2);
                if (typeof rebuildCashEntries === 'function') rebuildCashEntries();
            }
        } else if (currentType === 'bank') {
            const bankInput = document.getElementById("bank_amount");
            if (bankInput) {
                bankInput.value = parseFloat(amt).toFixed(2);
                if (typeof rebuildBankEntries === 'function') rebuildBankEntries();
            }
        } else if (currentType === 'general') {
            const discInput = document.getElementById("general_discount");
            if (discInput) {
                discInput.value = parseFloat(amt).toFixed(2);
                if (typeof rebuildGeneralEntries === 'function') rebuildGeneralEntries();
            }
        }
    }

    function initPartySelect2(force = false) {
        if (window.jQuery && $.fn.select2) {
            ['#cash_counter_ledger', '#bank_counter_ledger', '#general_counter_ledger'].forEach(selector => {
                const $el = $(selector);
                if (!$el.length) return;

                const currentVal = $el.val();

                if (force && $el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }

                if (!$el.hasClass('select2-hidden-accessible')) {
                    $el.select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#voucherDrawer'),
                        placeholder: '-- Search or Select Party --',
                        allowClear: true,
                        width: '100%'
                    });

                    if (currentVal) {
                        $el.val(currentVal).trigger('change.select2');
                    }
                }

                // Use namespaced events so Select2's internal change.select2 is NOT wiped out
                $el.off('.voucherParty')
                   .on('select2:select.voucherParty change.voucherParty', function () {
                       let type = 'cash';
                       if (selector.includes('bank')) type = 'bank';
                       else if (selector.includes('general')) type = 'general';
                       handlePartySelection(type);
                   })
                   .on('select2:clear.voucherParty', function () {
                       let type = 'cash';
                       if (selector.includes('bank')) type = 'bank';
                       else if (selector.includes('general')) type = 'general';
                       handlePartySelection(type);
                   });
            });
        }
    }

    // =========================================================================
    // 8. GENERAL VOUCHER JOURNAL GRID LOGIC
    // =========================================================================
    function initJournalRowDefaults() {
        const defaultRows = [
            { ledger_id: ALL_LEDGERS[0]?.id || '', description: '', debit: 0, credit: 0 },
            { ledger_id: ALL_LEDGERS[1]?.id || '', description: '', debit: 0, credit: 0 },
        ];
        renderDynamicRows(defaultRows);
    }

    function addJournalRow() {
        const tbody = document.getElementById("journalGridTbody");
        const rowIndex = tbody.children.length;

        let optionsHtml = '';
        ALL_LEDGERS.forEach(l => {
            optionsHtml += `<option value="${l.id}">${escapeHtml(l.name)} (${l.code})</option>`;
        });

        const tr = document.createElement("tr");
        tr.className = "journal-grid-row";
        tr.innerHTML = `
            <td>
                <select class="form-select form-select-sm entry-ledger" onchange="calculateJournalBalance()">
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm entry-desc" placeholder="Row description">
            </td>
            <td>
                <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace entry-debit" value="0.00" oninput="onDebitInput(this)">
            </td>
            <td>
                <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace entry-credit" value="0.00" oninput="onCreditInput(this)">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm text-danger p-0" onclick="removeJournalRow(this)" title="Remove row">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        calculateJournalBalance();
    }

    function removeJournalRow(btn) {
        const tbody = document.getElementById("journalGridTbody");
        if (tbody.children.length <= 2) {
            Swal.fire({
                icon: 'warning',
                title: 'Minimum 2 Rows Required',
                text: 'A double-entry voucher must contain at least two accounts (one Debit and one Credit).',
            });
            return;
        }
        btn.closest('tr').remove();
        calculateJournalBalance();
    }

    function onDebitInput(input) {
        const tr = input.closest('tr');
        const crInput = tr.querySelector('.entry-credit');
        if (parseFloat(input.value) > 0) {
            crInput.value = '0.00';
        }
        calculateJournalBalance();
    }

    function onCreditInput(input) {
        const tr = input.closest('tr');
        const drInput = tr.querySelector('.entry-debit');
        if (parseFloat(input.value) > 0) {
            drInput.value = '0.00';
        }
        calculateJournalBalance();
    }

    function renderDynamicRows(rows) {
        const tbody = document.getElementById("journalGridTbody");
        tbody.innerHTML = '';

        let optionsTemplate = '';
        ALL_LEDGERS.forEach(l => {
            optionsTemplate += `<option value="${l.id}">${escapeHtml(l.name)} (${l.code})</option>`;
        });

        rows.forEach(r => {
            const tr = document.createElement("tr");
            tr.className = "journal-grid-row";

            let rowOptions = optionsTemplate;
            if (r.ledger_id && !ALL_LEDGERS.some(l => String(l.id) === String(r.ledger_id))) {
                const label = r.party_name || 'Selected Account';
                rowOptions = `<option value="${r.ledger_id}">${escapeHtml(label)}</option>` + optionsTemplate;
            }

            tr.innerHTML = `
                <td>
                    <select class="form-select form-select-sm entry-ledger" onchange="calculateJournalBalance()">
                        ${rowOptions}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm entry-desc" value="${escapeHtml(r.description || '')}" placeholder="Row description">
                </td>
                <td>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace entry-debit" value="${r.debit ? r.debit.toFixed(2) : '0.00'}" oninput="onDebitInput(this)">
                </td>
                <td>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace entry-credit" value="${r.credit ? r.credit.toFixed(2) : '0.00'}" oninput="onCreditInput(this)">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm text-danger p-0" onclick="removeJournalRow(this)" title="Remove row">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);

            // Select ledger
            if (r.ledger_id) {
                tr.querySelector('.entry-ledger').value = r.ledger_id;
            }
        });

        calculateJournalBalance();
    }

    function calculateJournalBalance() {
        let totalDr = 0;
        let totalCr = 0;

        document.querySelectorAll('.journal-grid-row').forEach(tr => {
            const dr = parseFloat(tr.querySelector('.entry-debit').value) || 0;
            const cr = parseFloat(tr.querySelector('.entry-credit').value) || 0;
            totalDr += dr;
            totalCr += cr;
        });

        totalDr = Math.round(totalDr * 100) / 100;
        totalCr = Math.round(totalCr * 100) / 100;
        const diff = Math.abs(totalDr - totalCr);

        document.getElementById("liveTotalDebit").textContent = `₹${formatNumber(totalDr, 2)}`;
        document.getElementById("liveTotalCredit").textContent = `₹${formatNumber(totalCr, 2)}`;
        document.getElementById("liveDifference").textContent = `₹${formatNumber(diff, 2)}`;

        const statusBadge = document.getElementById("balanceStatusBadge");

        if (totalDr === 0 && totalCr === 0) {
            if (statusBadge) {
                statusBadge.className = "badge bg-secondary text-white";
                statusBadge.innerHTML = '<i class="fa-solid fa-circle-info"></i> Enter Amount';
            }
        } else if (diff < 0.01 && totalDr > 0) {
            if (statusBadge) {
                statusBadge.className = "balance-badge-ok";
                statusBadge.innerHTML = '<i class="fa-solid fa-circle-check"></i> Balanced ✓';
            }
        } else {
            if (statusBadge) {
                statusBadge.className = "balance-badge-unbalanced";
                statusBadge.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> Unbalanced (Diff: ₹${formatNumber(diff, 2)})`;
            }
        }

        updateSaveButtonState();
    }

    /**
     * Centralized Validator for Save Voucher Button.
     * Accurately enables / disables the button based on active voucher type rules.
     */
    function updateSaveButtonState() {
        const saveBtn = document.getElementById("btnSaveVoucher");
        if (!saveBtn) return;

        const voucherDate = document.getElementById("v_voucher_date")?.value;
        if (!voucherDate) {
            saveBtn.disabled = true;
            saveBtn.title = "Please select Voucher Date";
            return;
        }



        if (activeVoucherType === 'cash') {
            const counterSelect = document.getElementById("cash_counter_ledger");
            const counterLedgerId = (window.jQuery ? $('#cash_counter_ledger').val() : null) || counterSelect?.value || null;
            const amount = parseFloat(document.getElementById("cash_amount")?.value) || 0;

            if (!counterLedgerId) {
                saveBtn.disabled = true;
                saveBtn.title = "Please select a Party for Cash Voucher";
                return;
            }
            if (amount <= 0) {
                saveBtn.disabled = true;
                saveBtn.title = "Please enter Cash Amount greater than ₹0";
                return;
            }
            saveBtn.disabled = false;
            saveBtn.removeAttribute('title');
            return;
        }

        if (activeVoucherType === 'bank') {
            const counterSelect = document.getElementById("bank_counter_ledger");
            const counterLedgerId = (window.jQuery ? $('#bank_counter_ledger').val() : null) || counterSelect?.value || null;
            const amount = parseFloat(document.getElementById("bank_amount")?.value) || 0;

            if (!counterLedgerId) {
                saveBtn.disabled = true;
                saveBtn.title = "Please select a Party for Bank Voucher";
                return;
            }
            if (amount <= 0) {
                saveBtn.disabled = true;
                saveBtn.title = "Please enter Bank Amount greater than ₹0";
                return;
            }
            saveBtn.disabled = false;
            saveBtn.removeAttribute('title');
            return;
        }

        if (activeVoucherType === 'general') {
            const counterSelect = document.getElementById("general_counter_ledger");
            const counterLedgerId = (window.jQuery ? $('#general_counter_ledger').val() : null) || counterSelect?.value || null;
            const discount = parseFloat(document.getElementById("general_discount")?.value) || 0;

            if (!counterLedgerId) {
                saveBtn.disabled = true;
                saveBtn.title = "Please select a Party for General Voucher";
                return;
            }
            if (discount <= 0) {
                saveBtn.disabled = true;
                saveBtn.title = "Please enter Discount (INR) greater than ₹0";
                return;
            }
            saveBtn.disabled = false;
            saveBtn.removeAttribute('title');
            return;
        }

        // For Journal Grid Fallback:
        let totalDr = 0;
        let totalCr = 0;
        let validRows = 0;
        document.querySelectorAll('.journal-grid-row').forEach(tr => {
            const dr = parseFloat(tr.querySelector('.entry-debit')?.value) || 0;
            const cr = parseFloat(tr.querySelector('.entry-credit')?.value) || 0;
            const ledgerId = tr.querySelector('.entry-ledger')?.value;
            totalDr += dr;
            totalCr += cr;
            if (ledgerId && (dr > 0 || cr > 0)) validRows++;
        });

        totalDr = Math.round(totalDr * 100) / 100;
        totalCr = Math.round(totalCr * 100) / 100;
        const diff = Math.abs(totalDr - totalCr);

        if (diff < 0.01 && totalDr > 0 && validRows >= 2) {
            saveBtn.disabled = false;
            saveBtn.removeAttribute('title');
        } else {
            saveBtn.disabled = true;
            saveBtn.title = totalDr <= 0 ? "Enter transaction amounts" : `Voucher is unbalanced (Diff: ₹${diff.toFixed(2)})`;
        }
    }

    // =========================================================================
    // 9. FORM SUBMISSION (CREATE / UPDATE)
    // =========================================================================
    function handleVoucherSubmit(e) {
        e.preventDefault();
        if (isSubmitting) return;

        const voucherDate = document.getElementById("v_voucher_date").value;
        if (!voucherDate) {
            Swal.fire({
                icon: 'error',
                title: 'Date Required',
                text: 'Please select a valid Voucher Date.',
            });
            return;
        }

        if (activeVoucherType === 'cash') {
            const cSelect = document.getElementById("cash_counter_ledger");
            const counterVal = (window.jQuery ? $('#cash_counter_ledger').val() : null) || cSelect?.value;
            if (!counterVal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Party Required',
                    text: 'Please select a Party for this Cash Voucher.',
                });
                return;
            }
            const cashAmt = parseFloat(document.getElementById("cash_amount")?.value) || 0;
            if (cashAmt <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Amount Required',
                    text: 'Please enter Cash Amount greater than ₹0.',
                });
                return;
            }
            rebuildCashEntries();
        } else if (activeVoucherType === 'bank') {
            const bSelect = document.getElementById("bank_counter_ledger");
            const counterVal = (window.jQuery ? $('#bank_counter_ledger').val() : null) || bSelect?.value;
            if (!counterVal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Party Required',
                    text: 'Please select a Party for this Bank Voucher.',
                });
                return;
            }
            const bankAmt = parseFloat(document.getElementById("bank_amount")?.value) || 0;
            if (bankAmt <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Amount Required',
                    text: 'Please enter Bank Amount greater than ₹0.',
                });
                return;
            }
            rebuildBankEntries();
        } else if (activeVoucherType === 'general') {
            const gSelect = document.getElementById("general_counter_ledger");
            const counterVal = (window.jQuery ? $('#general_counter_ledger').val() : null) || gSelect?.value;
            if (!counterVal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Party Required',
                    text: 'Please select a Party for this General Voucher.',
                });
                return;
            }
            const discountAmt = parseFloat(document.getElementById("general_discount")?.value) || 0;
            if (discountAmt <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Amount Required',
                    text: 'Please enter Amount greater than ₹0.',
                });
                return;
            }
            rebuildGeneralEntries();
        }

        const rows = [];
        let totalDr = 0;
        let totalCr = 0;

        document.querySelectorAll('.journal-grid-row').forEach(tr => {
            const ledgerId = tr.querySelector('.entry-ledger').value;
            const desc = tr.querySelector('.entry-desc').value;
            const dr = parseFloat(tr.querySelector('.entry-debit').value) || 0;
            const cr = parseFloat(tr.querySelector('.entry-credit').value) || 0;

            if (dr > 0 || cr > 0) {
                rows.push({
                    ledger_id: ledgerId,
                    description: desc,
                    debit: dr,
                    credit: cr,
                });
                totalDr += dr;
                totalCr += cr;
            }
        });

        if (rows.length < 2 || totalDr <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Amount Required',
                text: 'Please enter a valid transaction amount greater than ₹0.',
            });
            return;
        }

        if (Math.abs(totalDr - totalCr) >= 0.01) {
            Swal.fire({
                icon: 'error',
                title: 'Voucher Unbalanced',
                text: `Total Debit (₹${totalDr.toFixed(2)}) must equal Total Credit (₹${totalCr.toFixed(2)}).`,
            });
            return;
        }

        // Gather Payload
        const voucherId = document.getElementById("v_id").value;
        const payload = {
            voucher_type: activeVoucherType,
            voucher_date: document.getElementById("v_voucher_date").value,
            reference_no: document.getElementById("v_reference_no").value,
            narration: document.getElementById("v_narration").value,
            party_id: document.getElementById("v_party_id").value || null,
            invoice_id: document.getElementById("v_invoice_id").value || null,
            status: 'posted',
            entries: rows,
        };

        // Enrich specialized parameters
        if (activeVoucherType === 'cash') {
            const cSelect = document.getElementById("cash_counter_ledger");
            const cVal = (window.jQuery ? $('#cash_counter_ledger').val() : null) || cSelect?.value;
            payload.transaction_mode = cashDirection;
            payload.payment_method = 'cash';
            payload.description = `Cash ${cashDirection === 'receipt' ? 'Credit' : 'Debit'}`;
            const opt = cSelect ? (cSelect.querySelector(`option[value="${cVal}"]`) || cSelect.options[cSelect.selectedIndex]) : null;
            payload.party_name = opt ? (opt.getAttribute('data-name') || opt.textContent.split(' (')[0].trim()) : '';
            payload.party_id = opt ? opt.getAttribute('data-party-id') : null;
        } else if (activeVoucherType === 'bank') {
            const bSelect = document.getElementById("bank_counter_ledger");
            const bVal = (window.jQuery ? $('#bank_counter_ledger').val() : null) || bSelect?.value;
            payload.transaction_mode = bankDirection;
            payload.bank_account_id = document.getElementById("bank_account_ledger") ? document.getElementById("bank_account_ledger").value : null;
            payload.payment_method = document.getElementById("bank_payment_method").value;
            payload.instrument_no = document.getElementById("bank_instrument_no").value;
            payload.instrument_date = document.getElementById("bank_instrument_date").value;
            payload.description = `Bank ${bankDirection === 'receipt' ? 'Credit' : 'Debit'}`;
            const opt = bSelect ? (bSelect.querySelector(`option[value="${bVal}"]`) || bSelect.options[bSelect.selectedIndex]) : null;
            payload.party_name = opt ? (opt.getAttribute('data-name') || opt.textContent.split(' (')[0].trim()) : '';
            payload.party_id = opt ? opt.getAttribute('data-party-id') : null;
        } else if (activeVoucherType === 'general') {
            const gSelect = document.getElementById("general_counter_ledger");
            const gVal = (window.jQuery ? $('#general_counter_ledger').val() : null) || gSelect?.value;
            payload.transaction_mode = generalDirection; // 'receipt' = Credit, 'payment' = Debit
            payload.payment_method = 'general';
            payload.party_type = 'customer';
            payload.description = `General Voucher (${generalDirection === 'payment' ? 'Debit' : 'Credit'})`;
            const opt = gSelect ? (gSelect.querySelector(`option[value="${gVal}"]`) || gSelect.options[gSelect.selectedIndex]) : null;
            payload.party_name = opt ? (opt.getAttribute('data-name') || opt.textContent.split(' (')[0].trim()) : '';
            payload.party_id = opt ? opt.getAttribute('data-party-id') : null;
        }

        isSubmitting = true;
        const saveBtn = document.getElementById("btnSaveVoucher");
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        const url = voucherId ? `/vouchers/${voucherId}` : '/vouchers';
        const method = voucherId ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(res => {
            isSubmitting = false;
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save Voucher';

            if (res.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Voucher Saved!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false,
                });
                bootstrap.Offcanvas.getInstance(document.getElementById("voucherDrawer")).hide();
                loadVouchersTable(currentVoucherPage);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: res.message || 'Could not save voucher. Please check entries.',
                });
            }
        })
        .catch(err => {
            isSubmitting = false;
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save Voucher';
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'System Error',
                text: 'An error occurred while saving the voucher.',
            });
        });
    }

    // =========================================================================
    // 10. VIEW VOUCHER DETAILS
    // =========================================================================
    function viewVoucherDetails(id) {
        fetch(`/vouchers/${id}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const v = res.data;
                    document.getElementById("viewVoucherNo").textContent = v.voucher_no;
                    document.getElementById("viewDate").textContent = v.voucher_date;
                    document.getElementById("viewParty").textContent = v.party_name;
                    document.getElementById("viewReference").textContent = v.reference_no;
                    document.getElementById("viewNarration").textContent = v.narration || 'No narration provided';
                    document.getElementById("viewCreatedBy").textContent = v.created_by;
                    document.getElementById("viewCreatedAt").textContent = v.created_at;
                    document.getElementById("viewUpdatedAt").textContent = v.updated_at;

                    const typeBadge = document.getElementById("viewTypeBadge");
                    typeBadge.className = `badge ${v.type_badge_class}`;
                    typeBadge.textContent = v.type_label;

                    // Balance status badge
                    const balBadge = document.getElementById("viewBalanceStatus");
                    if (v.is_balanced) {
                        balBadge.className = 'fs-7 fw-bold text-success';
                        balBadge.innerHTML = '<i class="fa-solid fa-circle-check"></i> Balanced ✓';
                    } else {
                        balBadge.className = 'fs-7 fw-bold text-danger';
                        balBadge.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Unbalanced ✗';
                    }

                    // Print & Edit buttons
                    document.getElementById("viewPrintBtn").href = `/vouchers/${v.id}/print`;
                    document.getElementById("viewEditBtn").onclick = () => {
                        bootstrap.Offcanvas.getInstance(document.getElementById("viewVoucherDrawer")).hide();
                        editVoucher(v.id);
                    };

                    // Entries table
                    const tbody = document.getElementById("viewEntriesTbody");
                    let html = '';
                    v.entries.forEach((e, i) => {
                        html += `
                            <tr>
                                <td class="text-muted fs-9">${i + 1}</td>
                                <td>
                                    <div class="fw-bold fs-8 text-dark">${escapeHtml(e.ledger_name)}</div>
                                    <small class="text-muted fs-9">${escapeHtml(e.description || '')}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border font-monospace fs-9">${e.ledger_code}</span>
                                </td>
                                <td class="text-end font-monospace fw-bold fs-7 ${e.debit > 0 ? 'text-dark' : 'text-muted'}">
                                    ${e.debit > 0 ? '₹' + formatNumber(e.debit, 2) : '-'}
                                </td>
                                <td class="text-end font-monospace fw-bold fs-7 ${e.credit > 0 ? 'text-dark' : 'text-muted'}">
                                    ${e.credit > 0 ? '₹' + formatNumber(e.credit, 2) : '-'}
                                </td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = html;

                    document.getElementById("viewTotalDebit").textContent = `₹${formatNumber(v.total_debit, 2)}`;
                    document.getElementById("viewTotalCredit").textContent = `₹${formatNumber(v.total_credit, 2)}`;

                    // Show Drawer
                    const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById("viewVoucherDrawer"));
                    drawer.show();
                }
            })
            .catch(console.error);
    }

    // =========================================================================
    // 11. EDIT VOUCHER
    // =========================================================================
    function editVoucher(id) {
        fetch(`/vouchers/${id}/edit`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const v = res.data;
                    document.getElementById("v_id").value = v.id;
                    document.getElementById("v_voucher_no").value = v.voucher_no;
                    document.getElementById("v_voucher_date").value = v.voucher_date;
                    document.getElementById("v_reference_no").value = v.reference_no || '';
                    document.getElementById("v_narration").value = v.narration || '';
                    document.getElementById("v_party_id").value = v.party_id || '';

                    document.getElementById("drawerTitleText").textContent = `Edit Voucher: ${v.voucher_no}`;
                    document.getElementById("drawerSubtitleText").textContent = 'Modify entries and atomically update ledger balances';
                    document.getElementById("voucherTypeSelectorWrapper").classList.add('d-none'); // Lock type in edit

                    selectVoucherType(v.voucher_type, true);

                    // Populate specialized form fields
                    if (v.voucher_type === 'cash') {
                        const cd = v.cash_data || {};
                        setCashDirection(cd.direction || 'receipt');
                        document.getElementById("cash_amount").value = cd.amount !== undefined ? Number(cd.amount).toFixed(2) : '0.00';
                        if (cd.counter_ledger_id && document.getElementById("cash_counter_ledger")) {
                            if (window.jQuery && $.fn.select2) {
                                $('#cash_counter_ledger').val(cd.counter_ledger_id).trigger('change');
                            } else {
                                document.getElementById("cash_counter_ledger").value = cd.counter_ledger_id;
                            }
                        }
                        document.getElementById("v_reference_no").disabled = false;
                        document.getElementById("v_reference_no").classList.remove('bg-light');
                    } else if (v.voucher_type === 'bank') {
                        const bd = v.bank_data || {};
                        setBankDirection(bd.direction || 'receipt');
                        document.getElementById("bank_amount").value = bd.amount !== undefined ? Number(bd.amount).toFixed(2) : '0.00';
                        if (v.bank_account_id && document.getElementById("bank_account_ledger")) {
                            document.getElementById("bank_account_ledger").value = v.bank_account_id;
                        }
                        if (bd.counter_ledger_id && document.getElementById("bank_counter_ledger")) {
                            if (window.jQuery && $.fn.select2) {
                                $('#bank_counter_ledger').val(bd.counter_ledger_id).trigger('change');
                            } else {
                                document.getElementById("bank_counter_ledger").value = bd.counter_ledger_id;
                            }
                        }
                        document.getElementById("v_reference_no").disabled = false;
                        document.getElementById("v_reference_no").classList.remove('bg-light');
                        if (v.payment_method && document.getElementById("bank_payment_method")) {
                            document.getElementById("bank_payment_method").value = v.payment_method;
                        }
                        if (v.instrument_no && document.getElementById("bank_instrument_no")) {
                            document.getElementById("bank_instrument_no").value = v.instrument_no;
                        }
                        if (v.instrument_date && document.getElementById("bank_instrument_date")) {
                            document.getElementById("bank_instrument_date").value = v.instrument_date;
                        }
                    } else if (v.voucher_type === 'general') {
                        const gd = v.general_data || {};
                        setGeneralDirection(gd.direction || 'receipt');
                        const discVal = gd.discount !== undefined ? Number(gd.discount).toFixed(2) : (gd.amount !== undefined ? Number(gd.amount).toFixed(2) : '0.00');
                        const discInput = document.getElementById("general_discount");
                        if (discInput) discInput.value = discVal;

                        if (gd.counter_ledger_id && document.getElementById("general_counter_ledger")) {
                            if (window.jQuery && $.fn.select2) {
                                $('#general_counter_ledger').val(gd.counter_ledger_id).trigger('change');
                            } else {
                                document.getElementById("general_counter_ledger").value = gd.counter_ledger_id;
                            }
                        }
                    }

                    const billSelect = document.getElementById("v_bill_select");
                    if (billSelect && v.reference_no) {
                        billSelect.disabled = false;
                        let found = false;
                        for (let i = 0; i < billSelect.options.length; i++) {
                            if (billSelect.options[i].value === v.reference_no) {
                                billSelect.selectedIndex = i;
                                found = true;
                                break;
                            }
                        }
                        if (!found) {
                            const opt = document.createElement('option');
                            opt.value = v.reference_no;
                            opt.textContent = v.reference_no;
                            opt.selected = true;
                            billSelect.appendChild(opt);
                        }
                    }

                    // Load entries into grid
                    renderDynamicRows(v.entries);

                    // Re-enable save button
                    const saveBtn = document.getElementById("btnSaveVoucher");
                    if (saveBtn) saveBtn.disabled = false;

                    // Open offcanvas
                    const drawer = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById("voucherDrawer"));
                    drawer.show();
                }
            })
            .catch(console.error);
    }



    // =========================================================================
    // 13. DELETE VOUCHER CONFIRMATION
    // =========================================================================
    function confirmDeleteVoucher(id, voucherNo) {
        Swal.fire({
            title: `Delete Voucher ${voucherNo}?`,
            text: 'This will atomically reverse all ledger entries and restore ledger balances. This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Delete and Reverse Ledger Impact',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/vouchers/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        Swal.fire('Deleted!', res.message, 'success');
                        loadVouchersTable(currentVoucherPage);
                    } else {
                        Swal.fire('Error', res.message || 'Could not delete voucher.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'Failed to communicate with server.', 'error');
                });
            }
        });
    }

    // Utility formatting helpers
    function formatNumber(val, decimals = 2) {
        return parseFloat(val || 0).toLocaleString('en-IN', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
</script>
@endpush
