<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    /**
     * Base URL for the ERP Divine APIs
     */
    private $apiBaseUrl = 'http://localhost/erpdivine/api/';

    /**
     * Display the Purchase Order List & Customer Dashboard Page.
     */
    public function index()
    {
        return view('purchase.purchase_order_list');
    }

    /**
     * Display Dedicated Purchase Order Details Page in a New Tab.
     */
    public function showDetails($poid)
    {
        return view('purchase.purchase_order_details', compact('poid'));
    }

    /**
     * Proxy/Fetch Customers List API (for Purchase Filter)
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
            Log::error('Purchase API Error - getCustomers: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => []], 500);
        }
    }

    /**
     * Proxy/Fetch Customer Purchase Orders API
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

            $response = Http::timeout(10)->get($this->apiBaseUrl . 'get_customer_purchase_orders.php', $params);
            if ($response->successful()) {
                return response()->json($response->json());
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch purchase orders',
                'totals' => ['total_orders' => 0, 'total_ordered_tons' => 0, 'total_received_tons' => 0, 'total_pending_tons' => 0],
                'data' => []
            ], 500);
        } catch (\Exception $e) {
            Log::error('Purchase API Error - getOrders: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'totals' => ['total_orders' => 0, 'total_ordered_tons' => 0, 'total_received_tons' => 0, 'total_pending_tons' => 0],
                'data' => []
            ], 500);
        }
    }

    /**
     * Proxy/Fetch Purchase Order Details & Line Items API
     */
    public function getOrderDetails(Request $request)
    {
        $poid = $request->input('poid');
        if (!$poid) {
            return response()->json(['status' => 'error', 'message' => 'Purchase Order ID (poid) is required'], 400);
        }

        try {
            $response = Http::timeout(10)->get($this->apiBaseUrl . 'get_purchase_order_details.php', ['poid' => $poid]);
            if ($response->successful()) {
                return response()->json($response->json());
            }
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch purchase order details'], 500);
        } catch (\Exception $e) {
            Log::error('Purchase API Error - getOrderDetails: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Proxy/Fetch Purchase Order Received Material API
     */
    public function getReceivedMaterial(Request $request)
    {
        $poid = $request->input('poid');
        if (!$poid) {
            return response()->json(['status' => 'error', 'message' => 'Purchase Order ID (poid) is required'], 400);
        }

        try {
            $response = Http::timeout(10)->get($this->apiBaseUrl . 'get_purchase_order_received_material.php', ['poid' => $poid]);
            if ($response->successful()) {
                return response()->json($response->json());
            }
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch received material'], 500);
        } catch (\Exception $e) {
            Log::error('Purchase API Error - getReceivedMaterial: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Proxy/Fetch Customer Purchase Summary API
     */
    public function getSummary(Request $request)
    {
        $custId = $request->input('cust_id');
        if (!$custId) {
            return response()->json(['status' => 'error', 'message' => 'Customer ID is required'], 400);
        }

        try {
            $response = Http::timeout(10)->get($this->apiBaseUrl . 'get_customer_purchase_summary.php', ['cust_id' => $custId]);
            if ($response->successful()) {
                return response()->json($response->json());
            }
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch purchase summary'], 500);
        } catch (\Exception $e) {
            Log::error('Purchase API Error - getSummary: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
