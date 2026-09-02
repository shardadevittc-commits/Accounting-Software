@extends('admin.layouts.app')

@section('title', 'Dispatch Invoicing & Transactions | Accounts ERP')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/accounting_dashboard.css') }}">
@endpush

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3 accounting-app-container">

    <!-- 1. BREADCRUMBS & PAGE HEADER -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-3">
        <div>
            <nav class="page-breadcrumb" aria-label="breadcrumb">
                <a href="{{ route('dashboard') }}"><i class="fa-solid fa-house-chimney me-1"></i> Home</a>
                <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
                <a href="javascript:void(0)">Transactions</a>
                <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
                <span class="breadcrumb-active">Dispatch Invoicing</span>
            </nav>
            <h1 class="header-title">
                <span class="header-icon-badge"><i class="fa-solid fa-receipt"></i></span>
                Dispatches & Invoice Management
            </h1>
            <p class="header-subtitle">Review factory dispatched materials and generate GST compliant tax invoices instantly.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="live-status-pill" id="erpStatusBadge">
                <span class="pulse-dot"></span>
                <span>ERP Sync: Active</span>
            </span>
            <button class="btn-header-action" onclick="refreshQueue()" id="btnSyncDispatches" title="Fetch fresh dispatch tokens from ERP">
                <i class="fa-solid fa-arrows-rotate" id="syncIcon"></i>
                <span>Sync Dispatches</span>
            </button>
        </div>
    </div>

    <!-- 2. KPI SUMMARY METRIC CARDS ROW -->
    <div class="kpi-row" id="kpiSummaryRow">
        <!-- Metric 1: Total Dispatches -->
        <div class="kpi-metric-card">
            <div class="kpi-info">
                <span class="kpi-label">Total Dispatches</span>
                <span class="kpi-value" id="kpiTotalDispatches">0</span>
                <span class="kpi-sub"><i class="fa-solid fa-layer-group text-primary me-1"></i> Active in queue</span>
            </div>
            <div class="kpi-icon-box kpi-icon-blue">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
        </div>

        <!-- Metric 2: Pending Invoicing -->
        <div class="kpi-metric-card">
            <div class="kpi-info">
                <span class="kpi-label">Pending Invoices</span>
                <span class="kpi-value text-warning" id="kpiPendingCount">0</span>
                <span class="kpi-sub" id="kpiPendingAmount">₹0.00 unbilled</span>
            </div>
            <div class="kpi-icon-box kpi-icon-amber">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>

        <!-- Metric 3: Invoiced -->
        <div class="kpi-metric-card">
            <div class="kpi-info">
                <span class="kpi-label">Invoices Completed</span>
                <span class="kpi-value text-success" id="kpiInvoicedCount">0</span>
                <span class="kpi-sub" id="kpiInvoicedAmount">₹0.00 billed</span>
            </div>
            <div class="kpi-icon-box kpi-icon-emerald">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <!-- Metric 4: Total Weight & Taxable Value -->
        <div class="kpi-metric-card">
            <div class="kpi-info">
                <span class="kpi-label">Dispatched Volume</span>
                <span class="kpi-value" id="kpiTotalTonnage">0.000 <small class="fs-7 fw-normal text-muted">Tons</small></span>
                <span class="kpi-sub" id="kpiTotalAmount">₹0.00 total value</span>
            </div>
            <div class="kpi-icon-box kpi-icon-purple">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
        </div>
    </div>

    <!-- 3. STAGE 1: PENDING DISPATCHES QUEUE SCREEN -->
    <div id="section-queue">
        
        <!-- Filter Toolbar -->
        <div class="filter-toolbar">
            <div class="filter-group-wrapper">
                <!-- Search Input -->
                <div class="filter-search-box">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" id="filterSearch" placeholder="Search customer, vehicle no, dispatch ID..." onkeyup="debounceFilter()">
                </div>

                <!-- Date Range -->
                <div class="filter-date-group">
                    <div class="filter-date-item">
                        <label for="filterDateFrom">From</label>
                        <input type="date" id="filterDateFrom" onchange="applyFilters()">
                    </div>
                    <div class="filter-date-item">
                        <label for="filterDateTo">To</label>
                        <input type="date" id="filterDateTo" onchange="applyFilters()">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="filter-select-item">
                    <label for="filterStatus">Status</label>
                    <select id="filterStatus" onchange="applyFilters()">
                        <option value="pending" selected>Pending Invoicing</option>
                        <option value="invoiced">Invoiced Only</option>
                        <option value="all">All Dispatches</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <button class="btn-filter-reset" onclick="clearFilters()" title="Clear all search & date filters">
                    <i class="fa-solid fa-arrow-rotate-left"></i>
                    <span>Reset</span>
                </button>
            </div>
        </div>

        <!-- Enterprise Table Container -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="enterprise-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Dispatch Ref</th>
                            <th style="width: 120px;">Date</th>
                            <th style="min-width: 240px;">Customer & GSTIN</th>
                            <th class="col-vehicle-highlight" style="width: 165px;"><i class="fa-solid fa-truck me-1" style="color: #6366f1;"></i> Vehicle No.</th>
                            <th class="text-end" style="width: 130px;">Weight (Tons)</th>
                            <th class="text-end" style="width: 150px;">Taxable Amount</th>
                            <th class="text-center" style="width: 150px;">Invoice Status</th>
                            <th class="text-end" style="width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="dispatchQueueTableBody">
                        <tr class="skeleton-row">
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                Connecting to ERP & synchronizing dispatches...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Meta Bar -->
            <div class="table-footer-bar">
                <div>
                    Showing <strong id="tableShowingCount">0</strong> of <strong id="tableTotalCount">0</strong> dispatches
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-8 text-muted"><i class="fa-solid fa-circle-info me-1"></i> Click Generate Invoice to create tax voucher</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. STAGE 3: INTERACTIVE INVOICE GENERATION VOUCHER / PREVIEW / EDIT WORKSPACE -->
    <div id="section-voucher" class="d-none">
        <div class="table-card p-4">
            
            <!-- Back header -->
            <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom" style="border-color: var(--border-color) !important;">
                <button class="btn-saas-ghost" onclick="showSection('queue')">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Dispatches
                </button>
                <div class="d-flex align-items-center gap-2">
                    <h5 class="mb-0 fw-bold d-flex align-items-center gap-2" style="color: var(--text-primary);">
                        <i class="fa-solid fa-file-invoice text-success"></i> Tax Invoice Voucher Preview
                    </h5>
                </div>
                <div class="badge bg-success-subtle text-success border border-success px-3 py-2 fw-bold font-monospace">
                    <i class="fa-solid fa-location-dot me-1"></i> Supplier State: Punjab (03)
                </div>
            </div>

            <form id="voucherForm">
                @csrf
                <!-- Hidden Metadata Fields -->
                <input type="hidden" id="v_vehicle_id" name="vehicle_id">
                <input type="hidden" id="v_dispatch_id" name="dispatch_id">
                <input type="hidden" id="v_customer_id" name="customer_id">

                <!-- Section: Invoice Info -->
                <div class="voucher-form-title">
                    <i class="fa-solid fa-receipt"></i> Invoice Information
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fs-8 fw-bold" style="color: var(--text-primary);">INVOICE NO. <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" class="form-control voucher-input font-monospace fw-bold" id="v_invoice_no" name="invoice_no" placeholder="INV-{{ date('Y') }}-0001" required style="padding-right: 68px;">
                            <div class="position-absolute end-0 top-50 translate-middle-y pe-2 d-flex align-items-center gap-1" style="z-index: 5;">
                                <button class="btn btn-sm p-1 text-muted hover-icon-btn" type="button" onclick="copyInvoiceNo()" title="Copy Invoice Number" style="border: none; background: transparent; cursor: pointer; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                    <i class="fa-regular fa-copy fs-7" id="invCopyIcon"></i>
                                </button>
                                <button class="btn btn-sm p-1 text-muted hover-icon-btn" type="button" onclick="fetchNextInvoiceNo()" title="Regenerate / Refresh Sequence" style="border: none; background: transparent; cursor: pointer; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                    <i class="fa-solid fa-arrows-rotate fs-7" id="invRefreshIcon"></i>
                                </button>
                            </div>
                        </div>
                        <div class="fs-9 text-muted mt-1 d-flex align-items-center gap-1">
                            <span id="invStatusCheck" class="text-success"><i class="fa-solid fa-check"></i></span>
                            <span>System generated • <strong class="font-monospace text-primary" id="invPrefixHint">INV-{{ date('Y') }}</strong></span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-8 fw-bold" style="color: var(--text-primary);">INVOICE DATE</label>
                        <input type="date" class="form-control voucher-input" id="v_invoice_date" name="invoice_date" value="{{ date('Y-m-d') }}" onchange="fetchNextInvoiceNo(this.value)">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-8 fw-bold" style="color: var(--text-primary);">DISPATCH NO.</label>
                        <input type="text" class="form-control voucher-input font-monospace fw-bold" id="v_dispatch_no" name="dispatch_no" placeholder="e.g. DSP-4">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-8 fw-bold" style="color: var(--text-primary);">VEHICLE NO.</label>
                        <input type="text" class="form-control voucher-input font-monospace fw-bold" id="v_vehicle_no" name="vehicle_no" placeholder="e.g. PB11DA2794">
                    </div>
                </div>

                <!-- Section: Buyer Info -->
                <div class="voucher-form-title">
                    <i class="fa-solid fa-building-user"></i> Bill To / Buyer Details
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fs-8 fw-bold" style="color: var(--text-primary);">CUSTOMER NAME</label>
                        <input type="text" class="form-control voucher-input fw-bold" id="v_customer_name" name="customer_name" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-8 fw-bold" style="color: var(--text-primary);">GSTIN</label>
                        <input type="text" class="form-control voucher-input font-monospace text-uppercase" id="v_customer_gst" name="customer_gst" oninput="handleGstinChange(this.value)">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-8 fw-bold" style="color: var(--text-primary);">BILLING ADDRESS</label>
                        <input type="text" class="form-control voucher-input" id="v_customer_address" name="customer_address">
                    </div>
                </div>

                <!-- Section: Transport Info -->
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fs-8 fw-bold" style="color: var(--text-primary);">TRANSPORT NAME / CARRIER</label>
                        <input type="text" class="form-control voucher-input" id="v_transport_name" name="transport_name" placeholder="Transport / Carrier Name">
                    </div>
                </div>

                <!-- Section: Product Details Table -->
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="voucher-form-title mb-0 border-0">
                        <i class="fa-solid fa-boxes-packing"></i> Product Details & Invoice Line Items
                    </div>
                    <button type="button" class="btn-saas-ghost" onclick="addVoucherRow()">
                        <i class="fa-solid fa-plus text-success me-1"></i> Add Line Item
                    </button>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle enterprise-table mb-0" id="voucherItemsTable" style="min-width: 1650px;">
                        <thead>
                            <tr class="text-center" style="background: var(--table-th-bg, #f1f5f9);">
                                <th style="width: 45px;">#</th>
                                <th style="min-width: 240px; text-align: left;">Product Name</th>
                                <th style="min-width: 130px;">Grade</th>
                                <th style="min-width: 95px;">Size</th>
                                <th style="min-width: 110px;">HSN</th>
                                <th style="min-width: 140px;">Qty (Tons)</th>
                                <th style="min-width: 90px;">Unit</th>
                                <th style="min-width: 140px;">Rate (₹)</th>
                                <th style="min-width: 140px;">Taxable Amt</th>
                                <th style="min-width: 95px;">GST %</th>
                                <th style="min-width: 115px;">CGST</th>
                                <th style="min-width: 115px;">SGST</th>
                                <th style="min-width: 115px;">IGST</th>
                                <th style="min-width: 150px;">Total Amt</th>
                                <th style="width: 45px;"></th>
                            </tr>
                        </thead>
                        <tbody id="voucherItemsBody">
                            <!-- Loaded dynamically -->
                        </tbody>
                    </table>
                </div>

                <!-- Section: Calculations and Summaries -->
                <div class="row g-4 mt-2">
                    <div class="col-lg-6">
                        <label class="form-label fs-8 fw-bold" style="color: var(--text-primary);">REMARKS / ACCOUNTING NOTES</label>
                        <textarea class="form-control voucher-input" id="v_remarks" name="remarks" rows="3" placeholder="Specify dispatch terms, payment schedule or delivery notes..." style="height: auto; min-height: 85px;"></textarea>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="p-4 rounded-3 border" style="background-color: var(--main-body-bg); border-color: var(--border-color);">
                            
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span style="color: var(--text-primary); font-weight: 500;">Sub Total:</span>
                                <span class="fw-bold font-monospace" style="color: var(--text-primary);" id="lblSubTotal">₹0.00</span>
                            </div>
                            <div class="row g-2 mb-3 align-items-center">
                                <div class="col-6">
                                    <span style="color: var(--text-primary); font-weight: 500;">Discount (if applicable):</span>
                                </div>
                                <div class="col-6 text-end">
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace voucher-input" style="width: 130px; display: inline-block;" id="v_discount" value="0.00" onchange="recalculateVoucherSummary()">
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3 border-top pt-2" style="border-color: var(--border-color) !important;">
                                <span class="fw-bold" style="color: var(--text-primary);">Taxable Amount:</span>
                                <span class="fw-bold fs-6 font-monospace" style="color: var(--text-primary);" id="lblTaxableAmount">₹0.00</span>
                                <input type="hidden" name="taxable_amount" id="valTaxableAmount">
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fs-8" style="color: var(--text-muted);">CGST Amount:</span>
                                <span class="fw-bold font-monospace fs-8" style="color: var(--text-primary);" id="lblCgstAmount">₹0.00</span>
                                <input type="hidden" name="cgst_amount" id="valCgstAmount">
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fs-8" style="color: var(--text-muted);">SGST Amount:</span>
                                <span class="fw-bold font-monospace fs-8" style="color: var(--text-primary);" id="lblSgstAmount">₹0.00</span>
                                <input type="hidden" name="sgst_amount" id="valSgstAmount">
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="fs-8" style="color: var(--text-muted);">IGST Amount:</span>
                                <span class="fw-bold font-monospace fs-8" style="color: var(--text-primary);" id="lblIgstAmount">₹0.00</span>
                                <input type="hidden" name="igst_amount" id="valIgstAmount">
                            </div>

                            <div class="row g-2 mb-2 align-items-center border-top pt-2" style="border-color: var(--border-color) !important;">
                                <div class="col-6">
                                    <span style="color: var(--text-primary); font-weight: 500;">Freight / Transport (₹):</span>
                                </div>
                                <div class="col-6 text-end">
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace voucher-input" style="width: 130px; display: inline-block;" id="v_freight" name="freight_charges" value="0.00" onchange="recalculateVoucherSummary()">
                                </div>
                            </div>
                            <div class="row g-2 mb-3 align-items-center">
                                <div class="col-6">
                                    <span style="color: var(--text-primary); font-weight: 500;">Other Charges (₹):</span>
                                </div>
                                <div class="col-6 text-end">
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace voucher-input" style="width: 130px; display: inline-block;" id="v_other_charges" name="other_charges" value="0.00" onchange="recalculateVoucherSummary()">
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-2 border-top pt-2" style="border-color: var(--border-color) !important;">
                                <span class="fs-8" style="color: var(--text-muted);">Round Off:</span>
                                <span class="fw-bold font-monospace fs-8" style="color: var(--text-primary);" id="lblRoundOff">₹0.00</span>
                            </div>

                            <div class="d-flex align-items-center justify-content-between border-top pt-3 mt-2" style="border-color: var(--border-color) !important;">
                                <span class="fw-bold fs-5" style="color: var(--text-primary);">GRAND TOTAL:</span>
                                <span class="fw-bold fs-3 font-monospace text-success" id="lblGrandTotal">₹0.00</span>
                                <input type="hidden" name="grand_total" id="valGrandTotal">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-3 border-top" style="border-color: var(--border-color) !important;">
                    <button type="button" class="btn-saas-ghost" onclick="showSection('queue')">
                        <i class="fa-solid fa-xmark me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn-saas-primary" onclick="triggerConfirmModal(false)">
                        <i class="fa-solid fa-file-invoice me-1"></i> Generate Invoice
                    </button>
                    <button type="button" class="btn-saas-outline-info" onclick="triggerConfirmModal(true)">
                        <i class="fa-solid fa-print me-1"></i> Generate & Print
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 5. STAGE 5: SUCCESS SCREEN / INVOICE CREATED -->
    <div id="section-success" class="d-none">
        <div class="col-md-7 mx-auto text-center py-5">
            <div class="table-card p-5">
                <div class="mb-3">
                    <i class="fa-solid fa-circle-check text-success" style="font-size: 3.5rem;"></i>
                </div>
                <h3 class="fw-bold mb-2" style="color: var(--text-primary);">Invoice Generated Successfully!</h3>
                <p style="color: var(--text-muted);" class="fs-6 mb-4">Tax Invoice number <strong class="text-primary font-monospace" id="lblSuccessInvoiceNo">INV-2026-0000</strong> has been persisted in records.</p>
                
                <div class="d-flex align-items-center justify-content-center gap-3 mb-4">
                    <button class="btn-saas-primary px-4 py-2" id="btnSuccessPrint">
                        <i class="fa-solid fa-print me-1"></i> Print Tax Invoice
                    </button>
                    <button class="btn-saas-ghost px-4 py-2" id="btnSuccessView">
                        <i class="fa-solid fa-eye me-1"></i> View Layout
                    </button>
                </div>

                <hr class="my-4" style="border-color: var(--border-color);">

                <button class="btn-saas-ghost px-4 py-2" onclick="returnToQueue()">
                    <i class="fa-solid fa-arrow-rotate-left me-1"></i> Return to Dispatches Queue
                </button>
            </div>
        </div>
    </div>

