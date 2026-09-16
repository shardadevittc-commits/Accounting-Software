<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Voucher;
use App\Models\Ledger;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LedgerReportController extends Controller
{
    /**
     * Check user permissions for ledger.
     */
    protected function checkPermission(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole(['admin', 'administrator', 'super-admin', 'accountant', 'sales'])) {
            return;
        }

        if (method_exists($user, 'hasPermission') && ($user->hasPermission('voucher.view') || $user->hasPermission('sales.view'))) {
            return;
        }

        abort(403, "Unauthorized access to Ledger Statement.");
    }

    /**
     * Display Ledger Statement page matching Account theme.
     */
    public function index(Request $request)
    {
        $this->checkPermission();

        // Distinct buyer/customer party list
        $invParties = Invoice::select('customer_name')
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->distinct()
            ->pluck('customer_name');

        $vchParties = Voucher::select('party_name')
            ->whereNotNull('party_name')
            ->where('party_name', '!=', '')
            ->distinct()
            ->pluck('party_name');

        $allParties = $invParties->merge($vchParties)->unique()->sort()->values();
        $selectedParty = $request->input('buyer_name', '');

        return view('accounting.ledger.index', compact('allParties', 'selectedParty'));
    }

    /**
     * Fetch dynamic ledger entries and running balance via AJAX.
     */
    public function data(Request $request): JsonResponse
    {
        $this->checkPermission();

        $buyerName = trim($request->input('buyer_name', ''));
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        $ledgerData = $this->buildLedgerTransactions($buyerName, $dateFrom, $dateTo, 'asc');

        return response()->json([
            'status' => 'success',
            'buyer' => $buyerName,
            'transactions' => $ledgerData['transactions'],
            'totals' => $ledgerData['totals'],
        ]);
    }

    /**
     * Dynamic Print statement matching ledger.dart layout and pagination.
     */
    public function print(Request $request)
    {
        $this->checkPermission();

        $buyerName = trim($request->input('buyer_name', ''));
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        $ledgerData = $this->buildLedgerTransactions($buyerName, $dateFrom, $dateTo, 'asc');
        $txList = $ledgerData['transactions'];
        $totals = $ledgerData['totals'];

        // Dynamic Company Details
        $company = [
            'name' => 'DIVINE BRIGHT STEELS',
            'address1' => 'Amloh Road, Village Kumbh',
            'address2' => 'Mandi Gobindgarh',
            'pa_no' => 'AAWFD2834M',
            'pan_ward' => 'AAXFB9027F,',
            'signatory_type' => 'PARTNERSHIP',
        ];

        // Fetch Buyer details from latest invoice or ledger
        $displayName = !empty($buyerName) ? $buyerName : 'All Parties Statement';
        $latestInv = !empty($buyerName) ? Invoice::where('customer_name', $buyerName)->latest('id')->first() : null;
        $accountGst = $latestInv ? ($latestInv->customer_gst ?: '') : '';
        $accountAddress = $latestInv ? ($latestInv->customer_address ?: 'Mandi Gobindgarh') : 'All Accounts Consolidated';
        $accountPan = '';
        if (!empty($accountGst) && strlen($accountGst) >= 12) {
            $accountPan = substr($accountGst, 2, 10);
        }

        // Separate street address and city
        $addressParts = explode(',', $accountAddress);
        $city = trim(end($addressParts) ?: 'Mandi Gobindgarh');
        $street = trim(count($addressParts) > 1 ? implode(',', array_slice($addressParts, 0, -1)) : $accountAddress);

        // Date range labels
        $dates = array_filter(array_column($txList, 'date_raw'), function($d) { return $d && $d !== '9999-99-99'; });
        $earliestTxDate = !empty($dates) ? min($dates) : ($dateFrom ?: date('Y-m-01'));
        $latestTxDate   = !empty($dates) ? max($dates) : ($dateTo ?: date('Y-m-d'));

        $account = [
            'name' => $buyerName,
            'address' => $street ?: 'Khasra No. 697/87',
            'city' => $city ?: 'Mandi Gobindgarh',
            'pan' => $accountPan ?: 'AAXFB9027F',
            'gst' => $accountGst ?: '03AAXFB9027F1ZB',
            'from_date' => $dateFrom ? Carbon::parse($dateFrom)->format('d-m-Y') : Carbon::parse($earliestTxDate)->format('d-m-Y'),
            'upto_date' => $dateTo ? Carbon::parse($dateTo)->format('d-m-Y') : Carbon::parse($latestTxDate)->format('d-m-Y'),
        ];

        // Dynamic Chunking for Print Pages (~35 rows per A4 page)
        $pageSize = 35;
        $chunks = array_chunk($txList, $pageSize);
        if (empty($chunks)) {
            $chunks = [[]];
        }

        $pages = [];
        $runningDebit = 0.0;
        $runningCredit = 0.0;
        $totalWeightSum = 0.0;

        foreach ($chunks as $pageIndex => $chunk) {
            $pageNum = $pageIndex + 1;
            $bfDebit = $pageNum > 1 ? number_format($runningDebit, 2, '.', '') : null;
            $bfCredit = $pageNum > 1 ? number_format($runningCredit, 2, '.', '') : null;

            $pageTransactions = [];
            foreach ($chunk as $item) {
                $d = (float)($item['debit_raw'] ?? 0);
                $c = (float)($item['credit_raw'] ?? 0);
                $w = (float)($item['weight_raw'] ?? 0);

                $runningDebit += $d;
                $runningCredit += $c;
                $totalWeightSum += $w;

                $pageTransactions[] = [
                    'date' => Carbon::parse($item['date_raw'])->format('d-m-Y'),
                    'narration' => $item['narration'],
                    'weight' => $item['weight_raw'] > 0 ? number_format($item['weight_raw'], 3, '.', '') : '',
                    'debit' => $d > 0 ? number_format($d, 2, '.', '') : null,
                    'credit' => $c > 0 ? number_format($c, 2, '.', '') : null,
                    'balance' => number_format($item['balance_raw'], 2, '.', ''),
                    'dc' => $item['dc'],
                ];
            }

            $isLast = ($pageNum === count($chunks));

            $pageData = [
                'page_number' => $pageNum,
                'total_bf_debit' => $bfDebit,
                'total_bf_credit' => $bfCredit,
                'transactions' => $pageTransactions,
            ];

            if (!$isLast) {
                $pageData['page_total_debit'] = number_format($runningDebit, 2, '.', '');
                $pageData['page_total_credit'] = number_format($runningCredit, 2, '.', '');
            } else {
                $pageData['grand_total_weight'] = number_format($totalWeightSum, 3, '.', '');
                $pageData['grand_total_debit'] = number_format($runningDebit, 2, '.', '');
                $pageData['grand_total_credit'] = number_format($runningCredit, 2, '.', '');
                $pageData['final_balance'] = number_format($totals['closing_balance'], 2, '.', '');
                $pageData['final_dc'] = $totals['dc'];
            }

            $pages[] = $pageData;
        }

        return view('accounting.ledger.print', compact('company', 'account', 'pages'));
    }

    /**
     * Core calculation logic: Invoices (Debit) + Vouchers (Credit) with Running Balances.
     */
    protected function buildLedgerTransactions(string $buyerName, ?string $dateFrom, ?string $dateTo, string $order = 'asc'): array
    {
        // 1. Fetch Invoices (filter by buyerName if provided)
        $invQuery = Invoice::with('items');
        if (!empty($buyerName)) {
            $invQuery->where('customer_name', $buyerName);
        }
        $allInvoices = $invQuery->orderBy('invoice_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 2. Fetch Vouchers (Posted only; filter by buyerName if provided)
        $vchQuery = Voucher::where('status', 'posted');
        if (!empty($buyerName)) {
            $vchQuery->where('party_name', $buyerName);
        }
        $allVouchers = $vchQuery->orderBy('voucher_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 3. Compile all raw chronological transactions
        $allTx = [];

        foreach ($allInvoices as $inv) {
            $invDate = $inv->invoice_date ? Carbon::parse($inv->invoice_date)->format('Y-m-d') : '9999-99-99';
            $weight = (float)$inv->items->sum('weight_tons');
            
            // Clean Bill Label e.g. "To Bill No. 01" or "To Bill No. INV-2026-0001"
            $billLabel = 'To Bill No. ' . $inv->invoice_no;
            if (preg_match('/^INV-\d+-(\d+)$/i', $inv->invoice_no, $m)) {
                $billLabel = 'To Bill No. ' . str_pad((int)$m[1], 2, '0', STR_PAD_LEFT);
            } elseif (preg_match('/^BSI-(\d+)$/i', $inv->invoice_no, $m)) {
                $billLabel = 'To B.No.' . $inv->invoice_no . ' Dt.' . Carbon::parse($inv->invoice_date)->format('d-m-Y');
            }

            $allTx[] = [
                'type' => 'invoice',
                'id' => (int)$inv->id,
                'party_name' => $inv->customer_name ?: '',
                'date_raw' => $invDate,
                'date_display' => $inv->invoice_date ? Carbon::parse($inv->invoice_date)->format('d M Y') : '—',
                'narration' => $billLabel,
                'weight_raw' => $weight,
                'weight_display' => $weight > 0 ? (rtrim(rtrim(number_format($weight, 3, '.', ''), '0'), '.') . ' T') : '',
                'debit_raw' => (float)$inv->grand_total,
                'credit_raw' => 0.0,
            ];
        }

        foreach ($allVouchers as $v) {
            $vDate = $v->voucher_date ? Carbon::parse($v->voucher_date)->format('Y-m-d') : '9999-99-99';
            $vType = strtolower($v->voucher_type ?: 'cash');
            
            // Receipts from customer and discounts/settlements are Credits (reduce debit balance)
            // Payments to customer/supplier are Debits
            $isDebit = ($v->transaction_mode === 'payment' && $vType !== 'general');
            $amt = (float)($v->total_credit > 0 ? $v->total_credit : $v->total_debit);

            $prefix = $isDebit ? 'To ' : 'By ';
            if (!empty($v->narration)) {
                $narr = $prefix . $v->narration;
            } elseif ($vType === 'cash') {
                $narr = $prefix . 'Cash';
            } elseif ($vType === 'general') {
                $narr = $prefix . (!empty($v->reference_no) ? 'Discount on ' . $v->reference_no : 'Discount / Bad Debts');
            } elseif (!empty($v->reference_no)) {
                $narr = $prefix . $v->reference_no;
            } elseif ($vType === 'bank') {
                $narr = $prefix . ($v->payment_method ? strtoupper($v->payment_method) : 'Bank');
            } else {
                $narr = $prefix . 'Voucher';
            }

            $allTx[] = [
                'type' => 'voucher',
                'id' => (int)$v->id,
                'party_name' => $v->party_name ?: '',
                'date_raw' => $vDate,
                'date_display' => $v->voucher_date ? Carbon::parse($v->voucher_date)->format('d M Y') : '—',
                'narration' => $narr,
                'weight_raw' => 0.0,
                'weight_display' => '',
                'debit_raw' => $isDebit ? $amt : 0.0,
                'credit_raw' => !$isDebit ? $amt : 0.0,
            ];
        }

        // Sort all events chronologically (Date ASC, ID ASC)
        usort($allTx, function ($a, $b) {
            if ($a['date_raw'] !== $b['date_raw']) {
                return strcmp($a['date_raw'], $b['date_raw']);
            }
            return $a['id'] <=> $b['id'];
        });

        // 4. Calculate Opening Balance if dateFrom is specified
        $openingDebit = 0.0;
        $openingCredit = 0.0;
        $filteredTx = [];

        foreach ($allTx as $tx) {
            if (!empty($dateFrom) && $tx['date_raw'] < $dateFrom) {
                $openingDebit += $tx['debit_raw'];
                $openingCredit += $tx['credit_raw'];
            } elseif (!empty($dateTo) && $tx['date_raw'] > $dateTo) {
                continue;
            } else {
                $filteredTx[] = $tx;
            }
        }

        // 5. Build final transaction list with running balance
        $finalList = [];
        $runningBalance = 0.0; // Positive = Debit (Dr), Negative = Credit (Cr)
        $totalWeight = 0.0;
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        // Add Opening Balance Row if prior transactions exist
        $netOpening = round($openingDebit - $openingCredit, 2);
        if ($netOpening > 0.01) {
            $runningBalance = $netOpening;
            $totalDebit += $netOpening;
            $finalList[] = [
                'id' => 0,
                'date_raw' => $dateFrom ?: $allTx[0]['date_raw'] ?? date('Y-m-d'),
                'date_display' => $dateFrom ? Carbon::parse($dateFrom)->format('d M Y') : '—',
                'narration' => 'To Opening Balance',
                'weight_raw' => 0.0,
                'weight_display' => '',
                'debit_raw' => $netOpening,
                'credit_raw' => 0.0,
                'balance_raw' => $netOpening,
                'balance_display' => number_format($netOpening, 2),
                'dc' => 'Dr',
            ];
        } elseif ($netOpening < -0.01) {
            $runningBalance = $netOpening;
            $absOpen = abs($netOpening);
            $totalCredit += $absOpen;
            $finalList[] = [
                'id' => 0,
                'date_raw' => $dateFrom ?: $allTx[0]['date_raw'] ?? date('Y-m-d'),
                'date_display' => $dateFrom ? Carbon::parse($dateFrom)->format('d M Y') : '—',
                'narration' => 'By Opening Balance',
                'weight_raw' => 0.0,
                'weight_display' => '',
                'debit_raw' => 0.0,
                'credit_raw' => $absOpen,
                'balance_raw' => $absOpen,
                'balance_display' => number_format($absOpen, 2),
                'dc' => 'Cr',
            ];
        }

        // Process in-range transactions
        foreach ($filteredTx as $tx) {
            $debit = $tx['debit_raw'];
            $credit = $tx['credit_raw'];
            $weight = $tx['weight_raw'];

            $totalDebit += $debit;
            $totalCredit += $credit;
            $totalWeight += $weight;

            $runningBalance = round($runningBalance + $debit - $credit, 2);
            $absBal = abs($runningBalance);
            $dc = ($runningBalance >= 0) ? 'Dr' : 'Cr';

            $finalList[] = [
                'id' => $tx['id'],
                'party_name' => $tx['party_name'] ?? '',
                'date_raw' => $tx['date_raw'],
                'date_display' => $tx['date_display'],
                'narration' => $tx['narration'],
                'weight_raw' => $weight,
                'weight_display' => $tx['weight_display'],
                'debit_raw' => $debit,
                'credit_raw' => $credit,
                'balance_raw' => $absBal,
                'balance_display' => number_format($absBal, 2),
                'dc' => $dc,
            ];
        }

        $finalNet = round($runningBalance, 2);
        $closingDc = ($finalNet >= 0) ? 'Dr' : 'Cr';

        if ($order === 'desc') {
            $finalList = array_reverse($finalList);
        }

        return [
            'transactions' => $finalList,
            'totals' => [
                'total_entries' => count($finalList),
                'total_weight' => $totalWeight > 0 ? (rtrim(rtrim(number_format($totalWeight, 3, '.', ''), '0'), '.') . ' T') : '0 T',
                'total_debit' => round($totalDebit, 2),
                'total_credit' => round($totalCredit, 2),
                'closing_balance' => abs($finalNet),
                'dc' => $closingDc,
            ],
        ];
    }

    /**
     * Export currently filtered ledger transactions to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $this->checkPermission();

        $buyerName = trim($request->input('buyer_name', ''));
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        $ledgerData = $this->buildLedgerTransactions($buyerName, $dateFrom, $dateTo, 'asc');
        $transactions = $ledgerData['transactions'];
        $totals = $ledgerData['totals'];

        $sanitizedName = !empty($buyerName) ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $buyerName) : 'All_Parties';
        $filename = "ledger_statement_{$sanitizedName}_" . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($transactions, $totals, $buyerName, $dateFrom, $dateTo) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel compatibility

            // Header metadata
            fputcsv($handle, ['DIVINE BRIGHT STEELS - LEDGER STATEMENT']);
            fputcsv($handle, ['PARTY:', !empty($buyerName) ? $buyerName : 'All Parties Consolidated']);
            if (!empty($dateFrom) || !empty($dateTo)) {
                fputcsv($handle, ['DATE RANGE:', ($dateFrom ?: 'Beginning') . ' to ' . ($dateTo ?: 'Today')]);
            }
            fputcsv($handle, ['GENERATED ON:', date('d-m-Y H:i:s')]);
            fputcsv($handle, []); // Blank line

            // Table Column Headers
            fputcsv($handle, [
                'DATE',
                'NARRATION / DESCRIPTION',
                'WEIGHT (T)',
                'DEBIT (INR)',
                'CREDIT (INR)',
                'BALANCE (INR)',
                'D/C'
            ]);

            foreach ($transactions as $tx) {
                $debit = ($tx['debit_raw'] > 0) ? number_format($tx['debit_raw'], 2, '.', '') : '';
                $credit = ($tx['credit_raw'] > 0) ? number_format($tx['credit_raw'], 2, '.', '') : '';
                $balance = number_format($tx['balance_raw'] ?? 0, 2, '.', '');
                $weight = !empty($tx['weight_display']) ? $tx['weight_display'] : '';

                fputcsv($handle, [
                    $tx['date_display'] ?? '',
                    $tx['narration'] ?? '',
                    $weight,
                    $debit,
                    $credit,
                    $balance,
                    $tx['dc'] ?? ''
                ]);
            }

            // Summary Footer
            fputcsv($handle, []); // Blank line
            fputcsv($handle, [
                'TOTAL',
                'Closing Balance: ' . number_format($totals['closing_balance'], 2, '.', '') . ' ' . $totals['dc'],
                $totals['total_weight'] ?? '',
                number_format($totals['total_debit'], 2, '.', ''),
                number_format($totals['total_credit'], 2, '.', ''),
                number_format($totals['closing_balance'], 2, '.', ''),
                $totals['dc']
            ]);

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
