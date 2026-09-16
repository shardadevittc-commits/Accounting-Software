<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Check user permissions for reports module.
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

        abort(403, "Unauthorized access to Accounting Reports.");
    }

    /**
     * Render the single Reports page view.
     */
    public function index()
    {
        $this->checkPermission();

        // Distinct customer/party list for filter dropdown
        $parties = Invoice::select('customer_name')
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->distinct()
            ->orderBy('customer_name', 'asc')
            ->pluck('customer_name');
            
        $voucherParties = Voucher::select('party_name')
            ->whereNotNull('party_name')
            ->where('party_name', '!=', '')
            ->distinct()
            ->pluck('party_name');
        
        $allParties = $parties->merge($voucherParties)->unique()->sort()->values();

        return view('accounting.reports.index', compact('allParties'));
    }

    /**
     * Fetch filtered report data and calculated summary metrics via AJAX.
     */
    public function data(Request $request): JsonResponse
    {
        $this->checkPermission();

        $billNo   = trim($request->input('bill_no', ''));
        $party    = trim($request->input('party', ''));
        $status   = trim($request->input('status', 'all'));
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $search   = trim($request->input('search', ''));

        $records = $this->buildReportQuery($billNo, $party, $status, $dateFrom, $dateTo, $search);

        // Calculate Grand Totals matching user screenshot logic
        $totals = [
            'total_entries' => count($records),
            'total_invoices' => 0,
            'total_net_amount' => 0.0,
            'total_received_amount' => 0.0,
            'total_balance_amount' => 0.0,
            'balance_type' => 'nil', // 'dr', 'cr', 'nil'
            'balance_label' => 'CR',
            'balance_formatted' => '₹0.00',
        ];

        $countedInvoiceIds = [];

        foreach ($records as $idx => &$item) {
            $item['s_no'] = $idx + 1;

            // Count Net Amount only ONCE per unique invoice
            if (!empty($item['invoice_id'])) {
                if (!in_array($item['invoice_id'], $countedInvoiceIds)) {
                    $totals['total_invoices'] += 1;
                    $totals['total_net_amount'] += (float)($item['net_amt'] ?? 0);
                    $countedInvoiceIds[] = $item['invoice_id'];
                }
            }

            $totals['total_received_amount'] += (float)($item['received_amt'] ?? 0);
        }
        unset($item);

        $totals['total_net_amount'] = round($totals['total_net_amount'], 2);
        $totals['total_received_amount'] = round($totals['total_received_amount'], 2);

        // Difference: Received - Net
        // If Received > Net => CR (Credit / Advance with party)
        // If Net > Received => DR (Debit / Pending on party)
        $diff = round($totals['total_received_amount'] - $totals['total_net_amount'], 2);

        if ($diff > 0.01) {
            $totals['total_balance_amount'] = $diff;
            $totals['balance_type'] = 'cr';
            $totals['balance_label'] = 'CR';
            $totals['balance_formatted'] = '₹' . number_format($diff, 2);
        } elseif ($diff < -0.01) {
            $totals['total_balance_amount'] = abs($diff);
            $totals['balance_type'] = 'dr';
            $totals['balance_label'] = 'DR';
            $totals['balance_formatted'] = '₹' . number_format(abs($diff), 2);
        } else {
            $totals['total_balance_amount'] = 0.0;
            $totals['balance_type'] = 'nil';
            $totals['balance_label'] = 'NIL';
            $totals['balance_formatted'] = '₹0.00';
        }

        return response()->json([
            'status' => 'success',
            'summary' => [
                'total_entries' => $totals['total_entries'],
                'total_invoices' => $totals['total_invoices'],
                'total_net_amount' => $totals['total_net_amount'],
                'total_received_amount' => $totals['total_received_amount'],
                'total_balance_amount' => $totals['total_balance_amount'],
                'balance_type' => $totals['balance_type'],
                'balance_label' => $totals['balance_label'],
                'balance_formatted' => $totals['balance_formatted'],
            ],
            'totals' => $totals,
            'data' => $records,
        ]);
    }

    /**
     * Export currently filtered report data as CSV with 13 columns + footer totals.
     */
    public function export(Request $request): StreamedResponse
    {
        $this->checkPermission();

        $billNo   = trim($request->input('bill_no', ''));
        $party    = trim($request->input('party', ''));
        $status   = trim($request->input('status', 'all'));
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $search   = trim($request->input('search', ''));

        $records = $this->buildReportQuery($billNo, $party, $status, $dateFrom, $dateTo, $search);

        $filename = 'account_bill_tracking_report_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            // 13 Columns matching user screenshot
            fputcsv($handle, [
                '#',
                'PARTY',
                'TOKEN NO.',
                'BILL NO.',
                'INVOICE DT / DUE DAYS',
                'TRUCK NO.',
                'TAXABLE AMT',
                'GST AMT',
                'TDS',
                'NET AMT',
                'RECEIVED AMT',
                'BALANCE AMT',
                'STATUS'
            ]);

            $totalNet = 0.0;
            $totalReceived = 0.0;
            $countedInvoices = [];

            foreach ($records as $index => $row) {
                if (!empty($row['invoice_id'])) {
                    if (!in_array($row['invoice_id'], $countedInvoices)) {
                        $totalNet += (float)($row['net_amt'] ?? 0);
                        $countedInvoices[] = $row['invoice_id'];
                    }
                }
                $totalReceived += (float)($row['received_amt'] ?? 0);

                $dateDisplay = $row['invoice_date_formatted'];
                if (!empty($row['credit_days'])) {
                    $dateDisplay .= "\n" . $row['credit_days'] . " days\n" . $row['due_text'];
                }

                $recvDisplay = $row['received_amt'] > 0 ? number_format($row['received_amt'], 2, '.', '') : '0';
                if (!empty($row['received_time'])) {
                    $recvDisplay .= " (" . $row['received_time'] . ")";
                }

                $balDisplay = $row['balance_amt'] !== null ? number_format($row['balance_amt'], 2, '.', '') : 'N/A';

                fputcsv($handle, [
                    $index + 1,
                    $row['party_name'],
                    $row['token_no'],
                    $row['bill_no'],
                    $dateDisplay,
                    $row['truck_no'],
                    $row['taxable_amt'] !== null ? number_format($row['taxable_amt'], 2, '.', '') : '—',
                    $row['gst_amt'] !== null ? number_format($row['gst_amt'], 2, '.', '') : '—',
                    $row['tds_amt'] !== null ? number_format($row['tds_amt'], 2, '.', '') : '—',
                    $row['net_amt'] !== null ? number_format($row['net_amt'], 2, '.', '') : '—',
                    $recvDisplay,
                    $balDisplay,
                    $row['status'] . (!empty($row['narration']) ? " - " . $row['narration'] : '')
                ]);
            }

            $diff = round($totalReceived - $totalNet, 2);
            $balLabel = $diff >= 0 ? 'CR' : 'DR';

            // Footer Total Row
            fputcsv($handle, [
                'Total',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                number_format($totalNet, 2, '.', ''),
                number_format($totalReceived, 2, '.', ''),
                number_format(abs($diff), 2, '.', ''),
                $balLabel
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Build chronological, bill-by-bill lifecycle transaction rows matching screenshot.
     * Features:
     * - FIFO Automatic Adjustment of Advance payments against open / new bills per party.
     * - Excludes "General Discount" as explicitly specified by the user.
     */
    protected function buildReportQuery(
        string $billNo = '',
        string $party = '',
        string $status = 'all',
        ?string $dateFrom = null,
        ?string $dateTo = null,
        string $search = ''
    ): array {
        // 1. Fetch Invoices matching filters
        $invoiceQuery = Invoice::query()->orderBy('invoice_date', 'asc')->orderBy('id', 'asc');

        if (!empty($party)) {
            $invoiceQuery->where('customer_name', $party);
        }
        if (!empty($billNo)) {
            $invoiceQuery->where('invoice_no', 'like', "%{$billNo}%");
        }
        if (!empty($dateFrom)) {
            $invoiceQuery->whereDate('invoice_date', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $invoiceQuery->whereDate('invoice_date', '<=', $dateTo);
        }

        $invoices = $invoiceQuery->get();

        // 2. Map vehicle tokens and sale order credit days from devine ERP if accessible
        $vehicleIds = $invoices->pluck('vehicle_id')->filter()->unique()->toArray();
        $dispatchIds = $invoices->pluck('dispatch_id')->filter()->unique()->toArray();

        $gateTokenMap = [];
        if (!empty($vehicleIds)) {
            try {
                $gates = DB::select("SELECT gid, tokenid, vehicleno FROM devine.gate WHERE gid IN (" . implode(',', array_map('intval', $vehicleIds)) . ")");
                foreach ($gates as $g) {
                    $gateTokenMap[$g->gid] = $g->tokenid;
                }
            } catch (\Exception $e) {
                // Ignore if cross-database query is not available
            }
        }

        $dispatchCreditMap = [];
        if (!empty($dispatchIds)) {
            try {
                $dispatches = DB::select("
                    SELECT d.dispatchid, so.payType 
                    FROM devine.dispatch d
                    LEFT JOIN devine.saleorder so ON so.slid = d.slid
                    WHERE d.dispatchid IN (" . implode(',', array_map('intval', $dispatchIds)) . ")
                ");
                foreach ($dispatches as $d) {
                    $dispatchCreditMap[$d->dispatchid] = $d->payType;
                }
            } catch (\Exception $e) {
                // Ignore if devine table is not available
            }
        }

        // 3. Fetch Vouchers (Exclude General Discount)
        $vouchersQuery = Voucher::query()
            ->where('status', 'posted')
            ->where(function ($q) {
                $q->whereNull('voucher_type')
                  ->orWhereNotIn(DB::raw('LOWER(voucher_type)'), ['discount', 'general_discount', 'general discount']);
            })
            ->where(function ($q) {
                $q->whereNull('payment_method')
                  ->orWhereNotIn(DB::raw('LOWER(payment_method)'), ['discount', 'general_discount']);
            })
            ->where(function ($q) {
                $q->whereNull('narration')
                  ->orWhere(DB::raw('LOWER(narration)'), 'NOT LIKE', '%general discount%');
            })
            ->orderBy('voucher_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc');

        if (!empty($party)) {
            $vouchersQuery->where('party_name', $party);
        }
        if (!empty($dateFrom)) {
            $vouchersQuery->whereDate('voucher_date', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $vouchersQuery->whereDate('voucher_date', '<=', $dateTo);
        }

        $allVouchers = $vouchersQuery->get();

        // 4. Map Invoices for linking direct vouchers
        $invoiceNoMap = [];
        $invoiceNumOnlyMap = [];
        foreach ($invoices as $inv) {
            $invNoUpper = strtoupper(trim($inv->invoice_no));
            $invoiceNoMap[$invNoUpper] = $inv->id;
            if (preg_match('/\d+/', $inv->invoice_no, $m)) {
                $invoiceNumOnlyMap[(int)$m[0]] = $inv->id;
            }
        }

        // Separate vouchers into:
        // - Direct vouchers linked to a specific invoice
        // - Advance vouchers without invoice reference (grouped by Party)
        $vouchersByInvoiceId = [];
        $advanceVouchersByParty = [];

        foreach ($allVouchers as $v) {
            $matchedInvId = null;
            if (!empty($v->invoice_id) && isset($invoices->keyBy('id')[$v->invoice_id])) {
                $matchedInvId = $v->invoice_id;
            } elseif (!empty($v->reference_no)) {
                $refUpper = strtoupper(trim($v->reference_no));
                if (isset($invoiceNoMap[$refUpper])) {
                    $matchedInvId = $invoiceNoMap[$refUpper];
                } elseif (preg_match('/\d+/', $v->reference_no, $m) && isset($invoiceNumOnlyMap[(int)$m[0]])) {
                    $matchedInvId = $invoiceNumOnlyMap[(int)$m[0]];
                }
            }

            if ($matchedInvId) {
                $vouchersByInvoiceId[$matchedInvId][] = $v;
            } else {
                $pNorm = strtoupper(trim($v->party_name ?: 'GENERAL'));
                $advanceVouchersByParty[$pNorm][] = $v;
            }
        }

        // Group Invoices by Party
        $invoicesByParty = [];
        foreach ($invoices as $inv) {
            $pNorm = strtoupper(trim($inv->customer_name ?: 'GENERAL'));
            $invoicesByParty[$pNorm][] = $inv;
        }

        $allPartyKeys = array_unique(array_merge(array_keys($invoicesByParty), array_keys($advanceVouchersByParty)));

        // 5. Party-wise FIFO Advance Auto-Adjustment & Bill Processing
        $allRows = [];

        foreach ($allPartyKeys as $pKey) {
            $partyInvoices = $invoicesByParty[$pKey] ?? [];
            $partyAdvVouchers = $advanceVouchersByParty[$pKey] ?? [];

            // Sort party invoices chronologically (Date & ID ascending)
            usort($partyInvoices, function ($a, $b) {
                $dA = $a->invoice_date ?: '9999-99-99';
                $dB = $b->invoice_date ?: '9999-99-99';
                if ($dA !== $dB) return strcmp($dA, $dB);
                return $a->id <=> $b->id;
            });

            // Sort advance vouchers chronologically
            usort($partyAdvVouchers, function ($a, $b) {
                $dA = $a->voucher_date ?: '9999-99-99';
                $dB = $b->voucher_date ?: '9999-99-99';
                if ($dA !== $dB) return strcmp($dA, $dB);
                return $a->id <=> $b->id;
            });

            // Track direct payments and remaining pending balance per invoice
            $invPendingBalance = [];
            $invDirectPayments = [];
            foreach ($partyInvoices as $inv) {
                $net = (float)$inv->grand_total;
                $directVouchers = $vouchersByInvoiceId[$inv->id] ?? [];

                usort($directVouchers, function ($a, $b) {
                    $tA = $a->created_at ? $a->created_at->format('Y-m-d H:i:s') : ($a->voucher_date . ' 00:00:00');
                    $tB = $b->created_at ? $b->created_at->format('Y-m-d H:i:s') : ($b->voucher_date . ' 00:00:00');
                    return strcmp($tA, $tB);
                });

                $directPaid = 0.0;
                foreach ($directVouchers as $dv) {
                    $amt = (float)($dv->total_debit > 0 ? $dv->total_debit : $dv->total_credit);
                    $directPaid += $amt;
                }
                $invDirectPayments[$inv->id] = $directVouchers;
                $invPendingBalance[$inv->id] = max(0.0, round($net - $directPaid, 2));
            }

            // FIFO Allocation: Allocate advance vouchers against open bills
            $invAdvanceAdjustments = [];
            $unadjustedAdvanceList = [];

            foreach ($partyAdvVouchers as $advVoucher) {
                $remAdvAmt = (float)($advVoucher->total_debit > 0 ? $advVoucher->total_debit : $advVoucher->total_credit);
                $vDate = $advVoucher->voucher_date ? Carbon::parse($advVoucher->voucher_date) : null;
                $vTimeStr = $advVoucher->created_at ? Carbon::parse($advVoucher->created_at)->format('d M Y g:i a') : ($vDate ? $vDate->format('d M Y') : '');
                $vCreatedSort = $advVoucher->created_at ? $advVoucher->created_at->format('Y-m-d H:i:s') : ($vDate ? $vDate->format('Y-m-d 12:00:00') : '');
                $vMode = $advVoucher->payment_method ? ucwords(str_replace('_', ' ', $advVoucher->payment_method)) : ($advVoucher->voucher_type === 'cash' ? 'Cash' : 'Bank');

                foreach ($partyInvoices as $inv) {
                    if ($remAdvAmt <= 0.01) {
                        break;
                    }

                    if ($invPendingBalance[$inv->id] <= 0.01) {
                        continue;
                    }

                    $alloc = min($remAdvAmt, $invPendingBalance[$inv->id]);
                    $invPendingBalance[$inv->id] = round($invPendingBalance[$inv->id] - $alloc, 2);
                    $remAdvAmt = round($remAdvAmt - $alloc, 2);

                    $invAdvanceAdjustments[$inv->id][] = [
                        'amount' => $alloc,
                        'voucher' => $advVoucher,
                        'mode' => $vMode,
                        'badge' => $vMode . ' (Adv)',
                        'time_str' => $vTimeStr,
                        'raw_time' => $vCreatedSort,
                        'entry_id' => (int)$advVoucher->id,
                        'sort_time' => ($vDate ? $vDate->format('Y-m-d') : '9999-99-99') . '_' . $vCreatedSort . '_1',
                        'narration' => 'Auto-adjusted from Adv Voucher ' . ($advVoucher->voucher_no ?: '') . ($advVoucher->narration ? " ({$advVoucher->narration})" : ''),
                    ];
                }

                // If any excess advance remains after settling open bills
                if ($remAdvAmt > 0.01) {
                    $unadjustedAdvanceList[] = [
                        'amount' => $remAdvAmt,
                        'voucher' => $advVoucher,
                        'mode' => $vMode,
                        'time_str' => $vTimeStr,
                        'entry_id' => (int)$advVoucher->id,
                        'sort_key' => ($vDate ? $vDate->format('Y-m-d') : '9999-99-99') . '_' . $vCreatedSort . '_2',
                        'narration' => $advVoucher->narration ?: ($advVoucher->description ?: 'Advance Balance'),
                    ];
                }
            }

            // Build Bill Rows with Running Balances
            foreach ($partyInvoices as $inv) {
                $invDate = $inv->invoice_date ? Carbon::parse($inv->invoice_date) : null;
                $payType = $dispatchCreditMap[$inv->dispatch_id] ?? null;

                $creditDays = 7;
                if (!empty($payType) && preg_match('/\d+/', $payType, $m)) {
                    $val = (int)$m[0];
                    if ($val > 0 && $val <= 365) $creditDays = $val;
                }

                $dueDate = $invDate ? $invDate->copy()->addDays($creditDays) : null;
                $diffDays = 0; $dueStr = ''; $isOverdue = false;
                if ($dueDate) {
                    $diffDays = (int)round(Carbon::today()->diffInDays($dueDate, false));
                    if ($diffDays < 0) {
                        $isOverdue = true;
                        $dueStr = "Due: " . $dueDate->format('d M Y') . " ({$diffDays} days)";
                    } else {
                        $dueStr = "Due: " . $dueDate->format('d M Y') . " (+{$diffDays} days)";
                    }
                }

                $taxableAmt = (float)$inv->taxable_amount;
                $gstAmt = (float)($inv->cgst_amount + $inv->sgst_amount + $inv->igst_amount);
                $tdsAmt = (float)($inv->tds_amount ?? 0);
                $netAmt = (float)$inv->grand_total;

                $tokenId = $gateTokenMap[$inv->vehicle_id] ?? ($inv->dispatch_id ? (string)$inv->dispatch_id : '—');
                $truckNo = $inv->vehicle_no ?: ($inv->transport_name ?: '—');
                $invTimeStr = $inv->created_at ? $inv->created_at->format('Y-m-d H:i:s') : ($invDate ? $invDate->format('Y-m-d 00:00:00') : '0000-00-00 00:00:00');

                // Collect all payments for this invoice: Direct + Advance Adjustments
                $allPaymentsForInv = [];

                // 1. Direct payments
                foreach (($invDirectPayments[$inv->id] ?? []) as $dv) {
                    $vAmt = (float)($dv->total_debit > 0 ? $dv->total_debit : $dv->total_credit);
                    $vDate = $dv->voucher_date ? Carbon::parse($dv->voucher_date) : null;
                    $vTimeStr = $dv->created_at ? Carbon::parse($dv->created_at)->format('d M Y g:i a') : ($vDate ? $vDate->format('d M Y') : '');
                    $vCreatedSort = $dv->created_at ? $dv->created_at->format('Y-m-d H:i:s') : ($vDate ? $vDate->format('Y-m-d 12:00:00') : '0000-00-00 00:00:00');
                    $vMode = $dv->payment_method ? ucwords(str_replace('_', ' ', $dv->payment_method)) : ($dv->voucher_type === 'cash' ? 'Cash' : 'Bank');

                    $allPaymentsForInv[] = [
                        'amount' => $vAmt,
                        'badge' => $vMode,
                        'time_str' => $vTimeStr,
                        'raw_time' => $vCreatedSort,
                        'entry_id' => (int)$dv->id,
                        'sort_time' => ($vDate ? $vDate->format('Y-m-d') : '9999-99-99') . '_' . $vCreatedSort . '_1',
                        'narration' => $dv->narration ?: ($dv->description ?: ''),
                    ];
                }

                // 2. Advance adjustments
                foreach (($invAdvanceAdjustments[$inv->id] ?? []) as $adj) {
                    $allPaymentsForInv[] = [
                        'amount' => $adj['amount'],
                        'badge' => $adj['badge'],
                        'time_str' => $adj['time_str'],
                        'raw_time' => $adj['raw_time'],
                        'entry_id' => (int)$adj['entry_id'],
                        'sort_time' => $adj['sort_time'],
                        'narration' => $adj['narration'],
                    ];
                }

                // Sort all payment events chronologically
                usort($allPaymentsForInv, function ($a, $b) {
                    return strcmp($a['sort_time'], $b['sort_time']);
                });

                // Bill group timestamp and group entry ID
                $billGroupTime = $invTimeStr;
                $groupEntryId = (int)$inv->id;

                // Row 1: Bill Creation Entry
                $runningBalance = $netAmt;
                $allRows[] = [
                    'row_time' => $invTimeStr,
                    'row_id' => (int)$inv->id,
                    'group_time' => $billGroupTime,
                    'group_entry_id' => $groupEntryId,
                    'item_order' => 0,
                    'sort_key' => ($invDate ? $invDate->format('Y-m-d') : '9999-99-99') . '_' . $invTimeStr . '_0',
                    'invoice_id' => $inv->id,
                    'party_name' => $inv->customer_name ?: 'Unknown Customer',
                    'token_no' => $tokenId,
                    'bill_no' => $inv->invoice_no,
                    'is_no_bill' => false,
                    'invoice_date_formatted' => $invDate ? $invDate->format('d M Y') : '—',
                    'credit_days' => $creditDays,
                    'due_text' => $dueStr,
                    'is_overdue' => $isOverdue,
                    'truck_no' => $truckNo,
                    'taxable_amt' => $taxableAmt,
                    'gst_amt' => $gstAmt,
                    'tds_amt' => $tdsAmt,
                    'net_amt' => $netAmt,
                    'received_amt' => 0.0,
                    'received_badge' => '',
                    'received_time' => '',
                    'narration' => $inv->remarks ?: '',
                    'balance_amt' => $runningBalance,
                    'status' => 'Unpaid',
                    'status_badge' => 'badge-status-unpaid',
                    'is_bill_header' => true,
                ];

                // Rows 2..N: Payment & Advance Adjustment events
                foreach ($allPaymentsForInv as $pIdx => $payItem) {
                    $runningBalance = round($runningBalance - $payItem['amount'], 2);

                    if ($runningBalance <= 0.01) {
                        $statusLabel = 'Paid';
                        $statusBadge = 'badge-status-paid';
                        $runningBalance = 0.0;
                    } else {
                        $statusLabel = 'Partially Paid';
                        $statusBadge = 'badge-status-partial';
                    }

                    $allRows[] = [
                        'row_time' => $payItem['raw_time'],
                        'row_id' => (int)$payItem['entry_id'],
                        'group_time' => $billGroupTime,
                        'group_entry_id' => $groupEntryId,
                        'item_order' => $pIdx + 1,
                        'sort_key' => $payItem['sort_time'],
                        'invoice_id' => $inv->id,
                        'party_name' => $inv->customer_name ?: 'Unknown Customer',
                        'token_no' => '—',
                        'bill_no' => '—',
                        'is_no_bill' => false,
                        'is_voucher' => true,
                        'invoice_date_formatted' => $invDate ? $invDate->format('d M Y') : '—',
                        'credit_days' => $creditDays,
                        'due_text' => $dueStr,
                        'is_overdue' => $isOverdue,
                        'truck_no' => '—',
                        'taxable_amt' => null,
                        'gst_amt' => null,
                        'tds_amt' => null,
                        'net_amt' => $netAmt,
                        'received_amt' => $payItem['amount'],
                        'received_badge' => $payItem['badge'],
                        'received_time' => $payItem['time_str'],
                        'narration' => $payItem['narration'],
                        'balance_amt' => $runningBalance,
                        'status' => $statusLabel,
                        'status_badge' => $statusBadge,
                        'is_bill_header' => false,
                    ];
                }
            }

            // Add Unadjusted Advance rows (*No Bill*)
            foreach ($unadjustedAdvanceList as $unadj) {
                // If filtering by bill_no and bill_no is given, exclude unlinked vouchers unless searching for 'No Bill'
                if (!empty($billNo) && stripos('No Bill', $billNo) === false) {
                    continue;
                }

                $v = $unadj['voucher'];
                $vDate = $v->voucher_date ? Carbon::parse($v->voucher_date) : null;
                $vCreatedSort = $v->created_at ? $v->created_at->format('Y-m-d H:i:s') : ($vDate ? $vDate->format('Y-m-d 12:00:00') : '0000-00-00 00:00:00');

                $allRows[] = [
                    'row_time' => $vCreatedSort,
                    'row_id' => (int)$v->id,
                    'group_time' => $vCreatedSort,
                    'group_entry_id' => (int)$v->id,
                    'item_order' => 0,
                    'sort_key' => $unadj['sort_key'],
                    'invoice_id' => null,
                    'party_name' => $v->party_name ?: 'General Party',
                    'token_no' => '—',
                    'bill_no' => '—',
                    'is_no_bill' => true,
                    'is_voucher' => true,
                    'invoice_date_formatted' => $vDate ? $vDate->format('d M Y') : '—',
                    'credit_days' => null,
                    'due_text' => null,
                    'is_overdue' => false,
                    'truck_no' => '—',
                    'taxable_amt' => null,
                    'gst_amt' => null,
                    'tds_amt' => null,
                    'net_amt' => null,
                    'received_amt' => $unadj['amount'],
                    'received_badge' => $unadj['mode'],
                    'received_time' => $unadj['time_str'],
                    'narration' => $unadj['narration'],
                    'balance_amt' => null, // N/A
                    'status' => 'Advance',
                    'status_badge' => 'badge-status-cash',
                    'is_bill_header' => false,
                ];
            }
        }

        // 6. Sort all transactions chronologically in ASCENDING order (ASC)
        usort($allRows, function ($a, $b) {
            // 1. Group / Bill time ASC (Earliest entry at the top)
            $timeA = $a['group_time'] ?? ($a['row_time'] ?? '');
            $timeB = $b['group_time'] ?? ($b['row_time'] ?? '');
            if ($timeA !== $timeB) {
                return strcmp($timeA, $timeB);
            }
            // 2. Group entry ID ASC
            $idA = (int)($a['group_entry_id'] ?? ($a['invoice_id'] ?? ($a['row_id'] ?? 0)));
            $idB = (int)($b['group_entry_id'] ?? ($b['invoice_id'] ?? ($b['row_id'] ?? 0)));
            if ($idA !== $idB) {
                return $idA <=> $idB;
            }
            // 3. Item order ASC (bill header item_order 0 first, then payment 1, 2...)
            $orderA = (int)($a['item_order'] ?? 0);
            $orderB = (int)($b['item_order'] ?? 0);
            if ($orderA !== $orderB) {
                return $orderA <=> $orderB;
            }
            return strcmp($a['sort_key'], $b['sort_key']);
        });

        // 7. Filter by Status & Search Keyword
        $filtered = [];
        foreach ($allRows as $r) {
            // Status filter: 'paid', 'partial', 'unpaid', 'all'
            if ($status !== 'all') {
                $rStatus = strtolower(str_replace(' ', '', $r['status']));
                $targetStatus = strtolower(str_replace(' ', '', $status));
                if ($targetStatus === 'partial' || $targetStatus === 'partiallypaid') {
                    if ($rStatus !== 'partial' && $rStatus !== 'partiallypaid') {
                        continue;
                    }
                } elseif ($rStatus !== $targetStatus) {
                    continue;
                }
            }

            // Search filter
            if (!empty($search)) {
                $haystack = strtolower(implode(' ', [
                    $r['party_name'],
                    $r['bill_no'],
                    $r['token_no'],
                    $r['truck_no'],
                    $r['status'],
                    $r['narration'] ?? '',
                ]));
                if (!str_contains($haystack, strtolower($search))) {
                    continue;
                }
            }

            $filtered[] = $r;
        }

        return $filtered;
    }
}