</div>

<!-- STAGE 2: PREVIEW & REVIEW INVOICE SLIDING DRAWER (Right Slide-In) -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeReviewDrawer()"></div>
<div class="drawer" id="reviewDrawer">
    
    <!-- Drawer Header -->
    <div class="drawer-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="header-icon-badge" style="width: 32px; height: 32px; font-size: 0.95rem;"><i class="fa-solid fa-file-invoice"></i></span>
            <h5 class="mb-0 fw-bold d-flex align-items-center gap-2" style="color: var(--text-primary);">
                Review ERP Dispatch & Invoice Preview
            </h5>
        </div>
        <button type="button" class="btn-close" onclick="closeReviewDrawer()" aria-label="Close"></button>
    </div>

    <!-- Drawer Body (Document Sheet) -->
    <div class="drawer-body p-3 p-lg-4" style="background-color: var(--main-body-bg);">
        
        <div class="invoice-preview-sheet shadow-sm">
            
            <!-- Invoice Document Header -->
            <div class="invoice-preview-header">
                <div>
                    <h2 class="invoice-preview-title">TAX INVOICE PREVIEW</h2>
                    <div class="invoice-ref-number" id="pvDispatchRef">DSP-000</div>
                </div>
                <div class="text-end">
                    <div class="invoice-brand-badge">Accounting <span class="text-muted fs-7 fw-normal">Accounts ERP</span></div>
                    <div class="badge bg-success-subtle text-success border border-success px-3 py-1 mt-2 fw-bold font-monospace">
                        Supplier State: Punjab (03)
                    </div>
                </div>
            </div>

            <!-- Two-Column Meta Grid: Buyer Info vs Dispatch Details -->
            <div class="invoice-meta-grid">
                
                <!-- Left Column: Buyer / Consignee Details -->
                <div class="invoice-meta-card">
                    <div class="invoice-meta-card-title">
                        <i class="fa-solid fa-building-user text-primary"></i> Buyer Information (Bill To)
                    </div>
                    <div class="invoice-meta-row">
                        <span class="invoice-meta-label">Customer:</span>
                        <span class="invoice-meta-value fs-6" id="pvCustomerName">Loading...</span>
                    </div>
                    <div class="invoice-meta-row">
                        <span class="invoice-meta-label">GSTIN:</span>
                        <span class="invoice-meta-value font-monospace text-uppercase" id="pvCustomerGst">-</span>
                    </div>
                    <div class="invoice-meta-row">
                        <span class="invoice-meta-label">Address:</span>
                        <span class="invoice-meta-value" id="pvCustomerAddress">-</span>
                    </div>
                    <div class="invoice-meta-row">
                        <span class="invoice-meta-label">City/State:</span>
                        <span class="invoice-meta-value" id="pvCustomerCityState">-</span>
                    </div>
                    <div class="invoice-meta-row">
                        <span class="invoice-meta-label">Contact:</span>
                        <span class="invoice-meta-value text-muted" id="pvCustomerContact">-</span>
                    </div>
                </div>

                <!-- Right Column: Dispatch & Transport Metadata -->
                <div class="invoice-meta-card">
                    <div class="invoice-meta-card-title">
                        <i class="fa-solid fa-truck text-warning"></i> Dispatch Metadata
                    </div>
                    <div class="invoice-meta-row">
                        <span class="invoice-meta-label">Dispatch Ref:</span>
                        <span class="invoice-meta-value font-monospace text-primary" id="pvDispatchNo">-</span>
                    </div>
                    <div class="invoice-meta-row">
                        <span class="invoice-meta-label">Dispatch Date:</span>
                        <span class="invoice-meta-value" id="pvDispatchDate">-</span>
                    </div>
                    <div class="invoice-meta-row">
                        <span class="invoice-meta-label">Vehicle No:</span>
                        <span class="invoice-meta-value" id="pvVehicleNo">-</span>
                    </div>
                    <div class="invoice-meta-row">
                        <span class="invoice-meta-label">Transporter:</span>
                        <span class="invoice-meta-value" id="pvTransportName">-</span>
                    </div>
                    <div class="invoice-meta-row">
                        <span class="invoice-meta-label">Place of Supply:</span>
                        <span class="invoice-meta-value" id="pvPlaceOfSupply">Punjab (03)</span>
                    </div>
                </div>

            </div>

            <!-- Dispatched Goods Table -->
            <div class="voucher-form-title">
                <i class="fa-solid fa-boxes-stacked"></i> Dispatched Goods & Deliverables
            </div>
            <div class="table-responsive mb-4">
                <table class="table table-bordered invoice-goods-table align-middle text-center mb-0" style="min-width: 720px;">
                    <thead>
                        <tr>
                            <th style="width: 35px;">#</th>
                            <th class="text-start" style="min-width: 170px;">Deliverable / Product</th>
                            <th style="width: 80px;">Grade</th>
                            <th style="width: 70px;">Size</th>
                            <th style="width: 75px;">HSN</th>
                            <th class="text-end" style="width: 105px;">Weight (Tons)</th>
                            <th class="text-end" style="width: 100px;">Rate (₹)</th>
                            <th class="text-end" style="width: 120px;">Taxable Amt</th>
                            <th style="width: 65px;">GST %</th>
                            <th class="text-end" style="width: 125px;">Total Amt (₹)</th>
                        </tr>
                    </thead>
                    <tbody id="pvItemsTableBody">
                        <!-- Loaded dynamically -->
                    </tbody>
                    <tfoot id="pvItemsTableFoot">
                        <tr>
                            <th colspan="5" class="text-start text-uppercase font-monospace fw-bold">TOTAL</th>
                            <th class="text-end font-monospace" id="pvFootTotalWeight">0.000</th>
                            <th class="text-end">-</th>
                            <th class="text-end font-monospace" id="pvFootTotalTaxable">₹0.00</th>
                            <th>-</th>
                            <th class="text-end font-monospace" id="pvFootTotalAmount">₹0.00</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Accounting Calculation Breakdown (Screenshot 2 Inspiration) -->
            <div class="row g-4 mt-1">
                <!-- <div class="col-lg-5"> -->
                    <!-- <div class="p-3 rounded-3 border h-100" style="background-color: var(--main-body-bg); border-color: var(--border-color);">
                        <h6 class="fw-bold fs-7 text-uppercase mb-2 text-muted"><i class="fa-solid fa-circle-info me-1 text-primary"></i> Tax & Accounting Summary</h6>
                        <p class="fs-8 text-muted mb-2">This is a verified ERP dispatch preview with GST breakdown calculated against place of supply rules.</p>
                        <div class="d-flex flex-column gap-1 mt-3">
                            <div class="badge bg-primary-subtle text-primary border border-primary px-2 py-1 fs-9 fw-semibold text-start">
                                <i class="fa-solid fa-shield-check me-1"></i> Intra-State: CGST (9%) + SGST (9%)
                            </div>
                            <div class="badge bg-info-subtle text-info border border-info px-2 py-1 fs-9 fw-semibold text-start">
                                <i class="fa-solid fa-globe me-1"></i> Inter-State: IGST (18%)
                            </div>
                        </div>
                    </div> -->
                <!-- </div> -->
                <div class="col-lg-12">
                    <div class="invoice-calc-breakdown-card">
                        <div class="invoice-calc-row">
                            <span class="invoice-calc-label">SubTotal (Gross Amount):</span>
                            <span class="invoice-calc-value" id="pvCalcSubtotal">₹0.00</span>
                        </div>
                        <div class="invoice-calc-row">
                            <span class="invoice-calc-label">Cash Discount (CD):</span>
                            <span class="invoice-calc-value" id="pvCalcDiscount">₹0.00</span>
                        </div>
                        <div class="invoice-calc-row">
                            <span class="invoice-calc-label">Labor / Freight:</span>
                            <span class="invoice-calc-value" id="pvCalcLabor">₹0.00</span>
                        </div>
                        <div class="invoice-calc-row">
                            <span class="invoice-calc-label">Other Charges:</span>
                            <span class="invoice-calc-value" id="pvCalcOther">₹0.00</span>
                        </div>
                        <div class="invoice-calc-row highlight-row">
                            <span class="invoice-calc-label">Taxable Amount (After Charges):</span>
                            <span class="invoice-calc-value" id="pvCalcAfterCharges">₹0.00</span>
                        </div>
                        <div class="invoice-calc-row" id="pvCgstRow">
                            <span class="invoice-calc-label">CGST Amount (9%):</span>
                            <span class="invoice-calc-value" id="pvCalcCgst">₹0.00</span>
                        </div>
                        <div class="invoice-calc-row" id="pvSgstRow">
                            <span class="invoice-calc-label">SGST Amount (9%):</span>
                            <span class="invoice-calc-value" id="pvCalcSgst">₹0.00</span>
                        </div>
                        <div class="invoice-calc-row" id="pvIgstRow">
                            <span class="invoice-calc-label">IGST Amount (18%):</span>
                            <span class="invoice-calc-value" id="pvCalcIgst">₹0.00</span>
                        </div>
                        <div class="invoice-calc-row" style="background: rgba(37, 99, 235, 0.04);">
                            <span class="invoice-calc-label text-primary">Total GST Amount:</span>
                            <span class="invoice-calc-value text-primary fw-bold" id="pvCalcTotalGst">₹0.00</span>
                        </div>
                        <div class="invoice-calc-row">
                            <span class="invoice-calc-label">TCS Amount:</span>
                            <span class="invoice-calc-value" id="pvCalcTcs">₹0.00</span>
                        </div>
                        <div class="invoice-calc-row">
                            <span class="invoice-calc-label">Round Off:</span>
                            <span class="invoice-calc-value" id="pvCalcRoundOff">₹0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Grand Total Banner (Screenshot 3 Inspiration) -->
            <div class="invoice-total-banner">
                <div class="invoice-total-banner-left">
                    <h6><i class="fa-solid fa-file-invoice-dollar me-2"></i> Ready for Invoice Generation</h6>
                    <p>Tax invoice will be generated directly from this ERP dispatched batch.</p>
                </div>
                <div class="invoice-total-banner-right">
                    <div class="total-label">Grand Total</div>
                    <div class="total-amount" id="pvGrandTotal">₹0.00</div>
                </div>
            </div>

        </div>

    </div>

    <!-- Drawer Footer Actions -->
    <div class="drawer-footer">
        <button type="button" class="btn-saas-ghost px-4" onclick="closeReviewDrawer()">
            Close
        </button>
        <button type="button" class="btn-saas-primary px-4" id="btnPreviewGenerate">
            <i class="fa-solid fa-file-invoice me-1"></i> Proceed to Generate Invoice
        </button>
    </div>

