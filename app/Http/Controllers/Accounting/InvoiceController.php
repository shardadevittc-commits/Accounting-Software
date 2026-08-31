<?php

namespace App\Http\Controllers\Accounting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InvoiceItem;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    private $apiBaseUrl = 'http://localhost/erpdivine/api/';

    /**
     * Display the Dispatch Invoicing UI page.
     */
    public function index()
    {
        return view('accounting.dispatch_invoicing');
    }

    /**
     * Fetch pending vehicles awaiting invoice generation from ERP Divine API.
     */
    public function getPendingVehicles()
    {
        try {
            $response = Http::timeout(10)->get($this->apiBaseUrl . 'get_pending_invoice_vehicles.php');
            if ($response->successful()) {
                return response()->json($response->json());
            }
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch pending vehicles', 'data' => []], 500);
        } catch (\Exception $e) {
            Log::error('Invoice API Error - getPendingVehicles: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => []], 500);
        }
    }

    /**
     * Fetch dispatch details for a vehicle or dispatch ID from ERP Divine API.
     */
    public function getDispatchDetails(Request $request)
    {
        $vehicleId = $request->input('vehicle_id') ?? $request->input('vehicleid');
        $dispatchId = $request->input('dispatch_id') ?? $request->input('dispatchid');
        $custId = $request->input('cust_id') ?? $request->input('custid');

        if (!$vehicleId && !$dispatchId && !$custId) {
            return response()->json(['status' => 'error', 'message' => 'Vehicle ID, Dispatch ID or Customer ID is required'], 400);
        }

        try {
            $params = [];
            if ($vehicleId) {
                $params['vehicleid'] = $vehicleId;
            } elseif ($dispatchId) {
                $params['dispatchid'] = $dispatchId;
            }

            if (!empty($params)) {
                $response = Http::timeout(10)->get($this->apiBaseUrl . 'get_dispatch_details.php', $params);
                if ($response->successful()) {
                    $json = $response->json();
                    if (isset($json['status']) && $json['status'] === 'success' && !empty($json['data']['items'])) {
                        return response()->json($json);
                    }
                }
            }

            // Fallback: If dispatch items not finalized, fetch from customer's Sale Order items!
            if ($custId) {
                $ordersRes = Http::timeout(10)->get($this->apiBaseUrl . 'get_customer_sale_orders.php', ['cust_id' => $custId]);
                if ($ordersRes->successful()) {
                    $ordersData = $ordersRes->json();
                    if (isset($ordersData['data']) && count($ordersData['data']) > 0) {
                        $firstOrder = $ordersData['data'][0];
                        $slid = $firstOrder['slid'];
                        $orderDetailsRes = Http::timeout(10)->get($this->apiBaseUrl . 'get_sale_order_details.php', ['slid' => $slid]);
                        if ($orderDetailsRes->successful()) {
                            $orderDetails = $orderDetailsRes->json();
                            if (isset($orderDetails['data'])) {
                                $dData = $orderDetails['data'];
                                $formattedItems = [];
                                if (isset($dData['items'])) {
                                    foreach ($dData['items'] as $it) {
                                        $weight = floatval($it['pending_qty_tons'] ?? $it['ordered_qty_tons'] ?? 0);
                                        $rate = floatval($it['rate'] ?? $dData['bprice'] ?? 0);
                                        $formattedItems[] = [
                                            'disp_item_id' => null,
                                            'slid' => $dData['slid'],
                                            'product_name' => $it['product_name'] ?? 'BRIGHT BAR',
                                            'size_name' => $it['size'] ?? '',
                                            'grade_name' => $it['grade'] ?? '',
                                            'planned_weight_tons' => $weight,
                                            'actual_weight_tons' => $weight,
                                            'planned_pcs' => $it['pcs'] ?? 0,
                                            'actual_pcs' => $it['pcs'] ?? 0,
                                            'rate' => $rate,
                                            'subtotal' => $weight * $rate
                                        ];
                                    }
                                }

                                return response()->json([
                                    'status' => 'success',
                                    'source' => 'sale_order_fallback',
                                    'data' => [
                                        'dispatch' => [
                                            'dispatchid' => null,
                                            'vehicleid' => $vehicleId,
                                            'custid' => $custId,
                                            'otherchr' => 0,
                                        ],
                                        'customer' => [
                                            'cust_id' => $dData['cust_id'],
                                            'name' => $dData['customer_name'],
                                            'gst' => $dData['customer_gst'] ?? '',
                                            'address' => $dData['customer_address'] ?? '',
                                        ],
                                        'items' => $formattedItems
                                    ]
                                ]);
                            }
                        }
                    }
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'No dispatch items finalized in ERP yet. Click "+ Add Row" to add items manually.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Invoice API Error - getDispatchDetails: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Fetch sale order dispatches for a given sale order (slid).
     */
    public function getSaleOrderDispatches(Request $request)
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
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch sale order dispatches'], 500);
        } catch (\Exception $e) {
            Log::error('Invoice API Error - getSaleOrderDispatches: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store newly created Sales Invoice in Accounting DB.
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_date' => 'required|date',
            'customer_name' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.rate' => 'required|numeric',
            'items.*.amount' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            // Generate sequential Invoice Number e.g., INV-2026-0001
            $year = date('Y', strtotime($request->input('invoice_date')));
            $lastInvoice = Invoice::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
            $nextSeq = $lastInvoice ? ($lastInvoice->id + 1) : 1;
            $invoiceNo = 'INV-' . $year . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'invoice_no' => $invoiceNo,
                'invoice_date' => $request->input('invoice_date'),
                'vehicle_id' => $request->input('vehicle_id'),
                'dispatch_id' => $request->input('dispatch_id'),
                'customer_id' => $request->input('customer_id'),
                'customer_name' => $request->input('customer_name'),
                'customer_gst' => $request->input('customer_gst'),
                'customer_address' => $request->input('customer_address'),
                'vehicle_no' => $request->input('vehicle_no'),
                'transport_name' => $request->input('transport_name'),
                'taxable_amount' => $request->input('taxable_amount', 0),
                'cgst_rate' => $request->input('cgst_rate', 9),
                'cgst_amount' => $request->input('cgst_amount', 0),
                'sgst_rate' => $request->input('sgst_rate', 9),
                'sgst_amount' => $request->input('sgst_amount', 0),
                'igst_rate' => $request->input('igst_rate', 0),
                'igst_amount' => $request->input('igst_amount', 0),
                'freight_charges' => $request->input('freight_charges', 0),
                'other_charges' => $request->input('other_charges', 0),
                'tcs_amount' => $request->input('tcs_amount', 0),
                'grand_total' => $request->input('grand_total', 0),
                'remarks' => $request->input('remarks'),
                'created_by' => Auth::id(),
            ]);

            foreach ($request->input('items') as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'disp_item_id' => $item['disp_item_id'] ?? null,
                    'slid' => $item['slid'] ?? null,
                    'product_name' => $item['product_name'],
                    'size_name' => $item['size_name'] ?? null,
                    'grade_name' => $item['grade_name'] ?? null,
                    'pcs' => $item['pcs'] ?? 0,
                    'weight_tons' => $item['weight_tons'] ?? 0,
                    'rate' => $item['rate'] ?? 0,
                    'amount' => $item['amount'] ?? 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice ' . $invoiceNo . ' generated successfully!',
                'invoice_id' => $invoice->id,
                'invoice_no' => $invoiceNo,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice Store Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Printable view for generated Tax Invoice.
     */
    public function printInvoice($id)
    {
        $invoice = Invoice::with('items')->findOrFail($id);
        return view('accounting.invoice_print', compact('invoice'));
    }

      public function invoiceGenerate(Request $request)
{
    $request->validate([
        'invoice_date' => 'required|date',
        'customer_name' => 'required|string',
        'items' => 'required|array|min:1',

        'items.*.product_name' => 'required|string',
        'items.*.rate' => 'required|numeric',
        'items.*.amount' => 'required|numeric',
    ]);

    DB::beginTransaction();
    try {
        /*
        |--------------------------------------------------------------------------
        | 1. Generate Invoice Number
        |--------------------------------------------------------------------------
        */
        $year = date('Y', strtotime($request->invoice_date));
        
        $lastInvoice = Invoice::whereYear('created_at', $year)
        ->orderBy('id', 'desc')
        ->first();
        $nextSeq = $lastInvoice ? ($lastInvoice->id + 1) : 1;
        $invoiceNo = 'INV-' . $year . '-' .
        str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        /*
        |--------------------------------------------------------------------------
        | 2. Create Invoice
        |--------------------------------------------------------------------------
        */
        $invoice = Invoice::create([
            'invoice_no' => $invoiceNo,
            'invoice_date' => $request->invoice_date,

            'vehicle_id' => $request->vehicle_id,
            'dispatch_id' => $request->dispatch_id,
            'customer_id' => $request->customer_id,

            'customer_name' => $request->customer_name,
            'customer_gst' => $request->customer_gst,
            'customer_address' => $request->customer_address,

            'vehicle_no' => $request->vehicle_no,
            'transport_name' => $request->transport_name,

            'taxable_amount' => $request->taxable_amount ?? 0,

            'cgst_rate' => $request->cgst_rate ?? 9,
            'cgst_amount' => $request->cgst_amount ?? 0,

            'sgst_rate' => $request->sgst_rate ?? 9,
            'sgst_amount' => $request->sgst_amount ?? 0,

            'igst_rate' => $request->igst_rate ?? 0,
            'igst_amount' => $request->igst_amount ?? 0,

            'freight_charges' => $request->freight_charges ?? 0,
            'other_charges' => $request->other_charges ?? 0,

            'tcs_amount' => $request->tcs_amount ?? 0,

            'grand_total' => $request->grand_total ?? 0,

            'remarks' => $request->remarks,

            'created_by' => Auth::id(),
        ]);
        /*
        |--------------------------------------------------------------------------
        | 3. Create Invoice Items
        |--------------------------------------------------------------------------
        */
        foreach ($request->items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'disp_item_id' => $item['disp_item_id'] ?? null,
                'slid' => $item['slid'] ?? null,
                'product_name' => $item['product_name'],
                'size_name' => $item['size_name'] ?? null,
                'grade_name' => $item['grade_name'] ?? null,
                'pcs' => $item['pcs'] ?? 0,
                'weight_tons' => $item['weight_tons'] ?? 0,
                'rate' => $item['rate'] ?? 0,
                'amount' => $item['amount'] ?? 0,
            ]);
        }
        /*
        |--------------------------------------------------------------------------
        | 4. Commit Transaction
        |--------------------------------------------------------------------------
        */
        DB::commit();
        /*
        |--------------------------------------------------------------------------
        | 5. Return Success
        |--------------------------------------------------------------------------
        */
        return response()->json([
            'status' => 'success',
            'message' => 'Invoice ' . $invoiceNo . ' generated successfully!',
            'invoice_id' => $invoice->id,
            'invoice_no' => $invoiceNo,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error(
            'Invoice Generate Error: ' . $e->getMessage()
        );
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}
}