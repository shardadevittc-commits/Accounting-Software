@extends('admin.layouts.app')

@section('title', 'Sale Orders List | Accounts ERP')

@push('styles')
<!-- Select2 Searchable Dropdown CSS & Bootstrap 5 Theme -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/css/sales_dashboard.css') }}">
@endpush

@section('content')
<!-- Title Bar & Top Quick Action Buttons -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom">
    <div>
        <h2 class="fw-extrabold text-dark fs-4 mb-0">
            <i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i> Sale Orders List
        </h2>
        <small class="text-muted fs-7">Select a customer from the dropdown below to view their Sales Orders, Line Items & Dispatch Summary</small>
    </div>

    <div class="d-flex align-items-center flex-wrap gap-2">
        <button class="btn btn-white border bg-white shadow-sm btn-sm fw-semibold text-secondary rounded-3 px-3 py-2 fs-7" onclick="fetchCustomersAndOrders()">
            <i class="fa-solid fa-rotate text-primary me-1" id="refreshBtnIcon"></i> Refresh Data
        </button>
        <!-- <button class="btn btn-primary btn-sm rounded-3 px-3 py-2 fw-semibold fs-7 shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Create Sale Order
        </button> -->
    </div>
</div>

<!-- SECTION 1: CUSTOMER SELECTION DROPDOWN & INFO CARD -->
<div class="sales-filter-card mb-4">
    <div class="row g-3 align-items-center">
        <!-- Customer Dropdown Selector -->
        <div class="col-lg-6 col-md-7">
            <label for="customerSelect" class="form-label fw-bold fs-7 text-uppercase letter-spacing-1 text-secondary">
                <i class="fa-solid fa-user-tag text-primary me-1"></i> Select Customer / Party Name:
            </label>
            <div>
                <select id="customerSelect" class="form-select py-2 fs-7 fw-semibold shadow-none" onchange="onCustomerChange()">
                    <option value="">-- All Customers (Select Customer to Filter) --</option>
                </select>
            </div>
        </div>

        <!-- Filter Action Controls -->
        <div class="col-lg-6 col-md-5 d-flex align-items-end justify-content-md-end gap-2 pt-2 pt-md-0">
            <button id="clearCustomerBtn" class="btn btn-light border btn-sm rounded-3 px-3 py-2 fw-semibold text-secondary fs-7 d-none" onclick="clearCustomerFilter()">
                <i class="fa-solid fa-xmark me-1 text-danger"></i> Clear Selection
            </button>
            <div class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-2 fs-7 fw-bold" id="customerCountBadge">
                Loading Customers...
            </div>
        </div>
    </div>

    <!-- Active Customer Profile Details Banner (Visible when a customer is picked) -->
    <div id="customerInfoBanner" class="customer-info-banner mt-3 d-none">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-box bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm" style="width: 46px; height: 46px;" id="custAvatar">
                    CU
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-1 fs-6" id="custNameDisplay">Customer Name</h5>
                    <div class="d-flex align-items-center flex-wrap gap-3 fs-8 text-secondary">
                        <span><i class="fa-solid fa-id-card text-muted me-1"></i> <strong>Party Code:</strong> <span id="custPCodeDisplay">-</span></span>
                        <span><i class="fa-solid fa-file-invoice text-muted me-1"></i> <strong>GSTIN:</strong> <span id="custGstDisplay">-</span></span>
                        <span><i class="fa-solid fa-location-dot text-muted me-1"></i> <strong>City:</strong> <span id="custCityDisplay">-</span></span>
                        <span><i class="fa-solid fa-phone text-muted me-1"></i> <strong>Mobile:</strong> <span id="custMobileDisplay">-</span></span>
                    </div>
                </div>
            </div>
            <div class="text-md-end">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold fs-8">
                    <i class="fa-solid fa-circle-check me-1"></i> Active Account
                </span>
                <div class="fs-9 text-muted mt-1" id="custAddressDisplay">Address Details...</div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 2: 4 SUMMARY KPI CARDS -->
