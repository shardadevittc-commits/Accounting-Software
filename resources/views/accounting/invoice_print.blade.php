<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estimate - {{ $invoice->invoice_no ?: $invoice->id }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #525659;
            color: #000000;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, Helvetica, sans-serif;
            font-size: 11px;
            padding: 20px;
            margin: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-controls {
            max-width: 780px;
            margin: 0 auto 15px auto;
            text-align: center;
        }
        .btn {
            display: inline-block;
            font-weight: 700;
            font-size: 12px;
            padding: 7px 18px;
            border-radius: 4px;
            border: 1px solid transparent;
            cursor: pointer;
            margin: 0 4px;
            text-decoration: none;
        }
        .btn-primary {
            background-color: #2563eb;
            color: #ffffff;
        }
        .btn-secondary {
            background-color: #64748b;
            color: #ffffff;
        }

        /* Invoice Container */
        .invoice-sheet {
            background: #ffffff;
            border: 2px solid #000000;
            width: 100%;
            max-width: 780px;
            margin: 0 auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        /* Header */
        .inv-header {
            text-align: center;
            padding: 10px 15px 8px 15px;
            border-bottom: 1.5px solid #000000;
        }
        .inv-header .doc-title {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 2.5px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .inv-header .company-title {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .inv-header .company-gstin {
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: 0.3px;
        }

        /* Info Section (Shipped to & Slip meta) */
        .meta-container {
            display: flex;
            border-bottom: 1.5px solid #000000;
        }
        .meta-col-left {
            flex: 1.25;
            padding: 6px 10px;
            border-right: 1.5px solid #000000;
            font-size: 11px;
            line-height: 1.45;
        }
        .meta-col-right {
            flex: 1;
            padding: 6px 10px;
            font-size: 11px;
            line-height: 1.45;
        }
        .meta-line {
            margin-bottom: 2px;
        }
        .meta-line:last-child {
            margin-bottom: 0;
        }

        /* Order remarks */
        .remarks-container {
            text-align: center;
            padding: 4px 10px;
            font-size: 11px;
            border-bottom: 1.5px solid #000000;
        }

        /* Main items table */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .main-table th, .main-table td {
            border: 1.5px solid #000000;
            padding: 3px 6px;
            font-size: 11px;
            box-sizing: border-box;
        }
        .main-table th {
            font-weight: 800;
            text-align: center;
            background: #ffffff;
            border-top: none;
        }
        
        /* Column Widths */
        .col-sn { width: 45px; text-align: center; }
        .col-prod { width: 100px; text-align: left; }
        .col-grade { width: 75px; text-align: center; }
        .col-qty { width: 75px; text-align: center; font-weight: 700; }
        .col-price { width: 80px; text-align: right; }
        .col-extra { width: 65px; text-align: right; }
        .col-net { width: 85px; text-align: right; font-weight: 700; }
        .col-total { width: 85px; text-align: right; font-weight: 700; }

        .item-data-row td {
            height: 135px;
            vertical-align: top;
            padding-top: 5px;
        }

        .total-summary-row td {
            font-weight: 800;
            padding: 4px 6px;
        }

        .discount-summary-row td {
            font-weight: 800;
            padding: 4px 6px;
        }

        /* Lower Split Grid Table */
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border-top: 1.5px solid #000000;
        }
        .bottom-table th, .bottom-table td {
            border: 1.5px solid #000000;
            padding: 3px 6px;
            font-size: 11px;
            box-sizing: border-box;
        }
        .btm-prod { width: 220px; }
        .btm-qty { width: 85px; text-align: center; }
        .btm-rate { width: 95px; text-align: center; font-weight: 700; }
        .btm-label { width: 160px; text-align: right; font-weight: 800; }
        .btm-val { width: 85px; text-align: right; font-weight: 800; }

        .avg-rate-header-row td {
            font-weight: 800;
            text-transform: uppercase;
            padding: 4px 6px;
        }
        .avg-rate-col-header td {
            font-weight: 800;
            padding: 3px 6px;
        }

        .grand-total-row td {
            background-color: #000000 !important;
            color: #ffffff !important;
            padding: 6px 8px;
            font-size: 12px;
            font-weight: 900;
            border-color: #000000 !important;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-controls {
                display: none !important;
            }
            .invoice-sheet {
                border: 2px solid #000000 !important;
                box-shadow: none !important;
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
            }
            @page {
                size: portrait;
                margin: 8mm;
            }
        }
    </style>
</head>
<body>

    <div class="print-controls">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print / Save as PDF</button>
        <a href="{{ route('sales.dispatch-invoicing') }}" class="btn btn-secondary">Close / Back to Dispatches</a>
    </div>

    @php
        $totalQty = 0;
        $totalAmount = 0;
        $groupedProducts = [];

        foreach($invoice->items as $item) {
            $qty = floatval($item->weight_tons);
            $rate = floatval($item->rate);
            $extra = floatval($item->extra_charges ?? 0);
            $netRate = $rate + $extra;
            $amount = floatval($item->amount);
            if ($amount <= 0 && $qty > 0) {
                $amount = $qty * $netRate;
            }

            $totalQty += $qty;
            $totalAmount += $amount;

            $pName = strtoupper($item->product_name ?: 'MS ANGLE');
            if (!isset($groupedProducts[$pName])) {
                $groupedProducts[$pName] = ['qty' => 0, 'amount' => 0];
            }
            $groupedProducts[$pName]['qty'] += $qty;
            $groupedProducts[$pName]['amount'] += $amount;
        }

        // Cash discount
        $discountPercent = 0;
        $discountAmount = 0;
        if (isset($invoice->discount_amount) && $invoice->discount_amount > 0) {
            $discountAmount = floatval($invoice->discount_amount);
            $discountPercent = $totalAmount > 0 ? round(($discountAmount / $totalAmount) * 100) : 0;
        } elseif (isset($dispatch) && isset($dispatch->cashdiscount) && $dispatch->cashdiscount > 0) {
            $discountAmount = floatval($dispatch->cashdiscount);
            $discountPercent = $totalAmount > 0 ? round(($discountAmount / $totalAmount) * 100) : 0;
        } elseif (isset($invoice->discount_percent) && $invoice->discount_percent > 0) {
            $discountPercent = floatval($invoice->discount_percent);
            $discountAmount = round(($totalAmount * $discountPercent) / 100);
        } else {
            $calcTaxable = floatval($invoice->taxable_amount ?? 0);
            if ($calcTaxable > 0 && $totalAmount > $calcTaxable) {
                $diff = $totalAmount - $calcTaxable + floatval($invoice->other_charges ?? 0);
                if ($diff > 0) {
                    $discountAmount = $diff;
                    $discountPercent = round(($discountAmount / $totalAmount) * 100);
                }
            }
        }

        $labor = floatval($invoice->freight_charges ?? ($dispatch->laborchr ?? 0));
        $otherCharges = floatval($invoice->other_charges ?? ($dispatch->otherchr ?? 0));
        
        $taxableAmt = floatval($invoice->taxable_amount ?? 0);
        if ($taxableAmt <= 0) {
            $taxableAmt = ($totalAmount - $discountAmount) + $otherCharges;
        }

        $gstRate = floatval(($invoice->cgst_rate ?? 0) + ($invoice->sgst_rate ?? 0) + ($invoice->igst_rate ?? 0));
        if ($gstRate <= 0) $gstRate = 18;

        $gstAmount = floatval(($invoice->cgst_amount ?? 0) + ($invoice->sgst_amount ?? 0) + ($invoice->igst_amount ?? 0));
        if ($gstAmount <= 0) {
            $gstAmount = round(($taxableAmt * $gstRate) / 100);
        }

        $tcs = floatval($invoice->tcs_amount ?? ($dispatch->tcs ?? 0));

        $grandTotal = floatval($invoice->grand_total ?? 0);
        if ($grandTotal <= 0) {
            $grandTotal = round($taxableAmt + $gstAmount + $labor + $tcs);
        }

        $tokenNo = $vehicle->tokenid ?? $dispatch->tokenid ?? $invoice->vehicle_id ?? $invoice->dispatch_id ?? '4';
    @endphp

    <div class="invoice-sheet">
        
        <!-- Header -->
        <div class="inv-header">
            <div class="doc-title">ESTIMATE</div>
            <div class="company-title">{{ $invoice->customer_name ?: '' }}</div>
            <div class="company-gstin">GST No. {{ $invoice->customer_gst ?: '' }}</div>
        </div>

        <!-- Shipped To & Metadata Grid -->
        <div class="meta-container">
            <div class="meta-col-left">
                <div class="meta-line"><strong>Shipped To.</strong> &nbsp;{{ strtoupper($invoice->customer_name ?: '') }}</div>
                <div class="meta-line"><strong>GST No.</strong> &nbsp;{{ strtoupper($invoice->customer_gst ?: '') }}</div>
                <div class="meta-line"><strong>Address.</strong> &nbsp;{{ $invoice->customer_address ?: '' }}</div>
            </div>
            <div class="meta-col-right">
                <div class="meta-line"><strong>Slip No.:</strong> &nbsp;<strong>{{ $invoice->invoice_no ?: $invoice->id }}</strong></div>
                <div class="meta-line"><strong>Date:</strong> &nbsp;{{ date('d/m/Y', strtotime($invoice->invoice_date ?: date('Y-m-d'))) }}</div>
                <div class="meta-line"><strong>Token No.:</strong> &nbsp;<strong>{{ $tokenNo }}</strong></div>
                <div class="meta-line"><strong>Vehicle No.:</strong> &nbsp;<strong>{{ strtoupper($invoice->vehicle_no ?: ($vehicle->vehicleno ?? '')) }}</strong></div>
            </div>
        </div>

        <!-- Order Remarks -->
        <div class="remarks-container">
            <strong>Order Remarks.:</strong> &nbsp;{{ $invoice->remarks ?: '' }}
        </div>

        <!-- Items Table -->
        <table class="main-table">
            <thead>
                <tr>
                    <th class="col-sn">S.N.</th>
                    <th class="col-prod">Product</th>
                    <th class="col-grade">Grade</th>
                    <th class="col-qty">Qty.</th>
                    <th class="col-price">Price (₹)</th>
                    <th class="col-extra">Extra (₹)</th>
                    <th class="col-net">Net Amnt. (₹)</th>
                    <th class="col-total">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                    @php
                        $qty = floatval($item->weight_tons);
                        $rate = floatval($item->rate);
                        $extra = floatval($item->extra_charges ?? 0);
                        $netRate = $rate + $extra;
                        $amount = floatval($item->amount);
                        if ($amount <= 0 && $qty > 0) {
                            $amount = $qty * $netRate;
                        }
                    @endphp
                    <tr class="item-data-row">
                        <td class="col-sn">{{ $index + 1 }}</td>
                        <td class="col-prod">{{ $item->size_name ?: $item->product_name }}</td>
                        <td class="col-grade">{{ $item->grade_name ?: 'MS' }}</td>
                        <td class="col-qty">{{ number_format($qty, 3) }}</td>
                        <td class="col-price">{{ number_format($rate, 0) }}</td>
                        <td class="col-extra">{{ number_format($extra, 0) }}</td>
                        <td class="col-net">{{ number_format($netRate, 0) }}</td>
                        <td class="col-total">{{ number_format($amount, 0) }}</td>
                    </tr>
                @endforeach

                <!-- Total Row -->
                <tr class="total-summary-row">
                    <td colspan="2" style="text-align: right; border-right: 1.5px solid #000;">Total</td>
                    <td style="border-left: none; border-right: 1.5px solid #000;"></td>
                    <td class="col-qty">{{ number_format($totalQty, 3) }}</td>
                    <td colspan="3" style="border-right: 1.5px solid #000;"></td>
                    <td class="col-total">{{ number_format($totalAmount, 0) }}</td>
                </tr>

                <!-- Cash Discount Row -->
                <tr class="discount-summary-row">
                    <td colspan="7" style="text-align: right; padding-right: 15px;">
                        Cash Discount {{ $discountPercent > 0 ? $discountPercent . '%' : '' }}
                    </td>
                    <td class="col-total">
                        {{ number_format($discountAmount, 0) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Lower Section: Average Sale Rate & Summary -->
        @php
            $groupList = [];
            foreach($groupedProducts as $pName => $pData) {
                $avgRate = $pData['qty'] > 0 ? ($pData['amount'] / $pData['qty']) : 0;
                $groupList[] = [
                    'name' => $pName,
                    'qty' => number_format($pData['qty'], 3),
                    'rate' => number_format($avgRate, 0)
                ];
            }

            $chargesList = [
                ['label' => 'Labor (₹)', 'val' => number_format($labor, 0)],
                ['label' => 'Other Charges (₹)', 'val' => number_format($otherCharges, 0)],
                ['label' => 'TAXABLE AMT.', 'val' => number_format($taxableAmt, 0)],
                ['label' => 'GST ' . ($gstRate > 0 ? (int)$gstRate . '%' : '18%'), 'val' => number_format($gstAmount, 0)],
                ['label' => 'TCS', 'val' => number_format($tcs, 0)]
            ];

            $totalRows = max(count($groupList), count($chargesList));
        @endphp

        <table class="bottom-table">
            <!-- Header Row for Average Sale Rate -->
            <tr class="avg-rate-header-row">
                <td colspan="3" style="border-right: 1.5px solid #000;">AVERAGE SALE RATE (₹):</td>
                <td colspan="2" style="border-left: none;"></td>
            </tr>
            <!-- Sub Header Row -->
            <tr class="avg-rate-col-header">
                <td class="btm-prod">Product</td>
                <td class="btm-qty">Qty (MT)</td>
                <td class="btm-rate">Avg Rate</td>
                <td colspan="2" style="border-left: 1.5px solid #000;"></td>
            </tr>

            <!-- Rows -->
            @for($i = 0; $i < $totalRows; $i++)
                @php
                    $prodItem = $groupList[$i] ?? null;
                    $chargeItem = $chargesList[$i] ?? null;
                @endphp
                <tr>
                    <td class="btm-prod" style="font-weight: 700;">{{ $prodItem ? $prodItem['name'] : '' }}</td>
                    <td class="btm-qty" style="font-weight: 700;">{{ $prodItem ? $prodItem['qty'] : '' }}</td>
                    <td class="btm-rate">{{ $prodItem ? $prodItem['rate'] : '' }}</td>
                    <td class="btm-label">{{ $chargeItem ? $chargeItem['label'] : '' }}</td>
                    <td class="btm-val">{{ $chargeItem ? $chargeItem['val'] : '' }}</td>
                </tr>
            @endfor

            <!-- Grand Total Row -->
            <tr class="grand-total-row">
                <td colspan="4" style="text-align: right; padding-right: 15px;">GRAND TOTAL</td>
                <td style="text-align: right; font-weight: 900;">{{ number_format($grandTotal, 0) }}</td>
            </tr>
        </table>

    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };

        window.addEventListener('afterprint', function() {
            window.location.href = "{{ route('sales.dispatch-invoicing') }}";
        });
    </script>
</body>
</html>
