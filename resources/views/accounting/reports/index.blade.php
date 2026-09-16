@extends('admin.layouts.app')

@section('title', 'Accounting Reports & Bill Ledger | Accounts ERP')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/accounting_dashboard.css') }}">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    /* 1. Main Container & Typography */
    .reports-container {
        padding: 1.25rem 1.5rem;
        background-color: transparent;
        color: var(--text-primary, #0f172a);
        font-family: var(--saas-font, 'Inter', -apple-system, sans-serif);
    }

    /* Page Header */
    .report-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .report-title-group h1 {
        font-size: 1.55rem;
        font-weight: 700;
        margin: 0;
        color: var(--text-primary, #0f172a);
        display: flex;
        align-items: center;
        gap: 0.6rem;
        letter-spacing: -0.02em;
    }

    .report-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        box-shadow: 0 4px 10px rgba(249, 115, 22, 0.25);
    }

    .report-subtitle {
        font-size: 0.83rem;
        color: var(--text-muted, #64748b);
        margin-top: 0.2rem;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* KPI Summary Cards - Perfectly theme matched */
    .summary-card-theme {
        background: var(--card-bg, #ffffff);
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 10px;
        padding: 0.9rem 1.1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease;
    }
    .summary-card-theme:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
    }

    .summary-card-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted, #64748b);
        margin-bottom: 0.25rem;
    }

    .summary-card-val {
        font-size: 1.28rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        font-family: var(--saas-mono, 'JetBrains Mono', Consolas, monospace);
        color: var(--text-primary, #0f172a);
    }

    /* Top Filter Bar - Theme Matched */
    .filter-bar-theme {
        background: var(--card-bg, #ffffff);
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 10px;
        padding: 1rem 1.2rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
    }

    .filter-label-theme {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted, #64748b);
        margin-bottom: 0.35rem;
        display: block;
    }

    .filter-input-theme {
        background-color: var(--card-bg, #ffffff) !important;
        border: 1.5px solid var(--border-color, #cbd5e1) !important;
        color: var(--text-primary, #0f172a) !important;
        border-radius: 6px !important;
        font-size: 0.835rem !important;
        padding: 0.45rem 0.75rem !important;
        min-height: 38px;
        transition: all 0.15s ease;
    }

    .filter-input-theme:focus {
        border-color: #f97316 !important;
        box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.2) !important;
        outline: none;
    }

    .btn-ss-apply {
        background: #f97316 !important;
        border: 1px solid #f97316 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 0.825rem !important;
        padding: 0.45rem 1.25rem !important;
        border-radius: 6px !important;
        min-height: 38px;
        box-shadow: 0 2px 6px rgba(249, 115, 22, 0.25);
        transition: all 0.15s ease;
    }

    .btn-ss-apply:hover {
        background: #ea580c !important;
        border-color: #ea580c !important;
    }

    .btn-ss-clear {
        background: var(--bg-light, #f1f5f9) !important;
        border: 1.5px solid var(--border-color, #cbd5e1) !important;
        color: var(--text-primary, #334155) !important;
        font-weight: 600 !important;
        font-size: 0.825rem !important;
        padding: 0.45rem 1.15rem !important;
        border-radius: 6px !important;
        min-height: 38px;
        transition: all 0.15s ease;
    }

    .btn-ss-clear:hover {
        background: #e2e8f0 !important;
        color: #0f172a !important;
    }

    /* Select2 Theme Adaptations */
    .select2-container--bootstrap-5 .select2-selection {
        background-color: var(--card-bg, #ffffff) !important;
        border: 1.5px solid var(--border-color, #cbd5e1) !important;
        border-radius: 6px !important;
        min-height: 38px !important;
        color: var(--text-primary, #0f172a) !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: var(--text-primary, #0f172a) !important;
        line-height: 36px !important;
        font-size: 0.835rem !important;
        padding-left: 0.75rem !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear {
        color: var(--text-muted, #94a3b8) !important;
    }
    .select2-dropdown {
        background-color: var(--card-bg, #ffffff) !important;
        border: 1.5px solid var(--border-color, #cbd5e1) !important;
        color: var(--text-primary, #0f172a) !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
    }
    .select2-results__option {
        color: var(--text-primary, #1e293b) !important;
        font-size: 0.825rem !important;
    }
    .select2-results__option--highlighted {
        background-color: #f97316 !important;
        color: #ffffff !important;
    }

    /* Report Table Card */
    .report-table-card-theme {
        background: var(--card-bg, #ffffff);
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.04);
    }

    .table-responsive-wrapper-theme {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        min-height: 320px;
    }

    .report-table-theme {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.815rem;
        color: var(--text-primary, #0f172a);
    }

    .report-table-theme thead th {
        background: #0f172a !important;
        color: #f8fafc !important;
        font-size: 0.71rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.85rem 0.75rem;
        border-bottom: 2px solid #334155;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
        vertical-align: middle;
    }

    .report-table-theme thead th .sort-icon {
        font-size: 0.65rem;
        margin-left: 0.2rem;
        opacity: 0.6;
    }

    .report-table-theme tbody td {
        padding: 0.85rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color, #e2e8f0);
        color: var(--text-primary, #1e293b);
        white-space: nowrap;
        background-color: var(--card-bg, #ffffff);
        transition: background 0.12s ease;
    }

    .report-table-theme tbody tr:hover td {
        background-color: rgba(249, 115, 22, 0.04) !important;
    }

    .font-num {
        font-family: var(--saas-mono, 'JetBrains Mono', Consolas, monospace);
        font-feature-settings: "tnum";
        font-variant-numeric: tabular-nums;
    }

    /* Column Typography */
    .party-cell {
        font-weight: 600;
        color: var(--text-primary, #0f172a);
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .bill-no-cell {
        font-weight: 700;
        color: var(--text-primary, #0f172a);
    }

    .no-bill-italic {
        font-style: italic;
        color: var(--text-muted, #94a3b8);
        font-weight: 500;
    }

    .truck-cell {
        color: var(--text-primary, #334155);
        font-weight: 600;
    }

    .due-days-primary {
        color: var(--text-primary, #0f172a);
        font-weight: 600;
    }
    .due-days-sub {
        font-size: 0.72rem;
        color: var(--text-muted, #64748b);
    }
    .due-days-overdue {
        font-size: 0.72rem;
        color: #dc2626;
        font-weight: 600;
    }
    .due-days-safe {
        font-size: 0.72rem;
        color: var(--text-muted, #64748b);
    }

    /* Pill Badges for Status matching theme */
    .pill-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.22rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1.2;
    }

    .badge-status-unpaid {
        background-color: rgba(239, 68, 68, 0.12);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #dc2626;
    }

    .badge-status-partial {
        background-color: rgba(245, 158, 11, 0.14);
        border: 1px solid rgba(245, 158, 11, 0.35);
        color: #b45309;
    }

    .badge-status-paid {
        background-color: rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: #047857;
    }

    .badge-status-cash {
        background-color: rgba(139, 92, 246, 0.12);
        border: 1px solid rgba(139, 92, 246, 0.3);
        color: #7c3aed;
    }

    /* Small pill next to received amount */
    .mini-badge-mode {
        display: inline-block;
        padding: 0.12rem 0.45rem;
        border-radius: 4px;
        font-size: 0.68rem;
        font-weight: 700;
        margin-left: 0.35rem;
        vertical-align: middle;
    }

    .mini-badge-cash {
        background: rgba(139, 92, 246, 0.14);
        color: #6d28d9;
        border: 1px solid rgba(139, 92, 246, 0.3);
    }

    .mini-badge-bank {
        background: rgba(6, 182, 212, 0.14);
        color: #0e7490;
        border: 1px solid rgba(6, 182, 212, 0.3);
    }

    /* Table Footer Total Row */
    .report-table-theme tfoot td {
        background: #f8fafc !important;
        border-top: 2.5px solid #0f172a;
        border-bottom: 2.5px solid #0f172a;
        padding: 0.95rem 0.75rem;
        font-weight: 700;
        white-space: nowrap;
        color: var(--text-primary, #0f172a);
    }

    .footer-total-label {
        font-size: 0.85rem;
        color: #0f172a;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        text-align: right;
    }

    .footer-status-cr {
        color: #047857 !important;
        font-size: 0.92rem;
        font-weight: 800;
    }

    .footer-status-dr {
        color: #dc2626 !important;
        font-size: 0.92rem;
        font-weight: 800;
    }

    /* Bottom records count indicator */
    .bottom-records-info {
        padding: 0.75rem 1.25rem;
        background: var(--card-bg, #ffffff);
        border-top: 1px solid var(--border-color, #e2e8f0);
        font-size: 0.78rem;
        color: var(--text-muted, #64748b);
        font-weight: 500;
    }

    /* Full support for Dark Theme when user/system activates dark theme */
    [data-theme="dark"] .reports-container,
    body[data-page-theme="dark"] .reports-container,
    :root[style*="--main-body-bg: #1"] .reports-container,
    :root[style*="--main-body-bg: #0"] .reports-container {
        background-color: var(--main-body-bg, #0f1115);
        color: var(--text-primary, #f1f5f9);
    }

    [data-theme="dark"] .report-table-theme tbody td,
    body[data-page-theme="dark"] .report-table-theme tbody td,
    :root[style*="--main-body-bg: #1"] .report-table-theme tbody td,
    :root[style*="--main-body-bg: #0"] .report-table-theme tbody td {
        background-color: var(--card-bg, #151828);
        border-bottom-color: var(--border-color, rgba(255, 255, 255, 0.08));
        color: var(--text-primary, #f1f5f9);
    }

    [data-theme="dark"] .report-table-theme tbody tr:hover td,
    body[data-page-theme="dark"] .report-table-theme tbody tr:hover td,
    :root[style*="--main-body-bg: #1"] .report-table-theme tbody tr:hover td,
    :root[style*="--main-body-bg: #0"] .report-table-theme tbody tr:hover td {
        background-color: rgba(255, 255, 255, 0.04) !important;
    }

    [data-theme="dark"] .report-table-theme tfoot td,
    body[data-page-theme="dark"] .report-table-theme tfoot td,
    :root[style*="--main-body-bg: #1"] .report-table-theme tfoot td,
    :root[style*="--main-body-bg: #0"] .report-table-theme tfoot td {
        background: #0d0f14 !important;
        border-top-color: #334155;
        border-bottom-color: #334155;
        color: #f8fafc;
    }

    [data-theme="dark"] .footer-total-label,
    body[data-page-theme="dark"] .footer-total-label,
    :root[style*="--main-body-bg: #1"] .footer-total-label,
    :root[style*="--main-body-bg: #0"] .footer-total-label {
        color: #f8fafc;
    }

    [data-theme="dark"] .bottom-records-info,
    body[data-page-theme="dark"] .bottom-records-info,
    :root[style*="--main-body-bg: #1"] .bottom-records-info,
    :root[style*="--main-body-bg: #0"] .bottom-records-info {
        background: var(--card-bg, #151828);
        border-top-color: var(--border-color, rgba(255, 255, 255, 0.08));
        color: var(--text-muted, #94a3b8);
    }
</style>
@endpush

@section('content')
<div class="reports-container">
    {{-- Breadcrumb --}}
    <nav class="page-breadcrumb mb-2" aria-label="breadcrumb">
        <a href="/"><i class="fa-solid fa-house"></i> Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('vouchers.index') }}">Accounting</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-active text-primary fw-bold">Reports</span>
    </nav>

    {{-- Top Header --}}
    <div class="report-page-header">
        <div class="report-title-group">
            <h1>
                <span class="report-icon-box"><i class="fa-solid fa-table-list"></i></span>
                 Reports
            </h1>
            <div class="report-subtitle">Real-time invoice payment lifecycle, running balances, and party ledger</div>
        </div>

        <div class="header-actions">
            <button type="button" class="btn btn-sm btn-light border text-secondary" id="btnRefreshReport" onclick="loadReportsData(true)" title="Refresh data">
                <i class="fa-solid fa-arrows-rotate me-1" id="refreshIcon"></i> Refresh
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnExportReport" onclick="exportReportData()" title="Export filtered rows to CSV">
                <i class="fa-solid fa-file-arrow-down me-1"></i> Export CSV
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnPrintReport" onclick="window.print()" title="Print report table">
                <i class="fa-solid fa-print me-1"></i> Print
            </button>
        </div>
    </div>

    {{-- Compact KPI Metric Cards --}}
    <div class="row g-2 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="summary-card-theme" style="border-top: 3px solid #0284c7;">
                <div>
                    <div class="summary-card-label">Total Records</div>
                    <div class="summary-card-val text-primary" id="kpiTotalEntries">0</div>
                </div>
                <div class="fs-3 text-primary opacity-50"><i class="fa-solid fa-layer-group"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="summary-card-theme" style="border-top: 3px solid #f97316;">
                <div>
                    <div class="summary-card-label">Net Billed Amount</div>
                    <div class="summary-card-val" id="kpiNetAmount">₹0</div>
                </div>
                <div class="fs-3 text-warning opacity-50"><i class="fa-solid fa-file-invoice-dollar"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="summary-card-theme" style="border-top: 3px solid #10b981;">
                <div>
                    <div class="summary-card-label">Total Received</div>
                    <div class="summary-card-val text-success" id="kpiReceivedAmount">₹0</div>
                </div>
                <div class="fs-3 text-success opacity-50"><i class="fa-solid fa-circle-check"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="summary-card-theme" id="kpiBalanceCard" style="border-top: 3px solid #6366f1;">
                <div>
                    <div class="summary-card-label" id="kpiBalanceLabel">Balance / Settled</div>
                    <div class="summary-card-val" id="kpiBalanceAmount">₹0</div>
                </div>
                <div class="fs-3 opacity-50" id="kpiBalanceIcon" style="color: #6366f1;"><i class="fa-solid fa-scale-balanced"></i></div>
            </div>
        </div>
    </div>

    {{-- Filter Bar: Theme-Matched Exact Layout from Screenshot --}}
    <div class="filter-bar-theme">
        <div class="row g-2 align-items-end">
            {{-- 1. BILL NO. --}}
            <div class="col-lg-2 col-md-4">
                <label class="filter-label-theme" for="filterBillNo">BILL NO.</label>
                <input type="text" class="form-control filter-input-theme font-num" id="filterBillNo" placeholder="Bill No..." onkeydown="if(event.key==='Enter') applyFilters()">
            </div>

            {{-- 2. PARTY --}}
            <div class="col-lg-3 col-md-5">
                <label class="filter-label-theme" for="filterParty">PARTY</label>
                <select class="form-select filter-input-theme" id="filterParty">
                    <option value="">-- All Parties --</option>
                    @foreach($allParties as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 3. PAID STATUS --}}
            <div class="col-lg-2 col-md-3">
                <label class="filter-label-theme" for="filterStatus">PAID STATUS</label>
                <select class="form-select filter-input-theme" id="filterStatus">
                    <option value="all" selected>All</option>
                    <option value="paid">Paid</option>
                    <option value="partial">Partially Paid</option>
                    <option value="unpaid">Unpaid</option>
                </select>
            </div>

            {{-- 4. INVOICE DATE FROM --}}
            <div class="col-lg-2 col-md-4">
                <label class="filter-label-theme" for="filterDateFrom">INVOICE DATE FROM</label>
                <input type="date" class="form-control filter-input-theme font-num" id="filterDateFrom" placeholder="dd-mm-yyyy">
            </div>

            {{-- 5. INVOICE DATE TO --}}
            <div class="col-lg-2 col-md-4">
                <label class="filter-label-theme" for="filterDateTo">INVOICE DATE TO</label>
                <input type="date" class="form-control filter-input-theme font-num" id="filterDateTo" placeholder="dd-mm-yyyy">
            </div>

            {{-- 6. Apply & Clear Buttons --}}
            <div class="col-lg-1 col-md-4 d-flex gap-1">
                <button type="button" class="btn btn-ss-apply flex-grow-1" id="btnApplyFilters" onclick="applyFilters()" title="Apply Filters">
                    Apply
                </button>
                <button type="button" class="btn btn-ss-clear" id="btnClearFilters" onclick="clearFilters()" title="Clear Filters">
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Main Report Table: EXACT 13 Columns Theme Matched --}}
    <div class="report-table-card-theme">
        <div class="table-responsive-wrapper-theme">
            <table class="report-table-theme">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 45px; cursor: pointer; user-select: none;" onclick="sortByCol('s_no')" title="Click to sort"># <span class="sort-icon" id="sort_s_no">⇅</span></th>
                        <th style="min-width: 170px; cursor: pointer; user-select: none;" onclick="sortByCol('party_name')" title="Click to sort">PARTY <span class="sort-icon" id="sort_party_name">⇅</span></th>
                        <th class="text-center" style="min-width: 100px; cursor: pointer; user-select: none;" onclick="sortByCol('token_no')" title="Click to sort">TOKEN NO. <span class="sort-icon" id="sort_token_no">⇅</span></th>
                        <th style="min-width: 110px; cursor: pointer; user-select: none;" onclick="sortByCol('bill_no')" title="Click to sort">BILL NO. <span class="sort-icon" id="sort_bill_no">⇅</span></th>
                        <th style="min-width: 170px; cursor: pointer; user-select: none;" onclick="sortByCol('date')" title="Click to sort by Date">INVOICE DT / DUE DAYS <span class="sort-icon" id="sort_date">▼</span></th>
                        <th style="min-width: 120px; cursor: pointer; user-select: none;" onclick="sortByCol('truck_no')" title="Click to sort">TRUCK NO. <span class="sort-icon" id="sort_truck_no">⇅</span></th>
                        <th class="text-end" style="min-width: 120px; cursor: pointer; user-select: none;" onclick="sortByCol('taxable_amt')" title="Click to sort">TAXABLE AMT <span class="sort-icon" id="sort_taxable_amt">⇅</span></th>
                        <th class="text-end" style="min-width: 105px; cursor: pointer; user-select: none;" onclick="sortByCol('gst_amt')" title="Click to sort">GST AMT <span class="sort-icon" id="sort_gst_amt">⇅</span></th>
                        <th class="text-end" style="min-width: 80px; cursor: pointer; user-select: none;" onclick="sortByCol('tds_amt')" title="Click to sort">TDS <span class="sort-icon" id="sort_tds_amt">⇅</span></th>
                        <th class="text-end" style="min-width: 120px; cursor: pointer; user-select: none;" onclick="sortByCol('net_amt')" title="Click to sort">NET AMT <span class="sort-icon" id="sort_net_amt">⇅</span></th>
                        <th class="text-end" style="min-width: 160px; cursor: pointer; user-select: none;" onclick="sortByCol('received_amt')" title="Click to sort">RECEIVED AMT <span class="sort-icon" id="sort_received_amt">⇅</span></th>
                        <th class="text-end" style="min-width: 130px; cursor: pointer; user-select: none;" onclick="sortByCol('balance_amt')" title="Click to sort">BALANCE AMT <span class="sort-icon" id="sort_balance_amt">⇅</span></th>
                        <th class="text-center" style="min-width: 120px; cursor: pointer; user-select: none;" onclick="sortByCol('status')" title="Click to sort">STATUS <span class="sort-icon" id="sort_status">⇅</span></th>
                    </tr>
                </thead>
                <tbody id="reportTableBody">
                    {{-- Dynamically populated via AJAX --}}
                </tbody>
                <tfoot id="reportTableFoot">
                    <tr>
                        <td colspan="9" class="footer-total-label">Total</td>
                        <td class="text-end font-num fs-6 fw-bold" id="footNetAmt">₹0</td>
                        <td class="text-end font-num fs-6 fw-bold text-success" id="footReceivedAmt">₹0</td>
                        <td class="text-end font-num fs-6 fw-bold" id="footBalanceAmt">₹0</td>
                        <td class="text-center" id="footStatusLabel">
                            <span class="footer-status-cr">CR</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Bottom Status Line --}}
        <div class="bottom-records-info" id="bottomRecordsInfo">
            Showing 0 records
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Initialize Select2 on Party dropdown
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
            balEl.innerHTML = `${formatCurrencyInt(balVal)} <span class="fs-7 fw-bold">CR</span>`;
            balEl.className = 'summary-card-val text-success';
            if (labelEl) labelEl.textContent = 'Party Advance (CR)';
        } else if (balType === 'dr') {
            balEl.innerHTML = `${formatCurrencyInt(balVal)} <span class="fs-7 fw-bold">DR</span>`;
            balEl.className = 'summary-card-val text-danger';
            if (labelEl) labelEl.textContent = 'Pending Balance (DR)';
        } else {
            balEl.innerHTML = '₹0';
            balEl.className = 'summary-card-val text-success';
            if (labelEl) labelEl.textContent = 'Settled (NIL)';
        }
    }

    /**
     * Render Footer Totals matching theme
     */
    function renderFooterTotals(totals, count) {
        if (!totals) totals = {};

        document.getElementById("bottomRecordsInfo").textContent = `Showing ${count} record${count === 1 ? '' : 's'}`;

        document.getElementById("footNetAmt").textContent = formatCurrencyInt(totals.total_net_amount || 0);
        document.getElementById("footReceivedAmt").textContent = formatCurrencyInt(totals.total_received_amount || 0);
        
        const balVal = parseFloat(totals.total_balance_amount) || 0;
        const balType = totals.balance_type || 'nil';
        const footBalEl = document.getElementById("footBalanceAmt");
        const statusEl = document.getElementById("footStatusLabel");

        footBalEl.textContent = formatCurrencyInt(balVal);

        if (balType === 'cr') {
            footBalEl.className = 'text-end font-num fs-6 fw-bold text-success';
            statusEl.innerHTML = `<span class="footer-status-cr">CR</span>`;
        } else if (balType === 'dr') {
            footBalEl.className = 'text-end font-num fs-6 fw-bold text-danger';
            statusEl.innerHTML = `<span class="footer-status-dr">DR</span>`;
        } else {
            footBalEl.className = 'text-end font-num fs-6 fw-bold text-success';
            statusEl.innerHTML = `<span class="footer-status-cr" style="color: #64748b;">NIL</span>`;
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
                    ${r.credit_days ? `<div class="due-days-sub">${r.credit_days} days</div>` : ''}
                    ${r.due_text ? `<div class="${r.is_overdue ? 'due-days-overdue' : 'due-days-safe'}">${escapeHtml(r.due_text)}</div>` : ''}
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
                    <div class="font-num fw-bold text-success">
                        ${formatCurrencyInt(recvAmtVal)}
                        ${r.received_badge ? `<span class="mini-badge-mode ${badgeClass}">${escapeHtml(r.received_badge)}</span>` : ''}
                    </div>
                    ${r.received_time ? `<div class="text-muted font-num" style="font-size: 0.72rem;">${escapeHtml(r.received_time)}</div>` : ''}
                    ${r.narration ? `<div class="text-secondary" style="font-size: 0.70rem;">${escapeHtml(r.narration)}</div>` : ''}
                `;
            } else {
                recvHtml = `<div class="font-num fw-bold text-success">₹0</div>`;
            }

            // Balance Amount presentation
            let balHtml = '';
            if (r.balance_amt === null || r.balance_amt === undefined) {
                balHtml = '<span class="text-muted">-</span>';
            } else {
                const balVal = parseFloat(r.balance_amt) || 0;
                if (balVal <= 0.01) {
                    balHtml = `<span class="font-num fw-bold text-success">₹0</span>`;
                } else {
                    balHtml = `<span class="font-num fw-bold text-danger">${formatCurrencyInt(balVal)}</span>`;
                }
            }

            // Status Badge: Unpaid (red), Partially Paid (orange), Paid (green), Cash (purple)
            let statusBadge = '';
            const st = (r.status || '').toLowerCase().replace(/\s+/g, '');
            if (st === 'unpaid') {
                statusBadge = '<span class="pill-badge badge-status-unpaid">Unpaid</span>';
            } else if (st === 'partiallypaid' || st === 'partial') {
                statusBadge = '<span class="pill-badge badge-status-partial">Partially Paid</span>';
            } else if (st === 'paid') {
                statusBadge = '<span class="pill-badge badge-status-paid">Paid</span>';
            } else if (st === 'advance') {
                statusBadge = '<span class="pill-badge badge-status-cash">Advance</span>';
            } else {
                // Cash / Other
                statusBadge = `<span class="pill-badge badge-status-cash">${escapeHtml(r.status)}</span>`;
            }

            html += `
                <tr>
                    <td class="text-center font-num text-muted">${r.s_no}</td>
                    <td class="party-cell">${escapeHtml(r.party_name)}</td>
                    <td class="text-center font-num">${r.token_no && r.token_no !== '—' ? escapeHtml(r.token_no) : '<span class="text-muted">—</span>'}</td>
                    <td>${billHtml}</td>
                    <td>${dtDueHtml}</td>
                    <td class="truck-cell font-num">${r.truck_no && r.truck_no !== '—' ? escapeHtml(r.truck_no) : '<span class="text-muted">—</span>'}</td>
                    <td class="text-end font-num">${r.taxable_amt !== null ? formatCurrencyInt(r.taxable_amt) : '<span class="text-muted">—</span>'}</td>
                    <td class="text-end font-num">${r.gst_amt !== null ? formatCurrencyInt(r.gst_amt) : '<span class="text-muted">—</span>'}</td>
                    <td class="text-end font-num">${r.tds_amt !== null ? formatCurrencyInt(r.tds_amt) : '<span class="text-muted">—</span>'}</td>
                    <td class="text-end font-num fw-bold">${r.net_amt !== null ? formatCurrencyInt(r.net_amt) : '<span class="text-muted">—</span>'}</td>
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