<div class="row g-3 mb-4">
    <!-- 1. Total Sale Orders -->
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="kpi-stat-card border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-bold fs-8 text-uppercase letter-spacing-1">Total Sales Orders</span>
                    <h3 class="fw-extrabold text-dark fs-3 mt-1 mb-0" id="kpiTotalOrders">0</h3>
                </div>
                <div class="kpi-icon-box bg-primary-subtle text-primary">
                    <i class="fa-solid fa-boxes-packing"></i>
                </div>
            </div>
            <div class="fs-8 text-muted mt-2">
                <i class="fa-solid fa-layer-group me-1 text-primary"></i> <span id="kpiOrdersSubtext">Count of orders placed</span>
            </div>
        </div>
    </div>

    <!-- 2. Total Ordered Qty (Tons) -->
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="kpi-stat-card border-start border-4 border-info">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-bold fs-8 text-uppercase letter-spacing-1">Total Ordered Qty</span>
                    <h3 class="fw-extrabold text-dark fs-3 mt-1 mb-0"><span id="kpiOrderedTons">0.000</span> <small class="fs-7 text-muted">MT</small></h3>
                </div>
                <div class="kpi-icon-box bg-info-subtle text-info">
                    <i class="fa-solid fa-weight-hanging"></i>
                </div>
            </div>
            <div class="fs-8 text-muted mt-2">
                <i class="fa-solid fa-scale-balanced me-1 text-info"></i> Total Metric Tons Booked
            </div>
        </div>
    </div>

    <!-- 3. Total Dispatched Qty (Tons) -->
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="kpi-stat-card border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-bold fs-8 text-uppercase letter-spacing-1">Dispatched Qty</span>
                    <h3 class="fw-extrabold text-success fs-3 mt-1 mb-0"><span id="kpiDispatchedTons">0.000</span> <small class="fs-7 text-muted">MT</small></h3>
                </div>
                <div class="kpi-icon-box bg-success-subtle text-success">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
            </div>
            <div class="fs-8 text-muted mt-2">
                <i class="fa-solid fa-circle-check me-1 text-success"></i> Materials Dispatched
            </div>
        </div>
    </div>

    <!-- 4. Total Pending Qty (Tons) -->
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="kpi-stat-card border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-bold fs-8 text-uppercase letter-spacing-1">Pending Delivery Qty</span>
                    <h3 class="fw-extrabold text-warning fs-3 mt-1 mb-0"><span id="kpiPendingTons">0.000</span> <small class="fs-7 text-muted">MT</small></h3>
                </div>
                <div class="kpi-icon-box bg-warning-subtle text-warning">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
            <div class="fs-8 text-muted mt-2">
                <i class="fa-solid fa-clock me-1 text-warning"></i> Balance Qty to Dispatch
            </div>
        </div>
    </div>
</div>

