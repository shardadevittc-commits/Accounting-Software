<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the Modern Accounting & Finance Management ERP Dashboard.
     */
    public function index()
    {
        $authUser = Auth::user();

        $user = (object)[
            'name' => $authUser ? $authUser->name : 'Admin',
            'email' => $authUser ? $authUser->email : 'admin@gmail.com',
            'role' => $authUser && $authUser->roles->first() ? $authUser->roles->first()->name : 'Admin',
            'company' => 'Tixx Accounting ERP Solutions Pvt Ltd',
            'financial_year' => 'FY 2025-26',
        ];

        // 6 Summary KPI Cards Data (Indian Currency ₹)
        $kpis = [
            'total_sales' => [
                'amount' => 1250000.00,
                'change' => '+12.5%',
                'trend' => 'up',
                'subtext' => 'vs. previous period'
            ],
            'total_purchases' => [
                'amount' => 820000.00,
                'change' => '+8.4%',
                'trend' => 'up',
                'subtext' => 'vs. previous period'
            ],
            'total_receivable' => [
                'amount' => 425000.00,
                'count' => 24,
                'subtext' => '24 Outstanding Invoices'
            ],
            'total_payable' => [
                'amount' => 280000.00,
                'count' => 18,
                'subtext' => '18 Pending Bills'
            ],
            'cash_bank_balance' => [
                'amount' => 780500.00,
                'count' => 4,
                'subtext' => '4 Active Bank Accounts'
            ],
            'net_profit' => [
                'amount' => 430000.00,
                'change' => '+15.8%',
                'trend' => 'up',
                'subtext' => 'vs. previous period'
            ]
        ];

        // Payment Due Summary
        $paymentDueSummary = [
            'today' => 35000.00,
            'this_week' => 85000.00,
            'this_month' => 240000.00,
            'overdue' => 65000.00,
        ];

        // GST Summary (Indian Tax System)
        $gstSummary = [
            'output_gst' => 145000.00,
            'input_gst' => 62600.00,
            'cgst' => 41200.00,
            'sgst' => 41200.00,
            'igst' => 0.00,
            'net_payable' => 82400.00,
        ];

        // Receivable Aging Table Data
        $receivableAging = [
            [
                'customer' => 'ABC Traders',
                'invoice' => 'INV-2025-084',
                'due_date' => '05-Feb-2026',
                'amount' => 85000.00,
                'days_overdue' => 5,
                'status' => '1-30 Days',
                'badge' => 'warning'
            ],
            [
                'customer' => 'XYZ Industries Ltd',
                'invoice' => 'INV-2025-072',
                'due_date' => '15-Jan-2026',
                'amount' => 140000.00,
                'days_overdue' => 26,
                'status' => '1-30 Days',
                'badge' => 'warning'
            ],
            [
                'customer' => 'PQR Pvt Ltd',
                'invoice' => 'INV-2025-045',
                'due_date' => '10-Dec-2025',
                'amount' => 95000.00,
                'days_overdue' => 62,
                'status' => '61-90 Days',
                'badge' => 'danger'
            ],
            [
                'customer' => 'Apex Global Solutions',
                'invoice' => 'INV-2025-012',
                'due_date' => '01-Nov-2025',
                'amount' => 105000.00,
                'days_overdue' => 101,
                'status' => '90+ Days',
                'badge' => 'danger'
            ]
        ];

        // Payable Aging Table Data
        $payableAging = [
            [
                'supplier' => 'Mahindra Components',
                'bill_no' => 'BILL-9041',
                'due_date' => '12-Feb-2026',
                'amount' => 65000.00,
                'days_overdue' => 0,
                'status' => 'Current',
                'badge' => 'info'
            ],
            [
                'supplier' => 'Reliance Logistics',
                'bill_no' => 'BILL-8820',
                'due_date' => '25-Jan-2026',
                'amount' => 115000.00,
                'days_overdue' => 16,
                'status' => '1-30 Days',
                'badge' => 'warning'
            ],
            [
                'supplier' => 'Tata Industrial Supplies',
                'bill_no' => 'BILL-8402',
                'due_date' => '05-Dec-2025',
                'amount' => 100000.00,
                'days_overdue' => 67,
                'status' => '61-90 Days',
                'badge' => 'danger'
            ]
        ];

        // Top Customers
        $topCustomers = [
            ['name' => 'ABC Traders', 'invoices' => 14, 'revenue' => 850000.00],
            ['name' => 'XYZ Industries', 'invoices' => 10, 'revenue' => 620000.00],
            ['name' => 'PQR Pvt Ltd', 'invoices' => 8, 'revenue' => 480000.00],
            ['name' => 'Global Tech Solutions', 'invoices' => 6, 'revenue' => 310000.00],
        ];

        // Top Selling Products
        $topProducts = [
            ['product' => 'Accounting Software License Pro', 'sku' => 'PRO-ACC-01', 'qty' => 120, 'sales' => 600000.00, 'profit' => 240000.00],
            ['product' => 'GST Filing Automation Module', 'sku' => 'MOD-GST-02', 'qty' => 85, 'sales' => 340000.00, 'profit' => 136000.00],
            ['product' => 'Inventory Sync Connector', 'sku' => 'CON-INV-03', 'qty' => 60, 'sales' => 180000.00, 'profit' => 72000.00],
        ];

        // Low Stock Inventory Alerts
        $lowStockAlerts = [
            ['product' => 'POS Thermal Invoice Paper Rolls', 'sku' => 'PAP-POS-01', 'current_stock' => 12, 'min_stock' => 50, 'status' => 'Critical Low'],
            ['product' => 'BarCode Scanner Handheld HD', 'sku' => 'HW-SCN-04', 'current_stock' => 4, 'min_stock' => 15, 'status' => 'Low Stock'],
            ['product' => 'Smart Card NFC Readers', 'sku' => 'HW-NFC-09', 'current_stock' => 8, 'min_stock' => 20, 'status' => 'Low Stock'],
        ];

        // Recent Transactions Ledger
        $recentTransactions = [
            [
                'date' => '10-Feb-2026',
                'reference' => 'INV-2026-104',
                'description' => 'Sales Invoice - ABC Traders',
                'account' => 'Sales Account',
                'type' => 'Sale',
                'amount' => 125000.00,
                'status' => 'Paid',
                'type_class' => 'success'
            ],
            [
                'date' => '09-Feb-2026',
                'reference' => 'BILL-2026-088',
                'description' => 'Raw Material Purchase - Tata Ltd',
                'account' => 'Purchase Account',
                'type' => 'Purchase',
                'amount' => -85000.00,
                'status' => 'Unpaid',
                'type_class' => 'danger'
            ],
            [
                'date' => '08-Feb-2026',
                'reference' => 'PAY-REC-441',
                'description' => 'Payment Received - XYZ Industries',
                'account' => 'HDFC Bank Account',
                'type' => 'Payment Received',
                'amount' => 140000.00,
                'status' => 'Cleared',
                'type_class' => 'success'
            ],
            [
                'date' => '07-Feb-2026',
                'reference' => 'PAY-MADE-209',
                'description' => 'Supplier Payment - Reliance Logistics',
                'account' => 'ICICI Bank Account',
                'type' => 'Payment Made',
                'amount' => -60000.00,
                'status' => 'Cleared',
                'type_class' => 'danger'
            ],
            [
                'date' => '06-Feb-2026',
                'reference' => 'EXP-2026-015',
                'description' => 'Office Rent & Maintenance',
                'account' => 'Rent Expense Account',
                'type' => 'Expense',
                'amount' => -45000.00,
                'status' => 'Paid',
                'type_class' => 'warning'
            ],
            [
                'date' => '05-Feb-2026',
                'reference' => 'JRN-2026-004',
                'description' => 'Depreciation Journal Adjustment',
                'account' => 'Fixed Assets Ledger',
                'type' => 'Journal Entry',
                'amount' => -15000.00,
                'status' => 'Posted',
                'type_class' => 'info'
            ]
        ];

        // Role-based transaction filtering for Sales role
        if ($authUser && $authUser->hasRole('sales') && !$authUser->isAdmin()) {
            $recentTransactions = array_values(array_filter($recentTransactions, function($t) {
                return in_array($t['type'], ['Sale', 'Payment Received']);
            }));
        }

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
            'recentTransactions'
        ));
    }
}
