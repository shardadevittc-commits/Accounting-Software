@extends('admin.layouts.app')

@section('title', 'Accounting Reports & Bill Ledger | Accounts ERP')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/accounting_dashboard.css') }}">
<!-- Select2 Searchable Dropdown CSS & Bootstrap 5 Theme -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    /* =========================================================
       ACCOUNTING REPORTS ENTERPRISE THEME
       ========================================================= */

    /* Tabular Numeric Font */
    .font-num {
        font-family: var(--saas-mono, 'JetBrains Mono', Consolas, monospace);
        font-feature-settings: "tnum";
        font-variant-numeric: tabular-nums;
    }

    /* Select2 Custom Styling matching Accounting Theme */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px !important;
        border: 1px solid var(--border-color, #cbd5e1) !important;
        border-radius: 8px !important;
        font-size: 0.875rem !important;
        display: flex !important;
        align-items: center !important;
        background-color: var(--main-body-bg, #ffffff) !important;
        color: var(--text-primary, #0f172a) !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: var(--text-primary, #0f172a) !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        line-height: 36px !important;
        padding-left: 0.75rem !important;
        padding-right: 2rem !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
        color: var(--text-muted, #64748b) !important;
        font-weight: 400 !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear {
        color: var(--text-muted, #94a3b8) !important;
    }
    .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .select2-container--bootstrap-5.select2-container--open .select2-selection {
        border-color: var(--theme-primary, #2563eb) !important;
        box-shadow: 0 0 0 3px var(--theme-primary-soft, rgba(37, 99, 235, 0.15)) !important;
    }
    .select2-container--bootstrap-5 .select2-dropdown {
        background-color: var(--card-bg, #ffffff) !important;
        border: 1px solid var(--border-color, #cbd5e1) !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12) !important;
        border-radius: 8px !important;
        color: var(--text-primary, #0f172a) !important;
        z-index: 1060 !important;
    }
    .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
        border-radius: 6px !important;
        border: 1px solid var(--border-color, #cbd5e1) !important;
        background-color: var(--main-body-bg, #ffffff) !important;
        color: var(--text-primary, #0f172a) !important;
        padding: 6px 10px !important;
    }
    .select2-results__option {
        color: var(--text-primary, #1e293b) !important;
        font-size: 0.85rem !important;
        padding: 7px 12px !important;
    }
    .select2-results__option--highlighted {
        background-color: var(--theme-primary, #2563eb) !important;
        color: #ffffff !important;
    }

    /* Filter Form Controls */
    .form-label-filter {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-muted, #64748b);
        margin-bottom: 0.35rem;
        display: block;
    }

    .filter-control {
        height: 38px;
        min-height: 38px;
        border: 1px solid var(--border-color, #cbd5e1);
        border-radius: 8px;
        font-size: 0.875rem;
        padding: 0.45rem 0.75rem;
        background-color: var(--main-body-bg, #ffffff);
        color: var(--text-primary, #0f172a);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .filter-control:focus {
        border-color: var(--theme-primary, #2563eb);
        box-shadow: 0 0 0 3px var(--theme-primary-soft, rgba(37, 99, 235, 0.15));
        background-color: var(--main-body-bg, #ffffff);
        color: var(--text-primary, #0f172a);
        outline: none;
    }

    .input-group > .filter-control:first-child {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    .input-group > .input-group-text {
        border-color: var(--border-color, #cbd5e1);
        height: 38px;
    }
    .input-group > .filter-control:last-child {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    .btn-filter-reset {
        height: 38px;
        border: 1px solid var(--border-color, #cbd5e1);
        border-radius: 8px;
        background: var(--main-body-bg, #ffffff);
        color: var(--text-muted, #64748b);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .btn-filter-reset:hover {
        background: #e2e8f0;
        color: var(--text-primary, #0f172a);
        border-color: #94a3b8;
    }

    /* Sortable Headers */
    .sortable-th {
        cursor: pointer;
        user-select: none;
        transition: color 0.15s ease;
    }
    .sortable-th:hover {
        color: var(--theme-primary, #2563eb) !important;
    }
    .sort-icon {
        font-size: 0.7rem;
        opacity: 0.6;
        display: inline-block;
        transition: transform 0.15s ease;
    }

    /* Table Column Presentation */
    .badge-index {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 6px;
        background: var(--main-body-bg, #f1f5f9);
        color: var(--text-muted, #64748b);
        font-weight: 600;
        font-size: 0.75rem;
        border: 1px solid var(--border-color, #e2e8f0);
    }

    .party-cell {
        font-weight: 600;
        color: var(--text-primary, #0f172a);
        max-width: 240px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .token-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 6px;
        background: rgba(37, 99, 235, 0.08);
        color: var(--theme-primary, #2563eb);
        border: 1px solid rgba(37, 99, 235, 0.2);
    }

    .bill-no-cell {
        font-weight: 700;
        color: var(--text-primary, #0f172a);
        letter-spacing: 0.02em;
    }

    .no-bill-italic {
        font-style: italic;
        color: var(--text-muted, #94a3b8);
        font-size: 0.8rem;
    }

    .truck-plate-tag {
        display: inline-block;
        font-family: var(--saas-mono, Consolas, monospace);
        font-weight: 700;
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 6px;
        background: var(--plate-bg, #eef2ff);
        color: var(--plate-text, #312e81);
        border: 1px solid var(--plate-border, #818cf8);
        letter-spacing: 0.03em;
    }

    .due-days-primary {
        font-weight: 600;
        color: var(--text-primary, #0f172a);
        font-size: 0.84rem;
    }

    .badge-credit-days {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 1px 6px;
        border-radius: 4px;
        background: var(--main-body-bg, #f1f5f9);
        color: var(--text-muted, #64748b);
        border: 1px solid var(--border-color, #e2e8f0);
    }

    .badge-due-overdue {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 4px;
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.25);
    }

    .badge-due-safe {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 1px 6px;
        border-radius: 4px;
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    /* Modern Pill Status Badges */
    .pill-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 0.25rem 0.7rem;
        border-radius: 9999px;
        font-size: 0.725rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1.2;
    }

    .badge-status-unpaid {
        background-color: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #ef4444;
    }

    .badge-status-partial {
        background-color: rgba(245, 158, 11, 0.12);
        border: 1px solid rgba(245, 158, 11, 0.3);
        color: #d97706;
    }

    .badge-status-paid {
        background-color: rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(16, 185, 129, 0.28);
        color: #10b981;
    }

    .badge-status-advance {
        background-color: rgba(99, 102, 241, 0.12);
        border: 1px solid rgba(99, 102, 241, 0.28);
        color: #6366f1;
    }

    .badge-status-general {
        background-color: rgba(100, 116, 139, 0.12);
        border: 1px solid rgba(100, 116, 139, 0.25);
        color: #475569;
    }

    /* Small pill next to received amount */
    .mini-badge-mode {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 4px;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .mini-badge-cash {
        background: rgba(147, 51, 234, 0.12);
        color: #7c3aed;
        border: 1px solid rgba(147, 51, 234, 0.25);
    }
    .mini-badge-bank {
        background: rgba(6, 182, 212, 0.12);
        color: #0891b2;
        border: 1px solid rgba(6, 182, 212, 0.25);
    }

    /* DR / CR Status Badges for Footer */
    .badge-dc-dr {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.65rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.25);
    }
    .badge-dc-cr {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.65rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        background: rgba(16, 185, 129, 0.12);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }

    .footer-total-label {
        font-size: 0.85rem;
        color: var(--text-primary, #0f172a);
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        text-align: right;
    }

    /* Print View Styling */
    @media print {
        .page-breadcrumb,
        .header-actions,
        .btn-header-action,
        .filter-toolbar,
        .table-footer-bar,
        .app-sidebar,
        .app-header,
        nav {
            display: none !important;
        }
        .accounting-app-container {
            padding: 0 !important;
        }
        .table-card {
            box-shadow: none !important;
            border: 1px solid #000 !important;
        }
        .enterprise-table {
            font-size: 0.75rem !important;
        }
    }

    /* Dark Mode Adaptations */
    [data-theme="dark"] .party-cell,
    body[data-page-theme="dark"] .party-cell,
    :root[style*="--main-body-bg: #1"] .party-cell,
    :root[style*="--main-body-bg: #0"] .party-cell {
        color: #f8fafc;
    }
    [data-theme="dark"] .bill-no-cell,
    body[data-page-theme="dark"] .bill-no-cell,
    :root[style*="--main-body-bg: #1"] .bill-no-cell,
    :root[style*="--main-body-bg: #0"] .bill-no-cell {
        color: #f8fafc;
    }
    [data-theme="dark"] .badge-index,
    body[data-page-theme="dark"] .badge-index,
    :root[style*="--main-body-bg: #1"] .badge-index,
    :root[style*="--main-body-bg: #0"] .badge-index {
        background: rgba(255, 255, 255, 0.06);
        border-color: rgba(255, 255, 255, 0.12);
        color: #94a3b8;
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
                <a href="{{ route('vouchers.index') }}">Accounting</a>
                <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
                <span class="breadcrumb-active">Reports</span>
            </nav>
            <h1 class="header-title">
                <span class="header-icon-badge"><i class="fa-solid fa-chart-pie"></i></span>
                Reports
            </h1>
            <p class="header-subtitle">Comprehensive invoice tracking, payment lifecycles, party-wise dues, and running balances.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn-header-action" id="btnRefreshReport" onclick="loadReportsData(true)" title="Refresh data">
                <i class="fa-solid fa-arrows-rotate" id="refreshIcon"></i>
                <span>Refresh</span>
            </button>
            <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2 px-3 py-2 fw-bold shadow-sm" id="btnExportReport" onclick="exportReportData()" title="Export filtered rows to CSV">
                <i class="fa-solid fa-file-arrow-down"></i>
                <span>Export CSV</span>
            </button>
            <!-- <button type="button" class="btn btn-primary d-flex align-items-center gap-2 px-3 py-2 fw-bold shadow-sm" id="btnPrintReport" onclick="window.print()" title="Print report table">
                <i class="fa-solid fa-print"></i>
                <span>Print</span>
            </button> -->
        </div>
    </div>

    <!-- 2. COMPACT KPI SUMMARY CARDS ROW -->
    <div class="kpi-row mb-3" id="kpiSummaryRow">
        <!-- Card 1: Total Records -->
        <div class="kpi-metric-card">
            <div class="kpi-info">
                <span class="kpi-label">Total Invoices</span>
                <span class="kpi-value font-num" id="kpiTotalEntries">0</span>
                <span class="kpi-sub"><i class="fa-solid fa-layer-group text-primary me-1"></i> Active ledger items</span>
            </div>
            <div class="kpi-icon-box kpi-icon-blue">
                <i class="fa-solid fa-file-invoice"></i>
            </div>
        </div>

        <!-- Card 2: Net Billed Amount -->
        <div class="kpi-metric-card">
            <div class="kpi-info">
                <span class="kpi-label">Net Billed Amount</span>
                <span class="kpi-value font-num" id="kpiNetAmount">₹0</span>
                <span class="kpi-sub"><i class="fa-solid fa-file-invoice-dollar text-primary me-1"></i> Invoiced revenue</span>
            </div>
            <div class="kpi-icon-box" style="background: rgba(37, 99, 235, 0.1); color: #2563eb;">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>

        <!-- Card 3: Total Received -->
        <div class="kpi-metric-card">
            <div class="kpi-info">
                <span class="kpi-label">Total Received</span>
                <span class="kpi-value text-success font-num" id="kpiReceivedAmount">₹0</span>
                <span class="kpi-sub"><i class="fa-solid fa-circle-check text-success me-1"></i> Collections via Cash &amp; Bank</span>
            </div>
            <div class="kpi-icon-box kpi-icon-emerald">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
        </div>

        <!-- Card 4: Balance / Settled -->
        <div class="kpi-metric-card" id="kpiBalanceCard">
            <div class="kpi-info">
                <span class="kpi-label" id="kpiBalanceLabel">Balance / Settled</span>
                <span class="kpi-value font-num" id="kpiBalanceAmount">₹0</span>
                <span class="kpi-sub"><i class="fa-solid fa-scale-balanced me-1"></i> Settlement status</span>
            </div>
            <div class="kpi-icon-box kpi-icon-purple" id="kpiBalanceIcon">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
        </div>
    </div>

    <!-- 3. MODERN FILTER TOOLBAR -->
    <div class="filter-toolbar mb-3">
        <div class="row g-2 align-items-end">
            <!-- 1. BILL NO. -->
            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6">
                <label class="form-label-filter" for="filterBillNo">
                    <i class="fa-solid fa-hashtag text-primary me-1"></i> Bill No.
                </label>
                <input type="text" class="form-control filter-control font-num" id="filterBillNo" placeholder="Search bill no..." onkeydown="if(event.key==='Enter') applyFilters()">
            </div>

            <!-- 2. PARTY (Searchable Select2) -->
            <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
                <label class="form-label-filter" for="filterParty">
                    <i class="fa-solid fa-user-tag text-primary me-1"></i> Party Name
                </label>
                <select class="form-select filter-control" id="filterParty">
                    <option value="">-- All Parties --</option>
                    @foreach($allParties as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 3. PAID STATUS -->
            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6">
                <label class="form-label-filter" for="filterStatus">
                    <i class="fa-solid fa-circle-check text-primary me-1"></i> Paid Status
                </label>
                <select class="form-select filter-control" id="filterStatus">
                    <option value="all" selected>All Statuses</option>
                    <option value="paid">Paid</option>
                    <option value="partial">Partially Paid</option>
                    <option value="unpaid">Unpaid</option>
                </select>
            </div>

            <!-- 4. INVOICE DATE RANGE (FROM & TO) -->
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <label class="form-label-filter">
                    <i class="fa-regular fa-calendar text-primary me-1"></i> Invoice Date Range
                </label>
                <div class="input-group">
                    <input type="date" class="form-control filter-control font-num" id="filterDateFrom" title="Date From">
                    <span class="input-group-text bg-light text-muted border px-2"><i class="fa-solid fa-arrow-right fs-9"></i></span>
                    <input type="date" class="form-control filter-control font-num" id="filterDateTo" title="Date To">
                </div>
            </div>

            <!-- 5. ACTION BUTTONS -->
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                <label class="form-label-filter text-transparent user-select-none d-none d-md-block">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2 flex-grow-1 fw-bold shadow-sm" id="btnApplyFilters" onclick="applyFilters()" style="height: 38px;" title="Apply Filters">
                        <i class="fa-solid fa-filter"></i>
                        <span>Apply</span>
                    </button>
                    <button type="button" class="btn-filter-reset d-inline-flex align-items-center justify-content-center" id="btnClearFilters" onclick="clearFilters()" style="height: 38px; width: 42px;" title="Reset Filters">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. ENTERPRISE DATA TABLE CARD -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="enterprise-table align-middle report-table-theme" id="reportTable">
                <thead>
                    <tr>
                        <th class="text-center sortable-th" style="width: 45px;" onclick="sortByCol('s_no')" title="Click to sort"># <span class="sort-icon text-muted ms-1" id="sort_s_no">⇅</span></th>
                        <th class="sortable-th" style="min-width: 190px;" onclick="sortByCol('party_name')" title="Click to sort">PARTY <span class="sort-icon text-muted ms-1" id="sort_party_name">⇅</span></th>
                        <th class="text-center sortable-th" style="min-width: 105px;" onclick="sortByCol('token_no')" title="Click to sort">TOKEN NO. <span class="sort-icon text-muted ms-1" id="sort_token_no">⇅</span></th>
                        <th class="sortable-th" style="min-width: 110px;" onclick="sortByCol('bill_no')" title="Click to sort">BILL NO. <span class="sort-icon text-muted ms-1" id="sort_bill_no">⇅</span></th>
                        <th class="sortable-th" style="min-width: 170px;" onclick="sortByCol('date')" title="Click to sort by Date">INVOICE DT / DUE <span class="sort-icon text-muted ms-1" id="sort_date">▼</span></th>
                        <th class="sortable-th" style="min-width: 130px;" onclick="sortByCol('truck_no')" title="Click to sort">TRUCK NO. <span class="sort-icon text-muted ms-1" id="sort_truck_no">⇅</span></th>
                        <th class="text-end sortable-th" style="min-width: 120px;" onclick="sortByCol('taxable_amt')" title="Click to sort">TAXABLE AMT <span class="sort-icon text-muted ms-1" id="sort_taxable_amt">⇅</span></th>
                        <th class="text-end sortable-th" style="min-width: 110px;" onclick="sortByCol('gst_amt')" title="Click to sort">GST AMT <span class="sort-icon text-muted ms-1" id="sort_gst_amt">⇅</span></th>
                        <th class="text-end sortable-th" style="min-width: 90px;" onclick="sortByCol('tds_amt')" title="Click to sort">TDS <span class="sort-icon text-muted ms-1" id="sort_tds_amt">⇅</span></th>
                        <th class="text-end sortable-th" style="min-width: 125px;" onclick="sortByCol('net_amt')" title="Click to sort">NET AMT <span class="sort-icon text-muted ms-1" id="sort_net_amt">⇅</span></th>
                        <th class="text-end sortable-th" style="min-width: 165px;" onclick="sortByCol('received_amt')" title="Click to sort">RECEIVED AMT <span class="sort-icon text-muted ms-1" id="sort_received_amt">⇅</span></th>
                        <th class="text-end sortable-th" style="min-width: 135px;" onclick="sortByCol('balance_amt')" title="Click to sort">BALANCE AMT <span class="sort-icon text-muted ms-1" id="sort_balance_amt">⇅</span></th>
                        <th class="text-center sortable-th" style="min-width: 120px;" onclick="sortByCol('status')" title="Click to sort">STATUS <span class="sort-icon text-muted ms-1" id="sort_status">⇅</span></th>
                    </tr>
                </thead>
                <tbody id="reportTableBody">
                    <!-- Dynamic Rows Populated via AJAX -->
                </tbody>
                <tfoot id="reportTableFoot">
                    <tr class="fw-bold" style="background-color: var(--table-th-bg, #f8fafc); border-top: 2px solid var(--border-color);">
                        <td colspan="9" class="footer-total-label">Total</td>
                        <td class="text-end font-num fs-6 fw-bold" id="footNetAmt">₹0</td>
                        <td class="text-end font-num fs-6 fw-bold text-success" id="footReceivedAmt">₹0</td>
                        <td class="text-end font-num fs-6 fw-bold" id="footBalanceAmt">₹0</td>
                        <td class="text-center" id="footStatusLabel">
                            <span class="badge-dc-cr">CR</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Table Footer Status Bar -->
        <div class="table-footer-bar d-flex align-items-center justify-content-between px-3 py-2" style="background: var(--card-bg); border-top: 1px solid var(--border-color); font-size: 0.85rem; color: var(--text-muted);">
            <div id="bottomRecordsInfo" class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-primary"></i>
                <span>Showing 0 records</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Initialize Select2 on Party dropdown with Bootstrap-5 theme
        if (window.jQuery && $.fn.select2) {
            $('#filterParty').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '-- All Parties --',
                allowClear: true
            });
        }

        loadReportsData();
    });

    /**
     * Trigger filter search
     */
    function applyFilters() {
        loadReportsData();
    }

    /**
     * Reset all filters
     */
    function clearFilters() {
        document.getElementById("filterBillNo").value = '';
        if (window.jQuery && $.fn.select2) {
            $('#filterParty').val('').trigger('change.select2');
        } else {
            const p = document.getElementById("filterParty");
            if (p) p.value = '';
        }
        document.getElementById("filterStatus").value = 'all';
        document.getElementById("filterDateFrom").value = '';
        document.getElementById("filterDateTo").value = '';

        loadReportsData();
    }

    let currentRowsData = [];
    let currentSortColumn = 'date';
    let currentSortDirection = 'desc';

    /**
     * Interactive Column Sorter
     */
    function sortByCol(column) {
        if (!currentRowsData || currentRowsData.length === 0) return;

        if (currentSortColumn === column) {
            currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            currentSortColumn = column;
            currentSortDirection = (column === 'party_name' || column === 'bill_no' || column === 's_no') ? 'asc' : 'desc';
        }

        // Reset all sort icons
        document.querySelectorAll('.sort-icon').forEach(el => el.textContent = '⇅');
        const activeIcon = document.getElementById('sort_' + column);
        if (activeIcon) {
            activeIcon.textContent = currentSortDirection === 'asc' ? '▲' : '▼';
        }

        currentRowsData.sort((a, b) => {
            if (column === 'date') {
                const timeA = a.row_time || a.group_time || a.sort_key || '';
                const timeB = b.row_time || b.group_time || b.sort_key || '';
                if (timeA !== timeB) {
                    return currentSortDirection === 'asc' ? (timeA < timeB ? -1 : 1) : (timeA > timeB ? -1 : 1);
                }
                const idA = parseInt(a.row_id || a.group_entry_id || a.invoice_id || 0);
                const idB = parseInt(b.row_id || b.group_entry_id || b.invoice_id || 0);
                if (idA !== idB) {
                    return currentSortDirection === 'asc' ? (idA - idB) : (idB - idA);
                }
                const orderA = parseInt(a.item_order || 0);
                const orderB = parseInt(b.item_order || 0);
                return currentSortDirection === 'asc' ? (orderA - orderB) : (orderB - orderA);
            }

            let vA = a[column];
            let vB = b[column];

            if (['s_no', 'taxable_amt', 'gst_amt', 'tds_amt', 'net_amt', 'received_amt', 'balance_amt'].includes(column)) {
                vA = parseFloat(vA) || 0;
                vB = parseFloat(vB) || 0;
                return currentSortDirection === 'asc' ? vA - vB : vB - vA;
            }

            vA = (vA || '').toString().toLowerCase();
            vB = (vB || '').toString().toLowerCase();

            if (vA < vB) return currentSortDirection === 'asc' ? -1 : 1;
            if (vA > vB) return currentSortDirection === 'asc' ? 1 : -1;
            return 0;
        });

        // Re-number s_no
        currentRowsData.forEach((r, i) => r.s_no = i + 1);
        renderReportTable(currentRowsData);
    }

    /**
     * Fetch Report Data via AJAX
     */
    function loadReportsData(isManualRefresh = false) {
        const tbody = document.getElementById("reportTableBody");
        const refreshIcon = document.getElementById("refreshIcon");

        if (isManualRefresh && refreshIcon) {
            refreshIcon.classList.add('fa-spin');
        }

        tbody.innerHTML = `
            <tr>
                <td colspan="13" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    Loading ledger report...
                </td>
            </tr>
        `;

        const partyVal = (window.jQuery ? $('#filterParty').val() : null) || document.getElementById("filterParty")?.value || '';

        const params = new URLSearchParams({
            bill_no: document.getElementById("filterBillNo").value.trim(),
            party: partyVal,
            status: document.getElementById("filterStatus").value,
            date_from: document.getElementById("filterDateFrom").value,
            date_to: document.getElementById("filterDateTo").value,
        });

        fetch(`/reports/data?${params.toString()}`)
            .then(res => res.json())
            .then(res => {
                if (refreshIcon) refreshIcon.classList.remove('fa-spin');

                if (res.status === 'success') {
                    currentRowsData = res.data || [];
                    currentSortColumn = 'date';
                    currentSortDirection = 'desc';
                    document.querySelectorAll('.sort-icon').forEach(el => el.textContent = '⇅');
                    const dateSortIcon = document.getElementById('sort_date');
                    if (dateSortIcon) dateSortIcon.textContent = '▼';

                    renderSummaryCards(res.summary);
                    renderReportTable(currentRowsData);
                    renderFooterTotals(res.totals, currentRowsData.length);
                } else {
                    tbody.innerHTML = `<tr><td colspan="13" class="text-center py-4 text-danger">${res.message || 'Error loading records.'}</td></tr>`;
                }
            })
            .catch(err => {
                console.error("Report fetch error:", err);
                if (refreshIcon) refreshIcon.classList.remove('fa-spin');
                tbody.innerHTML = `<tr><td colspan="13" class="text-center py-4 text-danger">Failed to communicate with server.</td></tr>`;
            });
    }

    /**
     * Render Top Summary Cards
     */
    function renderSummaryCards(summary) {
        if (!summary) return;

        document.getElementById("kpiTotalEntries").textContent = (summary.total_entries || 0).toLocaleString('en-IN');
        document.getElementById("kpiNetAmount").textContent = formatCurrencyInt(summary.total_net_amount || 0);
        document.getElementById("kpiReceivedAmount").textContent = formatCurrencyInt(summary.total_received_amount || 0);

        const balEl = document.getElementById("kpiBalanceAmount");
        const labelEl = document.getElementById("kpiBalanceLabel");
        const balVal = parseFloat(summary.total_balance_amount) || 0;
        const balType = summary.balance_type || 'nil';

        if (balType === 'cr') {
            balEl.innerHTML = `${formatCurrencyInt(balVal)} <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-0 ms-1 fs-7 fw-bold">CR</span>`;
            balEl.className = 'kpi-value summary-card-val text-success font-num';
            if (labelEl) labelEl.textContent = 'Party Advance (CR)';
        } else if (balType === 'dr') {
            balEl.innerHTML = `${formatCurrencyInt(balVal)} <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-2 py-0 ms-1 fs-7 fw-bold">DR</span>`;
            balEl.className = 'kpi-value summary-card-val text-danger font-num';
            if (labelEl) labelEl.textContent = 'Pending Balance (DR)';
        } else {
            balEl.innerHTML = '₹0';
            balEl.className = 'kpi-value summary-card-val text-success font-num';
            if (labelEl) labelEl.textContent = 'Settled (NIL)';
        }
    }

    /**
     * Render Footer Totals matching theme
     */
    function renderFooterTotals(totals, count) {
        if (!totals) totals = {};

        const countText = `Showing ${count.toLocaleString('en-IN')} record${count === 1 ? '' : 's'}`;
        document.getElementById("bottomRecordsInfo").innerHTML = `<i class="fa-solid fa-list-check text-primary me-1"></i> ${countText}`;

        document.getElementById("footNetAmt").textContent = formatCurrencyInt(totals.total_net_amount || 0);
        document.getElementById("footReceivedAmt").textContent = formatCurrencyInt(totals.total_received_amount || 0);
        
        const balVal = parseFloat(totals.total_balance_amount) || 0;
        const balType = totals.balance_type || 'nil';
        const footBalEl = document.getElementById("footBalanceAmt");
        const statusEl = document.getElementById("footStatusLabel");

        footBalEl.textContent = formatCurrencyInt(balVal);

        if (balType === 'cr') {
            footBalEl.className = 'text-end font-num fs-6 fw-bold text-success';
            statusEl.innerHTML = `<span class="badge-dc-cr">CR</span>`;
        } else if (balType === 'dr') {
            footBalEl.className = 'text-end font-num fs-6 fw-bold text-danger';
            statusEl.innerHTML = `<span class="badge-dc-dr">DR</span>`;
        } else {
            footBalEl.className = 'text-end font-num fs-6 fw-bold text-success';
            statusEl.innerHTML = `<span class="badge rounded-pill bg-secondary-subtle text-secondary px-2 py-1 fw-bold">NIL</span>`;
        }
    }

    /**
     * Render Table Rows: Exactly 13 Columns matching theme
     */
    function renderReportTable(rows) {
        const tbody = document.getElementById("reportTableBody");
        if (!rows || rows.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="13" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-inbox fs-2 mb-2 d-block opacity-40"></i>
                        No records found matching the specified filters.
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        rows.forEach(r => {
            // Bill No cell presentation
            let billHtml = '';
            if (r.is_no_bill || !r.bill_no || r.bill_no === 'No Bill') {
                billHtml = '<span class="no-bill-italic">No Bill</span>';
            } else {
                billHtml = `<span class="bill-no-cell font-num">${escapeHtml(r.bill_no)}</span>`;
            }

            // Invoice Dt / Due Days presentation
            let dtDueHtml = '';
            if (r.is_no_bill) {
                dtDueHtml = `<div class="due-days-primary font-num">${escapeHtml(r.invoice_date_formatted)}</div>`;
            } else {
                dtDueHtml = `
                    <div class="due-days-primary font-num">${escapeHtml(r.invoice_date_formatted)}</div>
                    <div class="d-flex align-items-center gap-1 mt-1">
                        ${r.credit_days ? `<span class="badge-credit-days">${r.credit_days}d</span>` : ''}
                        ${r.due_text ? `<span class="${r.is_overdue ? 'badge-due-overdue' : 'badge-due-safe'}">${escapeHtml(r.due_text)}</span>` : ''}
                    </div>
                `;
            }

            // Received Amount with badge and subtext timestamp
            const recvAmtVal = parseFloat(r.received_amt) || 0;
            let recvHtml = '';
            if (recvAmtVal > 0) {
                let badgeClass = 'mini-badge-cash';
                if (r.received_badge && r.received_badge.toLowerCase().includes('bank')) {
                    badgeClass = 'mini-badge-bank';
                }
                
                recvHtml = `
                    <div class="font-num fw-bold text-success d-flex align-items-center justify-content-end gap-1">
                        <span>${formatCurrencyInt(recvAmtVal)}</span>
                        ${r.received_badge ? `<span class="mini-badge-mode ${badgeClass}">${escapeHtml(r.received_badge)}</span>` : ''}
                    </div>
                    ${r.received_time ? `<div class="text-muted font-num" style="font-size: 0.72rem;">${escapeHtml(r.received_time)}</div>` : ''}
                    ${r.narration ? `<div class="text-secondary text-truncate" style="font-size: 0.70rem; max-width: 160px;" title="${escapeHtml(r.narration)}">${escapeHtml(r.narration)}</div>` : ''}
                `;
            } else {
                recvHtml = `<span class="font-num text-muted">₹0</span>`;
            }

            // Balance Amount presentation
            let balHtml = '';
            if (r.balance_amt === null || r.balance_amt === undefined) {
                balHtml = '<span class="text-muted">—</span>';
            } else {
                const balVal = parseFloat(r.balance_amt) || 0;
                if (balVal <= 0.01) {
                    balHtml = `<span class="font-num fw-bold text-success">₹0</span>`;
                } else {
                    balHtml = `<span class="font-num fw-bold text-danger">${formatCurrencyInt(balVal)}</span>`;
                }
            }

            // Status Badge: Unpaid (red), Partially Paid (orange), Paid (green), Cash/Advance (purple)
            let statusBadge = '';
            const st = (r.status || '').toLowerCase().replace(/\s+/g, '');
            if (st === 'unpaid') {
                statusBadge = '<span class="pill-badge badge-status-unpaid"><i class="fa-solid fa-circle-xmark me-1"></i> Unpaid</span>';
            } else if (st === 'partiallypaid' || st === 'partial') {
                statusBadge = '<span class="pill-badge badge-status-partial"><i class="fa-solid fa-clock-rotate-left me-1"></i> Partial</span>';
            } else if (st === 'paid') {
                statusBadge = '<span class="pill-badge badge-status-paid"><i class="fa-solid fa-check me-1"></i> Paid</span>';
            } else if (st === 'advance') {
                statusBadge = '<span class="pill-badge badge-status-advance"><i class="fa-solid fa-bolt me-1"></i> Advance</span>';
            } else {
                statusBadge = `<span class="pill-badge badge-status-general">${escapeHtml(r.status)}</span>`;
            }

            html += `
                <tr>
                    <td class="text-center font-num"><span class="badge-index">${r.s_no}</span></td>
                    <td><div class="party-cell" title="${escapeHtml(r.party_name)}">${escapeHtml(r.party_name)}</div></td>
                    <td class="text-center font-num">${r.token_no && r.token_no !== '—' ? `<span class="token-badge font-num">${escapeHtml(r.token_no)}</span>` : '<span class="text-muted">—</span>'}</td>
                    <td>${billHtml}</td>
                    <td>${dtDueHtml}</td>
                    <td>${r.truck_no && r.truck_no !== '—' ? `<span class="truck-plate-tag font-num">${escapeHtml(r.truck_no)}</span>` : '<span class="text-muted">—</span>'}</td>
                    <td class="text-end font-num text-muted">${r.taxable_amt !== null ? formatCurrencyInt(r.taxable_amt) : '—'}</td>
                    <td class="text-end font-num text-muted">${r.gst_amt !== null ? formatCurrencyInt(r.gst_amt) : '—'}</td>
                    <td class="text-end font-num text-muted">${r.tds_amt !== null ? formatCurrencyInt(r.tds_amt) : '—'}</td>
                    <td class="text-end font-num fw-bold text-dark">${r.net_amt !== null ? formatCurrencyInt(r.net_amt) : '—'}</td>
                    <td class="text-end">${recvHtml}</td>
                    <td class="text-end font-num">${balHtml}</td>
                    <td class="text-center">${statusBadge}</td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    /**
     * CSV Export Trigger
     */
    function exportReportData() {
        const partyVal = (window.jQuery ? $('#filterParty').val() : null) || document.getElementById("filterParty")?.value || '';

        const params = new URLSearchParams({
            bill_no: document.getElementById("filterBillNo").value.trim(),
            party: partyVal,
            status: document.getElementById("filterStatus").value,
            date_from: document.getElementById("filterDateFrom").value,
            date_to: document.getElementById("filterDateTo").value,
        });

        window.location.href = `/reports/export?${params.toString()}`;
    }

    /**
     * Currency formatter with Indian comma separators (₹20,000)
     */
    function formatCurrencyInt(amount) {
        const num = Math.round(parseFloat(amount) || 0);
        return '₹' + num.toLocaleString('en-IN');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
@endpush
