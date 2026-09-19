@php
    $dashboardTitle = 'Admin Dashboard';
    if (auth()->check()) {
        if (auth()->user()->hasRole('sales') && !auth()->user()->isAdmin()) {
            $dashboardTitle = 'Sales Dashboard';
        } elseif (auth()->user()->hasRole('accountant') && !auth()->user()->isAdmin()) {
            $dashboardTitle = 'Accountant Dashboard';
        } elseif (auth()->user()->hasRole('purchase') && !auth()->user()->isAdmin()) {
            $dashboardTitle = 'Purchase Dashboard';
        }
    }
@endphp

@extends('admin.layouts.app')

@section('title', $dashboardTitle . ' | Accounts ERP')

@section('content')
<!-- Dashboard Title Bar & Controls (Spacious Layout) -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom">
    <div>
        <h2 class="fw-extrabold text-dark fs-4 mb-0">{{ $dashboardTitle }}</h2>
    </div>

    <div class="d-flex align-items-center flex-wrap gap-2">
        <!-- Financial Year Selector -->
        <div class="dropdown">
            <button class="btn btn-white border bg-white shadow-sm btn-sm dropdown-toggle fw-semibold text-secondary rounded-3 px-3 py-2 fs-7" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-calendar-days text-warning me-1"></i> {{ $user->financial_year }}
            </button>
            <ul class="dropdown-menu shadow">
                <li><a class="dropdown-item active fw-bold fs-7" href="#">{{ $user->financial_year }} (Current)</a></li>
                <li><a class="dropdown-item fs-7" href="#">FY 2025-26</a></li>
                <li><a class="dropdown-item fs-7" href="#">FY 2024-25</a></li>
            </ul>
        </div>

        <!-- Date Range Filter -->
        <div class="dropdown">
            <button class="btn btn-white border bg-white shadow-sm btn-sm dropdown-toggle fw-semibold text-secondary rounded-3 px-3 py-2 fs-7" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-regular fa-clock text-info me-1"></i> This Month
            </button>
            <ul class="dropdown-menu shadow">
                <li><a class="dropdown-item fs-7" href="#">Today</a></li>
                <li><a class="dropdown-item fs-7" href="#">This Week</a></li>
                <li><a class="dropdown-item active fw-bold fs-7" href="#">This Month</a></li>
                <li><a class="dropdown-item fs-7" href="#">This Year</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item fs-7 text-primary" href="#"><i class="fa-solid fa-sliders me-1"></i> Custom Range...</a></li>
            </ul>
        </div>

        <!-- Global Search Box -->
        <div class="search-box-wrapper" style="width: 240px;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" class="top-search-input" placeholder="Search invoices, ledger...">
        </div>
    </div>
</div>

<!-- Quick Action Buttons Bar -->
<div class="quick-actions-card mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="quick-actions-title">
            <i class="fa-solid fa-bolt text-warning me-1"></i>
            <span class="fw-bold fs-7 text-uppercase letter-spacing-1">Quick Actions</span>
        </div>
        <div class="quick-actions-buttons d-flex align-items-center flex-wrap gap-2">
            <!-- @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole(['sales', 'accountant']) || auth()->user()->hasPermission('sales.create'))
            <a href="{{ route('sales.orders') }}" class="btn btn-primary btn-sm rounded-3 px-3 py-2 fw-semibold fs-7 shadow-sm text-decoration-none">
                <i class="fa-solid fa-file-invoice-dollar me-1"></i> Sales Orders
            </a>
            @endif -->

            @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole(['sales', 'accountant']) || auth()->user()->hasPermission('payments.create'))
            <a href="{{ route('vouchers.index') }}" class="btn btn-success btn-sm rounded-3 px-3 py-2 fw-semibold fs-7 text-white shadow-sm text-decoration-none">
                <i class="fa-solid fa-hand-holding-dollar me-1"></i> Receive Payment
            </a>
            @endif

            @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole('accountant'))
            <a href="{{ route('vouchers.index') }}" class="btn btn-info btn-sm rounded-3 px-3 py-2 fw-semibold fs-7 text-white text-decoration-none">
                <i class="fa-solid fa-pen-to-square me-1"></i> Accounting Vouchers
            </a>
            @endif

            <a href="{{ route('ledger.index') }}" class="btn btn-dark btn-sm rounded-3 px-3 py-2 fw-semibold fs-7 text-white text-decoration-none shadow-sm">
                <i class="fa-solid fa-book-open me-1"></i> Ledger Statement
            </a>
        </div>
    </div>