</div>

<!-- STAGE 4: CONFIRM & GENERATE MODAL -->
<div class="modal fade" id="confirmInvoiceModal" tabindex="-1" aria-labelledby="confirmInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg" style="background-color: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: 12px;">
            <div class="modal-header p-3" style="border-bottom: 1px solid var(--border-color);">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-2" id="confirmInvoiceModalLabel">
                    <i class="fa-solid fa-circle-question text-warning"></i> Confirm & Generate Tax Invoice
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <span class="d-block fs-8 text-muted mb-1">INVOICE NUMBER</span>
                    <strong id="cInvoiceNo" class="text-primary font-monospace fs-5"></strong>
                    <span class="d-block fs-8 text-muted mt-2 mb-1">BUYER / CUSTOMER</span>
                    <strong class="fs-6" style="color: var(--text-primary);" id="cBuyerName"></strong>
                </div>
                <div class="p-3 rounded-3 font-monospace mb-3" style="background-color: var(--main-body-bg); border: 1px solid var(--border-color);">
                    <div class="d-flex justify-content-between py-1 border-bottom" style="border-color: var(--border-color) !important;">
                        <span class="fs-8 text-muted">Taxable Amount:</span>
                        <span class="fw-bold" id="cTaxableAmt">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom" style="border-color: var(--border-color) !important;" id="cCgstRow">
                        <span class="fs-8 text-muted">CGST Total:</span>
                        <span class="fw-bold" id="cCgstAmt">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom" style="border-color: var(--border-color) !important;" id="cSgstRow">
                        <span class="fs-8 text-muted">SGST Total:</span>
                        <span class="fw-bold" id="cSgstAmt">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom d-none" style="border-color: var(--border-color) !important;" id="cIgstRow">
                        <span class="fs-8 text-muted">IGST Total:</span>
                        <span class="fw-bold" id="cIgstAmt">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom" style="border-color: var(--border-color) !important;">
                        <span class="fs-8 text-muted">Freight & Other:</span>
                        <span class="fw-bold" id="cChargesAmt">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between pt-2">
                        <span class="fw-bold text-dark">GRAND TOTAL:</span>
                        <span class="fw-bold text-success fs-5" id="cGrandTotal">₹0.00</span>
                    </div>
                </div>
                <div class="text-center fs-9 text-muted">
                    <i class="fa-solid fa-lock text-primary me-1"></i> Saving will lock this invoice sequential serial in the accounts database.
                </div>
            </div>
            <div class="modal-footer p-3" style="border-top: 1px solid var(--border-color);">
                <button type="button" class="btn-saas-ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-saas-primary px-4" id="btnConfirmGenerate" onclick="submitVoucherForm()">
                    <i class="fa-solid fa-check me-1"></i> Confirm & Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 5. FLOATING INVOICE SHARE POPOVER MENU -->
<div id="sharePopover" class="share-popover d-none">
    <div class="share-popover-header">
        <span class="share-popover-title"><i class="fa-solid fa-share-nodes text-primary me-1"></i> Share Invoice</span>
        <span class="share-popover-badge" id="popoverInvoiceNo">INV-2026</span>
    </div>
    <div class="d-flex flex-column">
        <button type="button" class="share-popover-item" onclick="triggerShareAction('email')">
            <div class="share-popover-icon icon-email">
                <i class="fa-regular fa-envelope"></i>
            </div>
            <div class="share-popover-text">
                <span class="share-popover-label">Email</span>
                <span class="share-popover-sub">Send invoice via email</span>
            </div>
        </button>
        <button type="button" class="share-popover-item" onclick="triggerShareAction('whatsapp')">
            <div class="share-popover-icon icon-whatsapp">
                <i class="fa-brands fa-whatsapp"></i>
            </div>
            <div class="share-popover-text">
                <span class="share-popover-label">WhatsApp</span>
                <span class="share-popover-sub">Send invoice through WhatsApp</span>
            </div>
        </button>
        <button type="button" class="share-popover-item" onclick="triggerShareAction('copy_link')">
            <div class="share-popover-icon icon-link">
                <i class="fa-regular fa-copy"></i>
            </div>
            <div class="share-popover-text">
                <span class="share-popover-label">Copy Link</span>
                <span class="share-popover-sub">Copy invoice link</span>
            </div>
        </button>
        
        <div class="share-popover-divider"></div>
        
        <button type="button" class="share-popover-item" onclick="triggerShareAction('download_pdf')">
            <div class="share-popover-icon icon-pdf">
                <i class="fa-solid fa-file-pdf"></i>
            </div>
            <div class="share-popover-text">
                <span class="share-popover-label">Download PDF</span>
                <span class="share-popover-sub">Save offline copy</span>
            </div>
        </button>
        <button type="button" class="share-popover-item" onclick="triggerShareAction('print')">
            <div class="share-popover-icon icon-print">
                <i class="fa-solid fa-print"></i>
            </div>
            <div class="share-popover-text">
                <span class="share-popover-label">Print Invoice</span>
                <span class="share-popover-sub">Open print dialog</span>
            </div>
        </button>
    </div>
