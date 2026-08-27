@extends('admin.layouts.app')

@section('title', 'Purchase Order #' . $poid . ' Details | Accounts ERP')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/purchase_dashboard.css') }}">
@endpush

@section('content')
<!-- Navigation Bar & Quick Actions -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom no-print">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('purchase.orders') }}" class="btn btn-white border bg-white shadow-sm btn-sm fw-semibold text-secondary rounded-3 px-3 py-2 fs-7">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Orders List
        </a>
        <div>
            <h2 class="fw-extrabold text-dark fs-4 mb-0">
                Purchase Order Details <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-3 px-2.5 py-1 fs-7 ms-2">#{{ $poid }}</span>
            </h2>
            <small class="text-muted fs-7">Complete Line Items Breakdown & Material Receipts Summary</small>
        </div>
    </div>

    <div class="d-flex align-items-center flex-wrap gap-2">
        <button class="btn btn-white border bg-white shadow-sm btn-sm fw-semibold text-secondary rounded-3 px-3 py-2 fs-7" onclick="loadOrderDetails()">
            <i class="fa-solid fa-rotate text-warning me-1" id="refreshBtnIcon"></i> Refresh Details
        </button>
        <button class="btn btn-warning btn-sm rounded-3 px-3 py-2 fw-semibold fs-7 shadow-sm text-dark" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print Purchase Order
        </button>
    </div>
</div>

<!-- SECTION 1: ORDER HEADER CARD -->
<div class="po-header-card mb-4">
    <div class="p-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-3 fs-7 fw-bold" id="badgePoNo">
                        #{{ $poid }}
                    </span>
                    <span class="badge bg-light text-dark px-3 py-1.5 rounded-3 fs-7 fw-bold" id="badgeStatus">
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
                <small class="text-warning fs-8 mt-1 d-block"><i class="fa-solid fa-file-invoice me-1"></i> Ref Sale Order: <span id="displayRefSaleOrder">-</span></small>
            </div>
        </div>
    </div>

    <!-- Summary Header Info Bar -->
    <div class="po-summary-grid">
        <div class="row g-3">
            <div class="col-md-3 col-6 border-end border-secondary border-opacity-25">
                <small class="text-white-50 text-uppercase fw-bold fs-9">Ref Sale Order #</small>
                <div class="fw-bold text-white fs-7 mt-0.5" id="displayRefSoGrid">-</div>
            </div>
            <div class="col-md-3 col-6 border-end border-secondary border-opacity-25">
                <small class="text-white-50 text-uppercase fw-bold fs-9">Basic Rate (₹/MT)</small>
                <div class="fw-bold text-warning fs-7 mt-0.5" id="displayBasePrice">₹0.00</div>
            </div>
            <div class="col-md-3 col-6 border-end border-secondary border-opacity-25">
                <small class="text-white-50 text-uppercase fw-bold fs-9">Total Ordered Qty</small>
                <div class="fw-extrabold text-white fs-7 mt-0.5" id="displayTotalQty">0.000 MT</div>
            </div>
            <div class="col-md-3 col-6">
                <small class="text-white-50 text-uppercase fw-bold fs-9">Line Items Count</small>
                <div class="fw-bold text-info fs-7 mt-0.5" id="displayItemsCount">0 Items</div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 2: 4 SUMMARY KPI METRIC CARDS -->
