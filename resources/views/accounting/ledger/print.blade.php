<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Copy of Account - {{ $account['name'] }} ({{ $account['from_date'] }} to {{ $account['upto_date'] }})</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #525659;
            color: #000000;
            font-family: "Courier New", Courier, monospace;
            font-size: 11px;
            line-height: 1.35;
            padding: 20px 10px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Top Action Bar for Screen View */
        .toolbar-container {
            max-width: 840px;
            margin: 0 auto 20px auto;
            background: #1e293b;
            border-radius: 8px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #ffffff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.35);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .toolbar-title {
            font-size: 14.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 18px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
        }

        .btn-print {
            background-color: #2563eb;
            color: #ffffff;
        }
        .btn-print:hover {
            background-color: #1d4ed8;
            box-shadow: 0 2px 8px rgba(37,99,235,0.4);
        }

        .btn-back {
            background-color: #475569;
            color: #f8fafc;
        }
        .btn-back:hover {
            background-color: #334155;
        }

        /* A4 Sheet Container */
        .ledger-page {
            background: #ffffff;
            width: 100%;
            max-width: 840px;
            min-height: 1140px;
            margin: 0 auto 30px auto;
            padding: 26px 30px 22px 30px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
            position: relative;
        }

        /* Company Header - Courier typography */
        .company-header {
            text-align: center;
            margin-bottom: 16px;
            font-family: "Courier New", Courier, monospace;
        }

        .company-title {
            font-size: 21px;
            font-weight: 900;
            letter-spacing: 2px;
            margin-bottom: 2px;
            text-transform: uppercase;
            color: #000000;
        }

        .company-subtitle {
            font-size: 15px;
            font-weight: 400;
            margin-bottom: 1px;
            letter-spacing: 0.3px;
            color: #000000;
        }

        /* Meta Account Block */
        .account-meta-grid {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            font-size: 12px;
            line-height: 1.45;
            font-family: "Courier New", Courier, monospace;
            color: #000000;
        }

        .meta-col-left {
            flex: 1.4;
        }

        .meta-col-center {
            flex: 1;
            text-align: center;
            padding-top: 14px;
        }

        .meta-col-center .doc-heading {
            font-size: 13.5px;
            font-weight: 900;
            letter-spacing: 1px;
            color: #000000;
        }

        .meta-col-right {
            flex: 0.9;
            text-align: right;
            font-weight: 400;
        }

        .meta-row {
            display: flex;
        }

        .meta-label {
            display: inline-block;
            width: 45px;
            font-weight: 400;
        }

        .meta-val-name {
            font-weight: 900;
        }

        .meta-val-address {
            margin-left: 45px;
            font-weight: 900;
        }

        .pan-gst-row {
            margin-top: 4px;
        }

        .pan-gst-row span {
            font-weight: 400;
        }

        .pan-gst-row strong {
            font-weight: 900;
        }

        /* Ledger Table Structure */
        .table-wrap {
            width: 100%;
        }

        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            font-size: 11px;
            line-height: 1.32;
        }

        /* Table Headers: Courier Bold, Background Color #e8e8e8 */
        .ledger-table thead th {
            border-top: 1.5px solid #000000;
            border-bottom: 1.5px solid #000000;
            border-right: 1px solid #000000;
            padding: 3.5px 5px;
            font-family: "Courier New", Courier, monospace;
            font-weight: 900;
            font-size: 14px;
            background-color: #e8e8e8 !important;
            color: #000000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .ledger-table thead th:last-child {
            border-right: none;
        }

        /* Table Body: Helvetica / Arial Regular */
        .ledger-table tbody td {
            border-right: 1px solid #000000;
            padding: 4px 5px;
            vertical-align: top;
            white-space: nowrap;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            color: #000000;
        }

        .ledger-table tbody td:last-child {
            border-right: none;
        }

        /* Column Alignments & Widths */
        .col-date {
            width: 88px;
            text-align: left;
        }

        .col-narration {
            width: auto;
            text-align: left;
            position: relative;
        }

        .col-debit {
            width: 105px;
            text-align: right;
        }

        .col-credit {
            width: 105px;
            text-align: right;
        }

        .col-balance {
            width: 105px;
            text-align: right;
        }

        .col-dc {
            width: 38px;
            text-align: center;
            font-weight: normal;
        }

        .narration-cell-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 100%;
        }

        .narration-desc {
            flex: 1;
            padding-right: 8px;
            white-space: pre-line;
        }

        .narration-weight {
            flex-shrink: 0;
            text-align: right;
            min-width: 50px;
            font-weight: normal;
        }

        /* Total b/f row: Grey Background #e8e8e8, Bold Helvetica amounts */
        .row-bf td {
            font-weight: bold;
            padding: 2.5px 4px;
            border-top: 1px solid #000000;
            border-bottom: 0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .row-bf .bf-label {
            text-align: right;
            padding-right: 15px;
            font-family: "Courier New", Courier, monospace;
            font-weight: 900;
            font-size: 11.5px;
        }

        .row-bf .col-debit,
        .row-bf .col-credit {
            font-family: "Courier New", Courier, monospace;
            font-weight: bold;
        }

        /* Subtotal / Page Footer Row: Bold, Double bottom border */
        .page-total-row td {
            border-top: 1px solid #000000;
            border-bottom: 3px double #000000;
            font-family: "Courier New", Courier, monospace;
            font-weight: bold;
            padding: 3px 4px;
            font-size: 11.5px;
        }

        /* Final Total Row on Last Page: Bold Helvetica amounts, Double bottom border */
        .grand-total-row td {
            border-top: 1px solid #000000;
            border-bottom: 3px double #000000;
            font-family: "Courier New", Courier, monospace;
            font-weight: bold;
            padding: 3px 4px;
            font-size: 11.2px;
        }

        .grand-total-row .total-label {
            font-family: "Courier New", Courier, monospace;
            font-weight: bold;
            padding-left: 120px;
            font-size: 11.5px;
        }

        .grand-total-row .total-weight {
            font-family: "Courier New", Courier, monospace;
            font-weight: normal;
        }

        /* Bottom Footer on Last Page: Blue / Navy color (#000080) for company & PAN */
        .ledger-signatures {
            margin-top: 18px;
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
            line-height: 1.45;
        }

        .sig-left {
            flex: 1.2;
            color: #000000;
        }

        .sig-left .sig-confirm {
            font-family: "Courier New", Courier, monospace;
            color: #000000;
        }

        .sig-left .sig-pan-ward {
            margin-top: 24px;
            font-family: "Courier New", Courier, monospace;
            font-weight: bold;
            font-size: 10px;
            color: #000080;
        }

        .sig-right {
            flex: 1;
            text-align: right;
            font-family: "Courier New", Courier, monospace;
            color: #000080;
        }

        .sig-right .sig-company {
            font-weight: bold;
            font-size: 11.5px;
            margin-bottom: 24px;
            color: #000080;
        }

        .sig-right .sig-type {
            font-weight: normal;
            color: #000080;
        }

        .sig-right .sig-pano {
            font-weight: normal;
            color: #000080;
        }

        /* Print Settings */
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm 8mm 10mm;
            }

            body {
                background: #ffffff !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            .ledger-page {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: 0 !important;
                page-break-after: always !important;
                break-after: page !important;
            }

            .ledger-page:last-child {
                page-break-after: avoid !important;
                break-after: avoid !important;
            }

            .ledger-table thead th,
            .row-bf td {
                background-color: #e8e8e8 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .sig-right,
            .sig-right .sig-company,
            .sig-right .sig-type,
            .sig-right .sig-pano,
            .sig-left .sig-pan-ward {
                color: #000080 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <!-- Screen Toolbar (Hidden on Print) -->
    <div class="toolbar-container no-print">
        <div class="toolbar-title">
            <span style="font-size: 16px;">📄</span>
            <span>Invoices Ledger Statement &bull; {{ $account['name'] }}</span>
        </div>
        <div class="toolbar-actions">
            <button onclick="window.print()" class="btn-action btn-print">
                <span>🖨️</span> Print Ledger
            </button>
            <a href="{{ route('ledger.index', ['buyer_name' => $account['name']]) }}" class="btn-action btn-back">
                <span>⬅️</span> Back to Ledger
            </a>
        </div>
    </div>

    @foreach($pages as $page)
    <!-- Page {{ $page['page_number'] }} -->
    <div class="ledger-page">
        <!-- Company Header -->
        <div class="company-header">
            <div class="company-title">{{ $company['name'] }}</div>
            <div class="company-subtitle">{{ $company['address1'] }}</div>
            <div class="company-subtitle">{{ $company['address2'] }}</div>
        </div>

        <!-- Account Meta Header -->
        <div class="account-meta-grid">
            <div class="meta-col-left">
                <div class="meta-row">
                    <span class="meta-label">A/c :</span>
                    <span class="meta-val-name">{{ $account['name'] }}</span>
                </div>
                <div class="meta-val-address">{{ $account['address'] }}</div>
                <div class="meta-val-address">{{ $account['city'] }}</div>
                <div class="pan-gst-row">
                    <span>PAN : <strong>{{ $account['pan'] }}</strong></span>
                    <span style="margin-left: 20px;">GST# <strong>{{ $account['gst'] }}</strong></span>
                </div>
            </div>

            <div class="meta-col-center">
                <div class="doc-heading">COPY OF ACCOUNT</div>
            </div>

            <div class="meta-col-right">
                <div>From {{ $account['from_date'] }}</div>
                <div>Upto {{ $account['upto_date'] }}</div>
                <div>Page {{ $page['page_number'] }}</div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="table-wrap">
            <table class="ledger-table">
                <thead>
                    <tr>
                        <th class="col-date">Date</th>
                        <th class="col-narration">Narration</th>
                        <th class="col-debit">Debit</th>
                        <th class="col-credit">Credit</th>
                        <th class="col-balance">Balance</th>
                        <th class="col-dc">D/C</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($page['total_bf_debit']) || !empty($page['total_bf_credit']))
                    <!-- Total Brought Forward Row with #e8e8e8 grey background -->
                    <tr class="row-bf">
                        <td class="col-date"></td>
                        <td class="col-narration">
                            <div class="bf-label">Total b/f</div>
                        </td>
                        <td class="col-debit">{{ $page['total_bf_debit'] }}</td>
                        <td class="col-credit">{{ $page['total_bf_credit'] }}</td>
                        <td class="col-balance"></td>
                        <td class="col-dc"></td>
                    </tr>
                    @endif

                    @forelse($page['transactions'] as $tx)
                    <tr>
                        <td class="col-date">{{ $tx['date'] }}</td>
                        <td class="col-narration">
                            <div class="narration-cell-content">
                                <span class="narration-desc">{{ $tx['narration'] }}</span>
                                @if(!empty($tx['weight']))
                                <span class="narration-weight">{{ $tx['weight'] }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="col-debit">{{ $tx['debit'] ?? '' }}</td>
                        <td class="col-credit">{{ $tx['credit'] ?? '' }}</td>
                        <td class="col-balance">{{ $tx['balance'] ?? '' }}</td>
                        <td class="col-dc">{{ $tx['dc'] ?? '' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 25px; color: #666; font-style: italic;">
                            No transactions recorded for this period.
                        </td>
                    </tr>
                    @endforelse

                    @if(isset($page['page_total_debit']))
                    <!-- Intermediate Page Footer Total with double bottom border -->
                    <tr class="page-total-row">
                        <td class="col-date"></td>
                        <td class="col-narration"></td>
                        <td class="col-debit">{{ $page['page_total_debit'] }}</td>
                        <td class="col-credit">{{ $page['page_total_credit'] }}</td>
                        <td class="col-balance"></td>
                        <td class="col-dc"></td>
                    </tr>
                    @endif

                    @if(isset($page['grand_total_debit']))
                    <!-- Final Grand Total Row on Last Page with double bottom border -->
                    <tr class="grand-total-row">
                        <td class="col-date"></td>
                        <td class="col-narration">
                            <div class="narration-cell-content">
                                <span class="total-label">Total</span>
                                <span class="narration-weight total-weight">{{ $page['grand_total_weight'] }}</span>
                            </div>
                        </td>
                        <td class="col-debit">{{ $page['grand_total_debit'] }}</td>
                        <td class="col-credit">{{ $page['grand_total_credit'] }}</td>
                        <td class="col-balance">{{ $page['final_balance'] }}</td>
                        <td class="col-dc">{{ $page['final_dc'] }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if($loop->last)
        <!-- Footer Signatures & Particulars on Last Page (with Blue / Navy Color) -->
        <div class="ledger-signatures">
            <div class="sig-left">
                <div class="sig-confirm">I/we Confirm that the above particulars</div>
                <div class="sig-confirm">are true and Correct.</div>
                <div class="sig-pan-ward">PAN & Ward :: {{ $company['pan_ward'] }}</div>
            </div>
            <div class="sig-right">
                <div class="sig-company">FOR {{ $company['name'] }}</div>
                <div class="sig-type">{{ $company['signatory_type'] }}</div>
                <div class="sig-pano">P.A.No.{{ $company['pa_no'] }}</div>
            </div>
        </div>
        @endif
    </div>
    @endforeach

    @if(request()->has('auto_print'))
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                window.print();
            }, 400);
        });
    </script>
    @endif

</body>
</html>
