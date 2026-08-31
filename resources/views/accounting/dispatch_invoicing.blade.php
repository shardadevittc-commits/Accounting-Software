@extends('admin.layouts.app')

@section('title', 'Dispatch Invoicing | Accounts ERP')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/accounting_dashboard.css') }}">
@endpush

@section('content')
<!-- Accountant Header Bar -->
<div class="accountant-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h4 class="mb-1 fw-bold text-white d-flex align-items-center gap-2">
            <i class="fa-solid fa-keyboard text-info"></i> Dispatch Sales Invoicing 
            <!-- <span class="badge bg-info text-dark fs-8">Keyboard-First UI</span> -->
        </h4>
        <small class="text-muted">Generate Tax Invoices directly for dispatched vehicles. Use <b>Arrow Keys</b> to navigate queue & <b>Enter</b> key to advance fields.</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-info btn-sm fw-bold px-3 py-2" onclick="loadPendingVehicles()">
            <i class="fa-solid fa-rotate me-1" id="refreshIcon"></i> Refresh Queue <span class="hotkey-badge ms-1">F5</span>
        </button>
    </div>
</div>

<div class="row g-3">
    <!-- Left Column: Pending Dispatched Vehicles List (Keyboard Navigable) -->
    <div class="col-lg-4 col-xl-3">
        <div class="pending-queue-card">
            <div class="pending-queue-header">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-white fs-7 text-uppercase"><i class="fa-solid fa-truck-dispatch text-warning me-1"></i> Pending Dispatches</span>
                    <span class="badge bg-warning text-dark fw-bold" id="pendingCount">0</span>
                </div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" id="vehicleSearchInput" class="form-control kb-input" placeholder="Search vehicle/party (F4)..." data-kb="0">
                </div>
            </div>
            <div class="pending-list-container" id="pendingVehiclesContainer">
                <div class="text-center text-muted py-5" id="queueLoadingState">
                    <div class="spinner-border spinner-border-sm text-info me-2" role="status"></div>
                    <span>Loading vehicles...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Tally-Style Tax Invoice Generation Voucher Form -->
    <div class="col-lg-8 col-xl-9">
        <div class="invoice-voucher-card">
            <form id="invoiceForm">
                @csrf
                <input type="hidden" id="selectedVehicleId" name="vehicle_id">
                <input type="hidden" id="selectedDispatchId" name="dispatch_id">
                <input type="hidden" id="selectedCustomerId" name="customer_id">

                <div class="d-flex align-items-center justify-content-between voucher-title">
                    <span><i class="fa-solid fa-file-invoice text-success me-2"></i> TAX INVOICE VOUCHER</span>
                    <span class="text-muted fs-7 fw-normal">Mode: <strong class="text-info">DISPATCH AUTO-FILL</strong></span>
                </div>

                <!-- Voucher Meta Details Row -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label text-info fs-7 fw-bold mb-1">INVOICE NO.</label>
                        <input type="text" class="form-control kb-input bg-dark text-info fw-bold" id="invoiceNoInput" value="AUTO-GENERATED" readonly tabindex="-1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-info fs-7 fw-bold mb-1">INVOICE DATE <span class="hotkey-badge">F2</span></label>
                        <input type="date" class="form-control kb-input" id="invoiceDateInput" name="invoice_date" value="{{ date('Y-m-d') }}" data-kb="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-info fs-7 fw-bold mb-1">VEHICLE NO.</label>
                        <input type="text" class="form-control kb-input" id="vehicleNoInput" name="vehicle_no" placeholder="e.g. DL01GE5276" data-kb="2">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-info fs-7 fw-bold mb-1">TRANSPORT</label>
                        <input type="text" class="form-control kb-input" id="transportInput" name="transport_name" placeholder="Transport Name" data-kb="3">
                    </div>
                </div>

                <!-- Customer Details Row -->
                <div class="row g-3 mb-4 p-3 rounded-3" style="background: #0f172a; border: 1px solid var(--kb-border);">
                    <div class="col-md-6">
                        <label class="form-label text-info fs-7 fw-bold mb-1"><i class="fa-solid fa-building me-1"></i> BUYER (BILL TO)</label>
                        <input type="text" class="form-control kb-input fw-bold" id="customerNameInput" name="customer_name" placeholder="Select a pending vehicle to auto-fill..." data-kb="4">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-info fs-7 fw-bold mb-1"><i class="fa-solid fa-id-card me-1"></i> GSTIN NO.</label>
                        <input type="text" class="form-control kb-input text-uppercase" id="customerGstInput" name="customer_gst" placeholder="Buyer GSTIN" data-kb="5">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label text-info fs-7 fw-bold mb-1">BUYER ADDRESS</label>
                        <input type="text" class="form-control kb-input" id="customerAddressInput" name="customer_address" placeholder="Billing & Shipping Address" data-kb="6">
                    </div>
                </div>

                <!-- Line Items Table -->
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-info fs-7 text-uppercase"><i class="fa-solid fa-boxes-packing me-1"></i> Dispatched Goods / Invoice Items</span>
                    <button type="button" class="btn btn-sm btn-info text-dark font-monospace fw-bold py-1 px-3" onclick="addNewItemRow()"><i class="fa-solid fa-plus me-1"></i> Add Row</button>
                </div>
                <div class="table-responsive mb-4">
                    <table class="accountant-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Item Description</th>
                                <th class="text-end" style="width: 110px;">Size / Grade</th>
                                <th class="text-end" style="width: 100px;">Pcs</th>
                                <th class="text-end" style="width: 120px;">Weight (Tons)</th>
                                <th class="text-end" style="width: 140px;">Rate (₹/Ton)</th>
                                <th class="text-end" style="width: 160px;">Amount (₹)</th>
                            </tr>
                        </thead>
                <tbody id="invoiceItemsBody">
                    <tr>
                        <td colspan="7" class="text-center py-4 text-white fw-bold">
                            <i class="fa-solid fa-arrow-left me-1 text-info"></i> Select a pending vehicle from the left queue to load dispatched items
                        </td>
                    </tr>
                </tbody>
                    </table>
                </div>

                <!-- Calculations & Summary Section -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-info fs-7 fw-bold mb-1">REMARKS / NOTES</label>
                        <textarea class="form-control kb-input" id="remarksInput" name="remarks" rows="5" placeholder="E.g. Payment due within 15 days..." data-kb="100"></textarea>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background: #0f172a; border: 1px solid var(--kb-border);">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-white fs-7 font-monospace fw-bold">Taxable Value:</span>
                                <span class="fw-bold fs-6 text-white" id="displayTaxable">₹0.00</span>
                                <input type="hidden" name="taxable_amount" id="inputTaxable" value="0">
                            </div>

                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col-6">
                                    <span class="text-white fs-7 fw-bold">CGST @ <input type="number" step="0.5" class="d-inline form-control form-control-sm kb-input text-center py-0 px-1" style="width: 50px;" id="cgstRateInput" name="cgst_rate" value="9" data-kb="101">%</span>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="fw-bold text-white" id="displayCgst">₹0.00</span>
                                    <input type="hidden" name="cgst_amount" id="inputCgst" value="0">
                                </div>
                            </div>

                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col-6">
                                    <span class="text-white fs-7 fw-bold">SGST @ <input type="number" step="0.5" class="d-inline form-control form-control-sm kb-input text-center py-0 px-1" style="width: 50px;" id="sgstRateInput" name="sgst_rate" value="9" data-kb="102">%</span>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="fw-bold text-white" id="displaySgst">₹0.00</span>
                                    <input type="hidden" name="sgst_amount" id="inputSgst" value="0">
                                </div>
                            </div>

                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col-6">
                                    <span class="text-white fs-7 fw-bold">Freight / Loading (₹):</span>
                                </div>
                                <div class="col-6 text-end">
                                    <input type="number" step="0.01" class="form-control form-control-sm kb-input text-end py-0" id="freightInput" name="freight_charges" value="0" data-kb="103">
                                </div>
                            </div>

                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col-6">
                                    <span class="text-white fs-7 fw-bold">Other Charges (₹):</span>
                                </div>
                                <div class="col-6 text-end">
                                    <input type="number" step="0.01" class="form-control form-control-sm kb-input text-end py-0" id="otherChargesInput" name="other_charges" value="0" data-kb="104">
                                </div>
                            </div>

                            <hr class="my-2" style="border-color: var(--kb-border);">

                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-bold text-warning fs-6">GRAND TOTAL:</span>
                                <span class="fw-extrabold fs-4 text-success" id="displayGrandTotal">₹0.00</span>
                                <input type="hidden" name="grand_total" id="inputGrandTotal" value="0">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex align-items-center justify-content-end gap-3 mt-4 pt-3 border-top border-secondary">
                    <button type="button" class="btn btn-outline-light px-4 py-2 fw-bold" onclick="resetForm()">
                        <i class="fa-solid fa-xmark me-1"></i> Cancel <span class="hotkey-badge ms-1">Esc</span>
                    </button>
                    <button type="button" class="btn btn-info text-dark px-4 py-2 fw-extrabold" onclick="submitInvoice(true)">
                        <i class="fa-solid fa-print me-1"></i> Save & Print <span class="hotkey-badge ms-1">Alt+P</span>
                    </button>
                    <button type="button" class="btn btn-success px-4 py-2 fw-extrabold" onclick="submitInvoice(false)">
                        <i class="fa-solid fa-check-double me-1"></i> Save Invoice <span class="hotkey-badge ms-1">Alt+S</span>
                    </button>
                    <button type="button" class="btn btn-warning text-dark px-4 py-2 fw-extrabold" onclick="generateInvoice()">
                        <i class="fa-solid fa-file-invoice me-1"></i> Generate Invoice <span class="hotkey-badge ms-1">Alt+G</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sticky Accountant Hotkey Footer Bar -->