<!-- SECTION 3: ACCOUNTING SALES ORDERS DATA TABLE CARD -->
<div class="custom-sales-table shadow-sm mb-4">
    <!-- Table Header Controls Bar -->
    <div class="p-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3 bg-white">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold text-dark fs-6 mb-0">
                <i class="fa-solid fa-list-check text-primary me-2"></i> Sales Orders List
            </h5>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1 fs-8 fw-bold" id="ordersRecordCount">0 Orders</span>
        </div>

        <div class="d-flex align-items-center flex-wrap gap-2">
            <!-- Search Inside Orders Table -->
            <div class="search-box-wrapper" style="width: 220px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="tableSearchInput" class="top-search-input" placeholder="Search SO#, PO#, Party..." onkeyup="filterTableRows()">
            </div>

            <!-- Status Filter Tabs -->
            <div class="btn-group btn-group-sm" role="group" aria-label="Status Filters">
                <button type="button" class="btn btn-outline-primary active fw-semibold fs-8" id="filterAllBtn" onclick="setStatusFilter('all')">All</button>
                <button type="button" class="btn btn-outline-primary fw-semibold fs-8" id="filterPendingBtn" onclick="setStatusFilter('pending')">Pending</button>
                <button type="button" class="btn btn-outline-primary fw-semibold fs-8" id="filterCompletedBtn" onclick="setStatusFilter('completed')">Completed</button>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th scope="col" style="width: 100px;">SO #</th>
                    <th scope="col">Order Date</th>
                    <th scope="col">Customer / Party Name</th>
                    <th scope="col">Cust PO #</th>
                    <th scope="col" class="text-end">Ordered Qty</th>
                    <th scope="col" class="text-end">Dispatched</th>
                    <th scope="col" class="text-end">Pending Qty</th>
                    <th scope="col" class="text-end">Base Rate</th>
                    <th scope="col" class="text-center">Status</th>
                    <th scope="col" class="text-center" style="width: 110px;">Action</th>
                </tr>
            </thead>
            <tbody id="salesOrdersTbody">
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted fs-7">
                        <i class="fa-solid fa-circle-notch fa-spin text-primary fs-4 mb-2 d-block"></i>
                        Fetching Sales Orders from API...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<!-- jQuery & Select2 JS CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Global State
    let customersList = [];
    let salesOrdersList = [];
    let selectedCustomerId = "";
    let activeStatusFilter = "all";

    // Direct API URLs
    const API_CUSTOMERS = '/sales/customers';
    const API_ORDERS = '/sales/orders';
    const API_ORDER_DETAILS = '/sales/order-details';

    document.addEventListener("DOMContentLoaded", function () {
        fetchCustomersAndOrders();
    });

    // 1. Fetch Customers and Initial Sales Orders List
    async function fetchCustomersAndOrders() {
        const refreshIcon = document.getElementById("refreshBtnIcon");
        if (refreshIcon) refreshIcon.classList.add("fa-spin");

        try {
            // Fetch Customers List
            const custRes = await fetch(API_CUSTOMERS);
            const custData = await custRes.json();
            
            // Format customer data (supports both raw array or wrapped {data: [...]})
            if (Array.isArray(custData)) {
                customersList = custData;
            } else if (custData && Array.isArray(custData.data)) {
                customersList = custData.data;
            } else {
                customersList = [];
            }

            populateCustomerDropdown(customersList);

            // Fetch All Sales Orders
            await fetchSalesOrders();
        } catch (err) {
            console.error("Error loading sales dashboard APIs:", err);
            document.getElementById("salesOrdersTbody").innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-5 text-danger fs-7">
                        <i class="fa-solid fa-triangle-exclamation fs-3 mb-2 d-block"></i>
                        Unable to connect to Sales API server (http://localhost/erpdivine/api/). Please Check.
                    </td>
                </tr>
            `;
        } finally {
            if (refreshIcon) refreshIcon.classList.remove("fa-spin");
        }
    }

    // 2. Populate Customer Dropdown & Initialize Select2 Live Search
    function populateCustomerDropdown(customers) {
        const select = document.getElementById("customerSelect");
        const countBadge = document.getElementById("customerCountBadge");

        select.innerHTML = '<option value="">-- All Customers (Select Customer to Filter) --</option>';
        
        customers.forEach(c => {
            const opt = document.createElement("option");
            opt.value = c.cust_id;
            const codeGst = c.gst ? `[GST: ${c.gst}]` : (c.p_code ? `[Code: ${c.p_code}]` : '');
            const city = c.city ? ` - ${c.city}` : '';
            opt.textContent = `${c.name} ${codeGst}${city}`;
            select.appendChild(opt);
        });

        countBadge.textContent = `${customers.length} Customers Loaded`;

        // Initialize / Refresh Select2 Searchable Dropdown
        if (window.jQuery && $.fn.select2) {
            $('#customerSelect').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Search or Select Customer (Filter by Name, GST, City) --',
                allowClear: true,
                width: '100%'
            }).off('change.select2').on('change.select2', function () {
                onCustomerChange();
            });
        }
    }

    // 3. Handle Customer Selection Change
    async function onCustomerChange() {
        const select = document.getElementById("customerSelect");
        selectedCustomerId = select.value;

        const clearBtn = document.getElementById("clearCustomerBtn");
        const infoBanner = document.getElementById("customerInfoBanner");

        if (selectedCustomerId) {
            clearBtn.classList.remove("d-none");
            
            // Find selected customer details
            const cust = customersList.find(c => String(c.cust_id) === String(selectedCustomerId));
            if (cust) {
                document.getElementById("custAvatar").textContent = cust.name ? cust.name.substring(0, 2).toUpperCase() : "CU";
                document.getElementById("custNameDisplay").textContent = cust.name || 'Unnamed Customer';
                document.getElementById("custPCodeDisplay").textContent = cust.p_code || '-';
                document.getElementById("custGstDisplay").textContent = cust.gst || 'N/A';
                document.getElementById("custCityDisplay").textContent = cust.city || (cust.state ? cust.state : '-');
                document.getElementById("custMobileDisplay").textContent = cust.mobile || 'N/A';
                document.getElementById("custAddressDisplay").textContent = cust.address ? `${cust.address}, ${cust.city || ''} ${cust.state || ''}` : '';
                infoBanner.classList.remove("d-none");
            }
        } else {
            clearBtn.classList.add("d-none");
            infoBanner.classList.add("d-none");
        }

        await fetchSalesOrders();
    }

    // Clear Customer Filter Button
    async function clearCustomerFilter() {
        if (window.jQuery && $.fn.select2) {
            $('#customerSelect').val('').trigger('change.select2');
        } else {
            document.getElementById("customerSelect").value = "";
            await onCustomerChange();
        }
    }

    // 4. Fetch Sales Orders (Filtered by Customer if selected)
    async function fetchSalesOrders() {
        const tbody = document.getElementById("salesOrdersTbody");
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center py-5 text-muted fs-7">
                    <i class="fa-solid fa-circle-notch fa-spin text-primary fs-4 mb-2 d-block"></i>
                    Fetching Sales Orders...
                </td>
            </tr>
        `;

        try {
            let url = API_ORDERS;
            if (selectedCustomerId) {
                url += `?cust_id=${selectedCustomerId}`;
            }

            const res = await fetch(url);
            const resData = await res.json();

            if (resData.status === "success" || Array.isArray(resData.data)) {
                salesOrdersList = resData.data || [];
                updateKpis(resData.totals, salesOrdersList);
                renderSalesOrdersTable(salesOrdersList);
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-4 text-warning fs-7">
                            <i class="fa-solid fa-info-circle me-1"></i> No sales orders found.
                        </td>
                    </tr>
                `;
            }
        } catch (err) {
            console.error("Error fetching orders:", err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-4 text-danger fs-7">
                        <i class="fa-solid fa-exclamation-triangle me-1"></i> Failed to fetch orders data.
                    </td>
                </tr>
            `;
        }
    }

    // 5. Update KPI Cards
    function updateKpis(totals, orders) {
        let totalOrders = 0;
        let totalOrderedTons = 0;
        let totalDispatchedTons = 0;
        let totalPendingTons = 0;

        if (totals) {
            totalOrders = totals.total_orders || 0;
            totalOrderedTons = totals.total_ordered_tons || 0;
            totalDispatchedTons = totals.total_dispatched_tons || 0;
            totalPendingTons = totals.total_pending_tons || 0;
        } else {
            totalOrders = orders.length;
            orders.forEach(o => {
                totalOrderedTons += parseFloat(o.ordered_qty_tons || 0);
                totalDispatchedTons += parseFloat(o.dispatched_qty_tons || 0);
                totalPendingTons += parseFloat(o.pending_qty_tons || 0);
            });
        }

        document.getElementById("kpiTotalOrders").textContent = totalOrders;
        document.getElementById("kpiOrderedTons").textContent = formatNumber(totalOrderedTons, 3);
        document.getElementById("kpiDispatchedTons").textContent = formatNumber(totalDispatchedTons, 3);
        document.getElementById("kpiPendingTons").textContent = formatNumber(totalPendingTons, 3);

        const subtext = selectedCustomerId ? "Filtered for selected customer" : "All customer orders";
        document.getElementById("kpiOrdersSubtext").textContent = subtext;
    }

    // 6. Render Sales Orders Table Rows
    function renderSalesOrdersTable(orders) {
        const tbody = document.getElementById("salesOrdersTbody");
        document.getElementById("ordersRecordCount").textContent = `${orders ? orders.length : 0} Orders`;

        if (!orders || orders.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted fs-7">
                        <i class="fa-solid fa-box-open fs-3 mb-2 d-block text-secondary opacity-50"></i>
                        No Sale Orders record available for this selection.
                    </td>
                </tr>
            `;
            return;
        }

        // Sort orders ascending by Order ID (1, 2, 3, 4, 5...)
        const sortedOrders = [...orders].sort((a, b) => parseInt(a.slid || 0) - parseInt(b.slid || 0));

        let html = '';
        sortedOrders.forEach(o => {
            // Apply Status Filter
            const pendingQty = parseFloat(o.pending_qty_tons || 0);
            const isCompleted = pendingQty <= 0;
            const statusLabel = o.status_label || (isCompleted ? 'Completed' : 'Pending');

            if (activeStatusFilter === 'pending' && isCompleted) return;
            if (activeStatusFilter === 'completed' && !isCompleted) return;

            const badgeClass = isCompleted ? 'bg-success-subtle text-success border-success-subtle' : 'bg-warning-subtle text-warning border-warning-subtle';
            const formattedDate = formatDate(o.createdon);
            const formattedDispatch = o.dispatch_on ? formatDate(o.dispatch_on) : '-';

            html += `
                <tr onclick="window.open('/sale-order-details/${o.slid}', '_blank')">
                    <td>
                        <span class="so-badge bg-primary-subtle text-primary border border-primary-subtle">
                            #${o.slid}
                        </span>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark fs-8">${formattedDate}</div>
                        <small class="text-muted fs-9"><i class="fa-solid fa-truck-fast text-info me-1"></i>Disp: ${formattedDispatch}</small>
                    </td>
                    <td>
                        <div class="fw-bold text-dark fs-7">${escapeHtml(o.customer_name || 'N/A')}</div>
                        <small class="text-muted fs-9">${o.customer_gst ? 'GST: ' + escapeHtml(o.customer_gst) : ''}</small>
                    </td>
                    <td>
                        <span class="badge bg-light text-secondary border fw-mono fs-8">
                            ${o.cust_po ? escapeHtml(o.cust_po) : 'N/A'}
                        </span>
                    </td>
                    <td class="text-end fw-bold text-dark fs-7">${formatNumber(o.ordered_qty_tons, 3)} MT</td>
                    <td class="text-end fw-semibold text-success fs-7">${formatNumber(o.dispatched_qty_tons, 3)} MT</td>
                    <td class="text-end fw-bold ${pendingQty > 0 ? 'text-warning' : 'text-muted'} fs-7">${formatNumber(o.pending_qty_tons, 3)} MT</td>
                    <td class="text-end fw-bold text-primary fs-7">₹${formatNumber(o.bprice, 2)}</td>
                    <td class="text-center">
                        <span class="badge ${badgeClass} border px-2.5 py-1.5 rounded-pill fs-8">
                            ${statusLabel}
                        </span>
                    </td>
                    <td class="text-center" onclick="event.stopPropagation()">
                        <a href="/sale-order-details/${o.slid}" target="_blank" class="btn btn-primary btn-sm rounded-3 px-2.5 py-1 fs-8 text-decoration-none" title="View Order Details in New Tab">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Items (${o.item_count || 0})
                        </a>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html || `
            <tr>
                <td colspan="10" class="text-center py-4 text-muted fs-7">
                    No orders match the active filter criteria.
                </td>
            </tr>
        `;
    }

    // 7. Status Filter Button Toggles
    function setStatusFilter(type) {
        activeStatusFilter = type;
        document.getElementById("filterAllBtn").classList.toggle("active", type === "all");
        document.getElementById("filterPendingBtn").classList.toggle("active", type === "pending");
        document.getElementById("filterCompletedBtn").classList.toggle("active", type === "completed");
        renderSalesOrdersTable(salesOrdersList);
    }

    // 8. Client Side Search inside Table
    function filterTableRows() {
        const query = document.getElementById("tableSearchInput").value.toLowerCase().trim();
        if (!query) {
            renderSalesOrdersTable(salesOrdersList);
            return;
        }

        const filtered = salesOrdersList.filter(o => {
            const slidMatch = `so-${o.slid}`.toLowerCase().includes(query) || String(o.slid).includes(query);
            const nameMatch = (o.customer_name || '').toLowerCase().includes(query);
            const poMatch = (o.cust_po || '').toLowerCase().includes(query);
            const gstMatch = (o.customer_gst || '').toLowerCase().includes(query);
            return slidMatch || nameMatch || poMatch || gstMatch;
        });

        renderSalesOrdersTable(filtered);
    }

    // 9. Fetch Sale Order Details & Line Items (Modal View)
    async function openSaleOrderDetails(slid) {
        const modalElement = document.getElementById("saleOrderDetailsModal");
        const modal = new bootstrap.Modal(modalElement);
        
        // Reset Modal content to loading state
        document.getElementById("modalSoBadge").textContent = `#${slid}`;
        document.getElementById("modalCustomerName").textContent = "Loading Order Details...";
        document.getElementById("modalCustomerMeta").textContent = "GSTIN: - | City: -";
        document.getElementById("modalCustPo").textContent = "-";
        document.getElementById("modalDispatchDate").textContent = "-";
        document.getElementById("modalBasePrice").textContent = "₹0.00";
        document.getElementById("modalTotalQty").textContent = "0.000 MT";
        document.getElementById("modalItemsCount").textContent = "0 Items";
        document.getElementById("modalLineItemsTbody").innerHTML = `
            <tr>
                <td colspan="10" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-circle-notch fa-spin text-primary fs-4 mb-2 d-block"></i>
                    Fetching Line Items for Order #${slid}...
                </td>
            </tr>
        `;

        modal.show();

        try {
            const res = await fetch(`${API_ORDER_DETAILS}?slid=${slid}`);
            const resData = await res.json();

            if (resData.status === "success" && resData.data) {
                const order = resData.data;
                
                document.getElementById("modalSoBadge").textContent = `#${order.slid}`;
                document.getElementById("modalCustomerName").textContent = order.customer_name || "N/A";
                document.getElementById("modalCustomerMeta").textContent = `GSTIN: ${order.customer_gst || 'N/A'} | Address: ${order.customer_address || 'N/A'}`;
                document.getElementById("modalCustPo").textContent = order.cust_po || "N/A";
                document.getElementById("modalDispatchDate").textContent = order.dispatch_on ? formatDate(order.dispatch_on) : "N/A";
                document.getElementById("modalBasePrice").textContent = `₹${formatNumber(order.bprice, 2)}`;
                document.getElementById("modalTotalQty").textContent = `${formatNumber(order.total_ordered_tons, 3)} MT`;

                const isCompleted = parseFloat(order.total_pending_tons || 0) <= 0;
                document.getElementById("modalStatusBadge").textContent = order.status_label || (isCompleted ? 'Completed' : 'Pending');
                document.getElementById("modalStatusBadge").className = `badge ${isCompleted ? 'bg-success text-white' : 'bg-warning text-dark'} px-2.5 py-1 rounded-3 fs-8 fw-bold`;

                // Render Line Items
                renderModalLineItems(order.items || []);
            } else {
                document.getElementById("modalLineItemsTbody").innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-4 text-danger">
                            Failed to load details for Sale Order #${slid}.
                        </td>
                    </tr>
                `;
            }
        } catch (err) {
            console.error("Error fetching order details:", err);
            document.getElementById("modalLineItemsTbody").innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-4 text-danger">
                        Error fetching order details from API.
                    </td>
                </tr>
            `;
        }
    }

    // 10. Render Line Items inside Modal Table
    function renderModalLineItems(items) {
        const tbody = document.getElementById("modalLineItemsTbody");
        document.getElementById("modalItemsCount").textContent = `${items.length} Items`;

        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-4 text-muted">
                        No line items found for this Sale Order.
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        items.forEach((item, index) => {
            const qtyTons = parseFloat(item.ordered_qty_tons || 0);
            const rate = parseFloat(item.rate || 0);
            const lineEstAmount = qtyTons * rate;
            const isItemDone = parseFloat(item.pending_qty_tons || 0) <= 0;

            html += `
                <tr>
                    <td class="text-center fw-bold text-muted fs-8">${index + 1}</td>
                    <td>
                        <div class="fw-bold text-dark fs-7">${escapeHtml(item.product_name || 'Item')}</div>
                        ${item.remarks ? `<small class="text-muted fs-9">${escapeHtml(item.remarks)}</small>` : ''}
                    </td>
                    <td><span class="badge bg-light text-dark border fs-8">${escapeHtml(item.grade || '-')}</span></td>
                    <td><span class="badge bg-light text-dark border fs-8">${escapeHtml(item.size || '-')}</span></td>
                    <td class="text-end fw-bold text-dark fs-7">${formatNumber(item.ordered_qty_tons, 3)} MT</td>
                    <td class="text-end fw-semibold text-success fs-7">${formatNumber(item.dispatched_qty_tons, 3)} MT</td>
                    <td class="text-end fw-bold ${item.pending_qty_tons > 0 ? 'text-warning' : 'text-muted'} fs-7">${formatNumber(item.pending_qty_tons, 3)} MT</td>
                    <td class="text-end fw-bold text-primary fs-7">₹${formatNumber(item.rate, 2)}</td>
                    <td class="text-end fw-bold text-dark fs-7">₹${formatNumber(lineEstAmount, 2)}</td>
                    <td class="text-center">
                        <span class="badge ${isItemDone ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'} rounded-pill px-2 py-1 fs-9 fw-bold">
                            ${isItemDone ? 'Dispatched' : 'Pending'}
                        </span>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    // Helper Utilities
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