<div class="row g-3 mb-4">
    <!-- 1. Total Ordered Qty -->
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="kpi-detail-card border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-bold fs-8 text-uppercase letter-spacing-1">Total Ordered Qty</span>
                    <h3 class="fw-extrabold text-dark fs-3 mt-1 mb-0"><span id="kpiOrderedQty">0.000</span> <small class="fs-7 text-muted">MT</small></h3>
                </div>
                <div class="kpi-icon-box bg-warning-subtle text-warning rounded-3 p-3">
                    <i class="fa-solid fa-weight-hanging fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Received Qty -->
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="kpi-detail-card border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-bold fs-8 text-uppercase letter-spacing-1">Received Qty</span>
                    <h3 class="fw-extrabold text-success fs-3 mt-1 mb-0"><span id="kpiReceivedQty">0.000</span> <small class="fs-7 text-muted">MT</small></h3>
                </div>
                <div class="kpi-icon-box bg-success-subtle text-success rounded-3 p-3">
                    <i class="fa-solid fa-truck-ramp-box fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Pending Qty -->
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="kpi-detail-card border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-bold fs-8 text-uppercase letter-spacing-1">Pending Delivery</span>
                    <h3 class="fw-extrabold text-danger fs-3 mt-1 mb-0"><span id="kpiPendingQty">0.000</span> <small class="fs-7 text-muted">MT</small></h3>
                </div>
                <div class="kpi-icon-box bg-danger-subtle text-danger rounded-3 p-3">
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
                    <span class="text-muted fw-bold fs-8 text-uppercase letter-spacing-1">Est. Total Amount</span>
                    <h3 class="fw-extrabold text-dark fs-4 mt-1 mb-0" id="kpiTotalAmount">₹0.00</h3>
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
            <i class="fa-solid fa-boxes-stacked text-warning me-2"></i> Purchase Order Item Details
        </h5>
        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fs-8 fw-bold" id="badgeTableItemsCount">0 Items</span>
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
                    <th scope="col" class="text-end">Received (MT)</th>
                    <th scope="col" class="text-end">Pending (MT)</th>
                    <th scope="col" class="text-end">Item Rate (₹)</th>
                    <th scope="col" class="text-end">Est. Line Amount (₹)</th>
                    <th scope="col" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody id="lineItemsTbody">
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted fs-7">
                        <i class="fa-solid fa-circle-notch fa-spin text-warning fs-4 mb-2 d-block"></i>
                        Fetching Line Items Details for Order #{{ $poid }}...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- SECTION 4: RECEIVED MATERIAL HISTORY DATA TABLE -->
