@extends('admin.layouts.app')

@section('title', 'Sale Order #' . $slid . ' Details | Accounts ERP')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/sales_dashboard.css') }}">
@endpush

@section('content')
<!-- Navigation Bar & Quick Actions -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom no-print">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('sales.orders') }}" class="btn btn-white border bg-white shadow-sm btn-sm fw-semibold text-secondary rounded-3 px-3 py-2 fs-7">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Orders List
        </a>
        <div>
            <h2 class="fw-extrabold text-dark fs-4 mb-0">
                Sale Order Details <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-3 px-2.5 py-1 fs-7 ms-2">#{{ $slid }}</span>
            </h2>
            <small class="text-muted fs-7">Complete Line Items Breakdown, Billing & Delivery Status</small>
        </div>
    </div>

    <div class="d-flex align-items-center flex-wrap gap-2">
        <button class="btn btn-white border bg-white shadow-sm btn-sm fw-semibold text-secondary rounded-3 px-3 py-2 fs-7" onclick="loadOrderDetails()">
            <i class="fa-solid fa-rotate text-primary me-1" id="refreshBtnIcon"></i> Refresh Details
        </button>
        <button class="btn btn-primary btn-sm rounded-3 px-3 py-2 fw-semibold fs-7 shadow-sm" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print Order Invoice
        </button>
    </div>
</div>

<!-- SECTION 1: ORDER HEADER CARD (ACCOUNTING TAX INVOICE HEADER VIEW) -->
<div class="order-header-card mb-4">
    <div class="p-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary text-white px-3 py-1.5 rounded-3 fs-7 fw-bold" id="badgeSoNo">
                        #{{ $slid }}
                    </span>
                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-3 fs-7 fw-bold" id="badgeStatus">
                        Loading Status...
                    </span>
                </div>
                <h3 class="fw-extrabold text-white mb-1 fs-4" id="displayCustomerName">
                    Loading Customer Details...
                </h3>
                <div class="fs-8 text-white-50" id="displayCustomerMeta">
                    GSTIN: - | Address: -
                </div>
            </div>

            <div class="text-md-end">
                <div class="text-white-50 fs-8 text-uppercase letter-spacing-1 fw-semibold">Order Created On</div>
                <div class="fw-bold text-white fs-6 mt-0.5" id="displayCreatedDate">-</div>
                <small class="text-info fs-8 mt-1 d-block"><i class="fa-solid fa-truck-fast me-1"></i> Dispatch Target: <span id="displayDispatchDate">-</span></small>
            </div>
        </div>
    </div>

    <!-- Summary Header Info Bar -->
    <div class="order-summary-grid">
        <div class="row g-3">
            <div class="col-md-3 col-6 border-end border-secondary border-opacity-25">
                <small class="text-white-50 text-uppercase fw-bold fs-9">Customer PO Number</small>
                <div class="fw-bold text-white fs-7 mt-0.5" id="displayCustPo">-</div>
            </div>
            <div class="col-md-3 col-6 border-end border-secondary border-opacity-25">
                <small class="text-white-50 text-uppercase fw-bold fs-9">Base Rate (₹/MT)</small>
                <div class="fw-bold text-info fs-7 mt-0.5" id="displayBasePrice">₹0.00</div>
            </div>
            <div class="col-md-3 col-6 border-end border-secondary border-opacity-25">
                <small class="text-white-50 text-uppercase fw-bold fs-9">Total Ordered Qty</small>
                <div class="fw-extrabold text-white fs-7 mt-0.5" id="displayTotalQty">0.000 MT</div>
            </div>
            <div class="col-md-3 col-6">
                <small class="text-white-50 text-uppercase fw-bold fs-9">Line Items Count</small>
                <div class="fw-bold text-warning fs-7 mt-0.5" id="displayItemsCount">0 Items</div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 2: 4 SUMMARY KPI METRIC CARDS -->
