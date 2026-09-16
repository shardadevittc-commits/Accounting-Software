<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ strtoupper($voucher->type_label) }} - {{ $voucher->voucher_no }}</title>
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
            padding: 10px 16px;
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

        /* A4 Page Container */
        .a4-page-sheet {
            background: #ffffff;
            width: 100%;
            max-width: 794px;
            margin: 0 auto 30px auto;
            padding: 24px 28px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            box-sizing: border-box;
        }

        .voucher-sheet {
            background: #ffffff;
            border: 2px solid #000000;
            width: 100%;
            margin: 0 auto;
            position: relative;
            box-sizing: border-box;
        }

        /* Generic table resets */
        table.v-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
        }
        table.v-table td, table.v-table th {
            padding: 6px 8px;
            font-size: 11px;
            line-height: 1.35;
            vertical-align: top;
            color: #000000;
        }

        /* Company Header */
        .company-header-title {
            font-family: Georgia, "Times New Roman", Times, serif;
            font-size: 24px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-align: center;
            line-height: 1.1;
            text-transform: uppercase;
        }
        .company-subtitle {
            text-align: center;
            font-size: 10px;
            font-weight: 700;
            margin-top: 2px;
        }
        .company-address {
            text-align: center;
            font-size: 10px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        /* Voucher Header Badge */
        .voucher-type-banner {
            background-color: #F4F1EA !important;
            font-size: 14px;
            font-weight: 900;
            text-align: center;
            padding: 6px 2px;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-top: 1.5px solid #000000;
            border-bottom: 1.5px solid #000000;
        }

        /* Table Border Utilities */
        .border-t { border-top: 1.5px solid #000000 !important; }
        .border-b { border-bottom: 1.5px solid #000000 !important; }
        .border-l { border-left: 1.5px solid #000000 !important; }
        .border-r { border-right: 1.5px solid #000000 !important; }

        .items-header th {
            background-color: #EBE4D6 !important;
            font-weight: 800;
            font-size: 10.5px;
            text-transform: uppercase;
            border-bottom: 1.5px solid #000000;
            border-top: 1.5px solid #000000;
        }

        .items-row td {
            border-bottom: 1px solid #e2e8f0;
        }

        /* Signature block */
        .sig-box {
            height: 50px;
            border-bottom: 1px solid #000000;
            margin-bottom: 4px;
        }

        @media print {
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
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
            .voucher-sheet {
                border: 2px solid #000000 !important;
                box-shadow: none !important;
            }
            .voucher-type-banner {
                background-color: #F4F1EA !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .items-header th {
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
            <i class="fa-solid fa-file-invoice-dollar text-primary"></i>
            <span>{{ $voucher->type_label }} Print Preview — {{ $voucher->voucher_no }}</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" onclick="window.print()" class="btn-action btn-print">
                <i class="fa-solid fa-print"></i> Print Voucher
            </button>
            <a href="{{ route('vouchers.index') }}" class="btn-action btn-back">
                <i class="fa-solid fa-arrow-left"></i> Back to Vouchers
            </a>
        </div>
    </div>

    @php
        // Helper function for Indian currency in words
        if (!function_exists('voucherAmountInWords')) {
            function voucherAmountInWords($amount) {
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
                    $remainder = $num;

                    $parts = [];
                    if ($crore > 0) $parts[] = $convertBelowThousand($crore) . ' Crore';
                    if ($lakh > 0) $parts[] = $convertBelowThousand($lakh) . ' Lakh';
                    if ($thousand > 0) $parts[] = $convertBelowThousand($thousand) . ' Thousand';
                    if ($remainder > 0) $parts[] = $convertBelowThousand($remainder);

                    $words = implode(' ', $parts);
                }

                $res = 'INR ' . trim($words);
                if ($fraction > 0) {
                    $res .= ' and ' . $fraction . '/100';
                }
                return $res . ' Only';
            }
        }

        $amountInWords = voucherAmountInWords($voucher->total_debit);
    @endphp

    <div class="a4-page-sheet">
        <div class="voucher-sheet">
            
            <!-- 1. TOP HEADER -->
            <table class="v-table">
                <tr>
                    <td style="width: 35%; text-align: left; font-size: 10px;">
                        <strong>GSTIN:</strong> 03AAWFD2834M1Z2<br>
                        <strong>PAN:</strong> AAWFD2834M<br>
                        <strong>State:</strong> Punjab (03)
                    </td>
                    <td style="width: 30%; text-align: center; font-size: 11px; font-weight: 800;">
                        ACCOUNTING VOUCHER
                    </td>
                    <td style="width: 35%; text-align: right; font-size: 10px;">
                        <strong>Phone:</strong> 8968778712, 9779802800<br>
                        <strong>Email:</strong> divinebrightsteels@gmail.com
                    </td>
                </tr>
            </table>

            <!-- 2. COMPANY DETAILS -->
            <div style="padding: 2px 10px 8px 10px;">
                <div class="company-header-title">DIVINE BRIGHT STEELS</div>
                <div class="company-subtitle">Manufacturers of Bright Bar, HB Wire, HHB Wire, Threaded Rods</div>
                <div class="company-address">Amloh Road, Village Kumbh, Mandi Gobindgarh - 147301 (PUNJAB)</div>
            </div>

            <!-- 3. VOUCHER TYPE BANNER -->
            <div class="voucher-type-banner">
                {{ strtoupper($voucher->type_label) }}
            </div>

            <!-- 4. VOUCHER METADATA GRID -->
            <table class="v-table border-b">
                <tr>
                    <td style="width: 25%;" class="border-r">
                        <strong>Voucher No:</strong><br>
                        <span style="font-size: 13px; font-weight: 800; font-family: monospace;">{{ $voucher->voucher_no }}</span>
                    </td>
                    <td style="width: 25%;" class="border-r">
                        <strong>Voucher Date:</strong><br>
                        <span style="font-weight: 700;">{{ $voucher->voucher_date ? $voucher->voucher_date->format('d-M-Y') : date('d-M-Y') }}</span>
                    </td>
                    <td style="width: 25%;" class="border-r">
                        <strong>Reference / Bill No:</strong><br>
                        <span>{{ $voucher->reference_no ?: '-' }}</span>
                    </td>
                    <td style="width: 25%;">
                        <strong>Status:</strong><br>
                        <span style="font-weight: 700; color: {{ $voucher->status === 'posted' ? '#15803d' : '#b45309' }};">
                            {{ strtoupper($voucher->status) }}
                        </span>
                    </td>
                </tr>
                @if($voucher->party_name || $voucher->bankAccount || $voucher->instrument_no)
                <tr class="border-t">
                    <td colspan="2" class="border-r">
                        <strong>Party / Counter Account:</strong><br>
                        <span style="font-weight: 700; font-size: 12px;">{{ $voucher->party_name ?: ($voucher->bankAccount?->name ?: 'General Ledger') }}</span>
                    </td>
                    <td colspan="2">
                        @if($voucher->bankAccount)
                            <strong>Bank Account:</strong> {{ $voucher->bankAccount->name }}<br>
                        @endif
                        @if($voucher->instrument_no)
                            <strong>Mode / Chq No:</strong> {{ strtoupper($voucher->payment_method ?: 'Bank') }} #{{ $voucher->instrument_no }} (Dt: {{ $voucher->instrument_date ? $voucher->instrument_date->format('d-M-Y') : '-' }})
                        @endif
                        @if($voucher->invoice)
                            <strong>Linked Sales Invoice:</strong> {{ $voucher->invoice->invoice_no }}
                        @endif
                    </td>
                </tr>
                @endif
            </table>

            <!-- 5. DOUBLE-ENTRY ACCOUNTING ENTRIES TABLE -->
            <table class="v-table">
                <thead>
                    <tr class="items-header">
                        <th style="width: 5%; text-align: center;">#</th>
                        <th style="width: 45%; text-align: left;" class="border-l">Particulars / Account Name</th>
                        <th style="width: 20%; text-align: left;" class="border-l">Line Description</th>
                        <th style="width: 15%; text-align: right;" class="border-l">Debit (₹)</th>
                        <th style="width: 15%; text-align: right;" class="border-l">Credit (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($voucher->entries as $index => $entry)
                    <tr class="items-row">
                        <td style="text-align: center; color: #555;">{{ $index + 1 }}</td>
                        <td class="border-l">
                            <strong style="font-size: 11.5px;">{{ $entry->ledger?->name ?? 'Unknown Ledger' }}</strong>
                            <div style="font-size: 9.5px; color: #666; font-family: monospace;">A/c Code: {{ $entry->ledger?->code ?? '-' }} | {{ ucfirst($entry->ledger?->category ?? '') }}</div>
                        </td>
                        <td class="border-l" style="font-size: 10px; color: #333;">
                            {{ $entry->description ?: '-' }}
                        </td>
                        <td class="border-l" style="text-align: right; font-family: monospace; font-weight: 700;">
                            {{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}
                        </td>
                        <td class="border-l" style="text-align: right; font-family: monospace; font-weight: 700;">
                            {{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}
                        </td>
                    </tr>
                    @endforeach

                    <!-- Filler empty rows to give standard printed voucher height -->
                    @for($i = $voucher->entries->count(); $i < 4; $i++)
                    <tr class="items-row" style="height: 28px;">
                        <td></td>
                        <td class="border-l"></td>
                        <td class="border-l"></td>
                        <td class="border-l"></td>
                        <td class="border-l"></td>
                    </tr>
                    @endfor
                </tbody>
                <tfoot>
                    <tr class="border-t" style="font-weight: 900; background: #F8FAFC;">
                        <td colspan="3" style="text-align: right; font-size: 11px;">TOTAL:</td>
                        <td class="border-l" style="text-align: right; font-family: monospace; font-size: 12px;">₹{{ number_format($voucher->total_debit, 2) }}</td>
                        <td class="border-l" style="text-align: right; font-family: monospace; font-size: 12px;">₹{{ number_format($voucher->total_credit, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <!-- 6. AMOUNT IN WORDS & NARRATION -->
            <table class="v-table border-t border-b">
                <tr>
                    <td colspan="2" style="background: #FAFAF9; padding: 8px 10px;">
                        <strong>Amount (in words):</strong>
                        <span style="font-weight: 700; font-size: 11px;">{{ $amountInWords }}</span>
                    </td>
                </tr>
                <tr class="border-t">
                    <td colspan="2" style="padding: 8px 10px;">
                        <strong>Narration:</strong><br>
                        <span style="font-style: italic; font-size: 11px;">{{ $voucher->narration ?: 'Being voucher posted for double-entry accounting transactions.' }}</span>
                    </td>
                </tr>
            </table>

            <!-- 7. SIGNATURE BLOCK -->
            <table class="v-table" style="margin-top: 25px;">
                <tr>
                    <td style="width: 33%; text-align: center; padding: 15px 20px;">
                        <div class="sig-box"></div>
                        <strong>Prepared By:</strong><br>
                        <span style="color: #666;">{{ $voucher->creator?->name ?? 'Accounts Dept.' }}</span>
                    </td>
                    <td style="width: 33%; text-align: center; padding: 15px 20px;">
                        <div class="sig-box"></div>
                        <strong>Verified / Checked By</strong><br>
                        <span style="color: #666;">Internal Auditor</span>
                    </td>
                    <td style="width: 34%; text-align: center; padding: 15px 20px;">
                        <div class="sig-box"></div>
                        <strong>Authorized Signatory</strong><br>
                        <span style="color: #666;">Divine Bright Steels</span>
                    </td>
                </tr>
            </table>

            <div style="font-size: 9px; color: #888; text-align: center; padding: 6px 10px; border-top: 1px solid #e2e8f0;">
                System generated voucher on {{ date('d-M-Y h:i A') }} • Accounts ERP Software
            </div>

        </div>
    </div>

</body>
</html>
