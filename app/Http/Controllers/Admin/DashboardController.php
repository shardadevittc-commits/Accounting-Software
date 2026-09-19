<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Ledger;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the Modern Accounting & Finance Management ERP Dashboard.
     */
    public function index()
    {
        $authUser = Auth::user();

        // 1. Current Financial Year Calculation (Indian FY: 01-Apr to 31-Mar)
        $now = Carbon::now();
        $fyStartYear = $now->month >= 4 ? $now->year : $now->year - 1;
        $fyEndYear = $fyStartYear + 1;
        $fyString = 'FY ' . $fyStartYear . '-' . substr((string)$fyEndYear, -2);

        $user = (object)[
            'name' => $authUser ? $authUser->name : 'Admin',
            'email' => $authUser ? $authUser->email : 'admin@gmail.com',
            'role' => $authUser && $authUser->roles->first() ? $authUser->roles->first()->name : 'Admin',
            'company' => 'DIVINE BRIGHT STEELS',
            'financial_year' => $fyString,
        ];

        // 2. Sales & Dispatch Metrics
        $totalSalesAmount = (float)Invoice::sum('grand_total');
        $totalWeightTons = (float)InvoiceItem::sum('weight_tons');
        $invoicesCount = Invoice::count();

        // Month-over-Month Sales Growth
        $currentMonthSales = (float)Invoice::whereMonth('invoice_date', $now->month)
            ->whereYear('invoice_date', $now->year)
            ->sum('grand_total');
        $prevMonthDate = (clone $now)->subMonth();
        $prevMonthSales = (float)Invoice::whereMonth('invoice_date', $prevMonthDate->month)
            ->whereYear('invoice_date', $prevMonthDate->year)
            ->sum('grand_total');

        if ($prevMonthSales > 0) {
            $salesChangePct = (($currentMonthSales - $prevMonthSales) / $prevMonthSales) * 100;
            $salesChange = ($salesChangePct >= 0 ? '+' : '') . number_format($salesChangePct, 1) . '%';
            $salesTrend = $salesChangePct >= 0 ? 'up' : 'down';
        } else {
            $salesChange = $currentMonthSales > 0 ? '+100%' : '0.0%';
            $salesTrend = 'up';
        }

        // 3. Cash & Bank Balances (Liquid Funds)
        $cashAccounts = Ledger::cashAccounts()->get();
        $cashBalance = (float)$cashAccounts->sum('current_balance');
        $bankAccounts = Ledger::bankAccounts()->get();
        $bankBalance = (float)$bankAccounts->sum('current_balance');
        $totalLiquidBalance = $cashBalance + $bankBalance;
        $activeAccountsCount = $cashAccounts->count() + $bankAccounts->count();

        // 4. Receivables (Customer Outstanding Debtors)
        $customerLedgers = Ledger::where('category', 'customer')->get();
        $totalReceivable = 0.0;
        $pendingCustomerCount = 0;

        foreach ($customerLedgers as $cl) {
            $bal = (float)$cl->current_balance;
            if ($bal > 0) {
                $totalReceivable += $bal;
                $pendingCustomerCount++;
            }
        }

        // Fallback: If party ledgers haven't recalculated yet, compute from Invoices minus Customer Vouchers
        if ($totalReceivable <= 0 && $totalSalesAmount > 0) {
            $totalReceived = (float)Voucher::where('status', 'posted')
                ->where('transaction_mode', 'receipt')
                ->sum('total_credit');
            $calcReceivable = max(0, $totalSalesAmount - $totalReceived);
            if ($calcReceivable > 0) {
                $totalReceivable = $calcReceivable;
                $pendingCustomerCount = Invoice::distinct('customer_name')->count('customer_name');
            }
        }

        // 5. Total Purchases & Payables (Creditors)
        $totalPurchases = (float)Voucher::where('voucher_type', 'purchase')->sum('total_debit');
        $totalPayable = (float)Ledger::where('category', 'supplier')->where('current_balance', '<', 0)->sum('current_balance');
        $totalPayable = abs($totalPayable);

        // Net Position (Sales - Purchases)
        $netProfit = $totalSalesAmount - $totalPurchases;

        // 6 Summary KPI Cards
        $kpis = [
            'total_sales' => [
                'amount' => $totalSalesAmount,
                'weight' => $totalWeightTons,
                'change' => $salesChange,
                'trend' => $salesTrend,
                'subtext' => $totalWeightTons > 0 ? (number_format($totalWeightTons, 2) . ' Tons Dispatched') : ($invoicesCount . ' Total Invoices'),
            ],
            'total_purchases' => [
                'amount' => $totalPurchases,
                'change' => '0.0%',
                'trend' => 'up',
                'subtext' => $totalPurchases > 0 ? 'Purchases Recorded' : 'Direct Inward Stock',
            ],
            'total_receivable' => [
                'amount' => $totalReceivable,
                'count' => $pendingCustomerCount,
                'subtext' => $pendingCustomerCount . ' Pending Parties',
            ],
            'total_payable' => [
                'amount' => $totalPayable,
                'count' => 0,
                'subtext' => $totalPayable > 0 ? 'Pending Supplier Bills' : 'No Overdue Payables',
            ],
            'cash_bank_balance' => [
                'amount' => $totalLiquidBalance,
                'count' => $activeAccountsCount,
                'subtext' => $activeAccountsCount . ' Liquid Account(s)',
            ],
            'net_profit' => [
                'amount' => $netProfit,
                'change' => $salesChange,
                'trend' => $salesTrend,
                'subtext' => 'Revenue Surplus',
            ],
        ];

        // 6. GST Summary (Real Invoice Tax Computations)
        $cgstTotal = (float)Invoice::sum('cgst_amount');
        $sgstTotal = (float)Invoice::sum('sgst_amount');
        $igstTotal = (float)Invoice::sum('igst_amount');
        $outputGst = $cgstTotal + $sgstTotal + $igstTotal;
        $inputGst = 0.00; // ITC from purchase bills if available
        $netGstPayable = max(0, $outputGst - $inputGst);

        $gstSummary = [
            'output_gst' => $outputGst,
            'input_gst' => $inputGst,
            'cgst' => $cgstTotal,
            'sgst' => $sgstTotal,
            'igst' => $igstTotal,
            'net_payable' => $netGstPayable,
        ];

        // Payment Due Breakdown
        $paymentDueSummary = [
            'today' => 0.00,
            'this_week' => 0.00,
            'this_month' => $totalReceivable,
            'overdue' => 0.00,
        ];

        // 7. Accounts Receivable Aging Table (Real Invoices with Due Calculation)
        $allInvoices = Invoice::orderBy('invoice_date', 'desc')->take(10)->get();
        $receivableAging = [];
        $overdueSum = 0.0;

        foreach ($allInvoices as $inv) {
            $invDate = $inv->invoice_date ? Carbon::parse($inv->invoice_date) : $now;
            $daysElapsed = (int)$now->diffInDays($invDate);

            if ($daysElapsed <= 30) {
                $status = '0-30 Days';
                $badge = 'info';
            } elseif ($daysElapsed <= 60) {
                $status = '31-60 Days';
                $badge = 'warning';
                $overdueSum += (float)$inv->grand_total;
            } elseif ($daysElapsed <= 90) {
                $status = '61-90 Days';
                $badge = 'danger';
                $overdueSum += (float)$inv->grand_total;
            } else {
                $status = '90+ Days';
                $badge = 'danger';
                $overdueSum += (float)$inv->grand_total;
            }

            $receivableAging[] = [
                'customer' => $inv->customer_name ?: 'General Party',
                'invoice' => $inv->invoice_no,
                'due_date' => $invDate->format('d-M-Y'),
                'amount' => (float)$inv->grand_total,
                'days_overdue' => $daysElapsed,
                'status' => $status,
                'badge' => $badge,
            ];
        }

        if ($overdueSum > 0) {
            $paymentDueSummary['overdue'] = $overdueSum;
        }

        // Payable Aging (Graceful fallback)
        $payableAging = [];

        // 8. Top Customers by Revenue & Invoices
        $topCustomersRaw = Invoice::select(
            'customer_name as name',
            DB::raw('count(id) as invoices'),
            DB::raw('sum(grand_total) as revenue')
        )
        ->whereNotNull('customer_name')
        ->where('customer_name', '!=', '')
        ->groupBy('customer_name')
        ->orderByDesc('revenue')
        ->take(5)
        ->get();

        $topCustomers = $topCustomersRaw->map(function ($item) {
            return [
                'name' => $item->name,
                'invoices' => (int)$item->invoices,
                'revenue' => (float)$item->revenue,
            ];
        })->toArray();

        // 9. Top Selling Products (From Invoice Items)
        $topProductsRaw = InvoiceItem::select(
            'product_name as product',
            DB::raw('sum(pcs) as qty'),
            DB::raw('sum(weight_tons) as weight'),
            DB::raw('sum(amount) as sales')
        )
        ->whereNotNull('product_name')
        ->where('product_name', '!=', '')
        ->groupBy('product_name')
        ->orderByDesc('sales')
        ->take(5)
        ->get();

        $topProducts = $topProductsRaw->map(function ($p, $idx) {
            return [
                'product' => $p->product,
                'sku' => 'STEEL-' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT),
                'qty' => (float)$p->qty ?: (float)$p->weight,
                'sales' => (float)$p->sales,
                'profit' => (float)$p->sales * 0.15, // estimated margin
            ];
        })->toArray();

        // Low Stock Inventory Alerts
        $lowStockAlerts = [];

        // 10. Recent Transactions Ledger Feed (Invoices + Vouchers unified)
        $recentInvoices = Invoice::latest('invoice_date')->latest('id')->take(6)->get();
        $recentVouchers = Voucher::with(['entries.ledger'])->latest('voucher_date')->latest('id')->take(6)->get();

        $unifiedTx = [];

        foreach ($recentInvoices as $inv) {
            $unifiedTx[] = [
                'date_raw' => $inv->invoice_date ? Carbon::parse($inv->invoice_date)->format('Y-m-d') : '9999-99-99',
                'date' => $inv->invoice_date ? Carbon::parse($inv->invoice_date)->format('d-M-Y') : '—',
                'reference' => $inv->invoice_no,
                'description' => 'Sales Bill - ' . ($inv->customer_name ?: 'Customer'),
                'account' => 'Sales Account',
                'type' => 'Sale',
                'amount' => (float)$inv->grand_total,
                'status' => 'Billed',
                'type_class' => 'success',
            ];
        }

        foreach ($recentVouchers as $v) {
            $isDebit = ($v->transaction_mode === 'payment');
            $vLabel = match ($v->voucher_type) {
                'cash' => 'Cash Voucher',
                'bank' => 'Bank (' . strtoupper($v->payment_method ?: 'Online') . ')',
                'general' => 'General Voucher',
                default => 'Voucher'
            };

            $unifiedTx[] = [
                'date_raw' => $v->voucher_date ? Carbon::parse($v->voucher_date)->format('Y-m-d') : '9999-99-99',
                'date' => $v->voucher_date ? Carbon::parse($v->voucher_date)->format('d-M-Y') : '—',
                'reference' => $v->voucher_no,
                'description' => $v->description ?: ($v->party_name ? "{$vLabel} - {$v->party_name}" : $vLabel),
                'account' => $v->party_name ?: 'General Ledger',
                'type' => $vLabel,
                'amount' => (float)($v->total_debit > 0 ? $v->total_debit : $v->total_credit) * ($isDebit ? -1 : 1),
                'status' => ucfirst($v->status ?: 'posted'),
                'type_class' => $isDebit ? 'danger' : 'success',
            ];
        }

        usort($unifiedTx, function ($a, $b) {
            return strcmp($b['date_raw'], $a['date_raw']);
        });

        $recentTransactions = array_slice($unifiedTx, 0, 8);

        // Role-based transaction filtering for Sales role
        if ($authUser && $authUser->hasRole('sales') && !$authUser->isAdmin()) {
            $recentTransactions = array_values(array_filter($recentTransactions, function ($t) {
                return in_array($t['type'], ['Sale', 'Cash Voucher', 'Bank Voucher']);
            }));
        }

        // 11. Chart Datasets (Monthly Sales vs Collections over Last 6 Months)
        $chartLabels = [];
        $chartSalesData = [];
        $chartCollectionData = [];

        for ($i = 5; $i >= 0; $i--) {
            $targetDate = (clone $now)->subMonths($i);
            $monthName = $targetDate->format('M Y');
            $chartLabels[] = $monthName;

            $mSales = (float)Invoice::whereMonth('invoice_date', $targetDate->month)
                ->whereYear('invoice_date', $targetDate->year)
                ->sum('grand_total');
            $chartSalesData[] = $mSales;

            $mCollections = (float)Voucher::where('status', 'posted')
                ->where('transaction_mode', 'receipt')
                ->whereMonth('voucher_date', $targetDate->month)
                ->whereYear('voucher_date', $targetDate->year)
                ->sum('total_credit');
            $chartCollectionData[] = $mCollections;
        }

        // Payment Method Breakdown for Donut Chart
        $paymentMethodsRaw = Voucher::where('status', 'posted')
            ->select('payment_method', DB::raw('count(id) as count'), DB::raw('sum(total_credit) as total'))
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->get();

        $donutLabels = [];
        $donutValues = [];
        foreach ($paymentMethodsRaw as $pm) {
            $donutLabels[] = strtoupper($pm->payment_method);
            $donutValues[] = (float)$pm->total;
        }

        if (empty($donutLabels) || array_sum($donutValues) == 0) {
            $donutLabels = ['Cash', 'Bank / Online', 'General Voucher'];
            $donutValues = [
                (float)Voucher::where('voucher_type', 'cash')->sum('total_credit'),
                (float)Voucher::where('voucher_type', 'bank')->sum('total_credit'),
                (float)Voucher::where('voucher_type', 'general')->sum('total_credit'),
            ];
            if (array_sum($donutValues) == 0) {
                $donutValues = [1, 0, 0];
            }
        }

        $chartDataJson = json_encode([
            'sales_purchase' => [
                'labels' => $chartLabels,
                'sales' => $chartSalesData,
                'collections' => $chartCollectionData,
            ],
            'payment_modes' => [
                'labels' => $donutLabels,
                'values' => $donutValues,
            ],
        ]);

        return view('admin.dashboard', compact(
            'user',
            'kpis',
            'paymentDueSummary',
            'gstSummary',
            'receivableAging',
            'payableAging',
            'topCustomers',
            'topProducts',
            'lowStockAlerts',
            'recentTransactions',
            'chartDataJson'
        ));
    }
}