<div class="row g-3 mb-4">
    <!-- 1. Total Ordered Qty -->
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="kpi-detail-card border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-bold fs-8 text-uppercase letter-spacing-1">Total Ordered Qty</span>
                    <h3 class="fw-extrabold text-dark fs-3 mt-1 mb-0"><span id="kpiOrderedQty">0.000</span> <small class="fs-7 text-muted">MT</small></h3>
                </div>
                <div class="kpi-icon-box bg-primary-subtle text-primary rounded-3 p-3">
                    <i class="fa-solid fa-weight-hanging fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Dispatched Qty -->
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="kpi-detail-card border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-bold fs-8 text-uppercase letter-spacing-1">Dispatched Qty</span>
                    <h3 class="fw-extrabold text-success fs-3 mt-1 mb-0"><span id="kpiDispatchedQty">0.000</span> <small class="fs-7 text-muted">MT</small></h3>
                </div>
                <div class="kpi-icon-box bg-success-subtle text-success rounded-3 p-3">
                    <i class="fa-solid fa-truck-fast fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Pending Qty -->
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="kpi-detail-card border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-bold fs-8 text-uppercase letter-spacing-1">Pending Delivery</span>
                    <h3 class="fw-extrabold text-warning fs-3 mt-1 mb-0"><span id="kpiPendingQty">0.000</span> <small class="fs-7 text-muted">MT</small></h3>
                </div>
                <div class="kpi-icon-box bg-warning-subtle text-warning rounded-3 p-3">
                    <i class="fa-solid fa-hourglass-half fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Total Line Items Amount -->
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="kpi-detail-card border-start border-4 border-info">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-bold fs-8 text-uppercase letter-spacing-1">Est. Total (Excl. GST)</span>
                    <h3 class="fw-extrabold text-primary fs-4 mt-1 mb-0" id="kpiTotalAmount">₹0.00</h3>
                    <small class="text-muted fs-9 fw-medium d-block mt-1" id="kpiGstSubtext">Incl. 18% GST: <strong class="text-success" id="kpiTotalWithGst">₹0.00</strong></small>
                </div>
                <div class="kpi-icon-box bg-info-subtle text-info rounded-3 p-3">
                    <i class="fa-solid fa-indian-rupee-sign fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 3: LINE ITEMS BREAKDOWN DATA TABLE -->
<div class="items-table-card shadow-sm mb-4">
    <div class="p-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3 bg-white">
        <h5 class="fw-bold text-dark fs-6 mb-0">
            <i class="fa-solid fa-boxes-stacked text-primary me-2"></i> Sales Order Item Details
        </h5>
        <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1 fs-8 fw-bold" id="badgeTableItemsCount">0 Items</span>
    </div>

    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th scope="col" class="text-center" style="width: 60px;">#</th>
                    <th scope="col">Product Name</th>
                    <th scope="col">Grade</th>
                    <th scope="col">Size</th>
                    <th scope="col" class="text-end">Ordered (MT)</th>
                    <th scope="col" class="text-end">Dispatched (MT)</th>
                    <th scope="col" class="text-end">Pending (MT)</th>
                    <th scope="col" class="text-end">Item Rate (₹)</th>
                    <th scope="col" class="text-end">Est. Line Amount (₹)</th>
                    <th scope="col" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody id="lineItemsTbody">
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted fs-7">
                        <i class="fa-solid fa-circle-notch fa-spin text-primary fs-4 mb-2 d-block"></i>
                        Fetching Line Items Details for Order #{{ $slid }}...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- SECTION 4: ITEMS DISPATCHED HISTORY DATA TABLE -->