<div class="hotkeys-footer-bar">
    <div class="d-flex align-items-center gap-3">
        <span class="shortcut-pill"><span class="kbd-btn">↑ / ↓</span> Navigate Queue</span>
        <span class="shortcut-pill"><span class="kbd-btn">Enter</span> Select / Next Field</span>
        <span class="shortcut-pill"><span class="kbd-btn">Shift+Enter</span> Previous Field</span>
        <span class="shortcut-pill"><span class="kbd-btn">F2</span> Change Date</span>
        <span class="shortcut-pill"><span class="kbd-btn">F4</span> Focus Search</span>
    </div>
    <div class="d-flex align-items-center gap-3">
        <span class="shortcut-pill"><span class="kbd-btn">Alt + S</span> Save Invoice</span>
        <span class="shortcut-pill"><span class="kbd-btn">Alt + P</span> Save & Print</span>
        <span class="shortcut-pill"><span class="kbd-btn">Esc</span> Reset Form</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
        function generateInvoice() {
        const customerName = document.getElementById('customerNameInput').value;

        if (!customerName) {
            alert('Please select a pending vehicle first!');
            return;
        }

        recalculateAmounts();

        const formData = new FormData(document.getElementById('invoiceForm'));

        fetch("{{ route('invoices.generate') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                alert(res.message);

                if (res.invoice_id) {
                    window.open(
                        `/invoices/print/${res.invoice_id}`,
                        '_blank'
                    );
                }

                resetForm();
                loadPendingVehicles();
            } else {
                alert('Error: ' + res.message);
            }
        })
        .catch(err => {
            alert('Network Error: ' + err.message);
        });
    }
    let pendingVehiclesList = [];
    let selectedVehicleIndex = -1;
    let currentLineItems = [];

    document.addEventListener('DOMContentLoaded', function () {
        loadPendingVehicles();
        setupKeyboardListeners();
    });

    // Fetch Pending Vehicles from Backend Proxy API
    function loadPendingVehicles() {
        const container = document.getElementById('pendingVehiclesContainer');
        const icon = document.getElementById('refreshIcon');
        if (icon) icon.classList.add('fa-spin');

        fetch("{{ route('invoices.pending-vehicles') }}")
            .then(res => res.json())
            .then(res => {
                if (icon) icon.classList.remove('fa-spin');
                if (res.status === 'success' && res.data && res.data.length > 0) {
                    pendingVehiclesList = res.data;
                    document.getElementById('pendingCount').innerText = pendingVehiclesList.length;
                    renderPendingQueue(pendingVehiclesList);
                } else {
                    container.innerHTML = `
                        <div class="text-center py-5 text-muted fs-7">
                            <i class="fa-solid fa-circle-check text-success fs-3 d-block mb-2"></i>
                            No pending dispatch vehicles found!
                        </div>`;
                    document.getElementById('pendingCount').innerText = '0';
                }
            })
            .catch(err => {
                if (icon) icon.classList.remove('fa-spin');
                container.innerHTML = `<div class="text-danger p-3 fs-7">Error loading dispatches: ${err.message}</div>`;
            });
    }

    // Render Pending Vehicles List
    function renderPendingQueue(list) {
        const container = document.getElementById('pendingVehiclesContainer');
        let html = '';
        list.forEach((v, index) => {
            html += `
                <div class="pending-item-row ${index === selectedVehicleIndex ? 'selected' : ''}" 
                     id="pending-row-${index}" 
                     onclick="selectVehicleRow(${index})">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <strong class="text-warning font-monospace">${v.vehicleno || 'NO VEHICLE'}</strong>
                        <span class="hotkey-badge">#${v.vehicle_id || v.tokenid}</span>
                    </div>
                    <div class="fw-bold text-white fs-7 text-truncate mb-1">${v.partyname || 'Customer'}</div>
                    <div class="d-flex align-items-center justify-content-between text-light fs-8 fw-semibold">
                        <span><i class="fa-solid fa-truck-moving text-info me-1"></i>${v.transport || 'Self'}</span>
                        <span class="text-white-50">${v.createdon ? v.createdon.substring(0, 10) : ''}</span>
                    </div>
                </div>`;
        });
        container.innerHTML = html;
    }

    // Handle Vehicle Selection (via Click or Enter)
    function selectVehicleRow(index) {
        if (index < 0 || index >= pendingVehiclesList.length) return;
        selectedVehicleIndex = index;
        renderPendingQueue(pendingVehiclesList);

        const v = pendingVehiclesList[index];
        document.getElementById('selectedVehicleId').value = v.vehicle_id || v.tokenid;
        document.getElementById('vehicleNoInput').value = v.vehicleno || '';
        document.getElementById('transportInput').value = v.transport || '';
        document.getElementById('customerNameInput').value = v.partyname || '';
        document.getElementById('customerGstInput').value = v.gst || '';
        document.getElementById('selectedCustomerId').value = v.cust_id || '';

        // Fetch detailed items for this vehicle
        fetchDispatchDetails(v.vehicle_id || v.tokenid, v.cust_id || '');
    }

    // Fetch Details & Line Items from API
    function fetchDispatchDetails(vehicleId, custId) {
        const body = document.getElementById('invoiceItemsBody');
        body.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-info"><div class="spinner-border spinner-border-sm me-2"></div>Loading dispatched line items...</td></tr>`;

        let url = `{{ route('invoices.dispatch-details') }}?vehicle_id=${vehicleId}`;
        if (custId) url += `&cust_id=${custId}`;

        fetch(url)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data) {
                    const data = res.data;
                    if (data.dispatch) {
                        document.getElementById('selectedDispatchId').value = data.dispatch.dispatchid || '';
                        document.getElementById('otherChargesInput').value = data.dispatch.otherchr || 0;
                    }
                    if (data.customer) {
                        document.getElementById('customerNameInput').value = data.customer.name || data.customer.partyname || document.getElementById('customerNameInput').value;
                        document.getElementById('customerGstInput').value = data.customer.gst || '';
                        document.getElementById('customerAddressInput').value = `${data.customer.address || ''}, ${data.customer.city || ''} ${data.customer.state || ''}`.trim(', ');
                    }
                    if (data.items && data.items.length > 0) {
                        currentLineItems = data.items;
                        renderInvoiceItems(currentLineItems);
                    } else {
                        body.innerHTML = `<tr><td colspan="7" class="text-center py-3 text-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i> No items found for this dispatch. <button type="button" class="btn btn-sm btn-info text-dark ms-2 fw-bold" onclick="addNewItemRow()">+ Add Item Row</button></td></tr>`;
                    }
                } else {
                    body.innerHTML = `<tr><td colspan="7" class="text-center py-3 text-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i> ${res.message || 'No dispatch details found.'} <button type="button" class="btn btn-sm btn-info text-dark ms-2 fw-bold" onclick="addNewItemRow()">+ Add Item Row</button></td></tr>`;
                }
            })
            .catch(err => {
                body.innerHTML = `<tr><td colspan="7" class="text-center py-3 text-danger"><i class="fa-solid fa-circle-exclamation me-1"></i> Error fetching details: ${err.message} <button type="button" class="btn btn-sm btn-info text-dark ms-2 fw-bold" onclick="addNewItemRow()">+ Add Item Row</button></td></tr>`;
            });
    }

    // Add Manual Item Row
    function addNewItemRow() {
        const body = document.getElementById('invoiceItemsBody');
        if (body.children.length === 1 && body.children[0].cells.length === 1) {
            body.innerHTML = '';
        }

        const idx = body.children.length;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="font-monospace text-muted">${idx + 1}</td>
            <td>
                <input type="text" class="form-control form-control-sm kb-input" name="items[${idx}][product_name]" value="BRIGHT BAR" placeholder="Item Name" data-kb="${20 + idx}">
            </td>
            <td class="text-end">
                <input type="text" class="form-control form-control-sm kb-input text-end" name="items[${idx}][size_name]" placeholder="Size/Grade">
            </td>
            <td class="text-end">
                <input type="number" class="form-control form-control-sm kb-input text-end" name="items[${idx}][pcs]" value="1">
            </td>
            <td class="text-end">
                <input type="number" step="0.001" class="form-control form-control-sm kb-input text-end item-weight" name="items[${idx}][weight_tons]" value="1.000" onchange="recalculateAmounts()">
            </td>
            <td class="text-end">
                <input type="number" step="0.01" class="form-control form-control-sm kb-input text-end item-rate" name="items[${idx}][rate]" value="45000" onchange="recalculateAmounts()">
            </td>
            <td class="text-end fw-bold text-info">
                <input type="number" step="0.01" class="form-control form-control-sm kb-input text-end item-amount fw-bold text-info" name="items[${idx}][amount]" value="45000.00" readonly tabindex="-1">
            </td>`;
        body.appendChild(tr);
    }

    // Render Line Items in Table
    function renderInvoiceItems(items) {
        const body = document.getElementById('invoiceItemsBody');
        let html = '';
        let taxableSum = 0;

        items.forEach((item, idx) => {
            const weight = parseFloat(item.actual_weight_tons || item.planned_weight_tons || 0);
            const pcs = parseInt(item.actual_pcs || item.planned_pcs || 0);
            const rate = parseFloat(item.rate || item.net_rate || 0);
            const amount = parseFloat(item.subtotal || (weight * rate) || 0);
            taxableSum += amount;

            const kbIndex = 20 + idx;

            html += `
                <tr>
                    <td class="font-monospace text-muted">${idx + 1}</td>
                    <td>
                        <input type="text" class="form-control form-control-sm kb-input" name="items[${idx}][product_name]" value="${item.product_name || 'Goods'}" data-kb="${kbIndex}">
                        <input type="hidden" name="items[${idx}][disp_item_id]" value="${item.disp_item_id || ''}">
                        <input type="hidden" name="items[${idx}][slid]" value="${item.slid || ''}">
                    </td>
                    <td class="text-end">
                        <input type="text" class="form-control form-control-sm kb-input text-end" name="items[${idx}][size_name]" value="${item.size_name || ''}" placeholder="Size">
                        <input type="hidden" name="items[${idx}][grade_name]" value="${item.grade_name || ''}">
                    </td>
                    <td class="text-end">
                        <input type="number" class="form-control form-control-sm kb-input text-end" name="items[${idx}][pcs]" value="${pcs}">
                    </td>
                    <td class="text-end">
                        <input type="number" step="0.001" class="form-control form-control-sm kb-input text-end item-weight" name="items[${idx}][weight_tons]" value="${weight}" onchange="recalculateAmounts()" data-item-idx="${idx}">
                    </td>
                    <td class="text-end">
                        <input type="number" step="0.01" class="form-control form-control-sm kb-input text-end item-rate" name="items[${idx}][rate]" value="${rate}" onchange="recalculateAmounts()" data-item-idx="${idx}">
                    </td>
                    <td class="text-end fw-bold text-info">
                        <input type="number" step="0.01" class="form-control form-control-sm kb-input text-end item-amount fw-bold text-info" name="items[${idx}][amount]" value="${amount.toFixed(2)}" readonly tabindex="-1">
                    </td>
                </tr>`;
        });

        body.innerHTML = html;
        recalculateAmounts();
    }

    // Recalculate Taxes, Freight, and Totals
    function recalculateAmounts() {
        let taxable = 0;
        const rows = document.querySelectorAll('#invoiceItemsBody tr');

        rows.forEach((row) => {
            const weightInput = row.querySelector('.item-weight');
            const rateInput = row.querySelector('.item-rate');
            const amountInput = row.querySelector('.item-amount');

            if (weightInput && rateInput && amountInput) {
                const w = parseFloat(weightInput.value) || 0;
                const r = parseFloat(rateInput.value) || 0;
                const amt = w * r;
                amountInput.value = amt.toFixed(2);
                taxable += amt;
            }
        });

        const cgstRate = parseFloat(document.getElementById('cgstRateInput').value) || 0;
        const sgstRate = parseFloat(document.getElementById('sgstRateInput').value) || 0;
        const freight = parseFloat(document.getElementById('freightInput').value) || 0;
        const otherChr = parseFloat(document.getElementById('otherChargesInput').value) || 0;

        const cgstAmt = (taxable * cgstRate) / 100;
        const sgstAmt = (taxable * sgstRate) / 100;
        const grandTotal = taxable + cgstAmt + sgstAmt + freight + otherChr;

        document.getElementById('displayTaxable').innerText = '₹' + taxable.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('inputTaxable').value = taxable.toFixed(2);

        document.getElementById('displayCgst').innerText = '₹' + cgstAmt.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('inputCgst').value = cgstAmt.toFixed(2);

        document.getElementById('displaySgst').innerText = '₹' + sgstAmt.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('inputSgst').value = sgstAmt.toFixed(2);

        document.getElementById('displayGrandTotal').innerText = '₹' + grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('inputGrandTotal').value = grandTotal.toFixed(2);
    }

    // Submit Invoice Form
    function submitInvoice(andPrint = false) {
        const customerName = document.getElementById('customerNameInput').value;
        if (!customerName) {
            alert('Please select a pending vehicle or enter Buyer Name first!');
            document.getElementById('customerNameInput').focus();
            return;
        }

        recalculateAmounts();
        const formData = new FormData(document.getElementById('invoiceForm'));

        fetch("{{ route('invoices.store') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                alert(`✅ ${res.message}`);
                if (andPrint && res.invoice_id) {
                    window.open(`/invoices/print/${res.invoice_id}?autoprint=1`, '_blank');
                }
                resetForm();
                loadPendingVehicles();
            } else {
                alert(`❌ Error: ${res.message}`);
            }
        })
        .catch(err => {
            alert(`❌ Network Error: ${err.message}`);
        });
    }

    // Reset Form
    function resetForm() {
        document.getElementById('invoiceForm').reset();
        document.getElementById('invoiceItemsBody').innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">Select a pending vehicle from the left queue</td></tr>`;
        document.getElementById('displayTaxable').innerText = '₹0.00';
        document.getElementById('displayCgst').innerText = '₹0.00';
        document.getElementById('displaySgst').innerText = '₹0.00';
        document.getElementById('displayGrandTotal').innerText = '₹0.00';
        selectedVehicleIndex = -1;
        renderPendingQueue(pendingVehiclesList);
    }

    // Keyboard Shortcuts & Enter Key Navigation Handler
    function setupKeyboardListeners() {
        document.addEventListener('keydown', function (e) {
            // F4: Focus Search Input
            if (e.key === 'F4') {
                e.preventDefault();
                const search = document.getElementById('vehicleSearchInput');
                if (search) search.focus();
            }
            // F2: Focus Invoice Date
            else if (e.key === 'F2') {
                e.preventDefault();
                const date = document.getElementById('invoiceDateInput');
                if (date) date.focus();
            }
            // F5: Refresh
            else if (e.key === 'F5') {
                e.preventDefault();
                loadPendingVehicles();
            }
            // Alt + S: Save Invoice
            else if (e.altKey && (e.key === 's' || e.key === 'S')) {
                e.preventDefault();
                submitInvoice(false);
            }
            // Alt + P: Save & Print Invoice
            else if (e.altKey && (e.key === 'p' || e.key === 'P')) {
                e.preventDefault();
                submitInvoice(true);
            }
            // Esc: Reset
            else if (e.key === 'Escape') {
                resetForm();
            }
            // Arrow Up / Down for Pending Vehicle Selection when searching or navigating queue
            else if (e.key === 'ArrowDown') {
                if (document.activeElement.id === 'vehicleSearchInput' || document.activeElement === document.body) {
                    e.preventDefault();
                    if (selectedVehicleIndex < pendingVehiclesList.length - 1) {
                        selectVehicleRow(selectedVehicleIndex + 1);
                    }
                }
            } else if (e.key === 'ArrowUp') {
                if (document.activeElement.id === 'vehicleSearchInput' || document.activeElement === document.body) {
                    e.preventDefault();
                    if (selectedVehicleIndex > 0) {
                        selectVehicleRow(selectedVehicleIndex - 1);
                    }
                }
            }
            // Enter Key Navigation inside form fields
            else if (e.key === 'Enter' && e.target.tagName !== 'BUTTON' && e.target.tagName !== 'TEXTAREA') {
                if (e.target.id === 'vehicleSearchInput') {
                    e.preventDefault();
                    if (pendingVehiclesList.length > 0) {
                        selectVehicleRow(selectedVehicleIndex >= 0 ? selectedVehicleIndex : 0);
                        document.getElementById('invoiceDateInput').focus();
                    }
                    return;
                }

                const inputs = Array.from(document.querySelectorAll('.kb-input:not([readonly])'));
                const currentIndex = inputs.indexOf(e.target);

                if (currentIndex !== -1) {
                    e.preventDefault();
                    if (e.shiftKey) {
                        // Shift + Enter: Move to previous field
                        if (currentIndex > 0) inputs[currentIndex - 1].focus();
                    } else {
                        // Enter: Move to next field
                        if (currentIndex < inputs.length - 1) {
                            inputs[currentIndex + 1].focus();
                        } else {
                            // Reached last input, submit form!
                            submitInvoice(false);
                        }
                    }
                }
            }
        });

        // Search Filter Input
        document.getElementById('vehicleSearchInput').addEventListener('input', function (e) {
            const query = e.target.value.toLowerCase();
            const filtered = pendingVehiclesList.filter(v => 
                (v.vehicleno && v.vehicleno.toLowerCase().includes(query)) ||
                (v.partyname && v.partyname.toLowerCase().includes(query)) ||
                (v.p_code && v.p_code.toLowerCase().includes(query))
            );
            renderPendingQueue(filtered);
        });
    }
</script>
@endpush