</div>

<!-- 6. ADVANCED SHARE INVOICE MODAL -->
<div class="modal fade" id="shareInvoiceModal" tabindex="-1" aria-labelledby="shareInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 580px;">
        <div class="modal-content shadow-lg" style="background-color: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: 14px; overflow: hidden;">
            
            <!-- Modal Header -->
            <div class="modal-header px-4 py-3" style="border-bottom: 1px solid var(--border-color); background: var(--main-body-bg);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: var(--theme-primary-soft, rgba(37, 99, 235, 0.1)); color: var(--theme-primary, #2563eb);">
                        <i class="fa-solid fa-share-nodes fs-6"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0" id="shareInvoiceModalLabel" style="color: var(--text-primary); font-size: 1.05rem;">Share Invoice</h6>
                        <div class="fs-8 text-muted" id="shareModalSubtitle">INV-2026 • Super Steel</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 share-modal-form">
                
                <!-- Invoice Summary Box -->
                <div class="share-summary-box">
                    <div class="share-summary-item">
                        <span class="share-summary-label">Invoice No</span>
                        <span class="share-summary-val font-monospace text-primary" id="smSummaryInvNo">INV-2026</span>
                    </div>
                    <div class="share-summary-item">
                        <span class="share-summary-label">Customer</span>
                        <span class="share-summary-val text-truncate" id="smSummaryCustomer">Super Steel</span>
                    </div>
                    <div class="share-summary-item">
                        <span class="share-summary-label">Invoice Date</span>
                        <span class="share-summary-val font-monospace" id="smSummaryDate">31 Aug 2026</span>
                    </div>
                    <div class="share-summary-item">
                        <span class="share-summary-label">Taxable Amount</span>
                        <span class="share-summary-val font-monospace text-success" id="smSummaryAmount">₹5,85,000.00</span>
                    </div>
                </div>

                <!-- Share Via Selector Cards -->
                <div class="mb-3">
                    <label class="form-label d-block text-muted fs-8 mb-2">Share via</label>
                    <div class="share-channel-grid">
                        <!-- Email Card -->
                        <div class="share-channel-card channel-email active" id="channelCardEmail" onclick="switchShareChannel('email')">
                            <div class="share-channel-card-icon">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <div>
                                <div class="share-channel-card-title">Email</div>
                                <div class="share-channel-card-sub">Send via Email</div>
                            </div>
                            <div class="share-channel-check">
                                <i class="fa-solid fa-check"></i>
                            </div>
                        </div>

                        <!-- WhatsApp Card -->
                        <div class="share-channel-card channel-whatsapp" id="channelCardWhatsapp" onclick="switchShareChannel('whatsapp')">
                            <div class="share-channel-card-icon">
                                <i class="fa-brands fa-whatsapp"></i>
                            </div>
                            <div>
                                <div class="share-channel-card-title">WhatsApp</div>
                                <div class="share-channel-card-sub">Send via WhatsApp</div>
                            </div>
                            <div class="share-channel-check">
                                <i class="fa-solid fa-check"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EMAIL FORM SECTION -->
                <div id="sectionShareEmail">
                    <form id="formShareEmail" onsubmit="event.preventDefault(); sendInvoiceEmail();">
                        <input type="hidden" id="emailInvoiceId" name="invoice_id">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-7">
                                <label class="form-label">To <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-transparent border-end-0 text-muted" style="border-color: var(--border-color);"><i class="fa-regular fa-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0" id="emailTo" name="to" placeholder="customer@email.com" required>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">CC <span class="text-muted fw-normal fs-9">(Optional)</span></label>
                                <input type="text" class="form-control" id="emailCc" name="cc" placeholder="accounts@company.com">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control fw-semibold" id="emailSubject" name="subject" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea class="form-control font-monospace" id="emailMessage" name="message" rows="4" style="resize: vertical;"></textarea>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="border-color: var(--border-color) !important;">
                            <label class="share-attach-pill mb-0">
                                <input type="checkbox" id="emailAttachPdf" class="form-check-input mt-0" checked>
                                <span><i class="fa-solid fa-paperclip text-muted me-1"></i> Attach Invoice PDF</span>
                            </label>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn-saas-ghost px-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn-saas-primary px-4" id="btnSubmitEmail">
                                    <i class="fa-regular fa-paper-plane me-1"></i> <span>Send Email</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- WHATSAPP FORM SECTION -->
                <div id="sectionShareWhatsapp" class="d-none">
                    <form id="formShareWhatsapp" onsubmit="event.preventDefault(); sendInvoiceWhatsapp();">
                        <input type="hidden" id="waInvoiceId" name="invoice_id">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Customer</label>
                                <input type="text" class="form-control fw-bold" id="waCustomerName" readonly style="opacity: 0.85; background: var(--main-body-bg);">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-transparent border-end-0 text-muted" style="border-color: var(--border-color);"><i class="fa-solid fa-phone"></i></span>
                                    <input type="tel" class="form-control border-start-0 ps-0 font-monospace fw-bold" id="waMobile" name="mobile" placeholder="+91 9876543210" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea class="form-control font-monospace" id="waMessage" name="message" rows="5" style="resize: vertical;"></textarea>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="border-color: var(--border-color) !important;">
                            <label class="share-attach-pill mb-0">
                                <input type="checkbox" id="waAttachPdf" class="form-check-input mt-0" checked>
                                <span><i class="fa-solid fa-paperclip text-muted me-1"></i> Attach Invoice PDF</span>
                            </label>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn-saas-ghost px-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn-whatsapp px-4" id="btnSubmitWhatsapp">
                                    <i class="fa-brands fa-whatsapp me-1"></i> <span>Send WhatsApp</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- 7. TOAST NOTIFICATION CONTAINER -->
<div id="enterpriseToastContainer" class="enterprise-toast-container"></div>
@endsection