</div>

<!-- 6 Main Summary KPI Cards (Indian Currency ₹) -->
<div class="row g-3 mb-4">
    <!-- 1. Total Sales -->
    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole(['sales', 'accountant']) || auth()->user()->hasPermission('sales.view'))
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="kpi-card h-100 border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="kpi-title">TOTAL SALES</span>
                <div class="kpi-icon-badge bg-primary-subtle text-primary">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
            </div>
            <div class="kpi-amount">₹{{ number_format($kpis['total_sales']['amount'], 2) }}</div>
            <div class="kpi-trend text-success">
                <i class="fa-solid fa-arrow-trend-up me-1"></i> {{ $kpis['total_sales']['change'] }}
                <span class="kpi-subtext ms-1">{{ $kpis['total_sales']['subtext'] }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- 2. Total Purchase -->
    <!-- @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole(['purchase', 'accountant']) || auth()->user()->hasPermission('purchase.view'))
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="kpi-card h-100 border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="kpi-title">TOTAL PURCHASE</span>
                <div class="kpi-icon-badge bg-warning-subtle text-warning">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
            </div>
            <div class="kpi-amount">₹{{ number_format($kpis['total_purchases']['amount'], 2) }}</div>
            <div class="kpi-trend text-success">
                <i class="fa-solid fa-arrow-trend-up me-1"></i> {{ $kpis['total_purchases']['change'] }}
                <span class="kpi-subtext ms-1">{{ $kpis['total_purchases']['subtext'] }}</span>
            </div>
        </div>
    </div>
    @endif -->

    <!-- 3. Total Receivable -->
    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole(['sales', 'accountant']))
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="kpi-card h-100 border-start border-4 border-info">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="kpi-title">TOTAL RECEIVABLE</span>
                <div class="kpi-icon-badge bg-info-subtle text-info">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>
            <div class="kpi-amount">₹{{ number_format($kpis['total_receivable']['amount'], 2) }}</div>
            <div class="kpi-subtext text-secondary mt-1">
                <i class="fa-solid fa-clock me-1"></i> {{ $kpis['total_receivable']['subtext'] }}
            </div>
        </div>
    </div>
    @endif

    <!-- 4. Total Payable -->
    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole(['purchase', 'accountant']))
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="kpi-card h-100 border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="kpi-title">TOTAL PAYABLE</span>
                <div class="kpi-icon-badge bg-danger-subtle text-danger">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </div>
            </div>
            <div class="kpi-amount text-danger">₹{{ number_format($kpis['total_payable']['amount'], 2) }}</div>
            <div class="kpi-subtext text-secondary mt-1">
                <i class="fa-solid fa-circle-exclamation me-1 text-danger"></i> {{ $kpis['total_payable']['subtext'] }}
            </div>
        </div>
    </div>
    @endif

    <!-- 5. Cash & Bank Balance -->
    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole('accountant'))
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="kpi-card h-100 border-start border-4 border-secondary">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="kpi-title">CASH & BANK BALANCE</span>
                <div class="kpi-icon-badge bg-secondary-subtle text-secondary">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
            </div>
            <div class="kpi-amount">₹{{ number_format($kpis['cash_bank_balance']['amount'], 2) }}</div>
            <div class="kpi-subtext text-secondary mt-1">
                <i class="fa-solid fa-vault me-1"></i> {{ $kpis['cash_bank_balance']['subtext'] }}
            </div>
        </div>
    </div>
    @endif

    <!-- 6. Net Profit -->
    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole('accountant'))
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="kpi-card h-100 border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="kpi-title">NET PROFIT</span>
                <div class="kpi-icon-badge bg-success-subtle text-success">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
            <div class="kpi-amount text-success">₹{{ number_format($kpis['net_profit']['amount'], 2) }}</div>
            <div class="kpi-trend text-success">
                <i class="fa-solid fa-arrow-trend-up me-1"></i> {{ $kpis['net_profit']['change'] }}
                <span class="kpi-subtext ms-1">{{ $kpis['net_profit']['subtext'] }}</span>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- CHARTS & SUMMARY ROW -->