<div class="items-table-card shadow-sm mb-4">
    <div class="p-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3 bg-white">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold text-dark fs-6 mb-0">
                <i class="fa-solid fa-truck-fast text-success me-2"></i> Items Dispatched
            </h5>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fs-8 fw-bold" id="badgeDispatchesCount">0 Dispatches</span>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap fs-7" id="dispatchSummaryHeader">
            <span class="text-muted">Dispatched Wt: <strong class="text-dark" id="headerActualWt">0.000 MT</strong></span>
            <span class="text-muted">Subtotal: <strong class="text-dark" id="headerSubtotal">₹0.00</strong></span>
            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-2 px-2 py-1 fs-8" id="headerGstBadge">+ 18% GST: ₹0.00</span>
            <span class="text-muted">Grand Total (Incl. GST): <strong class="text-success fs-6" id="headerGrandTotal">₹0.00</strong></span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th scope="col" class="text-center" style="width: 50px;">#</th>
                    <th scope="col">Dispatch Date</th>
                    <th scope="col">Vehicle No.</th>
                    <th scope="col">Product Name</th>
                    <th scope="col">Grade / Brand</th>
                    <th scope="col">Size & Length</th>
                    <th scope="col" class="text-end">Planned (MT)</th>
                    <th scope="col" class="text-end">Actual (MT)</th>
                    <th scope="col" class="text-center">Actual Pcs</th>
                    <th scope="col" class="text-end">Rate (₹)</th>
                    <th scope="col" class="text-end">Subtotal (₹)</th>
                </tr>
            </thead>
            <tbody id="dispatchesTbody">
                <tr>
                    <td colspan="11" class="text-center py-5 text-muted fs-7">
                        <i class="fa-solid fa-circle-notch fa-spin text-success fs-4 mb-2 d-block"></i>
                        Fetching Items Dispatched Data for Order #{{ $slid }}...
                    </td>
                </tr>
            </tbody>
            <tfoot id="dispatchesTfoot" style="display: none;">
                <tr class="bg-light border-top">
                    <td colspan="6" class="text-end fw-bold text-dark fs-7">Item Totals (Excl. GST):</td>
                    <td class="text-end fw-bold text-secondary fs-7" id="ftPlannedWt">0.000 MT</td>
                    <td class="text-end fw-bold text-success fs-7" id="ftActualWt">0.000 MT</td>
                    <td class="text-center fw-bold text-dark fs-7" id="ftActualPcs">0</td>
                    <td class="text-end text-muted fs-8 fw-semibold">Net Subtotal:</td>
                    <td class="text-end fw-bold text-dark fs-7" id="ftSubtotalAmt">₹0.00</td>
                </tr>
                <tr class="bg-white border-0">
                    <td colspan="9" class="text-end fw-semibold text-secondary fs-7">
                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-0.5 me-1 fs-9 fw-bold"><i class="fa-solid fa-percent me-1"></i>Tax</span>
                        Add: Integrated GST (18%):
                    </td>
                    <td colspan="2" class="text-end fw-bold text-info fs-7" id="ftGstAmt">+ ₹0.00</td>
                </tr>
                <tr class="bg-success-subtle border-top border-2 border-success">
                    <td colspan="9" class="text-end fw-extrabold text-dark fs-6">
                        <i class="fa-solid fa-calculator text-success me-1"></i> Grand Total (Incl. 18% GST):
                    </td>
                    <td colspan="2" class="text-end fw-extrabold text-success fs-5" id="ftGrandTotal">₹0.00</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const SLID = {{ $slid }};
    const API_ORDER_DETAILS = '/sales/order-details';
    const API_ORDER_DISPATCHES = '/sales/dispatches';

    document.addEventListener("DOMContentLoaded", function () {
        loadAllData();
    });

    async function loadAllData() {
        const refreshIcon = document.getElementById("refreshBtnIcon");
        if (refreshIcon) refreshIcon.classList.add("fa-spin");

        await loadOrderDetails();

        if (refreshIcon) refreshIcon.classList.remove("fa-spin");
    }

    async function loadOrderDetails() {
        const tbody = document.getElementById("lineItemsTbody");
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center py-5 text-muted fs-7">
                    <i class="fa-solid fa-circle-notch fa-spin text-primary fs-4 mb-2 d-block"></i>
                    Fetching Line Items Details for Order #${SLID}...
                </td>
            </tr>
        `;

        try {
            const res = await fetch(`${API_ORDER_DETAILS}?slid=${SLID}`);
            const resData = await res.json();

            if (resData.status === "success" && resData.data) {
                const order = resData.data;

                // Header Info
                document.getElementById("badgeSoNo").textContent = `#${order.slid}`;
                document.getElementById("displayCustomerName").textContent = order.customer_name || "N/A";
                
                let metaParts = [];
                if (order.customer_gst) metaParts.push(`GSTIN: ${order.customer_gst}`);
                if (order.customer_mobile) metaParts.push(`Mobile: ${order.customer_mobile}`);
                if (order.contact_person) metaParts.push(`Contact: ${order.contact_person} (${order.contact_mobile || 'N/A'})`);
                if (order.customer_address) metaParts.push(`Address: ${order.customer_address}`);
                document.getElementById("displayCustomerMeta").textContent = metaParts.join(" | ");

                document.getElementById("displayCreatedDate").textContent = order.createdon ? formatDate(order.createdon) : '-';
                document.getElementById("displayDispatchDate").textContent = order.dispatch_on ? formatDate(order.dispatch_on) : '-';
                document.getElementById("displayCustPo").textContent = order.cust_po || "N/A";
                document.getElementById("displayBasePrice").textContent = `₹${formatNumber(order.bprice, 2)}`;
                document.getElementById("displayTotalQty").textContent = `${formatNumber(order.total_ordered_tons, 3)} MT`;

                const isCompleted = parseFloat(order.total_pending_tons || 0) <= 0 || order.status === 1;
                document.getElementById("badgeStatus").textContent = order.status_label || (isCompleted ? 'Completed' : 'Pending');
                document.getElementById("badgeStatus").className = `badge ${isCompleted ? 'bg-success text-white' : 'bg-warning text-dark'} px-3 py-1.5 rounded-3 fs-7 fw-bold`;

                // Render Table 1: Line Items
                renderLineItems(order.items || []);

                // Render Table 2: Dispatched Items
                if (order.dispatched_items) {
                    renderDispatchedItems({
                        dispatched_items: order.dispatched_items,
                        summary: order.dispatch_summary || {}
                    });
                } else {
                    loadDispatchedItems();
                }
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-5 text-danger fs-7">
                            <i class="fa-solid fa-triangle-exclamation fs-3 mb-2 d-block"></i>
                            Failed to load details for Sale Order #${SLID}.
                        </td>
                    </tr>
                `;
            }
        } catch (err) {
            console.error("Error loading order details:", err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-5 text-danger fs-7">
                        <i class="fa-solid fa-exclamation-triangle fs-3 mb-2 d-block"></i>
                        Unable to connect to Sales API server (http://localhost/erpdivine/api/). Please Check.
                    </td>
                </tr>
            `;
        }
    }

    async function loadDispatchedItems() {
        const tbody = document.getElementById("dispatchesTbody");
        const tfoot = document.getElementById("dispatchesTfoot");
        if (tfoot) tfoot.style.display = "none";

        tbody.innerHTML = `
            <tr>
                <td colspan="11" class="text-center py-5 text-muted fs-7">
                    <i class="fa-solid fa-circle-notch fa-spin text-success fs-4 mb-2 d-block"></i>
                    Fetching Dispatched Items History for Order #${SLID}...
                </td>
            </tr>
        `;

        try {
            const res = await fetch(`${API_ORDER_DISPATCHES}?slid=${SLID}`);
            const resData = await res.json();

            if (resData.status === "success" && resData.data) {
                renderDispatchedItems(resData.data);
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted fs-7">
                            <i class="fa-solid fa-truck-ramp-box fs-3 mb-2 d-block text-secondary opacity-50"></i>
                            No items dispatched recorded yet for Sale Order #${SLID}.
                        </td>
                    </tr>
                `;
                document.getElementById("badgeDispatchesCount").textContent = "0 Dispatches";
            }
        } catch (err) {
            console.error("Error loading dispatched items:", err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center py-5 text-danger fs-7">
                        <i class="fa-solid fa-exclamation-triangle fs-3 mb-2 d-block"></i>
                        Unable to load dispatched items from API.
                    </td>
                </tr>
            `;
        }
    }

    function renderLineItems(items) {
        const tbody = document.getElementById("lineItemsTbody");
        document.getElementById("displayItemsCount").textContent = `${items.length} Items`;
        document.getElementById("badgeTableItemsCount").textContent = `${items.length} Items`;

        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted fs-7">
                        <i class="fa-solid fa-box-open fs-3 mb-2 d-block text-secondary opacity-50"></i>
                        No line items found for this Sale Order.
                    </td>
                </tr>
            `;
            return;
        }

        let totalOrdered = 0;
        let totalDispatched = 0;
        let totalPending = 0;
        let totalEstAmount = 0;

        let html = '';
        items.forEach((item, index) => {
            const qtyTons = parseFloat(item.ordered_qty_tons || 0);
            const dispatchedTons = parseFloat(item.dispatched_qty_tons || 0);
            const pendingTons = parseFloat(item.pending_qty_tons || 0);
            const rate = parseFloat(item.rate || 0);
            const lineEstAmount = qtyTons * rate;
            const isItemDone = pendingTons <= 0;

            totalOrdered += qtyTons;
            totalDispatched += dispatchedTons;
            totalPending += pendingTons;
            totalEstAmount += lineEstAmount;

            html += `
                <tr>
                    <td class="text-center fw-bold text-muted fs-8">${index + 1}</td>
                    <td>
                        <div class="fw-bold text-dark fs-7">${escapeHtml(item.product_name || 'Item')}</div>
                        ${item.remarks ? `<small class="text-muted fs-9">${escapeHtml(item.remarks)}</small>` : ''}
                    </td>
                    <td><span class="badge bg-light text-dark border fs-8">${escapeHtml(item.grade || '-')}</span></td>
                    <td><span class="badge bg-light text-dark border fs-8">${escapeHtml(item.size || '-')}</span></td>
                    <td class="text-end fw-bold text-dark fs-7">${formatNumber(qtyTons, 3)} MT</td>
                    <td class="text-end fw-semibold text-success fs-7">${formatNumber(dispatchedTons, 3)} MT</td>
                    <td class="text-end fw-bold ${pendingTons > 0 ? 'text-warning' : 'text-muted'} fs-7">${formatNumber(pendingTons, 3)} MT</td>
                    <td class="text-end fw-bold text-primary fs-7">₹${formatNumber(rate, 2)}</td>
                    <td class="text-end fw-bold text-dark fs-7">₹${formatNumber(lineEstAmount, 2)}</td>
                    <td class="text-center">
                        <span class="badge ${isItemDone ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'} rounded-pill px-2.5 py-1 fs-9 fw-bold">
                            ${isItemDone ? 'Dispatched' : 'Pending'}
                        </span>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;

        // Update KPI Metrics
        const totalEstWithGst = totalEstAmount * 1.18;
        document.getElementById("kpiOrderedQty").textContent = formatNumber(totalOrdered, 3);
        document.getElementById("kpiDispatchedQty").textContent = formatNumber(totalDispatched, 3);
        document.getElementById("kpiPendingQty").textContent = formatNumber(totalPending, 3);
        document.getElementById("kpiTotalAmount").textContent = `₹${formatNumber(totalEstAmount, 2)}`;
        
        const kpiGstEl = document.getElementById("kpiTotalWithGst");
        if (kpiGstEl) kpiGstEl.textContent = `₹${formatNumber(totalEstWithGst, 2)}`;
    }

    function renderDispatchedItems(data) {
        const tbody = document.getElementById("dispatchesTbody");
        const tfoot = document.getElementById("dispatchesTfoot");
        const items = data.dispatched_items || [];
        const summary = data.summary || {};

        document.getElementById("badgeDispatchesCount").textContent = `${items.length} Record${items.length === 1 ? '' : 's'}`;

        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center py-5 text-muted fs-7">
                        <i class="fa-solid fa-truck-ramp-box fs-3 mb-2 d-block text-secondary opacity-50"></i>
                        No items dispatched recorded yet for Sale Order #${SLID}.
                    </td>
                </tr>
            `;
            if (tfoot) tfoot.style.display = "none";
            document.getElementById("headerActualWt").textContent = "0.000 MT";
            document.getElementById("headerSubtotal").textContent = "₹0.00";
            document.getElementById("headerGstBadge").textContent = "+ 18% GST: ₹0.00";
            document.getElementById("headerGrandTotal").textContent = "₹0.00";
            return;
        }

        let html = '';
        items.forEach((disp, index) => {
            const planWt = parseFloat(disp.planned_weight_tons || 0);
            const actWt = parseFloat(disp.actual_weight_tons || 0);
            const actPcs = parseFloat(disp.actual_pcs || 0);
            const rate = parseFloat(disp.net_rate || 0);
            const subtotal = parseFloat(disp.subtotal || 0);

            html += `
                <tr>
                    <td class="text-center fw-bold text-muted fs-8">${index + 1}</td>
                    <td>
                        <div class="fw-semibold text-dark fs-7">${formatDate(disp.dispatch_date)}</div>
                        <small class="text-muted fs-9"><i class="fa-regular fa-clock me-1"></i>${formatTime(disp.dispatch_date)}</small>
                    </td>
                    <td>
                        <span class="badge bg-dark-subtle text-dark border rounded-2 px-2 py-1 fs-8 fw-bold">
                            <i class="fa-solid fa-truck me-1 text-primary"></i>${escapeHtml(disp.vehicle_no || 'N/A')}
                        </span>
                    </td>
                    <td class="fw-bold text-dark fs-7">${escapeHtml(disp.product_name || '-')}</td>
                    <td>
                        <span class="badge bg-light text-dark border fs-8">${escapeHtml(disp.grade || '-')}</span>
                        ${disp.brand ? `<small class="text-muted d-block fs-9">${escapeHtml(disp.brand)}</small>` : ''}
                    </td>
                    <td>
                        <div class="fs-8 fw-semibold text-dark">${escapeHtml(disp.size_name || '-')}</div>
                        ${disp.length ? `<small class="text-secondary fs-9">${escapeHtml(disp.length)}</small>` : ''}
                    </td>
                    <td class="text-end text-muted fs-7">${formatNumber(planWt, 3)} MT</td>
                    <td class="text-end fw-bold text-success fs-7">${formatNumber(actWt, 3)} MT</td>
                    <td class="text-center fw-bold text-dark fs-7">${formatNumber(actPcs, 0)}</td>
                    <td class="text-end fw-semibold text-primary fs-7">₹${formatNumber(rate, 2)}</td>
                    <td class="text-end fw-bold text-dark fs-7">₹${formatNumber(subtotal, 2)}</td>
                </tr>
            `;
        });

        tbody.innerHTML = html;

        // Footer Totals
        const actualWt = parseFloat(summary.actual_weight_tons || 0);
        const plannedWt = parseFloat(summary.planned_weight_tons || 0);
        const subtotalAmt = parseFloat(summary.subtotal_amount || 0);
        const gstAmt = parseFloat(summary.gst_18_percent || 0);
        const grandTotal = parseFloat(summary.grand_total_amount || 0);

        let totalPcsSum = items.reduce((sum, i) => sum + parseFloat(i.actual_pcs || 0), 0);

        document.getElementById("ftPlannedWt").textContent = `${formatNumber(plannedWt, 3)} MT`;
        document.getElementById("ftActualWt").textContent = `${formatNumber(actualWt, 3)} MT`;
        document.getElementById("ftActualPcs").textContent = formatNumber(totalPcsSum, 0);
        document.getElementById("ftSubtotalAmt").textContent = `₹${formatNumber(subtotalAmt, 2)}`;
        document.getElementById("ftGstAmt").textContent = `+ ₹${formatNumber(gstAmt, 2)}`;
        document.getElementById("ftGrandTotal").textContent = `₹${formatNumber(grandTotal, 2)}`;

        document.getElementById("headerActualWt").textContent = `${formatNumber(actualWt, 3)} MT`;
        document.getElementById("headerSubtotal").textContent = `₹${formatNumber(subtotalAmt, 2)}`;
        document.getElementById("headerGstBadge").textContent = `+ 18% GST: ₹${formatNumber(gstAmt, 2)}`;
        document.getElementById("headerGrandTotal").textContent = `₹${formatNumber(grandTotal, 2)}`;

        if (tfoot) tfoot.style.display = "table-footer-group";
    }

    function formatNumber(val, decimals = 2) {
        const num = parseFloat(val);
        if (isNaN(num)) return (0).toFixed(decimals);
        return num.toLocaleString('en-IN', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        try {
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        } catch (e) {
            return dateStr;
        }
    }

    function formatTime(dateStr) {
        if (!dateStr) return '';
        try {
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return '';
            return d.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
        } catch (e) {
            return '';
        }
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
