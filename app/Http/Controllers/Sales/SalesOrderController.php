<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SalesOrderController extends Controller
{
    /**
     * Base URL for the ERP Divine APIs
     */
    private $apiBaseUrl = 'http://localhost/erpdivine/api/';

    /**
     * Display the Sales Order List & Customer Dashboard Page.
     */
    public function index()
    {
        return view('sales.sale_order_list');
    }

    /**
     * Display Dedicated Sale Order Details Page in a New Tab.
     */
    public function showDetails($slid)
    {
        return view('sales.sale_order_details', compact('slid'));
    }

    /**
     * Proxy/Fetch Customers List API
     */
    public function getCustomers()
    {
        try {
            $response = Http::timeout(10)->get($this->apiBaseUrl . 'get_customers.php');
            if ($response->successful()) {
                return response()->json($response->json());
            }
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch customers', 'data' => []], 500);
        } catch (\Exception $e) {
            Log::error('Sales API Error - getCustomers: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => []], 500);
        }
    }

    /**
     * Proxy/Fetch Customer Sales Orders API
     */
    public function getOrders(Request $request)
    {
        try {
            $params = [];
            if ($request->filled('cust_id')) {
                $params['cust_id'] = $request->input('cust_id');
            }
            if ($request->filled('page')) {
                $params['page'] = $request->input('page');
            }
            if ($request->filled('search')) {
                $params['search'] = $request->input('search');
            }

            $response = Http::timeout(10)->get($this->apiBaseUrl . 'get_customer_sale_orders.php', $params);
            if ($response->successful()) {
                return response()->json($response->json());
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch sales orders',
                'totals' => ['total_orders' => 0, 'total_ordered_tons' => 0, 'total_dispatched_tons' => 0, 'total_pending_tons' => 0],
                'data' => []
            ], 500);
        } catch (\Exception $e) {
            Log::error('Sales API Error - getOrders: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'totals' => ['total_orders' => 0, 'total_ordered_tons' => 0, 'total_dispatched_tons' => 0, 'total_pending_tons' => 0],
                'data' => []
            ], 500);
        }
    }

    /**
     * Proxy/Fetch Sale Order Details & Line Items API
     */
    public function getOrderDetails(Request $request)
    {
        $slid = $request->input('slid');
        if (!$slid) {
            return response()->json(['status' => 'error', 'message' => 'Sale Order ID (slid) is required'], 400);
        }

        try {
            $response = Http::timeout(10)->get($this->apiBaseUrl . 'get_sale_order_details.php', ['slid' => $slid]);
            if ($response->successful()) {
                return response()->json($response->json());
            }
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch sale order details'], 500);
        } catch (\Exception $e) {
            Log::error('Sales API Error - getOrderDetails: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Proxy/Fetch Customer Sales Summary API
     */
    public function getSummary(Request $request)
    {
        $custId = $request->input('cust_id');
        if (!$custId) {
            return response()->json(['status' => 'error', 'message' => 'Customer ID is required'], 400);
        }

        try {
            $response = Http::timeout(10)->get($this->apiBaseUrl . 'get_customer_sales_summary.php', ['cust_id' => $custId]);
            if ($response->successful()) {
                return response()->json($response->json());
            }
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch sales summary'], 500);
        } catch (\Exception $e) {
            Log::error('Sales API Error - getSummary: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Proxy/Fetch Sale Order Dispatched Items API
     */
    public function getDispatches(Request $request)
    {
        $slid = $request->input('slid');
        if (!$slid) {
            return response()->json(['status' => 'error', 'message' => 'Sale Order ID (slid) is required'], 400);
        }

        try {
            $response = Http::timeout(10)->get($this->apiBaseUrl . 'get_sale_order_dispatches.php', ['slid' => $slid]);
            if ($response->successful()) {
                return response()->json($response->json());
            }
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch dispatched items'], 500);
        } catch (\Exception $e) {
            Log::error('Sales API Error - getDispatches: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
