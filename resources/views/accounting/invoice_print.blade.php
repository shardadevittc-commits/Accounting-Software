<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estimate / Invoice - {{ $invoice->invoice_no }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            background-color: #f8fafc;
            color: #000000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            padding: 20px;
            margin: 0;
        }

        .invoice-box {
            background: #ffffff;
            border: 2px solid #000000;
            max-width: 820px;
            margin: 0 auto;
            padding: 15px 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        /* Header styling */
        .header-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .company-name {
            text-align: center;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .company-gst {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        /* Border Table Box */
        .info-grid {
            border: 1px solid #000000;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .info-grid td {
            border: 1px solid #000000;
            padding: 5px 8px;
            vertical-align: top;
            font-size: 11px;
            line-height: 1.4;
        }

        /* Invoice Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            margin-bottom: 0px;
        }
        .items-table th, .items-table td {
            border: 1px solid #000000;
            padding: 4px 6px;
            font-size: 11px;
        }
        .items-table th {
            font-weight: bold;
            text-align: center;
            background-color: #ffffff;
            text-transform: uppercase;
        }

        /* Bottom Split Section */
        .bottom-section {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }
        .bottom-left {
            flex: 1.1;
        }
        .bottom-right {
            flex: 1;
        }

        .sub-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
        }
        .sub-table th, .sub-table td {
            border: 1px solid #000000;
            padding: 3px 6px;
            font-size: 11px;
        }
        .sub-table th {
            font-weight: bold;
            background-color: #ffffff;
            text-transform: uppercase;
        }

        .grand-total-row {
            font-weight: bold;
            font-size: 12px;
        }
        .grand-total-row td {
            border-top: 2px solid #000000 !important;
            padding: 5px 6px;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .invoice-box {
                border: 2px solid #000000 !important;
                box-shadow: none !important;
                max-width: 100% !important;
                width: 100% !important;
                padding: 10px 12px !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: portrait;
                margin: 6mm;
            }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mb-3">
        <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold me-2"><i class="fa-solid fa-print"></i> Print Invoice</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm fw-bold">Close</button>
    </div>

    <div class="invoice-box">
        <!-- Top Header -->
        <div class="header-title">ESTIMATE data</div>
        <div class="company-name">{{ $invoice->company_name ?? 'GURBAZ FARM MACHINERY PRIVATE LIMITED' }}</div>
        <div class="company-gst">GST NO. {{ $invoice->company_gst ?? '03AAJCG7865K1Z5' }}</div>

        <!-- Info Grid (Shipped To & Metadata) -->
        <table class="info-grid">
            <tr>
                <td style="width: 58%;">
                    <div><strong>SHIPPED TO.</strong> {{ strtoupper($invoice->customer_name) }}</div>
                    <div><strong>GST NO.</strong> {{ strtoupper($invoice->customer_gst ?: 'N/A') }}</div>
                    <div><strong>ADDRESS.</strong> {{ strtoupper($invoice->customer_address ?: 'PATIALA, PATIALA') }}</div>
                </td>
                <td style="width: 42%;">
                    <div><strong>SLIP NO. :</strong> {{ $invoice->invoice_no ?: $invoice->id }}</div>
                    <div><strong>DATE :</strong> {{ date('d-m-Y', strtotime($invoice->invoice_date)) }}</div>
                    <div><strong>TOKEN NO. :</strong> {{ $invoice->dispatch_id ?: $invoice->vehicle_id ?: '6' }}</div>
                    <div><strong>VEHICLE NO. :</strong> {{ strtoupper($invoice->vehicle_no ?: 'PB11DA2794') }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>ORDER REMARKS.:</strong> {{ $invoice->remarks ?: date('d-m-Y', strtotime($invoice->invoice_date)) }}
                </td>
            </tr>
        </table>

        <!-- Products Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 35px;">S.N.</th>
                    <th>PRODUCT</th>
                    <th style="width: 75px;" class="text-end">QTY. (MT)</th>
                    <th style="width: 90px;" class="text-end">PRICE (₹)</th>
                    <th style="width: 65px;" class="text-end">EXTRA (₹)</th>
                    <th style="width: 95px;" class="text-end">NET AMNT. (₹)</th>
                    <th style="width: 95px;" class="text-end">TOTAL (₹)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalQty = 0;
                    $totalAmount = 0;
                    $groupedProducts = [];
                @endphp

                @foreach($invoice->items as $index => $item)
                    @php
                        $qty = floatval($item->weight_tons);
                        $rate = floatval($item->rate);
                        $extra = floatval($item->extra_charges ?? 0);
                        $netAmnt = $rate + $extra;
                        $amount = floatval($item->amount);

                        $totalQty += $qty;
                        $totalAmount += $amount;

                        $prodName = strtoupper($item->product_name ?: 'BRIGHT BAR');
                        if(!isset($groupedProducts[$prodName])) {
                            $groupedProducts[$prodName] = ['qty' => 0, 'amount' => 0];
                        }
                        $groupedProducts[$prodName]['qty'] += $qty;
                        $groupedProducts[$prodName]['amount'] += $amount;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ strtoupper($item->product_name ?: 'BRIGHT BAR') }}</td>
                        <td class="text-end">{{ number_format($qty, 3) }}</td>
                        <td class="text-end">{{ number_format($rate, 2) }}</td>
                        <td class="text-end">{{ number_format($extra, 0) }}</td>
                        <td class="text-end">{{ number_format($netAmnt, 0) }}</td>
                        <td class="text-end fw-bold">{{ number_format($amount, 0) }}</td>
                    </tr>
                @endforeach

                <!-- Total Row -->
                <tr class="fw-bold">
                    <td colspan="2" class="text-end">TOTAL</td>
                    <td class="text-end">{{ number_format($totalQty, 3) }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-end">{{ number_format($totalAmount, 3) }}</td>
                </tr>

                <!-- Cash Discount Row -->
                <tr>
                    <td colspan="6" class="text-end fw-bold">CASH DISCOUNT {{ $invoice->discount_percent ? $invoice->discount_percent . '%' : '0%' }}</td>
                    <td class="text-end fw-bold">{{ number_format($invoice->discount_amount ?? 0, 0) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Bottom Split Section -->
        <div class="bottom-section">
            <!-- Left Box: Average Sale Rate -->
            <div class="bottom-left">
                <div class="fw-bold mb-1" style="font-size: 11px;">AVERAGE SALE RATE (₹):</div>
                <table class="sub-table">
                    <thead>
                        <tr>
                            <th>PRODUCT</th>
                            <th class="text-end">QTY (MT)</th>
                            <th class="text-end">AVG RATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groupedProducts as $pName => $pData)
                            @php
                                $avgRate = $pData['qty'] > 0 ? ($pData['amount'] / $pData['qty']) : 0;
                            @endphp
                            <tr>
                                <td class="fw-bold">{{ $pName }}</td>
                                <td class="text-end">{{ number_format($pData['qty'], 2) }}</td>
                                <td class="text-end">{{ number_format($avgRate, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Right Box: Totals & Charges -->
            <div class="bottom-right">
                <table class="sub-table">
                    <tbody>
                        <tr>
                            <td class="fw-bold">LABOR (₹)</td>
                            <td class="text-end fw-bold" style="width: 100px;">{{ number_format($invoice->freight_charges ?? 0, 0) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">OTHER CHARGES (₹)</td>
                            <td class="text-end fw-bold">{{ number_format($invoice->other_charges ?? 0, 3) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">TAXABLE AMT.</td>
                            <td class="text-end fw-bold">{{ number_format($invoice->taxable_amount ?? 0, 3) }}</td>
                        </tr>
                        @php
                            $gstTotal = ($invoice->cgst_amount ?? 0) + ($invoice->sgst_amount ?? 0) + ($invoice->igst_amount ?? 0);
                            $gstRate = ($invoice->cgst_rate ?? 0) + ($invoice->sgst_rate ?? 0) + ($invoice->igst_rate ?? 0);
                        @endphp
                        <tr>
                            <td class="fw-bold">GST {{ $gstRate > 0 ? (int)$gstRate . '%' : '18%' }}</td>
                            <td class="text-end fw-bold">{{ number_format($gstTotal, 0) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">TCS</td>
                            <td class="text-end fw-bold">{{ number_format($invoice->tcs_amount ?? 0, 3) }}</td>
                        </tr>
                        <tr class="grand-total-row">
                            <td class="fw-bold text-uppercase">GRAND TOTAL</td>
                            <td class="text-end fw-bold">{{ number_format($invoice->grand_total ?? 0, 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