<div class="row g-3 mb-4">
    <!-- Sales vs Purchase Large Line/Bar Chart -->
    <div class="{{ (!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole('accountant')) ? 'col-xl-8' : 'col-xl-12' }} col-lg-12">
        <div class="erp-card h-100">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom">
                <div>
                    <h5 class="erp-card-title mb-0">Sales vs Collections Analysis</h5>
                    <small class="text-muted fs-8">Monthly comparative revenue billing & collections inflow</small>
                </div>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active" id="btnFilterMonthly">Monthly</button>
                    <button type="button" class="btn btn-outline-secondary" id="btnFilterQuarterly">Quarterly</button>
                    <button type="button" class="btn btn-outline-secondary" id="btnFilterYearly">Yearly</button>
                </div>
            </div>
            <div style="height: 310px;">
                <canvas id="salesPurchaseChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Income vs Expense Donut Widget -->
    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole('accountant'))
    <div class="col-xl-4 col-lg-12">
        <div class="erp-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h5 class="erp-card-title mb-0">Payment Modes & Balances</h5>
                <span class="badge bg-success-subtle text-success rounded-pill fs-8">Live Balances</span>
            </div>

            <div class="position-relative d-flex justify-content-center align-items-center" style="height: 210px;">
                <canvas id="incomeExpenseDonutChart"></canvas>
                <div class="donut-center-text text-center">
                    <small class="text-muted fs-8 d-block text-uppercase">Liquid Funds</small>
                    <span class="fw-bold fs-6 text-success">₹{{ number_format($kpis['cash_bank_balance']['amount'], 2) }}</span>
                </div>
            </div>

            <div class="mt-3 pt-2 border-top">
                <div class="d-flex justify-content-between fs-7 mb-1">
                    <span class="text-muted"><i class="fa-solid fa-circle text-success me-1 fs-8"></i> Total Sales Billed:</span>
                    <span class="fw-bold text-dark">₹{{ number_format($kpis['total_sales']['amount'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between fs-7 mb-1">
                    <span class="text-muted"><i class="fa-solid fa-circle text-info me-1 fs-8"></i> Total Outstanding:</span>
                    <span class="fw-bold text-dark">₹{{ number_format($kpis['total_receivable']['amount'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- SECONDARY WIDGETS ROW (Payment Due & GST Summary) -->
@if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole('accountant'))
<div class="row g-3 mb-4">
    <!-- Payment Due Summary Widget -->
    <div class="col-xl-5 col-lg-12">
        <div class="erp-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h5 class="erp-card-title mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-warning"></i> Payment Due Summary</h5>
                <small class="text-muted fs-8">Upcoming Outflows</small>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <div class="due-box p-3 rounded-3 bg-light border">
                        <small class="text-muted d-block text-uppercase fs-8 fw-bold">Due Today</small>
                        <span class="fw-bold text-dark fs-5">₹{{ number_format($paymentDueSummary['today'], 2) }}</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="due-box p-3 rounded-3 bg-light border">
                        <small class="text-muted d-block text-uppercase fs-8 fw-bold">Due This Week</small>
                        <span class="fw-bold text-dark fs-5">₹{{ number_format($paymentDueSummary['this_week'], 2) }}</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="due-box p-3 rounded-3 bg-light border">
                        <small class="text-muted d-block text-uppercase fs-8 fw-bold">Due This Month</small>
                        <span class="fw-bold text-dark fs-5">₹{{ number_format($paymentDueSummary['this_month'], 2) }}</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="due-box p-3 rounded-3 bg-danger-subtle border border-danger-subtle">
                        <small class="text-danger d-block text-uppercase fs-8 fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i> Overdue</small>
                        <span class="fw-bold text-danger fs-5">₹{{ number_format($paymentDueSummary['overdue'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GST / Tax Summary Widget (Indian GST Framework) -->
    <div class="col-xl-7 col-lg-12">
        <div class="erp-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div>
                    <h5 class="erp-card-title mb-0"><i class="fa-solid fa-percent me-2 text-purple"></i> GST Summary (Current Return Period)</h5>
                    <small class="text-muted fs-8">GSTR-3B Computation & ITC Status</small>
                </div>
                <span class="badge bg-primary-subtle text-primary rounded-pill fs-8">Net Payable: ₹{{ number_format($gstSummary['net_payable'], 2) }}</span>
            </div>

            <div class="row g-3 align-items-center">
                <div class="col-md-7">
                    <div class="table-responsive">
                        <table class="table table-sm text-nowrap mb-0 fs-7">
                            <tbody>
                                <tr>
                                    <td class="text-muted fw-semibold">Output GST (Sales Tax Collected):</td>
                                    <td class="fw-bold text-end text-dark">₹{{ number_format($gstSummary['output_gst'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Input Tax Credit (ITC Claimed):</td>
                                    <td class="fw-bold text-end text-success">- ₹{{ number_format($gstSummary['input_gst'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">CGST (Central Tax 50%):</td>
                                    <td class="fw-bold text-end">₹{{ number_format($gstSummary['cgst'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">SGST (State Tax 50%):</td>
                                    <td class="fw-bold text-end">₹{{ number_format($gstSummary['sgst'], 2) }}</td>
                                </tr>
                                <tr class="table-light fw-bold border-top border-2">
                                    <td class="text-primary">Net GST Liability Payable:</td>
                                    <td class="text-end text-primary fs-6">₹{{ number_format($gstSummary['net_payable'], 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-5 border-start">
                    <div class="text-center mb-1">
                        <small class="text-muted fs-8 fw-bold text-uppercase">Monthly Tax Trend</small>
                    </div>
                    <div style="height: 110px;">
                        <canvas id="gstTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- AGING TABLES ROW (Receivable Aging & Payable Aging) -->
<div class="row g-3 mb-4">
    <!-- Accounts Receivable Aging Table -->
    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole(['sales', 'accountant']))
    <div class="{{ (!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole('accountant')) ? 'col-xl-6' : 'col-xl-12' }} col-lg-12">
        <div class="erp-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h5 class="erp-card-title mb-0"><i class="fa-solid fa-hourglass-half me-2 text-info"></i> Accounts Receivable Aging</h5>
                <a href="#" class="fs-8 text-primary fw-semibold text-decoration-none">View All Ledger <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="table-light">
                        <tr>
                            <th>Customer Name</th>
                            <th>Invoice #</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Days</th>
                            <th>Aging Category</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receivableAging as $row)
                        <tr>
                            <td class="fw-bold text-dark">{{ $row['customer'] }}</td>
                            <td class="text-muted font-monospace">{{ $row['invoice'] }}</td>
                            <td>{{ $row['due_date'] }}</td>
                            <td class="fw-bold">₹{{ number_format($row['amount'], 2) }}</td>
                            <td class="text-danger fw-semibold">{{ $row['days_overdue'] }}d</td>
                            <td>
                                <span class="badge bg-{{ $row['badge'] }}-subtle text-{{ $row['badge'] }} rounded-pill px-2">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-circle-check text-success fs-5 d-block mb-1"></i>
                                No overdue receivables found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Accounts Payable Aging Table -->
    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole(['purchase', 'accountant']))
    <div class="col-xl-6 col-lg-12">
        <div class="erp-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h5 class="erp-card-title mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-danger"></i> Supplier Payable Aging</h5>
                <a href="{{ route('ledger.index') }}" class="fs-8 text-primary fw-semibold text-decoration-none">View All Bills <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="table-light">
                        <tr>
                            <th>Supplier Name</th>
                            <th>Bill #</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Overdue</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payableAging as $row)
                        <tr>
                            <td class="fw-bold text-dark">{{ $row['supplier'] }}</td>
                            <td class="text-muted">{{ $row['bill_no'] }}</td>
                            <td>{{ $row['due_date'] }}</td>
                            <td class="fw-bold text-danger">₹{{ number_format($row['amount'], 2) }}</td>
                            <td class="text-danger fw-semibold">{{ $row['days_overdue'] }}d</td>
                            <td>
                                <span class="badge bg-{{ $row['badge'] }}-subtle text-{{ $row['badge'] }} rounded-pill px-2">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-circle-check text-success fs-5 d-block mb-1"></i>
                                No pending supplier payables recorded.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- EXPENSE & INVENTORY ROW -->
<div class="row g-3 mb-4">
    <!-- Expense Breakdown Bar Chart -->
    @if(!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole('accountant'))
    <div class="col-xl-6 col-lg-12">
        <div class="erp-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h5 class="erp-card-title mb-0"><i class="fa-solid fa-chart-bar me-2 text-danger"></i> Expense Breakdown</h5>
                <small class="text-muted fs-8">Current Month Categories</small>
            </div>
            <div style="height: 220px;">
                <canvas id="expenseBreakdownChart"></canvas>
            </div>
        </div>
    </div>
    @endif

    <!-- Inventory Low Stock Alerts Table -->
    <div class="{{ (!auth()->check() || auth()->user()->isAdmin() || auth()->user()->hasRole('accountant')) ? 'col-xl-6' : 'col-xl-12' }} col-lg-12">
        <div class="erp-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h5 class="erp-card-title mb-0"><i class="fa-solid fa-boxes-stacked me-2 text-warning"></i> Low Stock Alerts</h5>
                <span class="badge bg-danger text-white rounded-pill fs-8">Action Required</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="table-light">
                        <tr>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Current Stock</th>
                            <th>Minimum Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockAlerts as $item)
                        <tr>
                            <td class="fw-bold text-dark">{{ $item['product'] }}</td>
                            <td class="text-muted font-monospace fs-8">{{ $item['sku'] }}</td>
                            <td class="fw-bold text-danger">{{ $item['current_stock'] }} units</td>
                            <td class="text-muted">{{ $item['min_stock'] }} units</td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $item['status'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- TOP CUSTOMERS & TOP PRODUCTS ROW -->
<div class="row g-3 mb-4">
    <!-- Top Customers List -->
    <div class="col-xl-6 col-lg-12">
        <div class="erp-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h5 class="erp-card-title mb-0"><i class="fa-solid fa-award me-2 text-warning"></i> Top Revenue Customers</h5>
                <small class="text-muted fs-8">YTD Contribution</small>
            </div>
            <div class="list-group list-group-flush">
                @forelse($topCustomers as $index => $customer)
                <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-2 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-light text-dark border rounded-circle p-2 fs-7 fw-bold" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                            #{{ $index + 1 }}
                        </span>
                        <div>
                            <div class="fw-bold text-dark fs-7">{{ $customer['name'] }}</div>
                            <small class="text-muted fs-8">{{ $customer['invoices'] }} Billed Invoice(s)</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-success fs-7">₹{{ number_format($customer['revenue'], 2) }}</div>
                        <small class="text-muted fs-8">Total Revenue</small>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="fa-solid fa-users-slash fs-5 d-block mb-1"></i>
                    No customer revenue data yet.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Top Selling Products Table -->
    <div class="col-xl-6 col-lg-12">
        <div class="erp-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h5 class="erp-card-title mb-0"><i class="fa-solid fa-cubes me-2 text-primary"></i> Top Selling Products</h5>
                <small class="text-muted fs-8">YTD Performance</small>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Qty/Weight</th>
                            <th>Sales Value</th>
                            <th>Est. Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $prod)
                        <tr>
                            <td class="fw-bold text-dark">{{ $prod['product'] }}</td>
                            <td class="text-muted font-monospace fs-8">{{ $prod['sku'] }}</td>
                            <td class="fw-semibold">{{ $prod['qty'] }}</td>
                            <td class="fw-bold">₹{{ number_format($prod['sales'], 2) }}</td>
                            <td class="fw-bold text-success">₹{{ number_format($prod['profit'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-boxes-packing fs-5 d-block mb-1"></i>
                                No product sales recorded yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- RECENT TRANSACTIONS LEDGER TABLE WIDGET -->
<div class="erp-card mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom">
        <div>
            <h5 class="erp-card-title mb-0"><i class="fa-solid fa-list-check me-2 text-primary"></i> Recent Accounting Transactions</h5>
            <small class="text-muted fs-8">General Ledger Vouchers & Journal Postings</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="search-box-wrapper" style="width: 220px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="txTableSearch" class="top-search-input py-1 fs-7" placeholder="Search transactions...">
            </div>
            <button class="btn btn-outline-secondary btn-sm rounded-3 px-3"><i class="fa-solid fa-filter me-1"></i> Filter</button>
            <button class="btn btn-primary btn-sm rounded-3 px-3"><i class="fa-solid fa-plus me-1"></i> View All</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-7" id="recentTxTable">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Voucher Ref #</th>
                    <th>Description</th>
                    <th>Account Ledger</th>
                    <th>Type</th>
                    <th class="text-end">Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions as $tx)
                <tr>
                    <td class="text-muted">{{ $tx['date'] }}</td>
                    <td class="font-monospace fw-bold text-primary">{{ $tx['reference'] }}</td>
                    <td class="fw-semibold text-dark">{{ $tx['description'] }}</td>
                    <td class="text-secondary"><i class="fa-solid fa-book-bookmark me-1 text-muted"></i> {{ $tx['account'] }}</td>
                    <td>
                        <span class="badge bg-{{ $tx['type_class'] }}-subtle text-{{ $tx['type_class'] }} rounded-pill px-2">
                            {{ $tx['type'] }}
                        </span>
                    </td>
                    <td class="text-end fw-bold {{ $tx['amount'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $tx['amount'] >= 0 ? '+ ₹' . number_format($tx['amount'], 2) : '- ₹' . number_format(abs($tx['amount']), 2) }}
                    </td>
                    <td>
                        <span class="badge bg-success-subtle text-success rounded-pill px-2">
                            <i class="fa-solid fa-check me-1"></i> {{ $tx['status'] }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="fa-solid fa-receipt fs-5 d-block mb-1"></i>
                        No recent accounting transactions found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Table Footer -->
    <div class="d-flex align-items-center justify-content-between pt-3 mt-2 border-top fs-8">
        <span class="text-muted fw-medium">Showing latest {{ count($recentTransactions) }} live transaction records</span>
        <a href="{{ route('vouchers.index') }}" class="btn btn-sm btn-outline-primary fw-bold fs-8 text-decoration-none">
            View All Accounting Vouchers <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>
</div>

@push('scripts')
<script>
    window.DASHBOARD_CHART_DATA = {!! $chartDataJson !!};
</script>
@endpush
@endsection