@push('scripts')
<script>
    // State Manager
    let pendingDispatches = [];
    let selectedDispatchDetails = null;
    let supplierStateCode = "03"; // Punjab (default state code)
    let andPrintAfterSave = false;
    let debounceTimer = null;

    // Load initial queue
    document.addEventListener('DOMContentLoaded', function () {
        refreshQueue();
    });

    function debounceFilter() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            applyFilters();
        }, 300);
    }

    // Refresh Queue from ERP
    function refreshQueue() {
        const syncIcon = document.getElementById('syncIcon');
        const badge = document.getElementById('erpStatusBadge');
        if (syncIcon) syncIcon.classList.add('fa-spin');

        const params = new URLSearchParams({
            status_filter: document.getElementById('filterStatus').value
        });
        
        const dateFrom = document.getElementById('filterDateFrom').value;
        const dateTo = document.getElementById('filterDateTo').value;
        const search = document.getElementById('filterSearch').value;

        if (dateFrom) params.append('date1', dateFrom);
        if (dateTo) params.append('dateTo', dateTo);
        if (search) params.append('partyname', search);

        fetch(`{{ route('invoices.pending-vehicles') }}?${params.toString()}`)
            .then(res => res.json())
            .then(res => {
                if (syncIcon) syncIcon.classList.remove('fa-spin');
                if (badge) {
                    badge.innerHTML = '<span class="pulse-dot"></span><span>ERP Sync: Active</span>';
                }
                
                if (res.status === 'success' && res.data) {
                    pendingDispatches = res.data;
                    renderQueueTable(pendingDispatches);
                    updateKpiMetrics(pendingDispatches);
                } else {
                    document.getElementById('dispatchQueueTableBody').innerHTML = `
                        <tr>
                            <td colspan="8" class="empty-state-box">
                                <i class="fa-solid fa-inbox empty-state-icon"></i>
                                <div>No dispatches found matching the current filters.</div>
                            </td>
                        </tr>`;
                    updateKpiMetrics([]);
                }
            })
            .catch(err => {
                if (syncIcon) syncIcon.classList.remove('fa-spin');
                if (badge) {
                    badge.innerHTML = '<span class="pulse-dot" style="background:#ef4444;"></span><span>ERP Offline: Fallback</span>';
                }
                console.error("Queue Sync Error:", err);
                fetchDirectFallback(params);
            });
    }

    // Direct DB Fetch proxy fallback
    function fetchDirectFallback(params) {
        fetch(`{{ route('invoices.pending-vehicles') }}?${params.toString()}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data) {
                    pendingDispatches = res.data;
                    renderQueueTable(pendingDispatches);
                    updateKpiMetrics(pendingDispatches);
                } else {
                    document.getElementById('dispatchQueueTableBody').innerHTML = `
                        <tr>
                            <td colspan="8" class="empty-state-box">
                                <i class="fa-solid fa-circle-exclamation empty-state-icon text-warning"></i>
                                <div>No dispatches found!</div>
                            </td>
                        </tr>`;
                    updateKpiMetrics([]);
                }
            })
            .catch(err => {
                document.getElementById('dispatchQueueTableBody').innerHTML = `
                    <tr>
                        <td colspan="8" class="empty-state-box">
                            <i class="fa-solid fa-triangle-exclamation empty-state-icon text-danger"></i>
                            <div>Connection Error: ${err.message}</div>
                        </td>
                    </tr>`;
                updateKpiMetrics([]);
            });
    }

    // Update KPI Summary Strip
    function updateKpiMetrics(data) {
        let totalCount = data.length;
        let pendingCount = 0;
        let pendingAmount = 0;
        let invoicedCount = 0;
        let invoicedAmount = 0;
        let totalTonnage = 0;
        let totalAmount = 0;

        data.forEach(d => {
            const weight = parseFloat(d.total_qty || 0);
            const amount = parseFloat(d.total_amount || 0);
            totalTonnage += weight;
            totalAmount += amount;

            if (d.invoice_no) {
                invoicedCount++;
                invoicedAmount += amount;
            } else {
                pendingCount++;
                pendingAmount += amount;
            }
        });

        const fmt = (n) => '₹' + n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        document.getElementById('kpiTotalDispatches').innerText = totalCount;
        document.getElementById('kpiPendingCount').innerText = pendingCount;
        document.getElementById('kpiPendingAmount').innerText = fmt(pendingAmount) + ' unbilled';
        document.getElementById('kpiInvoicedCount').innerText = invoicedCount;
        document.getElementById('kpiInvoicedAmount').innerText = fmt(invoicedAmount) + ' billed';
        document.getElementById('kpiTotalTonnage').innerHTML = `${totalTonnage.toFixed(3)} <small class="fs-7 fw-normal text-muted">Tons</small>`;
        document.getElementById('kpiTotalAmount').innerText = fmt(totalAmount) + ' total value';

        document.getElementById('tableShowingCount').innerText = totalCount;
        document.getElementById('tableTotalCount').innerText = totalCount;
    }

    // Apply & Clear Filters
    function applyFilters() {
        refreshQueue();
    }
    
    function clearFilters() {
        document.getElementById('filterSearch').value = '';
        document.getElementById('filterDateFrom').value = '';
        document.getElementById('filterDateTo').value = '';
        document.getElementById('filterStatus').value = 'pending';
        refreshQueue();
    }

    // Render Table Body
    function renderQueueTable(data) {
        const body = document.getElementById('dispatchQueueTableBody');
        if (!data || data.length === 0) {
            body.innerHTML = `
                <tr>
                    <td colspan="8" class="empty-state-box">
                        <i class="fa-solid fa-circle-check empty-state-icon text-success"></i>
                        <div class="fw-semibold">All dispatches have been invoiced!</div>
                        <div class="fs-8 text-muted">No pending items found in queue.</div>
                    </td>
                </tr>`;
            return;
        }

        let html = '';
        data.forEach(d => {
            const hasInvoice = !!d.invoice_no;
            const refNo = d.dispatch_id ? `DSP-${d.dispatch_id}` : `TKN-${d.tokenid || d.vehicle_id}`;
            const dateStr = d.createdon ? d.createdon.substring(0, 10) : 'N/A';
            const qtyTons = parseFloat(d.total_qty || 0).toFixed(3);
            const amountVal = parseFloat(d.total_amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const gstinText = d.gst ? d.gst.toUpperCase() : null;

            let statusBadgeHtml = '';
            let actionsHtml = '';

            if (hasInvoice) {
                statusBadgeHtml = `
                    <div class="d-flex flex-column align-items-center">
                        <span class="status-badge-saas status-badge-invoiced">
                            <span class="status-dot"></span>
                            <span>Invoiced</span>
                        </span>
                        <span class="status-inv-subtag">${d.invoice_no}</span>
                    </div>`;
                
                const safeParty = (d.partyname || 'Customer').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                const safeEmail = (d.email || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                const safeMobile = (d.mobile || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                const safeVehicle = (d.vehicleno || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');

                actionsHtml = `
                    <div class="action-buttons-wrap">
                        <a href="/invoices/print/${d.invoice_id}" target="_blank" class="btn-saas-outline-info" title="View & Print Tax Invoice">
                            <i class="fa-solid fa-file-invoice"></i>
                            <span>View Invoice</span>
                        </a>
                        <button type="button" class="btn-saas-outline-share" onclick="openSharePopover(event, '${d.invoice_id}', '${d.invoice_no}', '${safeParty}', '${dateStr}', '${amountVal}', '${safeMobile}', '${safeEmail}', '${safeVehicle}')" title="Share Invoice via Email, WhatsApp, Link or PDF">
                            <i class="fa-solid fa-share-nodes"></i>
                            <span>Share</span>
                        </button>
                    </div>`;
            } else {
                statusBadgeHtml = `
                    <span class="status-badge-saas status-badge-pending">
                        <span class="status-dot"></span>
                        <span>Pending Invoicing</span>
                    </span>`;
                actionsHtml = `
                    <div class="action-buttons-wrap">
                        <button type="button" class="btn-saas-ghost" onclick="openReviewDrawer(${d.vehicle_id}, ${d.dispatch_id || 'null'}, ${d.cust_id})" title="Review details">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <span>Review</span>
                        </button>
                        <button type="button" class="btn-saas-primary" onclick="openGenerateInvoice(${d.vehicle_id}, ${d.dispatch_id || 'null'}, ${d.cust_id})" title="Generate Tax Invoice">
                            <i class="fa-solid fa-file-invoice"></i>
                            <span>Generate Invoice</span>
                        </button>
                    </div>`;
            }

            html += `
                <tr>
                    <td><span class="dispatch-ref-tag">${refNo}</span></td>
                    <td class="font-monospace text-muted fs-8">${dateStr}</td>
                    <td>
                        <div class="customer-cell">
                            <span class="customer-name">${d.partyname || 'Consignee'}</span>
                            <span class="customer-gstin-tag ${gstinText ? 'has-gstin' : 'no-gstin'}">
                                ${gstinText ? `<i class="fa-solid fa-id-card me-1"></i>${gstinText}` : '<i class="fa-regular fa-circle-xmark me-1"></i>Unregistered / No GST'}
                            </span>
                        </div>
                    </td>
                    <td class="col-vehicle-highlight">
                        <span class="vehicle-plate-badge">
                            <i class="fa-solid fa-truck"></i>
                            <span>${d.vehicleno || 'UNASSIGNED'}</span>
                        </span>
                    </td>
                    <td class="text-end num-weight">${qtyTons} <span class="fs-9 text-muted font-sans fw-normal">T</span></td>
                    <td class="text-end num-amount">₹${amountVal}</td>
                    <td class="text-center">${statusBadgeHtml}</td>
                    <td class="text-end">${actionsHtml}</td>
                </tr>`;
        });
        body.innerHTML = html;
    }

    // Toggle sections
    function showSection(sectionId) {
        document.getElementById('section-queue').classList.add('d-none');
        document.getElementById('section-voucher').classList.add('d-none');
        document.getElementById('section-success').classList.add('d-none');

        document.getElementById(`section-${sectionId}`).classList.remove('d-none');
        
        if (sectionId === 'queue') {
            refreshQueue();
        }
    }

    // Stage 2: Open Invoice Preview Modal
    function openReviewDrawer(vehicleId, dispatchId, custId) {
        const body = document.getElementById('pvItemsTableBody');
        body.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Syncing ERP Dispatch Items...</td></tr>`;

        // Pre-fill header metadata placeholders
        document.getElementById('pvDispatchRef').innerText = 'Syncing...';
        document.getElementById('pvCustomerName').innerText = 'Loading customer...';
        document.getElementById('pvCustomerGst').innerText = '-';
        document.getElementById('pvCustomerAddress').innerText = '-';
        document.getElementById('pvCustomerCityState').innerText = '-';
        document.getElementById('pvCustomerContact').innerText = '-';
        document.getElementById('pvDispatchNo').innerText = '-';
        document.getElementById('pvDispatchDate').innerText = '-';
        document.getElementById('pvVehicleNo').innerHTML = '-';
        document.getElementById('pvTransportName').innerText = '-';
        document.getElementById('pvPlaceOfSupply').innerText = 'Punjab (03)';

        document.getElementById('drawerOverlay').style.display = 'block';
        document.getElementById('reviewDrawer').classList.add('open');

        // Fetch details
        let url = `{{ route('invoices.dispatch-details') }}?vehicle_id=${vehicleId}`;
        if (dispatchId) url += `&dispatch_id=${dispatchId}`;
        if (custId) url += `&cust_id=${custId}`;

        const fmt = (n) => '₹' + parseFloat(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        fetch(url)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data) {
                    selectedDispatchDetails = res.data;
                    const d = res.data;

                    const ref = d.dispatch && d.dispatch.dispatchid ? `DSP-${d.dispatch.dispatchid}` : (d.vehicle ? `TKN-${d.vehicle.tokenid}` : `REF-${vehicleId}`);
                    document.getElementById('pvDispatchRef').innerText = ref;
                    document.getElementById('pvDispatchNo').innerText = ref;

                    // Customer details
                    document.getElementById('pvCustomerName').innerText = d.customer ? (d.customer.name || 'Consignee') : 'Consignee';
                    document.getElementById('pvCustomerGst').innerText = d.customer && d.customer.gst ? d.customer.gst.toUpperCase() : 'Unregistered / No GST';
                    document.getElementById('pvCustomerAddress').innerText = d.customer ? (d.customer.address || 'Address on file') : 'Address on file';
                    
                    const cityState = [d.customer?.city, d.customer?.state, d.customer?.pincode ? `PIN: ${d.customer.pincode}` : null].filter(Boolean).join(', ');
                    document.getElementById('pvCustomerCityState').innerText = cityState || 'State on file';
                    
                    const contact = [d.customer?.mobile ? `Mob: ${d.customer.mobile}` : null, d.customer?.email ? `Email: ${d.customer.email}` : null].filter(Boolean).join(' | ');
                    document.getElementById('pvCustomerContact').innerText = contact || '-';

                    // Dispatch details
                    let dispDate = 'N/A';
                    if (d.dispatch && d.dispatch.createdon) {
                        dispDate = String(d.dispatch.createdon).substring(0, 10);
                    } else if (d.vehicle && d.vehicle.createdon) {
                        dispDate = String(d.vehicle.createdon).substring(0, 10);
                    }
                    document.getElementById('pvDispatchDate').innerText = dispDate;

                    const vNo = d.vehicle ? (d.vehicle.vehicleno || 'UNASSIGNED') : 'UNASSIGNED';
                    document.getElementById('pvVehicleNo').innerHTML = `<span class="vehicle-plate-badge"><i class="fa-solid fa-truck"></i> ${vNo}</span>`;
                    document.getElementById('pvTransportName').innerText = d.vehicle ? (d.vehicle.transport || 'Direct Factory Dispatch') : 'Direct Factory Dispatch';

                    // Place of supply
                    const buyerGst = (d.customer && d.customer.gst) ? d.customer.gst.trim() : '';
                    const isInterstate = (buyerGst.length >= 2 && buyerGst.substring(0, 2) !== supplierStateCode);
                    const stateName = d.customer?.state || (isInterstate ? 'Inter-State' : 'Punjab (03)');
                    document.getElementById('pvPlaceOfSupply').innerText = stateName;

                    // Items table
                    let html = '';
                    let totalWeight = 0;
                    let subtotal = 0;
                    let totalItemsAmount = 0;

                    if (d.items && d.items.length > 0) {
                        d.items.forEach((it, idx) => {
                            const weight = parseFloat(it.actual_weight_tons || it.planned_weight_tons || 0);
                            const rate = parseFloat(it.rate || 0);
                            const taxable = weight * rate;
                            const gstPct = parseFloat(it.gst_rate || 18);
                            const itemGst = taxable * (gstPct / 100);
                            const itemTotal = taxable + itemGst;

                            totalWeight += weight;
                            subtotal += taxable;
                            totalItemsAmount += itemTotal;

                            html += `
                                <tr>
                                    <td class="font-monospace text-muted">${idx + 1}</td>
                                    <td class="text-start fw-semibold" style="color: var(--text-primary);">${it.product_name || 'Goods Material'}</td>
                                    <td>${it.grade_name || '-'}</td>
                                    <td>${it.size_name || '-'}</td>
                                    <td class="font-monospace">${it.hsn || '7214'}</td>
                                    <td class="fw-bold text-end font-monospace">${weight.toFixed(3)}</td>
                                    <td class="text-end font-monospace">${fmt(rate)}</td>
                                    <td class="fw-bold text-end font-monospace" style="color: var(--text-primary);">${fmt(taxable)}</td>
                                    <td class="text-center font-monospace"><span class="badge bg-light text-dark border px-2">${gstPct}%</span></td>
                                    <td class="fw-bold text-end font-monospace text-primary">${fmt(itemTotal)}</td>
                                </tr>`;
                        });
                    } else {
                        html = `<tr><td colspan="10" class="text-center py-3 text-muted">No items recorded in ERP dispatch!</td></tr>`;
                    }
                    body.innerHTML = html;

                    // Footer totals
                    document.getElementById('pvFootTotalWeight').innerText = totalWeight.toFixed(3) + ' Tons';
                    document.getElementById('pvFootTotalTaxable').innerText = fmt(subtotal);
                    document.getElementById('pvFootTotalAmount').innerText = fmt(totalItemsAmount);

                    // Accounting Calculations (Screenshot 2 Breakdown)
                    const cashdiscount = d.dispatch ? parseFloat(d.dispatch.cashdiscount || 0) : 0;
                    const labor = d.dispatch ? parseFloat(d.dispatch.laborchr || 0) : 0;
                    const other = d.dispatch ? parseFloat(d.dispatch.otherchr || 0) : 0;
                    const afterCharges = Math.max(0, subtotal - cashdiscount) + labor + other;

                    let cgst = 0, sgst = 0, igst = 0;
                    if (isInterstate) {
                        igst = afterCharges * 0.18;
                        cgst = 0;
                        sgst = 0;
                    } else {
                        cgst = afterCharges * 0.09;
                        sgst = afterCharges * 0.09;
                        igst = 0;
                    }
                    const totalGst = cgst + sgst + igst;

                    const tcs = d.dispatch ? parseFloat(d.dispatch.tcs || 0) : 0;
                    const rawGrandTotal = afterCharges + totalGst + tcs;
                    const grandTotal = Math.round(rawGrandTotal);
                    const roundOff = grandTotal - rawGrandTotal;

                    // Populate breakdown elements
                    document.getElementById('pvCalcSubtotal').innerText = fmt(subtotal);
                    document.getElementById('pvCalcDiscount').innerText = fmt(cashdiscount);
                    document.getElementById('pvCalcLabor').innerText = fmt(labor);
                    document.getElementById('pvCalcOther').innerText = fmt(other);
                    document.getElementById('pvCalcAfterCharges').innerText = fmt(afterCharges);
                    document.getElementById('pvCalcCgst').innerText = fmt(cgst);
                    document.getElementById('pvCalcSgst').innerText = fmt(sgst);
                    document.getElementById('pvCalcIgst').innerText = fmt(igst);
                    document.getElementById('pvCalcTotalGst').innerText = fmt(totalGst);
                    document.getElementById('pvCalcTcs').innerText = fmt(tcs);
                    document.getElementById('pvCalcRoundOff').innerText = (roundOff >= 0 ? '+' : '') + roundOff.toFixed(2);
                    document.getElementById('pvGrandTotal').innerText = fmt(grandTotal);

                    // Configure proceed CTA
                    document.getElementById('btnPreviewGenerate').onclick = function() {
                        closeReviewDrawer();
                        openGenerateInvoice(vehicleId, dispatchId, custId);
                    };
                } else {
                    body.innerHTML = `<tr><td colspan="8" class="text-center py-3 text-danger">Error: ${res.message}</td></tr>`;
                }
            })
            .catch(err => {
                body.innerHTML = `<tr><td colspan="8" class="text-center py-3 text-danger">Connection Error: ${err.message}</td></tr>`;
            });
    }

    function closeReviewDrawer() {
        document.getElementById('drawerOverlay').style.display = 'none';
        document.getElementById('reviewDrawer').classList.remove('open');
    }

    // Stage 3: Open Generate Invoice (Populate form)
    function openGenerateInvoice(vehicleId, dispatchId, custId) {
        document.getElementById('voucherForm').reset();
        document.getElementById('voucherItemsBody').innerHTML = '';

        let url = `{{ route('invoices.dispatch-details') }}?vehicle_id=${vehicleId}`;
        if (dispatchId) url += `&dispatch_id=${dispatchId}`;
        if (custId) url += `&cust_id=${custId}`;

        showSection('voucher');
        document.getElementById('voucherItemsBody').innerHTML = `
            <tr>
                <td colspan="15" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    Retrieving ERP Dispatch Items & pricing data...
                </td>
            </tr>`;

        fetch(url)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data) {
                    const d = res.data;
                    selectedDispatchDetails = d;

                    document.getElementById('v_vehicle_id').value = vehicleId || '';
                    document.getElementById('v_dispatch_id').value = dispatchId || '';
                    document.getElementById('v_customer_id').value = custId || '';
                    
                    // Auto-generate invoice number
                    fetchNextInvoiceNo(document.getElementById('v_invoice_date').value);
                    
                    const dNo = d.dispatch && d.dispatch.dispatchid ? `DSP-${d.dispatch.dispatchid}` : (d.vehicle ? `TKN-${d.vehicle.tokenid}` : '');
                    const vNo = d.vehicle ? d.vehicle.vehicleno : '';
                    document.getElementById('v_dispatch_no').value = dNo;
                    document.getElementById('v_vehicle_no').value = vNo;

                    document.getElementById('v_customer_name').value = d.customer ? d.customer.name : '';
                    document.getElementById('v_customer_gst').value = d.customer ? d.customer.gst : '';
                    document.getElementById('v_customer_address').value = d.customer ? `${d.customer.address || ''}, ${d.customer.city || ''}, ${d.customer.state || ''}`.trim(', ') : '';

                    document.getElementById('v_transport_name').value = d.vehicle ? (d.vehicle.transport || '') : '';
                    document.getElementById('v_other_charges').value = d.dispatch ? (d.dispatch.otherchr || 0.00).toFixed(2) : '0.00';

                    const buyerGst = d.customer ? d.customer.gst : '';
                    handleGstinChange(buyerGst);

                    renderVoucherItems(d.items);
                } else {
                    alert("Failed to load dispatch details: " + res.message);
                    showSection('queue');
                }
            })
            .catch(err => {
                alert("Connection error loading dispatch details: " + err.message);
                showSection('queue');
            });
    }

    // Render Items inside Invoice Voucher Table
    function renderVoucherItems(items) {
        const body = document.getElementById('voucherItemsBody');
        body.innerHTML = '';

        if (!items || items.length === 0) {
            body.innerHTML = `
                <tr>
                    <td colspan="15" class="text-center py-3 text-warning">
                        No goods items found in ERP. Click "+ Add Line Item" to add items manually.
                    </td>
                </tr>`;
            return;
        }

        items.forEach((it, idx) => {
            addVoucherRow(it);
        });
        recalculateVoucherSummary();
    }

    // Add row to Voucher table
    function addVoucherRow(item = null) {
        const body = document.getElementById('voucherItemsBody');
        const rowsCount = body.querySelectorAll('tr').length;
        
        if (rowsCount === 1 && body.children[0].cells.length === 1) {
            body.innerHTML = '';
        }

        const idx = body.querySelectorAll('tr').length;

        const product = item ? (item.product_name || '') : 'BRIGHT BAR';
        const grade = item ? (item.grade_name || '') : '';
        const size = item ? (item.size_name || '') : '';
        const hsn = item ? (item.hsn || '7214') : '7214';
        const qty = item ? parseFloat(item.actual_weight_tons || item.planned_weight_tons || 1.000) : 1.000;
        const rate = item ? parseFloat(item.rate || 0.00) : 0.00;
        const unit = item ? (item.unit || 'Tons') : 'Tons';
        const pcs = item ? parseInt(item.actual_pcs || item.planned_pcs || 1) : 1;
        const gstRate = item ? parseFloat(item.gst_rate || 18) : 18;
        const dispItemId = item ? item.disp_item_id : '';
        const slid = item ? item.slid : '';

        const tr = document.createElement('tr');
        tr.className = "voucher-row";
        tr.innerHTML = `
            <td class="font-monospace text-muted text-center">${idx + 1}</td>
            <td>
                <input type="text" class="form-control voucher-input" name="items[${idx}][product_name]" value="${product}" required>
                <input type="hidden" name="items[${idx}][disp_item_id]" value="${dispItemId || ''}">
                <input type="hidden" name="items[${idx}][slid]" value="${slid || ''}">
            </td>
            <td>
                <input type="text" class="form-control voucher-input text-center" name="items[${idx}][grade_name]" value="${grade}">
            </td>
            <td>
                <input type="text" class="form-control voucher-input text-center" name="items[${idx}][size_name]" value="${size}">
            </td>
            <td>
                <input type="text" class="form-control voucher-input text-center font-monospace" name="items[${idx}][hsn]" value="${hsn}">
            </td>
            <td>
                <input type="number" step="0.001" class="form-control voucher-input text-end font-monospace item-weight" name="items[${idx}][weight_tons]" value="${qty.toFixed(3)}" onchange="rowCalc(this)">
                <input type="hidden" name="items[${idx}][pcs]" value="${pcs}">
            </td>
            <td>
                <input type="text" class="form-control voucher-input text-center text-muted" value="${unit}" readonly tabindex="-1" style="background: var(--main-body-bg);">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control voucher-input text-end font-monospace item-rate" name="items[${idx}][rate]" value="${rate.toFixed(2)}" onchange="rowCalc(this)">
            </td>
            <td class="text-end font-monospace fw-bold" style="color: var(--text-primary);"><span class="item-taxable">₹0.00</span></td>
            <td>
                <input type="number" step="0.5" class="form-control voucher-input text-center font-monospace item-gst-rate" name="items[${idx}][gst_rate]" value="${gstRate}" onchange="rowCalc(this)">
            </td>
            <td class="text-end font-monospace" style="color: var(--text-primary); font-size: 0.9rem;"><span class="item-cgst">₹0.00</span></td>
            <td class="text-end font-monospace" style="color: var(--text-primary); font-size: 0.9rem;"><span class="item-sgst">₹0.00</span></td>
            <td class="text-end font-monospace" style="color: var(--text-primary); font-size: 0.9rem;"><span class="item-igst">₹0.00</span></td>
            <td class="text-end font-monospace fw-bold text-primary" style="font-size: 0.95rem;">
                <span class="item-total">₹0.00</span>
                <input type="hidden" name="items[${idx}][amount]" class="item-amount-val">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeVoucherRow(this)" title="Delete Row">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>`;
        
        body.appendChild(tr);
        rowCalc(tr.querySelector('.item-weight'));
    }

    // Remove row from Voucher table
    function removeVoucherRow(btn) {
        const row = btn.closest('tr');
        row.remove();
        
        const body = document.getElementById('voucherItemsBody');
        const rows = body.querySelectorAll('tr');
        if (rows.length === 0) {
            body.innerHTML = `<tr><td colspan="15" class="text-center py-3 text-warning">No items. Click "+ Add Line Item" to add manual rows.</td></tr>`;
        } else {
            rows.forEach((r, idx) => {
                r.querySelector('td').innerText = idx + 1;
            });
        }
        recalculateVoucherSummary();
    }

    // Handle Buyer GSTIN state code toggle
    function handleGstinChange(gstin) {
        if (gstin && gstin.trim().length >= 2) {
            const state = gstin.trim().substring(0, 2);
            if (state === supplierStateCode) {
                document.getElementById('cCgstRow').classList.remove('d-none');
                document.getElementById('cSgstRow').classList.remove('d-none');
                document.getElementById('cIgstRow').classList.add('d-none');
            } else {
                document.getElementById('cCgstRow').classList.add('d-none');
                document.getElementById('cSgstRow').classList.add('d-none');
                document.getElementById('cIgstRow').classList.remove('d-none');
            }
        } else {
            document.getElementById('cCgstRow').classList.remove('d-none');
            document.getElementById('cSgstRow').classList.remove('d-none');
            document.getElementById('cIgstRow').classList.add('d-none');
        }
        
        const rows = document.querySelectorAll('.voucher-row');
        rows.forEach(r => {
            rowCalc(r.querySelector('.item-weight'));
        });
    }

    // Individual Row Math
    function rowCalc(input) {
        const row = input.closest('tr');
        const qty = parseFloat(row.querySelector('.item-weight').value) || 0;
        const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
        const gstPercent = parseFloat(row.querySelector('.item-gst-rate').value) || 0;
        
        const taxable = qty * rate;
        row.querySelector('.item-taxable').innerText = '₹' + taxable.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const gstin = document.getElementById('v_customer_gst').value || '';
        const state = gstin.trim().substring(0, 2);
        
        let cgst = 0;
        let sgst = 0;
        let igst = 0;

        if (gstin.trim().length >= 2 && state !== supplierStateCode) {
            igst = (taxable * gstPercent) / 100;
            row.querySelector('.item-cgst').innerText = '₹0.00';
            row.querySelector('.item-sgst').innerText = '₹0.00';
            row.querySelector('.item-igst').innerText = '₹' + igst.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } else {
            cgst = (taxable * (gstPercent / 2)) / 100;
            sgst = cgst;
            row.querySelector('.item-cgst').innerText = '₹' + cgst.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            row.querySelector('.item-sgst').innerText = '₹' + sgst.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            row.querySelector('.item-igst').innerText = '₹0.00';
        }

        const rowTotal = taxable + cgst + sgst + igst;
        row.querySelector('.item-total').innerText = '₹' + rowTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        row.querySelector('.item-amount-val').value = taxable.toFixed(2);

        recalculateVoucherSummary();
    }

    // Recalculate voucher totals
    function recalculateVoucherSummary() {
        let subtotal = 0;
        let cgstTotal = 0;
        let sgstTotal = 0;
        let igstTotal = 0;

        const rows = document.querySelectorAll('.voucher-row');
        const gstin = document.getElementById('v_customer_gst').value || '';
        const state = gstin.trim().substring(0, 2);
        const isInterstate = (gstin.trim().length >= 2 && state !== supplierStateCode);

        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.item-weight').value) || 0;
            const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
            const gstPercent = parseFloat(row.querySelector('.item-gst-rate').value) || 0;
            const taxable = qty * rate;

            subtotal += taxable;
            if (isInterstate) {
                igstTotal += (taxable * gstPercent) / 100;
            } else {
                const cgst = (taxable * (gstPercent / 2)) / 100;
                cgstTotal += cgst;
                sgstTotal += cgst;
            }
        });

        const discount = parseFloat(document.getElementById('v_discount').value) || 0;
        const taxableAmount = Math.max(0, subtotal - discount);

        if (discount > 0 && subtotal > 0) {
            const ratio = taxableAmount / subtotal;
            cgstTotal = cgstTotal * ratio;
            sgstTotal = sgstTotal * ratio;
            igstTotal = igstTotal * ratio;
        }

        const freight = parseFloat(document.getElementById('v_freight').value) || 0;
        const other = parseFloat(document.getElementById('v_other_charges').value) || 0;

        const rawGrandTotal = taxableAmount + cgstTotal + sgstTotal + igstTotal + freight + other;
        const roundedGrandTotal = Math.round(rawGrandTotal);
        const roundOff = roundedGrandTotal - rawGrandTotal;

        document.getElementById('lblSubTotal').innerText = '₹' + subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('lblTaxableAmount').innerText = '₹' + taxableAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('valTaxableAmount').value = taxableAmount.toFixed(2);

        document.getElementById('lblCgstAmount').innerText = '₹' + cgstTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('valCgstAmount').value = cgstTotal.toFixed(2);
        
        document.getElementById('lblSgstAmount').innerText = '₹' + sgstTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('valSgstAmount').value = sgstTotal.toFixed(2);
        
        document.getElementById('lblIgstAmount').innerText = '₹' + igstTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('valIgstAmount').value = igstTotal.toFixed(2);

        document.getElementById('lblRoundOff').innerText = '₹' + roundOff.toFixed(2);
        document.getElementById('lblGrandTotal').innerText = '₹' + roundedGrandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('valGrandTotal').value = roundedGrandTotal.toFixed(2);
    }

    // Auto-fetch Next Sequential Invoice Number
    function fetchNextInvoiceNo(targetDate = null) {
        const d = targetDate || document.getElementById('v_invoice_date').value || '';
        const refreshIcon = document.getElementById('invRefreshIcon');
        if (refreshIcon) refreshIcon.classList.add('fa-spin');

        fetch(`{{ route('invoices.next-number') }}?date=${d}`)
            .then(res => res.json())
            .then(res => {
                if (refreshIcon) refreshIcon.classList.remove('fa-spin');
                if (res.status === 'success' && res.invoice_no) {
                    document.getElementById('v_invoice_no').value = res.invoice_no;
                    const prefixEl = document.getElementById('invPrefixHint');
                    if (prefixEl) prefixEl.innerText = res.prefix || 'INV-{{ date('Y') }}';
                }
            })
            .catch(err => {
                if (refreshIcon) refreshIcon.classList.remove('fa-spin');
                const yr = new Date().getFullYear();
                document.getElementById('v_invoice_no').value = `INV-${yr}-0001`;
            });
    }

    // Copy Invoice No to clipboard with checkmark feedback
    function copyInvoiceNo() {
        const invNo = document.getElementById('v_invoice_no').value;
        if (!invNo) return;
        navigator.clipboard.writeText(invNo).then(() => {
            const icon = document.getElementById('invCopyIcon');
            const check = document.getElementById('invStatusCheck');
            if (icon) icon.className = "fa-solid fa-check text-success";
            if (check) check.classList.add('text-primary');
            setTimeout(() => {
                if (icon) icon.className = "fa-regular fa-copy";
                if (check) check.classList.remove('text-primary');
            }, 1800);
        }).catch(err => {
            // Fallback for older browsers
            const input = document.getElementById('v_invoice_no');
            input.select();
            document.execCommand('copy');
        });
    }

    // Trigger Confirm Modal
    function triggerConfirmModal(andPrint = false) {
        andPrintAfterSave = andPrint;

        const invoiceNo = document.getElementById('v_invoice_no').value;
        if (!invoiceNo || invoiceNo.trim() === '') {
            alert("Please enter the Invoice Number!");
            document.getElementById('v_invoice_no').focus();
            return;
        }

        const buyerName = document.getElementById('v_customer_name').value;
        if (!buyerName || buyerName.trim() === '') {
            alert("Customer Name is required to generate invoice!");
            document.getElementById('v_customer_name').focus();
            return;
        }

        const rows = document.querySelectorAll('.voucher-row');
        if (rows.length === 0) {
            alert("Please add at least one line item before generating the invoice!");
            return;
        }

        document.getElementById('cInvoiceNo').innerText = invoiceNo.trim();
        document.getElementById('cBuyerName').innerText = buyerName;
        document.getElementById('cTaxableAmt').innerText = document.getElementById('lblTaxableAmount').innerText;
        document.getElementById('cGrandTotal').innerText = document.getElementById('lblGrandTotal').innerText;

        const gstin = document.getElementById('v_customer_gst').value || '';
        const state = gstin.trim().substring(0, 2);
        const isInterstate = (gstin.trim().length >= 2 && state !== supplierStateCode);

        const freight = parseFloat(document.getElementById('v_freight').value) || 0;
        const other = parseFloat(document.getElementById('v_other_charges').value) || 0;
        document.getElementById('cChargesAmt').innerText = '₹' + (freight + other).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (isInterstate) {
            document.getElementById('cIgstRow').classList.remove('d-none');
            document.getElementById('cCgstRow').classList.add('d-none');
            document.getElementById('cSgstRow').classList.add('d-none');
            document.getElementById('cIgstAmt').innerText = document.getElementById('lblIgstAmount').innerText;
        } else {
            document.getElementById('cIgstRow').classList.add('d-none');
            document.getElementById('cCgstRow').classList.remove('d-none');
            document.getElementById('cSgstRow').classList.remove('d-none');
            document.getElementById('cCgstAmt').innerText = document.getElementById('lblCgstAmount').innerText;
            document.getElementById('cSgstAmt').innerText = document.getElementById('lblSgstAmount').innerText;
        }

        const confirmModal = new bootstrap.Modal(document.getElementById('confirmInvoiceModal'));
        confirmModal.show();
    }

    // Submit Voucher Form
    function submitVoucherForm() {
        const btn = document.getElementById('btnConfirmGenerate');
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Persisting...`;

        const form = document.getElementById('voucherForm');
        const formData = new FormData(form);

        fetch("{{ route('invoices.generate') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-check me-1"></i> Confirm & Save`;

            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmInvoiceModal'));
            if (confirmModal) confirmModal.hide();

            if (res.status === 'success') {
                document.getElementById('lblSuccessInvoiceNo').innerText = res.invoice_no;
                
                document.getElementById('btnSuccessPrint').onclick = function() {
                    window.open(`/invoices/print/${res.invoice_id}?autoprint=1`, '_blank');
                };
                document.getElementById('btnSuccessView').onclick = function() {
                    window.open(`/invoices/print/${res.invoice_id}`, '_blank');
                };

                if (andPrintAfterSave) {
                    window.open(`/invoices/print/${res.invoice_id}?autoprint=1`, '_blank');
                }

                showSection('success');
            } else {
                alert(`❌ Generation Error: ${res.message}`);
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-check me-1"></i> Confirm & Save`;
            alert(`❌ Network Failure: ${err.message}`);
        });
    }

    // Return to Queue
    function returnToQueue() {
        showSection('queue');
    }

    /* ==========================================================================
       INVOICE SHARING POPOVER, MODAL & TOAST HANDLERS
       ========================================================================== */

    window.currentShareInvoice = null;

    // Open Floating Share Popover Menu
    function openSharePopover(e, invoiceId, invoiceNo, customerName, invoiceDate, amount, mobile, email, vehicleNo) {
        if (e && e.stopPropagation) e.stopPropagation();
        
        window.currentShareInvoice = {
            id: invoiceId,
            invoice_no: invoiceNo,
            customer_name: customerName,
            invoice_date: invoiceDate,
            amount: amount,
            mobile: mobile,
            email: email,
            vehicle_no: vehicleNo || ''
        };

        const popover = document.getElementById('sharePopover');
        document.getElementById('popoverInvoiceNo').innerText = invoiceNo;

        // Reset active state on any existing share buttons
        document.querySelectorAll('.btn-saas-outline-share.active').forEach(b => b.classList.remove('active'));
        
        const btn = e.currentTarget;
        if (btn) btn.classList.add('active');

        // Position popover relative to button with boundary protection
        const rect = btn.getBoundingClientRect();
        popover.classList.remove('d-none');
        
        const popoverWidth = 250;
        const popoverHeight = 230;

        let top = rect.bottom + 6;
        let left = rect.right - popoverWidth;

        // Ensure left boundary
        if (left < 12) left = 12;
        
        // If flips near bottom of viewport
        if (top + popoverHeight > window.innerHeight) {
            top = Math.max(12, rect.top - popoverHeight - 6);
        }

        popover.style.top = top + 'px';
        popover.style.left = left + 'px';

        // Trigger CSS entry animation
        requestAnimationFrame(() => {
            popover.classList.add('show');
        });
    }

    // Close Floating Popover
    function closeSharePopover() {
        const popover = document.getElementById('sharePopover');
        if (popover && popover.classList.contains('show')) {
            popover.classList.remove('show');
            setTimeout(() => {
                popover.classList.add('d-none');
            }, 150);
        }
        document.querySelectorAll('.btn-saas-outline-share.active').forEach(b => b.classList.remove('active'));
    }

    // Outside Click & Key Listeners for Popover
    document.addEventListener('click', function(e) {
        const popover = document.getElementById('sharePopover');
        if (popover && !popover.contains(e.target) && !e.target.closest('.btn-saas-outline-share')) {
            closeSharePopover();
        }
    });

    window.addEventListener('resize', closeSharePopover);
    window.addEventListener('scroll', closeSharePopover, true);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSharePopover();
        }
    });

    // Route Popover Actions
    function triggerShareAction(action) {
        if (!window.currentShareInvoice) return;
        const data = window.currentShareInvoice;
        closeSharePopover();

        if (action === 'email') {
            openShareModal('email');
        } else if (action === 'whatsapp') {
            openShareModal('whatsapp');
        } else if (action === 'copy_link') {
            const printUrl = window.location.origin + '/invoices/print/' + data.id;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(printUrl).then(() => {
                    showEnterpriseToast('Invoice link copied successfully.', 'success');
                }).catch(() => {
                    fallbackCopyText(printUrl);
                });
            } else {
                fallbackCopyText(printUrl);
            }
        } else if (action === 'download_pdf') {
            window.open('/invoices/print/' + data.id + '?download=1', '_blank');
            showEnterpriseToast('Opening invoice PDF...', 'info');
        } else if (action === 'print') {
            window.open('/invoices/print/' + data.id + '?autoprint=1', '_blank');
        }
    }

    // Clipboard Fallback
    function fallbackCopyText(text) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            showEnterpriseToast('Invoice link copied successfully.', 'success');
        } catch (err) {
            showEnterpriseToast('Failed to copy link', 'error');
        }
        document.body.removeChild(ta);
    }

    // Open Interactive Share Modal
    function openShareModal(channel = 'email') {
        if (!window.currentShareInvoice) return;
        const data = window.currentShareInvoice;

        const vNo = (data.vehicle_no && data.vehicle_no !== 'UNASSIGNED') ? data.vehicle_no : '';
        const vLine = vNo ? `\nVehicle No: ${vNo}` : '';

        // Fill modal summary banner
        document.getElementById('shareModalSubtitle').innerText = `${data.invoice_no} • ${data.customer_name}${vNo ? ` • ${vNo}` : ''}`;
        document.getElementById('smSummaryInvNo').innerText = data.invoice_no;
        document.getElementById('smSummaryCustomer').innerText = data.customer_name;
        document.getElementById('smSummaryDate').innerText = data.invoice_date || 'N/A';
        document.getElementById('smSummaryAmount').innerText = '₹' + data.amount;

        // Fill Email Form
        document.getElementById('emailInvoiceId').value = data.id;
        document.getElementById('emailTo').value = data.email || '';
        document.getElementById('emailCc').value = '';
        document.getElementById('emailSubject').value = `Invoice ${data.invoice_no} - ${data.customer_name}${vNo ? ` (${vNo})` : ''}`;
        document.getElementById('emailMessage').value = `Dear ${data.customer_name},\n\nPlease find attached invoice ${data.invoice_no} for ₹${data.amount}.${vLine}\n\nThank you for your business.`;

        // Fill WhatsApp Form
        document.getElementById('waInvoiceId').value = data.id;
        document.getElementById('waCustomerName').value = data.customer_name;
        document.getElementById('waMobile').value = data.mobile || '';
        document.getElementById('waMessage').value = `Dear ${data.customer_name},\n\nYour invoice ${data.invoice_no} is ready.${vLine}\nInvoice Amount: ₹${data.amount}\n\nPlease find the invoice attached.\n\nThank you.`;

        // Switch to chosen channel
        switchShareChannel(channel);

        // Dynamic background sync: Fetch latest verified customer email, mobile & vehicle_no for this invoice
        if (data.id) {
            fetch(`{{ route('invoices.share.details') }}?invoice_id=${data.id}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success' && res.data) {
                        if (res.data.email) {
                            document.getElementById('emailTo').value = res.data.email;
                            data.email = res.data.email;
                        }
                        if (res.data.mobile) {
                            document.getElementById('waMobile').value = res.data.mobile;
                            data.mobile = res.data.mobile;
                        }
                        if (res.data.customer_name) {
                            document.getElementById('waCustomerName').value = res.data.customer_name;
                            document.getElementById('smSummaryCustomer').innerText = res.data.customer_name;
                        }
                        if (res.data.vehicle_no && (!data.vehicle_no || data.vehicle_no === 'UNASSIGNED')) {
                            data.vehicle_no = res.data.vehicle_no;
                            const syncVLine = `\nVehicle No: ${res.data.vehicle_no}`;
                            document.getElementById('shareModalSubtitle').innerText = `${data.invoice_no} • ${data.customer_name} • ${res.data.vehicle_no}`;
                            document.getElementById('emailSubject').value = `Invoice ${data.invoice_no} - ${data.customer_name} (${res.data.vehicle_no})`;
                            document.getElementById('emailMessage').value = `Dear ${data.customer_name},\n\nPlease find attached invoice ${data.invoice_no} for ₹${data.amount}.${syncVLine}\n\nThank you for your business.`;
                            document.getElementById('waMessage').value = `Dear ${data.customer_name},\n\nYour invoice ${data.invoice_no} is ready.${syncVLine}\nInvoice Amount: ₹${data.amount}\n\nPlease find the invoice attached.\n\nThank you.`;
                        }
                    }
                })
                .catch(err => {
                    console.log("Customer contact sync notice:", err);
                });
        }

        // Show Bootstrap modal
        const modalEl = document.getElementById('shareInvoiceModal');
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
    }

    // Switch between Email & WhatsApp Channels inside modal
    function switchShareChannel(channel) {
        const cardEmail = document.getElementById('channelCardEmail');
        const cardWa = document.getElementById('channelCardWhatsapp');
        const secEmail = document.getElementById('sectionShareEmail');
        const secWa = document.getElementById('sectionShareWhatsapp');

        if (channel === 'whatsapp') {
            cardEmail.classList.remove('active');
            cardWa.classList.add('active');
            secEmail.classList.add('d-none');
            secWa.classList.remove('d-none');
            setTimeout(() => {
                const mobInput = document.getElementById('waMobile');
                if (mobInput) mobInput.focus();
            }, 100);
        } else {
            cardWa.classList.remove('active');
            cardEmail.classList.add('active');
            secWa.classList.add('d-none');
            secEmail.classList.remove('d-none');
            setTimeout(() => {
                const toInput = document.getElementById('emailTo');
                if (toInput) toInput.focus();
            }, 100);
        }
    }

    // Send Invoice Email AJAX
    function sendInvoiceEmail() {
        const btn = document.getElementById('btnSubmitEmail');
        const form = document.getElementById('formShareEmail');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Sending...`;

        const formData = new FormData(form);
        formData.append('attach_pdf', document.getElementById('emailAttachPdf').checked ? '1' : '0');

        fetch("{{ route('invoices.share.email') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = origHtml;

            if (res.status === 'success') {
                const modal = bootstrap.Modal.getInstance(document.getElementById('shareInvoiceModal'));
                if (modal) modal.hide();
                showEnterpriseToast(res.message || 'Invoice sent via email successfully!', 'success');
            } else {
                showEnterpriseToast(res.message || 'Failed to send email.', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            showEnterpriseToast('Network error: ' + err.message, 'error');
        });
    }

    // Send Invoice WhatsApp via Meta WhatsApp Cloud API
    function sendInvoiceWhatsapp() {
        const btn = document.getElementById('btnSubmitWhatsapp');
        const form = document.getElementById('formShareWhatsapp');
        if (!form) return;

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Sending WhatsApp...`;

        const formData = new FormData(form);
        formData.append('attach_pdf', document.getElementById('waAttachPdf').checked ? '1' : '0');

        fetch("{{ route('invoices.share.whatsapp') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = origHtml;

            if (res.status === 'success') {
                const modal = bootstrap.Modal.getInstance(document.getElementById('shareInvoiceModal'));
                if (modal) modal.hide();
                showEnterpriseToast(res.message || 'WhatsApp message and PDF sent successfully!', 'success');
            } else {
                showEnterpriseToast(res.message || 'Failed to send WhatsApp message.', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            showEnterpriseToast('An error occurred while sending WhatsApp message.', 'error');
        });
    }

    // Modern Enterprise Toast Notification System
    function showEnterpriseToast(message, type = 'success') {
        const container = document.getElementById('enterpriseToastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `enterprise-toast toast-${type}`;
        
        let iconClass = 'fa-circle-check';
        if (type === 'error') iconClass = 'fa-circle-exclamation';
        else if (type === 'info') iconClass = 'fa-circle-info';

        toast.innerHTML = `
            <div class="enterprise-toast-icon">
                <i class="fa-solid ${iconClass}"></i>
            </div>
            <div class="enterprise-toast-content">${message}</div>
            <button type="button" class="enterprise-toast-close" onclick="this.parentElement.remove()" title="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(40px)';
            setTimeout(() => {
                if (toast.parentElement) toast.parentElement.removeChild(toast);
            }, 200);
        }, 3500);
    }
</script>
@endpush
