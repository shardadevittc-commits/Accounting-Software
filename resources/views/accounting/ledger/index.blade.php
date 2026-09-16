@extends('admin.layouts.app')

@section('title', 'Ledger Statement | Accounts ERP')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/accounting_dashboard.css') }}">
<!-- Select2 Searchable Dropdown CSS & Bootstrap 5 Theme -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    /* Select2 custom styling matching Accounting Theme */
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

    /* Tabular Numeric Font */
    .font-num {
        font-family: var(--saas-mono, 'JetBrains Mono', Consolas, monospace);
        font-feature-settings: "tnum";
        font-variant-numeric: tabular-nums;
    }

    /* Narration & Weight Badge Cell */
    .narration-flex-cell {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        width: 100%;
    }
    .narration-text {
        font-weight: 500;
        color: var(--text-primary, #0f172a);
    }
    .weight-badge {
        font-size: 0.725rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 6px;
        background: var(--main-body-bg, #f1f5f9);
        color: var(--text-muted, #64748b);
        border: 1px solid var(--border-color, #e2e8f0);
        font-family: var(--saas-mono, Consolas, monospace);
        flex-shrink: 0;
    }

    /* Dr / Cr Status Badges matching Account Theme */
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

    /* Sortable Column Indicators */
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
        opacity: 0.55;
        display: inline-block;
        transition: transform 0.15s ease;
    }

    /* Dark Mode specific tweaks */
    [data-theme="dark"] .narration-text,
    body[data-page-theme="dark"] .narration-text,
    :root[style*="--main-body-bg: #1"] .narration-text,
    :root[style*="--main-body-bg: #0"] .narration-text {
        color: var(--text-primary, #f8fafc);
    }
    [data-theme="dark"] .weight-badge,
    body[data-page-theme="dark"] .weight-badge,
    :root[style*="--main-body-bg: #1"] .weight-badge,
    :root[style*="--main-body-bg: #0"] .weight-badge {
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
                <span class="breadcrumb-active">Ledger</span>
            </nav>
            <h1 class="header-title">
                <span class="header-icon-badge"><i class="fa-solid fa-book-open"></i></span>
                Ledger Statement
            </h1>
            <p class="header-subtitle">Chronological party statement, transaction entries, running debit/credit balances & printable copy of account.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn-header-action" onclick="loadLedgerData()" id="btnRefreshLedger" title="Refresh Statement">
                <i class="fa-solid fa-arrows-rotate" id="syncIcon"></i>
                <span>Refresh</span>
            </button>
            <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2 px-3 py-2 fw-bold shadow-sm" onclick="exportLedgerCsv()" title="Export Statement to CSV">
                <i class="fa-solid fa-file-arrow-down"></i>
                <span>Export CSV</span>
            </button>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2 px-3 py-2 fw-bold shadow-sm" onclick="openPrintView()" title="Print A4 Copy of Account">
                <i class="fa-solid fa-print"></i>
                <span>Print Ledger</span>
            </button>
        </div>
    </div>

    <!-- 2. KPI SUMMARY ROW -->
    <div class="kpi-row mb-3" id="kpiSummaryRow">
        <!-- KPI 1: Total Entries -->
        <div class="kpi-metric-card">
            <div class="kpi-info">
                <span class="kpi-label">Total Entries</span>
                <span class="kpi-value font-num" id="kpiTotalEntries">0</span>
                <span class="kpi-sub" id="kpiTotalWeight"><i class="fa-solid fa-scale-balanced text-primary me-1"></i> Weight: 0 T</span>
            </div>
            <div class="kpi-icon-box kpi-icon-blue">
                <i class="fa-solid fa-file-invoice"></i>
            </div>
        </div>

        <!-- KPI 2: Total Debit -->
        <div class="kpi-metric-card">
            <div class="kpi-info">
                <span class="kpi-label">Total Debit</span>
                <span class="kpi-value text-danger font-num" id="kpiTotalDebit">₹0.00</span>
                <span class="kpi-sub"><i class="fa-solid fa-arrow-down text-danger me-1"></i> Billed / Dispatched</span>
            </div>
            <div class="kpi-icon-box" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                <i class="fa-solid fa-arrow-trend-down"></i>
            </div>
        </div>

        <!-- KPI 3: Total Credit -->
        <div class="kpi-metric-card">
            <div class="kpi-info">
                <span class="kpi-label">Total Credit</span>
                <span class="kpi-value text-success font-num" id="kpiTotalCredit">₹0.00</span>
                <span class="kpi-sub"><i class="fa-solid fa-arrow-up text-success me-1"></i> Payments Received</span>
            </div>
            <div class="kpi-icon-box kpi-icon-emerald">
                <i class="fa-solid fa-arrow-trend-up"></i>
            </div>
        </div>

        <!-- KPI 4: Closing Balance -->
        <div class="kpi-metric-card">
            <div class="kpi-info">
                <span class="kpi-label">Closing Balance</span>
                <span class="kpi-value font-num" id="kpiClosingBalance">₹0.00</span>
                <span class="kpi-sub" id="kpiBalanceType">
                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle fw-bold px-2 py-0" id="kpiDcBadge">Cr (Credit)</span>
                </span>
            </div>
            <div class="kpi-icon-box kpi-icon-purple">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
        </div>
    </div>

    <!-- 3. FILTER TOOLBAR -->
    <div class="filter-toolbar mb-3">
        <div class="filter-group-wrapper">
            <!-- Party Dropdown (Searchable Select2) -->
            <div class="filter-select-item" style="flex: 2 1 320px; min-width: 260px;">
                <label for="filterBuyerName"><i class="fa-solid fa-user-tag text-primary me-1"></i> Party</label>
                <div class="flex-grow-1">
                    <select class="form-select" id="filterBuyerName" onchange="loadLedgerData()">
                        <option value="">-- All Parties (All Ledgers) --</option>
                        @foreach($allParties as $party)
                            <option value="{{ $party }}" {{ $party === $selectedParty ? 'selected' : '' }}>{{ $party }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Date From / To -->
            <div class="filter-date-group">
                <div class="filter-date-item">
                    <label for="filterDateFrom">From</label>
                    <input type="date" id="filterDateFrom" class="font-num" onchange="loadLedgerData()">
                </div>
                <div class="filter-date-item">
                    <label for="filterDateTo">To</label>
                    <input type="date" id="filterDateTo" class="font-num" onchange="loadLedgerData()">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-bold shadow-sm" id="btnApply" onclick="loadLedgerData()" title="Apply Filters">
                    <i class="fa-solid fa-filter"></i>
                    <span>Apply</span>
                </button>
                <button type="button" class="btn-filter-reset" id="btnClear" onclick="clearLedgerFilters()" title="Reset Filters">
                    <i class="fa-solid fa-arrow-rotate-left"></i>
                    <span>Clear</span>
                </button>
                <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold" onclick="exportLedgerCsv()" title="Export CSV">
                    <i class="fa-solid fa-file-arrow-down"></i>
                    <span>CSV</span>
                </button>
                <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold" onclick="openPrintView()" title="Print Statement">
                    <i class="fa-solid fa-print"></i>
                    <span>Print</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 4. ENTERPRISE DATA TABLE CARD -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="enterprise-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 130px;" class="sortable-th" onclick="sortLedger('date')">
                            Date <span class="sort-icon text-muted ms-1" id="sort_date">▼</span>
                        </th>
                        <th style="min-width: 280px;" class="sortable-th" onclick="sortLedger('narration')">
                            Narration / Description <span class="sort-icon text-muted ms-1" id="sort_narration">⇅</span>
                        </th>
                        <th class="text-end sortable-th" style="width: 150px;" onclick="sortLedger('debit')">
                            Debit (₹) <span class="sort-icon text-muted ms-1" id="sort_debit">⇅</span>
                        </th>
                        <th class="text-end sortable-th" style="width: 150px;" onclick="sortLedger('credit')">
                            Credit (₹) <span class="sort-icon text-muted ms-1" id="sort_credit">⇅</span>
                        </th>
                        <th class="text-end sortable-th" style="width: 160px;" onclick="sortLedger('balance')">
                            Balance (₹) <span class="sort-icon text-muted ms-1" id="sort_balance">⇅</span>
                        </th>
                        <th class="text-center" style="width: 90px;">
                            D/C
                        </th>
                    </tr>
                </thead>
                <tbody id="ledgerTableBody">
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                            Loading ledger statement...
                        </td>
                    </tr>
                </tbody>
                <tfoot id="ledgerTableFoot">
                    <tr class="fw-bold" style="background-color: var(--table-th-bg, #f8fafc); border-top: 2px solid var(--border-color);">
                        <td>Total</td>
                        <td class="text-end font-num text-muted" id="footTotalWeight">0 T</td>
                        <td class="text-end font-num text-danger" id="footTotalDebit">0.00</td>
                        <td class="text-end font-num text-success" id="footTotalCredit">0.00</td>
                        <td class="text-end font-num" id="footClosingBalance">0.00</td>
                        <td class="text-center" id="footDcBadge">
                            <span class="badge-dc-cr">Cr</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Table Footer Status Bar -->
        <div class="table-footer-bar d-flex align-items-center justify-content-between px-3 py-2" style="background: var(--card-bg); border-top: 1px solid var(--border-color); font-size: 0.85rem; color: var(--text-muted);">
            <div id="bottomRecordsInfo">
                Showing 0 ledger entries
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill bg-light text-muted border px-2 py-1 font-num" id="activePartyBadge">All Parties</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let currentTransactions = [];
    let sortColumn = 'date';
    let sortDirection = 'desc';

    document.addEventListener("DOMContentLoaded", function () {
        // Initialize Select2 on Party dropdown
        if (window.jQuery && $.fn.select2) {
            $('#filterBuyerName').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '-- All Parties (All Ledgers) --',
                allowClear: true
            });

            $('#filterBuyerName').on('change', function () {
                loadLedgerData();
            });
        }

        // Always load ledger data on page load (shows all parties by default)
        loadLedgerData();
    });

    /**
     * Clear all filters
     */
    function clearLedgerFilters() {
        if (window.jQuery && $.fn.select2) {
            $('#filterBuyerName').val('').trigger('change.select2');
        }
        document.getElementById("filterBuyerName").value = '';
        document.getElementById("filterDateFrom").value = '';
        document.getElementById("filterDateTo").value = '';
        loadLedgerData();
    }

    /**
     * Open dynamic print page matching ledger.dart
     */
    function openPrintView() {
        const buyerVal = (window.jQuery ? $('#filterBuyerName').val() : null) || document.getElementById("filterBuyerName")?.value || '';
        const dateFrom = document.getElementById("filterDateFrom").value;
        const dateTo = document.getElementById("filterDateTo").value;

        const params = new URLSearchParams({
            buyer_name: buyerVal,
            date_from: dateFrom,
            date_to: dateTo,
            auto_print: '1'
        });

        window.open(`/ledger/print?${params.toString()}`, '_blank');
    }

    /**
     * Interactive Column Sorter
     */
    function sortLedger(column) {
        if (!currentTransactions || currentTransactions.length === 0) return;

        if (sortColumn === column) {
            sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            sortColumn = column;
            sortDirection = (column === 'debit' || column === 'credit' || column === 'balance' || column === 'date') ? 'desc' : 'asc';
        }

        // Update Icons
        document.querySelectorAll('.sort-icon').forEach(el => el.textContent = '⇅');
        const activeIcon = document.getElementById('sort_' + column);
        if (activeIcon) {
            activeIcon.textContent = sortDirection === 'asc' ? '▲' : '▼';
        }

        currentTransactions.sort((a, b) => {
            if (column === 'date') {
                const dA = a.date_raw || '';
                const dB = b.date_raw || '';
                if (dA !== dB) return sortDirection === 'asc' ? (dA < dB ? -1 : 1) : (dA > dB ? -1 : 1);
                return sortDirection === 'asc' ? ((a.id || 0) - (b.id || 0)) : ((b.id || 0) - (a.id || 0));
            }

            if (['debit', 'credit', 'balance'].includes(column)) {
                const key = column + '_raw';
                const vA = parseFloat(a[key]) || 0;
                const vB = parseFloat(b[key]) || 0;
                return sortDirection === 'asc' ? (vA - vB) : (vB - vA);
            }

            const sA = (a[column] || '').toString().toLowerCase();
            const sB = (b[column] || '').toString().toLowerCase();
            if (sA < sB) return sortDirection === 'asc' ? -1 : 1;
            if (sA > sB) return sortDirection === 'asc' ? 1 : -1;
            return 0;
        });

        const buyerVal = (window.jQuery ? $('#filterBuyerName').val() : null) || document.getElementById("filterBuyerName")?.value || '';
        renderLedgerTable(currentTransactions, buyerVal);
    }

    /**
     * Fetch Ledger Data dynamically via AJAX
     */
    function loadLedgerData() {
        const tbody = document.getElementById("ledgerTableBody");
        const buyerVal = (window.jQuery ? $('#filterBuyerName').val() : null) || document.getElementById("filterBuyerName")?.value || '';
        const syncIcon = document.getElementById("syncIcon");

        if (syncIcon) syncIcon.classList.add("fa-spin");

        const loadingMsg = buyerVal
            ? `Loading ledger statement for <strong>${escapeHtml(buyerVal)}</strong>...`
            : `Loading ledger statement for <strong>All Parties</strong>...`;

        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    ${loadingMsg}
                </td>
            </tr>
        `;

        const params = new URLSearchParams({
            buyer_name: buyerVal,
            date_from: document.getElementById("filterDateFrom").value,
            date_to: document.getElementById("filterDateTo").value,
        });

        fetch(`/ledger/data?${params.toString()}`)
            .then(res => res.json())
            .then(res => {
                if (syncIcon) syncIcon.classList.remove("fa-spin");

                if (res.status === 'success') {
                    currentTransactions = res.transactions || [];
                    sortColumn = 'date';
                    sortDirection = 'desc';
                    document.querySelectorAll('.sort-icon').forEach(el => el.textContent = '⇅');
                    const dateSortIcon = document.getElementById('sort_date');
                    if (dateSortIcon) dateSortIcon.textContent = '▼';

                    renderLedgerTable(currentTransactions, buyerVal);
                    renderFooter(res.totals, buyerVal);
                } else {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">${res.message || 'Error loading ledger.'}</td></tr>`;
                }
            })
            .catch(err => {
                if (syncIcon) syncIcon.classList.remove("fa-spin");
                console.error("Ledger fetch error:", err);
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">Failed to communicate with server.</td></tr>`;
            });
    }

    /**
     * Render Table Rows matching Account Theme
     */
    function renderLedgerTable(rows, buyerVal) {
        const tbody = document.getElementById("ledgerTableBody");
        if (!rows || rows.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-inbox fs-2 mb-2 d-block opacity-40"></i>
                        ${buyerVal ? 'No transactions found for this party in the selected period.' : 'No transactions found across all parties in the selected period.'}
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        rows.forEach(r => {
            const debitVal = parseFloat(r.debit_raw) || 0;
            const creditVal = parseFloat(r.credit_raw) || 0;
            const balVal = parseFloat(r.balance_raw) || 0;

            const debitStr = debitVal > 0 ? `<span class="text-danger fw-semibold">₹${formatNumber(debitVal)}</span>` : '<span class="text-muted">—</span>';
            const creditStr = creditVal > 0 ? `<span class="text-success fw-semibold">₹${formatNumber(creditVal)}</span>` : '<span class="text-muted">—</span>';
            const balStr = `<span class="fw-bold">₹${formatNumber(balVal)}</span>`;

            const dcBadge = (r.dc === 'Dr')
                ? '<span class="badge-dc-dr">Dr</span>'
                : '<span class="badge-dc-cr">Cr</span>';

            const partyTag = (!buyerVal && r.party_name)
                ? `<span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2 py-1 px-2 font-sans" style="font-size: 0.72rem; font-weight: 600;"><i class="fa-solid fa-user me-1"></i>${escapeHtml(r.party_name)}</span>`
                : '';

            html += `
                <tr>
                    <td class="font-num fw-semibold">${escapeHtml(r.date_display)}</td>
                    <td>
                        <div class="narration-flex-cell">
                            <div class="d-flex align-items-center flex-wrap gap-1">
                                <span class="narration-text">${escapeHtml(r.narration)}</span>
                                ${partyTag}
                            </div>
                            ${r.weight_display ? `<span class="weight-badge">${escapeHtml(r.weight_display)}</span>` : ''}
                        </div>
                    </td>
                    <td class="text-end font-num">${debitStr}</td>
                    <td class="text-end font-num">${creditStr}</td>
                    <td class="text-end font-num">${balStr}</td>
                    <td class="text-center">${dcBadge}</td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    /**
     * Render Footer Totals and KPI Summary Cards
     */
    function renderFooter(totals, buyerName) {
        if (!totals) totals = {};

        const entriesCount = totals.total_entries || 0;
        const totalDebit = totals.total_debit || 0;
        const totalCredit = totals.total_credit || 0;
        const closingBal = totals.closing_balance || 0;
        const dc = totals.dc || 'Cr';
        const weight = totals.total_weight || '0 T';

        // Update Bottom Bar
        document.getElementById("bottomRecordsInfo").textContent = `Showing ${entriesCount} ledger entr${entriesCount === 1 ? 'y' : 'ies'}`;
        const partyBadge = document.getElementById("activePartyBadge");
        if (partyBadge) {
            partyBadge.textContent = buyerName ? buyerName : 'All Parties';
        }

        // Update Table Foot
        document.getElementById("footTotalWeight").textContent = weight;
        document.getElementById("footTotalDebit").textContent = '₹' + formatNumber(totalDebit);
        document.getElementById("footTotalCredit").textContent = '₹' + formatNumber(totalCredit);
        document.getElementById("footClosingBalance").textContent = '₹' + formatNumber(closingBal);

        const footDc = document.getElementById("footDcBadge");
        if (dc === 'Dr') {
            footDc.innerHTML = '<span class="badge-dc-dr">Dr</span>';
        } else {
            footDc.innerHTML = '<span class="badge-dc-cr">Cr</span>';
        }

        // Update Top KPI Cards
        document.getElementById("kpiTotalEntries").textContent = entriesCount;
        document.getElementById("kpiTotalWeight").innerHTML = `<i class="fa-solid fa-scale-balanced text-primary me-1"></i> Weight: ${escapeHtml(weight)}`;
        document.getElementById("kpiTotalDebit").textContent = '₹' + formatNumber(totalDebit);
        document.getElementById("kpiTotalCredit").textContent = '₹' + formatNumber(totalCredit);
        document.getElementById("kpiClosingBalance").textContent = '₹' + formatNumber(closingBal);

        const kpiDcBadge = document.getElementById("kpiDcBadge");
        if (kpiDcBadge) {
            if (dc === 'Dr') {
                kpiDcBadge.className = 'badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle fw-bold px-2 py-0';
                kpiDcBadge.textContent = 'Dr (Debit)';
            } else {
                kpiDcBadge.className = 'badge rounded-pill bg-success-subtle text-success border border-success-subtle fw-bold px-2 py-0';
                kpiDcBadge.textContent = 'Cr (Credit)';
            }
        }
    }

    function formatNumber(num) {
        const val = parseFloat(num) || 0;
        return val.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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

    /**
     * Export Ledger CSV
     */
    function exportLedgerCsv() {
        const buyerVal = (window.jQuery ? $('#filterBuyerName').val() : null) || document.getElementById("filterBuyerName")?.value || '';

        const params = new URLSearchParams({
            buyer_name: buyerVal,
            date_from: document.getElementById("filterDateFrom") ? document.getElementById("filterDateFrom").value : '',
            date_to: document.getElementById("filterDateTo") ? document.getElementById("filterDateTo").value : '',
        });

        window.location.href = `/ledger/export?${params.toString()}`;
    }
</script>
@endpush
