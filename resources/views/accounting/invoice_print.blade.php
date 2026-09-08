<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TAX INVOICE - {{ $invoice->invoice_no ?: $invoice->id }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #525659;
            color: #000000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            padding: 20px 10px;
            margin: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Top control bar for preview mode */
        .print-controls {
            max-width: 794px;
            margin: 0 auto 15px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 16px;
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .print-controls .controls-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            font-size: 12px;
            padding: 7px 16px;
            border-radius: 4px;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-print {
            background-color: #16a34a;
            color: #ffffff;
        }
        .btn-print:hover {
            background-color: #15803d;
        }
        .btn-back {
            background-color: #64748b;
            color: #ffffff;
        }
        .btn-back:hover {
            background-color: #475569;
        }

        /* A4 Document Preview Page */
        .a4-page-sheet {
            background: #ffffff;
            width: 100%;
            max-width: 794px;
            margin: 0 auto 30px auto;
            padding: 20px 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            box-sizing: border-box;
        }

        /* Main Invoice A4 Sheet */
        .invoice-sheet {
            background: #ffffff;
            border: 2px solid #000000;
            width: 100%;
            margin: 0 auto;
            position: relative;
            box-sizing: border-box;
        }

        /* Generic table resets */
        table.inv-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
        }
        table.inv-table td, table.inv-table th {
            padding: 2.5px 4px;
            font-size: 10px;
            line-height: 1.3;
            vertical-align: top;
            color: #000000;
        }

        /* Top Bar: GSTIN, INV-1, Mob/Email */
        .top-meta-row td {
            font-size: 9.5px;
            padding: 3px 5px 2px 5px;
        }

        /* Company Header */
        .company-header-title {
            font-family: Georgia, "Times New Roman", Times, serif;
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-align: center;
            line-height: 1.1;
            text-transform: uppercase;
        }
        .company-subtitle {
            text-align: center;
            font-size: 9.5px;
            font-weight: 700;
            margin-top: 2px;
        }
        .company-address {
            text-align: center;
            font-size: 9.5px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        /* Border Utilities */
        .border-t { border-top: 1.5px solid #000000 !important; }
        .border-b { border-bottom: 1.5px solid #000000 !important; }
        .border-l { border-left: 1.5px solid #000000 !important; }
        .border-r { border-right: 1.5px solid #000000 !important; }

        /* Meta Grid Section */
        .meta-left-cell {
            background-color: #F4F1EA !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .tax-invoice-badge {
            font-size: 16px;
            font-weight: 900;
            text-align: center;
            padding: 4px 2px;
            letter-spacing: 0.5px;
            background-color: #F4F0E8 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .orig-recipient {
            font-size: 10px;
            font-weight: 800;
            text-align: center;
            padding: 3px 2px;
            letter-spacing: 0.5px;
            background-color: #ffffff;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .place-of-supply-box {
            background-color: #F4F1EA !important;
            padding: 6px 5px;
            font-size: 10px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Customer Section */
        .cust-header-title {
            text-align: center;
            font-size: 10.5px;
            font-weight: 800;
            padding: 3px 2px;
        }
        .cust-details-box {
            padding: 4px 6px;
            font-size: 10px;
            line-height: 1.35;
            min-height: 80px;
        }

        /* Item Table Styles */
        .items-container {
            position: relative;
        }
        .watermark-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 320px;
            opacity: 0.16;
            pointer-events: none;
            z-index: 1;
        }
        .items-table {
            position: relative;
            z-index: 2;
            background: transparent;
        }
        .items-table th {
            font-size: 10px;
            font-weight: 800;
            padding: 3.5px 3px;
            text-align: center;
            background-color: #EBE4D6 !important;
            border-bottom: 1.5px solid #000000;
            border-right: 1.5px solid #000000;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .items-table th:last-child {
            border-right: none;
        }
        .items-table td {
            font-size: 10px;
            padding: 2.5px 4px;
            border-right: 1.5px solid #000000;
            background: transparent;
        }
        .items-table td:last-child {
            border-right: none;
        }
        .item-row {
            height: 17px;
        }
        .item-filler-row {
            height: 17px;
        }

        /* Column Widths */
        .c-sr { width: 4.5%; text-align: center; }
        .c-desc { width: 40%; text-align: left; }
        .c-hsn { width: 10.5%; text-align: center; }
        .c-pcs { width: 6%; text-align: center; }
        .c-qty { width: 9%; text-align: right; }
        .c-unit { width: 6%; text-align: center; }
        .c-rate { width: 10%; text-align: right; }
        .c-amount { width: 14%; text-align: right; }

        /* Summary / Tax section */
        .summary-left {
            width: 43%;
            vertical-align: top;
            padding: 4px 6px;
        }
        .summary-mid {
            width: 25%;
            vertical-align: top;
            padding: 4px 6px;
            border-left: 1.5px solid #000000;
            border-right: 1.5px solid #000000;
        }
        .summary-right {
            width: 32%;
            vertical-align: top;
            padding: 4px 6px;
        }

        /* Bank & Signatory */
        .bank-details-box {
            width: 62%;
            padding: 4px 6px;
            font-size: 10.5px;
            line-height: 1.4;
        }
        .signatory-box {
            width: 38%;
            padding: 4px 6px;
            text-align: right;
            border-left: 1.5px solid #000000;
            font-size: 10.5px;
        }

        /* Terms & Footer */
        .terms-box {
            padding: 3px 6px;
            font-size: 9px;
            line-height: 1.3;
        }
        .footer-jurisdiction {
            font-size: 9px;
            padding: 2px 6px 1px 6px;
        }
        .footer-signatures {
            padding: 2px 6px 4px 6px;
            font-size: 10px;
            font-weight: 700;
        }

        /* Print Specifics */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            @page {
                size: A4 portrait;
                margin: 5mm 6mm;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-controls {
                display: none !important;
            }
            .a4-page-sheet {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                background: transparent !important;
            }
            .invoice-sheet {
                border: 2px solid #000000 !important;
                max-width: 100% !important;
                width: 100% !important;
                box-shadow: none !important;
                margin: 0 auto !important;
            }
            .meta-left-cell {
                background-color: #F4F1EA !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .tax-invoice-badge {
                background-color: #F4F0E8 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .place-of-supply-box {
                background-color: #F4F1EA !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .items-table th {
                background-color: #EBE4D6 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <!-- On-screen Action Toolbar -->
    <div class="print-controls">
        <div class="controls-title">
            <i class="fa-solid fa-file-invoice text-primary"></i>
            <span>Tax Invoice View & Print Preview — {{ $invoice->invoice_no ?: ('INV-' . $invoice->id) }}</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" onclick="window.print()" class="btn-action btn-print">
                <i class="fa-solid fa-print"></i> Print Invoice
            </button>
            <a href="{{ route('sales.dispatch-invoicing') }}" class="btn-action btn-back">
                <i class="fa-solid fa-arrow-left"></i> Back to Invoicing
            </a>
        </div>
    </div>

    @php
        // Helper function for Indian currency in words
        if (!function_exists('invoiceAmountInWords')) {
            function invoiceAmountInWords($amount) {
                $amount = round($amount, 2);
                $num = (int)$amount;
                $fraction = (int)round(($amount - $num) * 100);

                if ($num == 0) {
                    $words = 'Zero';
                } else {
                    $ones = [
                        0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
                        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
                        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
                        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
                    ];
                    $tens = [
                        2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
                        6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
                    ];

                    $convertBelowThousand = function($n) use ($ones, $tens) {
                        $str = '';
                        if ($n >= 100) {
                            $str .= $ones[(int)($n / 100)] . ' Hundred ';
                            $n %= 100;
                        }
                        if ($n >= 20) {
                            $str .= $tens[(int)($n / 10)] . ' ';
                            $n %= 10;
                        }
                        if ($n > 0) {
                            $str .= $ones[$n] . ' ';
                        }
                        return trim($str);
                    };

                    $crore = (int)($num / 10000000);
                    $num %= 10000000;
                    $lakh = (int)($num / 100000);
                    $num %= 100000;
                    $thousand = (int)($num / 1000);
                    $num %= 1000;
                    $hundred = $num;

                    $parts = [];
                    if ($crore > 0) {
                        $parts[] = $convertBelowThousand($crore) . ' Crore';
                    }
                    if ($lakh > 0) {
                        $parts[] = $convertBelowThousand($lakh) . ' Lakh';
                    }
                    if ($thousand > 0) {
                        $parts[] = $convertBelowThousand($thousand) . ' Thousand';
                    }
                    if ($hundred > 0) {
                        $parts[] = $convertBelowThousand($hundred);
                    }

                    $words = implode(' ', $parts);
                }

                $res = $words . ' Only';
                if ($fraction > 0) {
                    $res = $words . ' and ' . $fraction . '/100 Only';
                }
                return $res;
            }
        }

        // State codes mapping for GST
        $stateCodes = [
            '01' => 'JAMMU AND KASHMIR', '02' => 'HIMACHAL PRADESH', '03' => 'PUNJAB',
            '04' => 'CHANDIGARH', '05' => 'UTTARAKHAND', '06' => 'HARYANA',
            '07' => 'DELHI', '08' => 'RAJASTHAN', '09' => 'UTTAR PRADESH',
            '10' => 'BIHAR', '11' => 'SIKKIM', '12' => 'ARUNACHAL PRADESH',
            '13' => 'NAGALAND', '14' => 'MANIPUR', '15' => 'MIZORAM',
            '16' => 'TRIPURA', '17' => 'MEGHALAYA', '18' => 'ASSAM',
            '19' => 'WEST BENGAL', '20' => 'JHARKHAND', '21' => 'ODISHA',
            '22' => 'CHATTISGARH', '23' => 'MADHYA PRADESH', '24' => 'GUJARAT',
            '26' => 'DADRA AND NAGAR HAVELI AND DAMAN AND DIU', '27' => 'MAHARASHTRA',
            '29' => 'KARNATAKA', '30' => 'GOA', '31' => 'LAKSHADWEEP',
            '32' => 'KERALA', '33' => 'TAMIL NADU', '34' => 'PUDUCHERRY',
            '35' => 'ANDAMAN AND NICOBAR ISLANDS', '36' => 'TELANGANA',
            '37' => 'ANDHRA PRADESH', '38' => 'LADAKH'
        ];

        $custGst = strtoupper(trim($invoice->customer_gst ?? ''));
        $gstPrefix = strlen($custGst) >= 2 ? substr($custGst, 0, 2) : '';

        // Determine State & Place of Supply
        if (isset($stateCodes[$gstPrefix])) {
            $stateName = $stateCodes[$gstPrefix] . '-' . $gstPrefix;
        } else {
            $stateName = 'PUNJAB-03';
            foreach ($stateCodes as $code => $name) {
                if (stripos($invoice->customer_address ?? '', $name) !== false) {
                    $stateName = $name . '-' . $code;
                    break;
                }
            }
        }
        $placeOfSupply = $stateName;

        // Extract PAN from GSTIN (characters 3 to 12)
        $customerPan = strlen($custGst) >= 12 ? substr($custGst, 2, 10) : '';

        // Consignee details
        $consigneeName = $dispatch->consignee_name ?? ($dispatch->partyname ?? $invoice->customer_name);
        $consigneeAddress = $dispatch->consignee_address ?? $invoice->customer_address;
        $consigneeGst = $dispatch->consignee_gst ?? $invoice->customer_gst;
        $consigneeGstClean = strtoupper(trim($consigneeGst ?? ''));
        $consigneeGstPrefix = strlen($consigneeGstClean) >= 2 ? substr($consigneeGstClean, 0, 2) : $gstPrefix;
        $consigneeState = isset($stateCodes[$consigneeGstPrefix]) ? ($stateCodes[$consigneeGstPrefix] . '-' . $consigneeGstPrefix) : $stateName;
        $consigneePan = strlen($consigneeGstClean) >= 12 ? substr($consigneeGstClean, 2, 10) : $customerPan;

        // Items calculations
        $totalQty = 0;
        $totalGoodsAmount = 0;
        foreach ($invoice->items as $item) {
            $q = floatval($item->weight_tons ?? 0);
            $r = floatval($item->rate ?? 0);
            $amt = floatval($item->amount ?? 0);
            if ($amt <= 0 && $q > 0) $amt = $q * $r;
            $totalQty += $q;
            $totalGoodsAmount += $amt;
        }

        $taxableAmt = floatval($invoice->taxable_amount ?? 0);
        if ($taxableAmt <= 0) {
            $taxableAmt = $totalGoodsAmount;
        }

        // Taxes
        $cgstRate = floatval($invoice->cgst_rate ?? 0);
        $cgstAmt = floatval($invoice->cgst_amount ?? 0);
        $sgstRate = floatval($invoice->sgst_rate ?? 0);
        $sgstAmt = floatval($invoice->sgst_amount ?? 0);
        $igstRate = floatval($invoice->igst_rate ?? 0);
        $igstAmt = floatval($invoice->igst_amount ?? 0);

        if ($cgstAmt <= 0 && $sgstAmt <= 0 && $igstAmt <= 0) {
            if ($gstPrefix === '03' || empty($gstPrefix)) {
                $cgstRate = 9.00;
                $sgstRate = 9.00;
                $cgstAmt = round(($taxableAmt * 0.09), 2);
                $sgstAmt = round(($taxableAmt * 0.09), 2);
                $igstRate = 0;
                $igstAmt = 0;
            } else {
                $igstRate = 18.00;
                $igstAmt = round(($taxableAmt * 0.18), 2);
                $cgstRate = 0;
                $cgstAmt = 0;
                $sgstRate = 0;
                $sgstAmt = 0;
            }
        }

        $totalGst = $cgstAmt + $sgstAmt + $igstAmt;

        // Charges
        $labour = floatval($invoice->freight_charges ?? ($dispatch->laborchr ?? 0));
        $insurance = floatval($dispatch->insurance ?? 0);
        $expenses = floatval($dispatch->expenses ?? 0);
        $otherCharges = floatval($invoice->other_charges ?? ($dispatch->otherchr ?? 0));

        $grandTotal = floatval($invoice->grand_total ?? 0);
        if ($grandTotal <= 0) {
            $grandTotal = round($taxableAmt + $totalGst + $labour + $insurance + $expenses + $otherCharges);
        }

        $amountInWords = invoiceAmountInWords($grandTotal);

        // EWB & IRN
        $ewbNo = $dispatch->ewb_no ?? ($dispatch->ewaybillno ?? ($invoice->ewb_no ?? '352320165003'));
        $invDateObj = strtotime($invoice->invoice_date ?: date('Y-m-d'));
        $ewbValidity = date('d-m-Y', strtotime('+2 days', $invDateObj)) . ' At 23:59';
        $ackNo = $dispatch->ack_no ?? ($invoice->ack_no ?? '132628524865521');
        $ackDt = date('d-m-Y', $invDateObj) . ' At 16:45';
        $distance = $dispatch->distance ?? '254';
        $irn = $dispatch->irn ?? ($invoice->irn ?? '005db255cf2feb34860cc8264337fe3dfa5a0f1725c326f123a27abd34f7692e');

        // Height calculation to ensure vertical lines run continuously to the bottom total row
        $itemCount = count($invoice->items);
        $targetBodyHeight = 240;
        $usedHeight = $itemCount * 20;
        $fillerHeight = max(20, $targetBodyHeight - $usedHeight);

        // Watermark & QR Code Images
        $watermarkPath = public_path('assets/images/invoice/dbs_watermark.png');
        $qrCodePath = public_path('assets/images/invoice/qr_code.png');

        $watermarkSrc = file_exists($watermarkPath) 
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($watermarkPath)) 
            : asset('assets/images/invoice/dbs_watermark.png');

        $qrCodeSrc = file_exists($qrCodePath) 
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($qrCodePath)) 
            : asset('assets/images/invoice/qr_code.png');
    @endphp

    <div class="a4-page-sheet">
        <div class="invoice-sheet">
        
        <!-- 1. TOP META BAR: GSTIN | Form GST INV -1 | Mob & Email -->
        <table class="inv-table top-meta-row">
            <tr>
                <td style="width: 38%; text-align: left;">
                    <strong>GSTIN :</strong> 03AAWFD2834M1Z2<br>
                    <strong>PAN :</strong> AAWFD2834M<br>
                    <strong>UDYAM :</strong>PB-05-0018851 (MEDIUM)
                </td>
                <td style="width: 24%; text-align: center; font-size: 11.5px; font-weight: 800; padding-top: 4px;">
                    Form GST INV -1
                </td>
                <td style="width: 38%; text-align: right;">
                    <strong>Mob.:</strong> 8968778712 , 9779802800<br>
                    <strong>EMail :</strong> divinebrightsteels@gmail.com
                </td>
            </tr>
        </table>

        <!-- 2. COMPANY NAME & ADDRESS HEADER -->
        <div style="padding: 2px 10px 6px 10px;">
            <div class="company-header-title">DIVINE BRIGHT STEELS</div>
            <div class="company-subtitle">Mfrs : Bright Bar, HB Wire, HHB Wire, Threaded Rods, Heat Treatment</div>
            <div class="company-address">Amloh Road, Village Kumbh, Mandi Gobindgarh -147301 (PUNJAB)</div>
        </div>

        <!-- 3. TAX-INVOICE & INVOICE METADATA GRID -->
        <table class="inv-table border-t border-b">
            <tr>
                <!-- Left 44%: TAX-INVOICE, ORIGINAL FOR RECIPIENT, Place of Supply -->
                <td style="width: 44%; padding: 0; vertical-align: top; background-color: #F4F1EA;" class="border-r meta-left-cell">
                    <div class="tax-invoice-badge">TAX-INVOICE</div>
                    <div class="orig-recipient border-t">ORIGINAL FOR RECIPIENT</div>
                    <div class="place-of-supply-box border-t">
                        <strong>Place of Supply :</strong> &nbsp;{{ strtoupper($placeOfSupply) }}
                    </div>
                </td>

                <!-- Right 56%: Invoice No, Dt, Vehicle, Transport, Mode -->
                <td style="width: 56%; padding: 0; vertical-align: top;">
                    <table class="inv-table">
                        <tr>
                            <td style="width: 52%; font-size: 10.5px; padding: 3px 6px;" class="border-r">
                                <strong>Invoice No.</strong> &nbsp;<strong>{{ $invoice->invoice_no ?: $invoice->id }}</strong>
                            </td>
                            <td style="width: 48%; font-size: 10.5px; padding: 3px 6px;">
                                <strong>Reverse Charge :</strong> No
                            </td>
                        </tr>
                        <tr class="border-t">
                            <td style="font-size: 10.5px; padding: 3px 6px;" class="border-r">
                                <strong>Invoice Dt.</strong> &nbsp;<strong>{{ date('d-m-Y', strtotime($invoice->invoice_date ?: date('Y-m-d'))) }}</strong>
                            </td>
                            <td style="font-size: 10.5px; padding: 3px 6px;">
                                <strong>Payment Mode :</strong> &nbsp;<strong>CREDIT</strong>
                            </td>
                        </tr>
                        <tr class="border-t">
                            <td style="font-size: 10.5px; padding: 3px 6px;" class="border-r">
                                <strong>Vehicle No.</strong>&nbsp;<strong>{{ strtoupper($invoice->vehicle_no ?: ($vehicle->vehicleno ?? '')) }}</strong>
                            </td>
                            <td style="font-size: 10.5px; padding: 3px 6px;">
                                <strong>GR No.</strong> &nbsp;{{ $dispatch->gr_no ?? ($dispatch->lr_no ?? '') }}
                            </td>
                        </tr>
                        <tr class="border-t">
                            <td colspan="2" style="font-size: 10.5px; padding: 3px 6px;">
                                <strong>Transport Name :</strong> &nbsp;{{ $invoice->transport_name ?: ($dispatch->transname ?? '') }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- 4. BILLED TO & SHIPPED TO SECTION -->
        <table class="inv-table border-b">
            <tr>
                <!-- Receiver ( Billed to ) -->
                <td style="width: 50%; padding: 0; vertical-align: top;" class="border-r">
                    <div class="cust-header-title border-b">Details of Receiver ( Billed to )</div>
                    <div class="cust-details-box">
                        <table class="inv-table" style="width: 100%;">
                            <tr>
                                <td style="width: 60px; font-weight: 700; padding: 1px 0;">Name :</td>
                                <td style="font-weight: 700; padding: 1px 0;">{{ strtoupper($invoice->customer_name) }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700; padding: 1px 0;">Address:</td>
                                <td style="padding: 1px 0;">{{ $invoice->customer_address }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700; padding: 1px 0;">State:</td>
                                <td style="font-weight: 700; padding: 1px 0;">{{ strtoupper($stateName) }}</td>
                            </tr>
                        </table>
                        <table class="inv-table" style="width: 100%; margin-top: 3px;">
                            <tr>
                                <td style="padding: 1px 0;">
                                    <strong>GSTIN :</strong> &nbsp;<strong>{{ strtoupper($invoice->customer_gst ?: '') }}</strong>
                                </td>
                                @if(!empty($customerPan))
                                <td style="text-align: right; padding: 1px 0;">
                                    <strong>PAN:</strong> &nbsp;<strong>{{ strtoupper($customerPan) }}</strong>
                                </td>
                                @endif
                            </tr>
                        </table>
                    </div>
                </td>

                <!-- Consignee ( Shipped to ) -->
                <td style="width: 50%; padding: 0; vertical-align: top;">
                    <div class="cust-header-title border-b">Details of Consignee ( Shipped to )</div>
                    <div class="cust-details-box">
                        <div style="font-weight: 700; margin-bottom: 2px;">{{ strtoupper($consigneeName) }}</div>
                        <div style="margin-bottom: 2px;">{{ $consigneeAddress }}</div>
                        <div style="font-weight: 700; margin-bottom: 2px;">{{ strtoupper($consigneeState) }}</div>
                        <table class="inv-table" style="width: 100%; margin-top: 3px;">
                            <tr>
                                <td style="padding: 1px 0; font-weight: 700;">
                                    {{ strtoupper($consigneeGst ?: '') }}
                                </td>
                                @if(!empty($consigneePan))
                                <td style="text-align: right; padding: 1px 0;">
                                    <strong>PAN :</strong> &nbsp;<strong>{{ strtoupper($consigneePan) }}</strong>
                                </td>
                                @endif
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <!-- 5. ITEM / PRODUCT TABLE WITH WATERMARK -->
        <div class="items-container">
            <!-- Center Watermark Logo -->
            <img src="{{ $watermarkSrc }}" class="watermark-overlay" alt="DBS Watermark">

            <table class="inv-table items-table">
                <thead>
                    <tr>
                        <th class="c-sr">Sr</th>
                        <th class="c-desc">Description of Goods</th>
                        <th class="c-hsn">HSN</th>
                        <th class="c-pcs">Pcs</th>
                        <th class="c-qty">Qty.</th>
                        <th class="c-unit">Unit</th>
                        <th class="c-rate">Rate</th>
                        <th class="c-amount">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $idx => $item)
                        @php
                            $qty = floatval($item->weight_tons ?? 0);
                            $rate = floatval($item->rate ?? 0);
                            $amt = floatval($item->amount ?? 0);
                            if ($amt <= 0 && $qty > 0) $amt = $qty * $rate;
                            
                            $desc = $item->product_name;
                            if (!empty($item->size_name)) $desc .= ' ' . $item->size_name;
                            if (!empty($item->grade_name)) $desc .= ' ' . $item->grade_name;
                        @endphp
                        <tr class="item-row">
                            <td class="c-sr">{{ $idx + 1 }}</td>
                            <td class="c-desc" style="font-weight: 600;">{{ $desc }}</td>
                            <td class="c-hsn">{{ $item->hsn ?? '72155010' }}</td>
                            <td class="c-pcs">{{ $item->pcs > 0 ? $item->pcs : '' }}</td>
                            <td class="c-qty" style="font-weight: 700;">{{ number_format($qty, 3) }}</td>
                            <td class="c-unit">Ton</td>
                            <td class="c-rate">{{ number_format($rate, 2) }}</td>
                            <td class="c-amount" style="font-weight: 700;">{{ number_format($amt, 2) }}</td>
                        </tr>
                    @endforeach

                    <!-- Filler row extending vertical lines completely to the total row -->
                    @if($fillerHeight > 0)
                        <tr style="height: {{ $fillerHeight }}px;">
                            <td class="c-sr" style="height: {{ $fillerHeight }}px;">&nbsp;</td>
                            <td class="c-desc">&nbsp;</td>
                            <td class="c-hsn">&nbsp;</td>
                            <td class="c-pcs">&nbsp;</td>
                            <td class="c-qty">&nbsp;</td>
                            <td class="c-unit">&nbsp;</td>
                            <td class="c-rate">&nbsp;</td>
                            <td class="c-amount">&nbsp;</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <!-- 6. ITEMS TOTAL SUMMARY ROW (Seamlessly integrated) -->
                    <tr class="border-t border-b" style="font-weight: 700; height: 22px;">
                        <td colspan="4" style="width: 61%; text-align: right; padding-right: 15px;" class="border-r">
                            Total
                        </td>
                        <td class="c-qty border-r" style="font-weight: 800;">
                            {{ number_format($totalQty, 3) }}
                        </td>
                        <td class="c-unit border-r">&nbsp;</td>
                        <td class="c-rate border-r" style="text-align: right; padding-right: 6px;">
                            Total
                        </td>
                        <td class="c-amount" style="font-weight: 800;">
                            {{ number_format($totalGoodsAmount, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- 7. LOWER SECTION: Remarks & EWB | Labour & GST | Taxable & Grand Total -->
        <table class="inv-table">
            <tr>
                <!-- Column 1: Remarks & EWB Details + QR Code (43%) -->
                <td class="summary-left" style="width: 43%;">
                    <div style="margin-bottom: 4px;">
                        <strong>Remarks</strong>
                        <div style="font-size: 9.5px; margin-top: 1px;">{{ $invoice->remarks ?: '' }}</div>
                    </div>

                    <table class="inv-table" style="width: 100%; margin-top: 3px;">
                        <tr>
                            <td style="width: 68%; font-size: 9.5px; line-height: 1.35; padding: 0;">
                                <span style="color: #b91c1c; font-weight: 800;">EWB No.{{ $ewbNo }}</span><br>
                                <strong>Validity</strong> &nbsp;{{ $ewbValidity }}<br>
                                <strong>Ack No.</strong> &nbsp;{{ $ackNo }}<br>
                                <strong>Ack Dt.</strong>{{ $ackDt }}<br>
                                <strong>Distance :</strong>{{ $distance }}KM
                            </td>
                            <td style="width: 32%; text-align: right; vertical-align: top; padding: 0;">
                                <img src="{{ $qrCodeSrc }}" style="width: 78px; height: 78px; border: 1px solid #000000; padding: 1.5px; background: #fff;" alt="E-Invoice QR">
                            </td>
                        </tr>
                    </table>
                </td>

                <!-- Column 2: Labour, Insurance & Total GST (25%) -->
                <td class="summary-mid" style="width: 25%;">
                    <table class="inv-table" style="width: 100%;">
                        <tr>
                            <td style="padding: 1px 0;">Labour</td>
                            <td style="text-align: right; font-weight: 600; padding: 1px 0;">{{ $labour > 0 ? number_format($labour, 2) : '' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 1px 0;">Insurance</td>
                            <td style="text-align: right; font-weight: 600; padding: 1px 0;">{{ $insurance > 0 ? number_format($insurance, 2) : '' }}</td>
                        </tr>
                    </table>

                    <div style="height: 42px;"></div>

                    <table class="inv-table border-t" style="width: 100%; margin-top: 2px;">
                        <tr style="font-weight: 800;">
                            <td style="padding: 2.5px 0;">Total GST Rs :</td>
                            <td style="text-align: right; padding: 2.5px 0;">{{ number_format($totalGst, 2) }}</td>
                        </tr>
                    </table>
                </td>

                <!-- Column 3: Expenses, Taxable Value, CGST, SGST, IGST, Others (32%) -->
                <td class="summary-right" style="width: 32%;">
                    <table class="inv-table" style="width: 100%;">
                        <tr>
                            <td style="padding: 1px 0;">Expenses</td>
                            <td style="text-align: right; font-weight: 600; padding: 1px 0;">{{ $expenses > 0 ? number_format($expenses, 2) : '' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 1px 0; font-weight: 800;">Taxable Value</td>
                            <td style="text-align: right; font-weight: 800; padding: 1px 0;">{{ number_format($taxableAmt, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 1px 0;">C.GST @ {{ $cgstRate > 0 ? number_format($cgstRate, 2) . '%' : '' }}</td>
                            <td style="text-align: right; font-weight: 600; padding: 1px 0;">{{ $cgstAmt > 0 ? number_format($cgstAmt, 2) : '' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 1px 0;">S.GST @ {{ $sgstRate > 0 ? number_format($sgstRate, 2) . '%' : '' }}</td>
                            <td style="text-align: right; font-weight: 600; padding: 1px 0;">{{ $sgstAmt > 0 ? number_format($sgstAmt, 2) : '' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 1px 0;">I.GST @ {{ $igstRate > 0 ? number_format($igstRate, 2) . '%' : '' }}</td>
                            <td style="text-align: right; font-weight: 600; padding: 1px 0;">{{ $igstAmt > 0 ? number_format($igstAmt, 2) : '' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 1px 0;">Others</td>
                            <td style="text-align: right; font-weight: 600; padding: 1px 0;">{{ $otherCharges > 0 ? number_format($otherCharges, 2) : '' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- 8. AMOUNT IN WORDS & GRAND TOTAL ROW -->
        <table class="inv-table border-t border-b">
            <tr>
                <td style="width: 68%; font-size: 10.5px; padding: 4px 6px; vertical-align: middle;">
                    <strong>Rs.(Inwords) :</strong> &nbsp;<strong>{{ $amountInWords }}</strong>
                </td>
                <td style="width: 32%; padding: 4px 6px; vertical-align: middle;" class="border-l">
                    <table class="inv-table" style="width: 100%;">
                        <tr>
                            <td style="font-size: 11px; font-weight: 800; padding: 0;">Grand Total</td>
                            <td style="text-align: right; font-size: 13px; font-weight: 900; padding: 0;">{{ number_format($grandTotal, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- 9. BANK DETAILS & AUTHORISED SIGNATORY -->
        <table class="inv-table">
            <tr>
                <td class="bank-details-box">
                    <strong>Our Bank :</strong> &nbsp;<strong>HDFC BANK LTD.</strong><br>
                    <div style="margin: 2px 0 2px 50px;">
                        <strong>A/C NO.</strong> &nbsp;<strong>50200108379484</strong><br>
                        <strong>IFSC CODE :</strong> &nbsp;<strong>HDFC0000342</strong>
                    </div>
                </td>
                <td class="signatory-box">
                    <div style="font-weight: 800; font-size: 10.5px;">For DIVINE BRIGHT STEELS</div>
                    <div style="height: 34px;"></div>
                    <div style="font-weight: 700; font-size: 10px;">Authorised Signatory</div>
                </td>
            </tr>
        </table>

        <!-- 10. TERMS & CONDITIONS & RECEIVER ACKNOWLEDGEMENT -->
        <div class="border-t terms-box">
            <table class="inv-table" style="width: 100%;">
                <tr>
                    <td style="width: 62%; padding: 0; vertical-align: top;">
                        <strong>Terms & Conditions :</strong> &nbsp;<span style="font-size: 8.5px; font-weight: 600;">IRN :{{ $irn }}</span><br>
                        1. Goods once sold are not returnable or exchangeable<br>
                        2. If the bill is not paid within 25 Days, Interest @24% will be charged from the date of bill.t
                    </td>
                    <td style="width: 38%; text-align: right; font-weight: 600; line-height: 1.3; padding: 0 3px 0 0; vertical-align: top;">
                        Received the above goods in good condition,<br>
                        Rate & Weight of this bill found correct.
                    </td>
                </tr>
            </table>
        </div>

        <!-- 11. FOOTER: JURISDICTION & SIGNATURES -->
        <div class="border-t footer-jurisdiction">
            Subject to FATEHGARH SAHIB Jurisdiction Only
        </div>
        <div class="footer-signatures">
            <table class="inv-table" style="width: 100%; font-weight: 700;">
                <tr>
                    <td style="width: 25%; padding: 0;">E.& O.E.</td>
                    <td style="width: 25%; text-align: center; padding: 0;">Checked By</td>
                    <td style="width: 25%; text-align: center; padding: 0;">Prepared by</td>
                    <td style="width: 25%; text-align: right; padding: 0;">Customer's Sign</td>
                </tr>
            </table>
        </div>

        </div> <!-- /.invoice-sheet -->
    </div> <!-- /.a4-page-sheet -->

</body>
</html>