<div class="items-table-card shadow-sm mb-4">
    <div class="p-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3 bg-white">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold text-dark fs-6 mb-0">
                <i class="fa-solid fa-truck-ramp-box text-success me-2"></i> Received Material History
            </h5>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fs-8 fw-bold" id="badgeReceivedCount">0 Items</span>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap fs-7" id="receivedSummaryHeader">
            <span class="text-muted">Actual Received Wt: <strong class="text-dark" id="headerActualWt">0.000 MT</strong></span>
            <span class="text-muted">Total Amount: <strong class="text-success fs-6" id="headerSubtotal">₹0.00</strong></span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th scope="col" class="text-center" style="width: 50px;">#</th>
                    <th scope="col">Product Name</th>
                    <th scope="col">Grade / Brand</th>
                    <th scope="col">Size</th>
                    <th scope="col" class="text-end">Planned Qty (MT)</th>
                    <th scope="col" class="text-end">Actual Received Qty (MT)</th>
                    <th scope="col" class="text-end">Unit Price (₹)</th>
                    <th scope="col" class="text-end">Line Total (₹)</th>
                    <th scope="col" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody id="receivedMaterialTbody">
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted fs-7">
                        <i class="fa-solid fa-circle-notch fa-spin text-success fs-4 mb-2 d-block"></i>
                        Fetching Received Material Data for Order #{{ $poid }}...
                    </td>
                </tr>
            </tbody>
            <tfoot id="receivedMaterialTfoot" style="display: none;">
                <tr class="bg-light border-top">
                    <td colspan="4" class="text-end fw-bold text-dark fs-7">Item Totals:</td>
                    <td class="text-end fw-bold text-secondary fs-7" id="ftPlannedWt">0.000 MT</td>
                    <td class="text-end fw-bold text-success fs-7" id="ftActualWt">0.000 MT</td>
                    <td class="text-end fw-bold text-dark fs-7">Total Amount:</td>
                    <td class="text-end fw-extrabold text-success fs-6" id="ftSubtotalAmt">₹0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const POID = {{ $poid }};
    const API_ORDER_DETAILS = '/purchase/order-details';
    const API_RECEIVED_MATERIAL = '/purchase/received-material';

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
                    <i class="fa-solid fa-circle-notch fa-spin text-warning fs-4 mb-2 d-block"></i>
                    Fetching Line Items Details for Purchase Order #${POID}...
                </td>
            </tr>
        `;

        try {
            const res = await fetch(`${API_ORDER_DETAILS}?poid=${POID}`);
            const resData = await res.json();

            if (resData.status === "success" && resData.data) {
                const data = resData.data;
                const order = data.order || {};
                const summary = data.summary || {};
                const items = data.items || [];
                const receivedMaterial = data.received_material || {};

                // Header Info
                document.getElementById("badgePoNo").textContent = `#${order.poid || POID}`;
                document.getElementById("displayCustomerName").textContent = order.customer_name || "N/A";
                
                let metaParts = [];
                if (order.customer_gst) metaParts.push(`GSTIN: ${order.customer_gst}`);
                if (order.mobile) metaParts.push(`Mobile: ${order.mobile}`);
                if (order.contact_person) metaParts.push(`Contact: ${order.contact_person}`);
                if (order.address) metaParts.push(`Address: ${order.address}`);
                if (order.city) metaParts.push(`City: ${order.city}`);
                document.getElementById("displayCustomerMeta").textContent = metaParts.join(" | ") || 'N/A';

                document.getElementById("displayCreatedDate").textContent = order.createdon ? formatDate(order.createdon) : '-';
                document.getElementById("displayRefSaleOrder").textContent = order.refsaleorder ? `SO #${order.refsaleorder}` : 'N/A';
                document.getElementById("displayRefSoGrid").textContent = order.refsaleorder ? `SO #${order.refsaleorder}` : 'N/A';
                document.getElementById("displayBasePrice").textContent = `₹${formatNumber(order.basic_rate || order.basicprice, 2)}`;
                
                const totalOrdQty = summary.order_qty_tons !== undefined ? summary.order_qty_tons : (order.order_qty_tons || 0);
                document.getElementById("displayTotalQty").textContent = `${formatNumber(totalOrdQty, 3)} MT`;

                const isCompleted = parseFloat(summary.pending_qty_tons || 0) <= 0 || order.pmarkedcompleted === 1;
                document.getElementById("badgeStatus").textContent = order.status_label || (isCompleted ? 'Completed' : 'Pending');
                document.getElementById("badgeStatus").className = `badge ${isCompleted ? 'bg-success text-white' : 'bg-warning text-dark'} px-3 py-1.5 rounded-3 fs-7 fw-bold`;

                // Render Table 1: Line Items
                renderLineItems(items, summary);

                // Render Table 2: Received Material History
                if (receivedMaterial.items && receivedMaterial.items.length > 0) {
                    renderReceivedMaterial(receivedMaterial);
                } else {
                    loadReceivedMaterialDirect();
                }
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-5 text-danger fs-7">
                            <i class="fa-solid fa-triangle-exclamation fs-3 mb-2 d-block"></i>
                            Failed to load details for Purchase Order #${POID}.
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
                        Unable to connect to Purchase API server (http://localhost/erpdivine/api/). Please Check.
                    </td>
                </tr>
            `;
        }
    }

    async function loadReceivedMaterialDirect() {
        const tbody = document.getElementById("receivedMaterialTbody");
        const tfoot = document.getElementById("receivedMaterialTfoot");
        if (tfoot) tfoot.style.display = "none";

        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-5 text-muted fs-7">
                    <i class="fa-solid fa-circle-notch fa-spin text-success fs-4 mb-2 d-block"></i>
                    Fetching Received Material History for Purchase Order #${POID}...
                </td>
            </tr>
        `;

        try {
            const res = await fetch(`${API_RECEIVED_MATERIAL}?poid=${POID}`);
            const resData = await res.json();

            if (resData.status === "success" && resData.data) {
                renderReceivedMaterial(resData.data);
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted fs-7">
                            <i class="fa-solid fa-truck-ramp-box fs-3 mb-2 d-block text-secondary opacity-50"></i>
                            No received material recorded yet for Purchase Order #${POID}.
                        </td>
                    </tr>
                `;
                document.getElementById("badgeReceivedCount").textContent = "0 Items";
            }
        } catch (err) {
            console.error("Error loading received material:", err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-5 text-danger fs-7">
                        <i class="fa-solid fa-exclamation-triangle fs-3 mb-2 d-block"></i>
                        Unable to load received material data from API.
                    </td>
                </tr>
            `;
        }
    }

    function renderLineItems(items, summary) {
        const tbody = document.getElementById("lineItemsTbody");
        document.getElementById("displayItemsCount").textContent = `${items.length} Items`;
        document.getElementById("badgeTableItemsCount").textContent = `${items.length} Items`;

        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted fs-7">
                        <i class="fa-solid fa-box-open fs-3 mb-2 d-block text-secondary opacity-50"></i>
                        No line items found for this Purchase Order.
                    </td>
                </tr>
            `;
            return;
        }

        let totalOrdered = 0;
        let totalReceived = 0;
        let totalPending = 0;
        let totalEstAmount = 0;

        let html = '';
        items.forEach((item, index) => {
            const qtyTons = parseFloat(item.weightintons || item.qty || 0);
            const receivedTons = parseFloat(item.received_qty_tons || 0);
            const pendingTons = parseFloat(item.pending_qty_tons || 0);
            const rate = parseFloat(item.poprice || 0);
            const lineEstAmount = qtyTons * rate;
            const isItemDone = pendingTons <= 0 || item.completed === 1;

            totalOrdered += qtyTons;
            totalReceived += receivedTons;
            totalPending += pendingTons;
            totalEstAmount += lineEstAmount;

            html += `
                <tr>
                    <td class="text-center fw-bold text-muted fs-8">${index + 1}</td>
                    <td>
                        <div class="fw-bold text-dark fs-7">${escapeHtml(item.productname || item.product_name || 'Item')}</div>
                        ${item.itemremarks ? `<small class="text-muted fs-9">${escapeHtml(item.itemremarks)}</small>` : ''}
                    </td>
                    <td><span class="badge bg-light text-dark border fs-8">${escapeHtml(item.grade || '-')}</span></td>
                    <td><span class="badge bg-light text-dark border fs-8">${escapeHtml(item.size || '-')}</span></td>
                    <td class="text-end fw-bold text-dark fs-7">${formatNumber(qtyTons, 3)} MT</td>
                    <td class="text-end fw-semibold text-success fs-7">${formatNumber(receivedTons, 3)} MT</td>
                    <td class="text-end fw-bold ${pendingTons > 0 ? 'text-danger' : 'text-muted'} fs-7">${formatNumber(pendingTons, 3)} MT</td>
                    <td class="text-end fw-bold text-warning fs-7">₹${formatNumber(rate, 2)}</td>
                    <td class="text-end fw-bold text-dark fs-7">₹${formatNumber(lineEstAmount, 2)}</td>
                    <td class="text-center">
                        <span class="badge ${isItemDone ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'} rounded-pill px-2.5 py-1 fs-9 fw-bold">
                            ${isItemDone ? 'Received' : 'Pending'}
                        </span>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;

        // Summary metric values
        if (summary) {
            if (summary.order_qty_tons !== undefined) totalOrdered = parseFloat(summary.order_qty_tons);
            if (summary.received_qty_tons !== undefined) totalReceived = parseFloat(summary.received_qty_tons);
            if (summary.pending_qty_tons !== undefined) totalPending = parseFloat(summary.pending_qty_tons);
        }

        // Update KPI Metrics
        document.getElementById("kpiOrderedQty").textContent = formatNumber(totalOrdered, 3);
        document.getElementById("kpiReceivedQty").textContent = formatNumber(totalReceived, 3);
        document.getElementById("kpiPendingQty").textContent = formatNumber(totalPending, 3);
        document.getElementById("kpiTotalAmount").textContent = `₹${formatNumber(totalEstAmount, 2)}`;
    }

    function renderReceivedMaterial(data) {
        const tbody = document.getElementById("receivedMaterialTbody");
        const tfoot = document.getElementById("receivedMaterialTfoot");
        const items = data.received_material || data.items || [];
        const summary = data.summary || {};

        document.getElementById("badgeReceivedCount").textContent = `${items.length} Item${items.length === 1 ? '' : 's'}`;

        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted fs-7">
                        <i class="fa-solid fa-truck-ramp-box fs-3 mb-2 d-block text-secondary opacity-50"></i>
                        No received material recorded yet for Purchase Order #${POID}.
                    </td>
                </tr>
            `;
            if (tfoot) tfoot.style.display = "none";
            document.getElementById("headerActualWt").textContent = "0.000 MT";
            document.getElementById("headerSubtotal").textContent = "₹0.00";
            return;
        }

        let html = '';
        let totalPlanWt = 0;
        let totalActualWt = 0;
        let totalSubtotal = 0;

        items.forEach((item, index) => {
            const planWt = parseFloat(item.qty || 0);
            const actWt = parseFloat(item.actual || item.actual_qty || 0);
            const price = parseFloat(item.price || 0);
            const lineTotal = parseFloat(item.line_total || (actWt * price) || 0);
            const isCompleted = item.completed === 1;

            totalPlanWt += planWt;
            totalActualWt += actWt;
            totalSubtotal += lineTotal;

            html += `
                <tr>
                    <td class="text-center fw-bold text-muted fs-8">${index + 1}</td>
                    <td>
                        <div class="fw-bold text-dark fs-7">${escapeHtml(item.productname || item.product_name || 'Item')}</div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border fs-8">${escapeHtml(item.grade || '-')}</span>
                        ${item.brandname ? `<small class="text-muted d-block fs-9">${escapeHtml(item.brandname)}</small>` : ''}
                    </td>
                    <td>
                        <div class="fs-8 fw-semibold text-dark">${escapeHtml(item.size || '-')}</div>
                    </td>
                    <td class="text-end text-muted fs-7">${formatNumber(planWt, 3)} MT</td>
                    <td class="text-end fw-bold text-success fs-7">${formatNumber(actWt, 3)} MT</td>
                    <td class="text-end fw-semibold text-warning fs-7">₹${formatNumber(price, 2)}</td>
                    <td class="text-end fw-bold text-dark fs-7">₹${formatNumber(lineTotal, 2)}</td>
                    <td class="text-center">
                        <span class="badge ${isCompleted ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'} rounded-pill px-2 py-1 fs-9 fw-bold">
                            ${isCompleted ? 'Completed' : 'Received'}
                        </span>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;

        // Summary Calculations
        const actualWt = summary.total_actual !== undefined ? parseFloat(summary.total_actual) : totalActualWt;
        const plannedWt = summary.total_qty !== undefined ? parseFloat(summary.total_qty) : totalPlanWt;
        const subtotalAmt = summary.total_amount !== undefined ? parseFloat(summary.total_amount) : totalSubtotal;

        document.getElementById("ftPlannedWt").textContent = `${formatNumber(plannedWt, 3)} MT`;
        document.getElementById("ftActualWt").textContent = `${formatNumber(actualWt, 3)} MT`;
        document.getElementById("ftSubtotalAmt").textContent = `₹${formatNumber(subtotalAmt, 2)}`;

        document.getElementById("headerActualWt").textContent = `${formatNumber(actualWt, 3)} MT`;
        document.getElementById("headerSubtotal").textContent = `₹${formatNumber(subtotalAmt, 2)}`;

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
