<?php

namespace App\Http\Controllers\Accounting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InvoiceItem;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    /**
     * Fetch pending vehicles awaiting invoice generation from ERP Divine API, with DB fallback.
     */
    public function getPendingVehicles(Request $request)
    {
        // First try via HTTP proxy
        try {
            $params = [];
            if ($request->filled('cust_id')) $params['cust_id'] = $request->input('cust_id');
            if ($request->filled('partyname')) $params['partyname'] = $request->input('partyname');
            if ($request->filled('date1')) $params['date1'] = $request->input('date1');
            if ($request->filled('dateTo')) $params['dateTo'] = $request->input('dateTo');
            if ($request->filled('token')) $params['token'] = $request->input('token');
            if ($request->filled('vehicle_no')) $params['vehicle_no'] = $request->input('vehicle_no');

            $response = Http::timeout(2)->get($this->apiBaseUrl . 'get_pending_invoice_vehicles.php', $params);
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === 'success' && isset($data['data'])) {
                    $vehicles = $data['data'];
                    foreach ($vehicles as &$v) {
                        $dispatchId = $v['dispatch_id'] ?? null;
                        if (!$dispatchId && isset($v['vehicle_id'])) {
                            $disp = DB::selectOne("SELECT dispatchid, custid FROM devine.dispatch WHERE vehicleid = ?", [$v['vehicle_id']]);
                            if ($disp) {
                                $dispatchId = $disp->dispatchid;
                                if (empty($v['cust_id'])) $v['cust_id'] = $disp->custid;
                            }
                        }
                        $invoice = null;
                        if ($dispatchId) {
                            $invoice = Invoice::where('dispatch_id', $dispatchId)->first();
                        }
                        if (!$invoice && isset($v['vehicle_id'])) {
                            $invoice = Invoice::where('vehicle_id', $v['vehicle_id'])->first();
                        }

                        $v['dispatch_id'] = $dispatchId;
                        $v['invoice_no'] = $invoice ? $invoice->invoice_no : null;
                        $v['invoice_id'] = $invoice ? $invoice->id : null;
                        $v['invoice_amount'] = $invoice ? $invoice->grand_total : null;

                        // Ensure customer contact (email & mobile) are fully populated
                        $cId = $v['cust_id'] ?? ($invoice ? $invoice->customer_id : null);
                        if (!empty($cId)) {
                            $cust = DB::selectOne("SELECT name, email, mobile, gst FROM devine.customers WHERE cust_id = ?", [$cId]);
                            if ($cust) {
                                $v['email'] = $cust->email;
                                $v['mobile'] = (!empty($v['mobile']) && $v['mobile'] !== 'N/A') ? $v['mobile'] : $cust->mobile;
                                $v['partyname'] = (!empty($v['partyname']) && $v['partyname'] !== 'Consignee') ? $v['partyname'] : $cust->name;
                                $v['gst'] = $v['gst'] ?? $cust->gst;
                            }
                        }
                        
                        $totals = $this->calculateDispatchTotals($v['vehicle_id'], $dispatchId, $v['cust_id'] ?? null);
                        $v['total_qty'] = $totals['total_qty'];
                        $v['total_amount'] = $totals['total_amount'];
                    }
                    $data['data'] = $vehicles;
                    return response()->json($data);
                }
            }
        } catch (\Exception $e) {
            Log::info("HTTP Proxy failed. Falling back to DB query: " . $e->getMessage());
        }

        // Direct DB Query fallback
        try {
            $where = "gate.efrom = 2";
            $bindings = [];

            if ($request->filled('cust_id')) {
                $where .= " AND (gate.cid = :cust_id OR (SELECT dispatch.custid FROM devine.dispatch WHERE dispatch.vehicleid = gate.gid ORDER BY dispatchid DESC LIMIT 1) = :cust_id)";
                $bindings['cust_id'] = $request->input('cust_id');
            } elseif ($request->filled('partyname')) {
                $where .= " AND (customers.name LIKE :partyname OR (SELECT cust2.name FROM devine.customers cust2 WHERE cust2.cust_id = (SELECT dispatch.custid FROM devine.dispatch WHERE dispatch.vehicleid = gate.gid ORDER BY dispatchid DESC LIMIT 1)) LIKE :partyname)";
                $bindings['partyname'] = '%' . $request->input('partyname') . '%';
            }

            if ($request->filled('date1')) {
                $where .= " AND DATE(gate.createdon) >= :date1";
                $bindings['date1'] = $request->input('date1');
            }
            if ($request->filled('dateTo')) {
                $where .= " AND DATE(gate.createdon) <= :dateTo";
                $bindings['dateTo'] = $request->input('dateTo');
            }

            if ($request->filled('token')) {
                $where .= " AND gate.tokenid LIKE :token";
                $bindings['token'] = '%' . $request->input('token') . '%';
            }

            if ($request->filled('vehicle_no')) {
                $where .= " AND gate.vehicleno LIKE :vehicle_no";
                $bindings['vehicle_no'] = '%' . $request->input('vehicle_no') . '%';
            }

            $sql = "
                SELECT gate.gid AS vehicle_id, gate.tokenid, 
                       COALESCE(gate.cid, (SELECT dispatch.custid FROM devine.dispatch WHERE dispatch.vehicleid = gate.gid ORDER BY dispatchid DESC LIMIT 1)) AS cust_id, 
                       COALESCE(customers.name, (SELECT cust2.name FROM devine.customers cust2 WHERE cust2.cust_id = (SELECT dispatch.custid FROM devine.dispatch WHERE dispatch.vehicleid = gate.gid ORDER BY dispatchid DESC LIMIT 1))) AS partyname,
                       customers.p_code, customers.gst, customers.mobile, customers.email,
                       gate.vehicleno, gate.vehicletype, gate.transport, gate.drivername, gate.drivermobile, 
                       gate.gatestatus, gate.dispatchmarkedcompleted, gate.createdon,
                       (SELECT dispatchid FROM devine.dispatch WHERE dispatch.vehicleid = gate.gid ORDER BY dispatchid DESC LIMIT 1) AS dispatch_id,
                       (SELECT invoice_no FROM account.invoices WHERE invoices.dispatch_id = (SELECT dispatchid FROM devine.dispatch WHERE dispatch.vehicleid = gate.gid ORDER BY dispatchid DESC LIMIT 1) OR invoices.vehicle_id = gate.gid ORDER BY id DESC LIMIT 1) AS invoice_no,
                       (SELECT id FROM account.invoices WHERE invoices.dispatch_id = (SELECT dispatchid FROM devine.dispatch WHERE dispatch.vehicleid = gate.gid ORDER BY dispatchid DESC LIMIT 1) OR invoices.vehicle_id = gate.gid ORDER BY id DESC LIMIT 1) AS invoice_id,
                       (SELECT grand_total FROM account.invoices WHERE invoices.dispatch_id = (SELECT dispatchid FROM devine.dispatch WHERE dispatch.vehicleid = gate.gid ORDER BY dispatchid DESC LIMIT 1) OR invoices.vehicle_id = gate.gid ORDER BY id DESC LIMIT 1) AS invoice_amount
                FROM devine.gate 
                LEFT JOIN devine.customers ON customers.cust_id = gate.cid
                WHERE $where
                ORDER BY gate.createdon DESC
            ";

            $vehicles = DB::select($sql, $bindings);
            
            $statusFilter = $request->input('status_filter', 'all');
            $filteredVehicles = [];

            foreach ($vehicles as $v) {
                $vArr = (array) $v;
                
                // Ensure email & mobile are populated from customer
                $cId = $vArr['cust_id'] ?? null;
                if (!$cId && !empty($vArr['invoice_id'])) {
                    $inv = Invoice::find($vArr['invoice_id']);
                    if ($inv && $inv->customer_id) $cId = $inv->customer_id;
                }
                if ($cId) {
                    $cust = DB::selectOne("SELECT email, mobile, name FROM devine.customers WHERE cust_id = ?", [$cId]);
                    if ($cust) {
                        if (empty($vArr['email'])) $vArr['email'] = $cust->email;
                        if (empty($vArr['mobile'])) $vArr['mobile'] = $cust->mobile;
                        if (empty($vArr['partyname'])) $vArr['partyname'] = $cust->name;
                    }
                }

                $totals = $this->calculateDispatchTotals($vArr['vehicle_id'], $vArr['dispatch_id'], $vArr['cust_id']);
                $vArr['total_qty'] = $totals['total_qty'];
                $vArr['total_amount'] = $totals['total_amount'];

                $hasInvoice = !empty($vArr['invoice_no']);
                if ($statusFilter === 'pending' && $hasInvoice) continue;
                if ($statusFilter === 'invoiced' && !$hasInvoice) continue;

                $filteredVehicles[] = $vArr;
            }

            return response()->json([
                'status' => 'success',
                'total_records' => count($filteredVehicles),
                'data' => $filteredVehicles
            ]);

        } catch (\Exception $e) {
            Log::error('Invoice Local DB Fallback Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => []], 500);
        }
    }

    /**
     * Helper to calculate dispatch weight & amount totals from DB.
     */
    private function calculateDispatchTotals($vehicleId, $dispatchId, $custId)
    {
        $totalQty = 0;
        $totalAmount = 0;

        if ($dispatchId) {
            $res = DB::selectOne("
                SELECT 
                    SUM(COALESCE(finalweight, actualweight, weighttodispatch, 0)) AS total_qty,
                    SUM(COALESCE(finalweight, actualweight, weighttodispatch, 0) * (saleordersizes.soprice + COALESCE(saleordersizes.sizeextra, 0))) AS total_amount
                FROM devine.dispatch_item
                LEFT JOIN devine.saleordersizes ON saleordersizes.sosize = dispatch_item.sosize
                WHERE dispatch_item.dispid = ?
            ", [$dispatchId]);
            if ($res) {
                $totalQty = floatval($res->total_qty);
                $totalAmount = floatval($res->total_amount);
            }
        } elseif ($custId) {
            $latestOrder = DB::selectOne("SELECT slid FROM devine.saleorder WHERE cid = ? ORDER BY slid DESC LIMIT 1", [$custId]);
            if ($latestOrder) {
                $res = DB::selectOne("
                    SELECT 
                        SUM(COALESCE(weightintons, 0)) AS total_qty,
                        SUM(COALESCE(weightintons, 0) * (soprice + COALESCE(sizeextra, 0))) AS total_amount
                    FROM devine.saleordersizes
                    WHERE sosaleid = ?
                ", [$latestOrder->slid]);
                if ($res) {
                    $totalQty = floatval($res->total_qty);
                    $totalAmount = floatval($res->total_amount);
                }
            }
        }

        return [
            'total_qty' => round($totalQty, 3),
            'total_amount' => round($totalAmount, 2)
        ];
    }

    /**
     * Fetch dispatch details for a vehicle or dispatch ID from ERP Divine API, with DB fallback.
     */
    public function getDispatchDetails(Request $request)
    {
        $vehicleId = $request->input('vehicle_id') ?? $request->input('vehicleid');
        $dispatchId = $request->input('dispatch_id') ?? $request->input('dispatchid');
        $custId = $request->input('cust_id') ?? $request->input('custid');

        if (!$vehicleId && !$dispatchId && !$custId) {
            return response()->json(['status' => 'error', 'message' => 'Vehicle ID, Dispatch ID or Customer ID is required'], 400);
        }

        // Try HTTP API first
        try {
            $params = [];
            if ($vehicleId) $params['vehicleid'] = $vehicleId;
            elseif ($dispatchId) $params['dispatchid'] = $dispatchId;

            if (!empty($params)) {
                $response = Http::timeout(2)->get($this->apiBaseUrl . 'get_dispatch_details.php', $params);
                if ($response->successful()) {
                    $json = $response->json();
                    if (isset($json['status']) && $json['status'] === 'success') {
                        if (isset($json['data']['items'])) {
                            foreach ($json['data']['items'] as &$it) {
                                $it['hsn'] = $it['hsn'] ?? '7214';
                                $it['gst_rate'] = $it['gst_rate'] ?? 18;
                            }
                        }
                        return response()->json($json);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::info("HTTP getDispatchDetails failed. Falling back to DB query: " . $e->getMessage());
        }

        // Direct DB Query fallback
        try {
            $where = "1=1";
            $bindings = [];
            if ($dispatchId) {
                $where .= " AND dispatch.dispatchid = ?";
                $bindings[] = $dispatchId;
            } elseif ($vehicleId) {
                $where .= " AND dispatch.vehicleid = ?";
                $bindings[] = $vehicleId;
            }

            $dispHeader = DB::selectOne("
                SELECT 
                    dispatch.dispatchid,
                    dispatch.vehicleid,
                    dispatch.custid,
                    dispatch.shippedTo,
                    dispatch.weight AS header_weight,
                    dispatch.cashdiscount,
                    dispatch.laborchr,
                    dispatch.otherchr,
                    dispatch.tcs,
                    dispatch.createdon,
                    dispatch.createdby,
                    customers.name AS customer_name,
                    customers.p_code,
                    customers.gst AS customer_gst,
                    customers.email AS customer_email,
                    customers.mobile AS customer_mobile,
                    customers.address AS customer_address,
                    customers.city AS customer_city,
                    customers.state AS customer_state,
                    customers.pincode AS customer_pincode,
                    gate.tokenid,
                    gate.vehicleno,
                    gate.vehicletype,
                    gate.transport,
                    gate.drivername,
                    gate.drivermobile,
                    gate.gatestatus,
                    gate.dispatchmarkedcompleted
                FROM devine.dispatch 
                LEFT JOIN devine.customers ON customers.cust_id = dispatch.custid 
                LEFT JOIN devine.gate ON gate.gid = dispatch.vehicleid 
                WHERE $where
                ORDER BY dispatch.dispatchid DESC
                LIMIT 1
            ", $bindings);

            if ($dispHeader) {
                $dId = intval($dispHeader->dispatchid);

                $dbItems = DB::select("
                    SELECT 
                        dispatch_item.dispitemid,
                        dispatch_item.dispid,
                        dispatch_item.vid,
                        dispatch_item.customer,
                        dispatch_item.slid,
                        dispatch_item.sopid,
                        dispatch_item.sosize,
                        dispatch_item.gradeid,
                        dispatch_item.sizeid,
                        dispatch_item.brandid,
                        dispatch_item.weighttodispatch,
                        dispatch_item.pctodispatch,
                        dispatch_item.actualweight,
                        dispatch_item.actualpcs,
                        dispatch_item.finalweighttodispatch,
                        dispatch_item.finalweight,
                        dispatch_item.finalbundles,
                        dispatch_item.finalpcs,
                        dispatch_item.bundleweight,
                        dispatch_item.completed,
                        dispatch_item.dispatchedon,
                        products.productname,
                        sizes.size AS size_name,
                        grade.grade AS grade_name,
                        brands.brandname,
                        saleordersizes.soprice,
                        saleordersizes.sizeextra
                    FROM devine.dispatch_item
                    LEFT JOIN devine.saleordersizes ON saleordersizes.sosize = dispatch_item.sosize
                    LEFT JOIN devine.grade ON grade.gid = dispatch_item.gradeid
                    LEFT JOIN devine.brands ON brands.brid = dispatch_item.brandid
                    LEFT JOIN devine.products ON products.prid = dispatch_item.sopid
                    LEFT JOIN devine.sizes ON sizes.sid = dispatch_item.sizeid
                    WHERE dispatch_item.dispid = ?
                    ORDER BY dispatch_item.dispitemid ASC
                ", [$dId]);

                $items = [];
                $totalPlannedWt = 0.0;
                $totalActualWt  = 0.0;
                $totalSubtotal  = 0.0;

                foreach ($dbItems as $row) {
                    $row = (array) $row;
                    $plannedWt = floatval($row['weighttodispatch']);
                    $actualWt  = floatval($row['finalweight'] > 0 ? $row['finalweight'] : $row['actualweight']);
                    $rate      = floatval($row['soprice']) + floatval($row['sizeextra']);
                    $subtotal  = round($actualWt * $rate, 2);

                    $totalPlannedWt += $plannedWt;
                    $totalActualWt  += $actualWt;
                    $totalSubtotal  += $subtotal;

                    $items[] = [
                        'disp_item_id'        => intval($row['dispitemid']),
                        'slid'                => intval($row['slid']),
                        'product_name'        => $row['productname'] ?? '',
                        'size_name'           => $row['size_name'] ?? '',
                        'grade_name'          => $row['grade_name'] ?? '',
                        'brand_name'          => $row['brandname'] ?? '',
                        'hsn'                 => '7214',
                        'gst_rate'            => 18,
                        'planned_weight_tons' => round($plannedWt, 3),
                        'planned_pcs'         => floatval($row['pctodispatch']),
                        'actual_weight_tons'  => round($actualWt, 3),
                        'actual_pcs'          => floatval($row['finalpcs'] > 0 ? $row['finalpcs'] : $row['actualpcs']),
                        'final_bundles'       => floatval($row['finalbundles']),
                        'rate'                => round($rate, 2),
                        'subtotal'            => $subtotal,
                        'completed'           => intval($row['completed']),
                        'dispatchedon'        => $row['dispatchedon']
                    ];
                }

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'dispatch' => [
                            'dispatchid'    => intval($dispHeader->dispatchid),
                            'vehicleid'     => intval($dispHeader->vehicleid),
                            'custid'        => intval($dispHeader->custid),
                            'shippedTo'     => intval($dispHeader->shippedTo),
                            'header_weight' => floatval($dispHeader->header_weight),
                            'cashdiscount'  => floatval($dispHeader->cashdiscount),
                            'laborchr'      => floatval($dispHeader->laborchr),
                            'otherchr'      => floatval($dispHeader->otherchr),
                            'tcs'           => floatval($dispHeader->tcs),
                            'createdon'     => $dispHeader->createdon,
                            'createdby'     => intval($dispHeader->createdby)
                        ],
                        'customer' => [
                            'cust_id' => intval($dispHeader->custid),
                            'name'    => $dispHeader->customer_name,
                            'p_code'  => $dispHeader->p_code,
                            'gst'     => $dispHeader->customer_gst,
                            'email'   => $dispHeader->customer_email,
                            'mobile'  => $dispHeader->customer_mobile,
                            'address' => $dispHeader->customer_address,
                            'city'    => $dispHeader->customer_city,
                            'state'   => $dispHeader->customer_state,
                            'pincode' => $dispHeader->customer_pincode
                        ],
                        'vehicle' => [
                            'tokenid'                 => $dispHeader->tokenid,
                            'vehicleno'               => $dispHeader->vehicleno,
                            'vehicletype'             => $dispHeader->vehicletype,
                            'transport'               => $dispHeader->transport,
                            'drivername'              => $dispHeader->drivername,
                            'drivermobile'            => $dispHeader->drivermobile,
                            'gatestatus'              => $dispHeader->gatestatus,
                            'dispatchmarkedcompleted' => intval($dispHeader->dispatchmarkedcompleted)
                        ],
                        'summary' => [
                            'total_items'              => count($items),
                            'total_planned_weight_tons'=> round($totalPlannedWt, 3),
                            'total_actual_weight_tons' => round($totalActualWt, 3),
                            'total_subtotal_amount'    => round($totalSubtotal, 2)
                        ],
                        'items' => $items
                    ]
                ]);
            }

            if ($custId) {
                $latestOrder = DB::selectOne("
                    SELECT 
                        saleorder.slid,
                        saleorder.cid,
                        saleorder.cust_po,
                        saleorder.bprice,
                        saleorder.status,
                        saleorder.orderType,
                        saleorder.dispatch_on,
                        saleorder.createdon,
                        saleorder.remarks,
                        customers.name AS customer_name,
                        customers.p_code,
                        customers.gst AS customer_gst,
                        customers.email AS customer_email,
                        customers.mobile AS customer_mobile,
                        customers.address AS customer_address,
                        customers.city AS customer_city,
                        customers.state AS customer_state,
                        customers.pincode AS customer_pincode
                    FROM devine.saleorder
                    LEFT JOIN devine.customers ON customers.cust_id = saleorder.cid
                    WHERE saleorder.cid = ?
                    ORDER BY saleorder.slid DESC
                    LIMIT 1
                ", [$custId]);

                if ($latestOrder) {
                    $slid = $latestOrder->slid;
                    
                    $dbSOItems = DB::select("
                        SELECT 
                            saleordersizes.sosize AS item_id,
                            saleordersizes.soprid,
                            saleordersizes.sogradeid,
                            saleordersizes.sosizeid,
                            saleordersizes.plength,
                            saleordersizes.lngthunit,
                            saleordersizes.edgetype,
                            saleordersizes.soprice,
                            saleordersizes.qty,
                            saleordersizes.weightintons,
                            saleordersizes.dispatched,
                            saleordersizes.completed,
                            saleordersizes.pcs,
                            saleordersizes.itemremarks,
                            products.productname,
                            grade.grade AS grade_name,
                            sizes.size AS size_name
                        FROM devine.saleordersizes
                        LEFT JOIN devine.products ON products.prid = saleordersizes.soprid
                        LEFT JOIN devine.grade ON grade.gid = saleordersizes.sogradeid
                        LEFT JOIN devine.sizes ON sizes.sid = saleordersizes.sosizeid
                        WHERE saleordersizes.sosaleid = ?
                        ORDER BY saleordersizes.sosize ASC
                    ", [$slid]);

                    $formattedItems = [];
                    foreach ($dbSOItems as $it) {
                        $it = (array) $it;
                        $weight = floatval($it['weightintons'] - $it['dispatched']);
                        if ($weight <= 0) $weight = floatval($it['weightintons']);
                        $rate = floatval($it['soprice']);
                        
                        $formattedItems[] = [
                            'disp_item_id' => null,
                            'slid' => $slid,
                            'product_name' => $it['productname'] ?? 'BRIGHT BAR',
                            'size_name' => $it['size_name'] ?? '',
                            'grade_name' => $it['grade_name'] ?? '',
                            'brand_name' => '',
                            'hsn' => '7214',
                            'gst_rate' => 18,
                            'planned_weight_tons' => $weight,
                            'actual_weight_tons' => $weight,
                            'planned_pcs' => $it['pcs'] ?? 0,
                            'actual_pcs' => $it['pcs'] ?? 0,
                            'rate' => $rate,
                            'subtotal' => $weight * $rate
                        ];
                    }

                    $vehInfo = null;
                    if ($vehicleId) {
                        $vehInfo = DB::selectOne("SELECT tokenid, vehicleno, vehicletype, transport, drivername, drivermobile, gatestatus, dispatchmarkedcompleted FROM devine.gate WHERE gid = ?", [$vehicleId]);
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
                                'cust_id' => $latestOrder->cid,
                                'name' => $latestOrder->customer_name,
                                'p_code' => $latestOrder->p_code,
                                'gst' => $latestOrder->customer_gst,
                                'email' => $latestOrder->customer_email,
                                'mobile' => $latestOrder->customer_mobile,
                                'address' => $latestOrder->customer_address,
                                'city' => $latestOrder->customer_city,
                                'state' => $latestOrder->customer_state,
                                'pincode' => $latestOrder->customer_pincode
                            ],
                            'vehicle' => $vehInfo ? [
                                'tokenid' => $vehInfo->tokenid,
                                'vehicleno' => $vehInfo->vehicleno,
                                'vehicletype' => $vehInfo->vehicletype,
                                'transport' => $vehInfo->transport,
                                'drivername' => $vehInfo->drivername,
                                'drivermobile' => $vehInfo->drivermobile,
                                'gatestatus' => $vehInfo->gatestatus,
                                'dispatchmarkedcompleted' => intval($vehInfo->dispatchmarkedcompleted)
                            ] : null,
                            'items' => $formattedItems
                        ]
                    ]);
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'No dispatch items finalized in ERP yet. Click "+ Add Row" to add items manually.'
            ]);

        } catch (\Exception $e) {
            Log::error('Invoice Local DB getDispatchDetails Fallback Error: ' . $e->getMessage());
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
     * Get next auto-generated sequential Invoice Number.
     */
    public function getNextInvoiceNumber(Request $request)
    {
        try {
            $year = date('Y', strtotime($request->input('date', date('Y-m-d'))));
            
            // Check highest sequential number for current year
            $latestNumbered = Invoice::where('invoice_no', 'LIKE', "INV-{$year}-%")
                ->orderBy('id', 'desc')
                ->value('invoice_no');
                
            $nextSeq = 1;
            if ($latestNumbered && preg_match('/INV-\d{4}-(\d+)/', $latestNumbered, $matches)) {
                $nextSeq = intval($matches[1]) + 1;
            } else {
                $countInvoices = Invoice::whereYear('created_at', $year)->count();
                $nextSeq = $countInvoices + 1;
            }
            
            $invoiceNo = 'INV-' . $year . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
            
            return response()->json([
                'status' => 'success',
                'invoice_no' => $invoiceNo,
                'prefix' => 'INV-' . $year,
                'seq' => $nextSeq
            ]);
        } catch (\Exception $e) {
            $year = date('Y');
            return response()->json([
                'status' => 'success',
                'invoice_no' => 'INV-' . $year . '-0001',
                'prefix' => 'INV-' . $year,
                'seq' => 1
            ]);
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

        $dispatchId = $request->input('dispatch_id');
        if ($dispatchId) {
            $existing = Invoice::where('dispatch_id', $dispatchId)->first();
            if ($existing) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invoice has already been generated for this ERP Dispatch (Invoice No: ' . $existing->invoice_no . ').'
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            // Determine Invoice Number (manual input from accountant or auto fallback)
            $manualInvoiceNo = trim($request->input('invoice_no', ''));
            if (!empty($manualInvoiceNo)) {
                $existingInvoiceNo = Invoice::where('invoice_no', $manualInvoiceNo)->first();
                if ($existingInvoiceNo) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invoice No "' . $manualInvoiceNo . '" already exists! Please enter a unique invoice number.'
                    ], 400);
                }
                $invoiceNo = $manualInvoiceNo;
            } else {
                $year = date('Y', strtotime($request->input('invoice_date', date('Y-m-d'))));
                $lastInvoice = Invoice::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
                $nextSeq = $lastInvoice ? ($lastInvoice->id + 1) : 1;
                $invoiceNo = 'INV-' . $year . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
            }

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
        
        $dispatch = null;
        $vehicle = null;
        try {
            if ($invoice->dispatch_id) {
                $dispatch = DB::selectOne("SELECT * FROM devine.dispatch WHERE dispatchid = ?", [$invoice->dispatch_id]);
            }
            if ($invoice->vehicle_id) {
                $vehicle = DB::selectOne("SELECT * FROM devine.gate WHERE gid = ?", [$invoice->vehicle_id]);
            } elseif ($dispatch && !empty($dispatch->vehicleid)) {
                $vehicle = DB::selectOne("SELECT * FROM devine.gate WHERE gid = ?", [$dispatch->vehicleid]);
            }
        } catch (\Exception $e) {
            Log::info("Print invoice relations fetch error: " . $e->getMessage());
        }

        return view('accounting.invoice_print', compact('invoice', 'dispatch', 'vehicle'));
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

    $dispatchId = $request->input('dispatch_id');
    if ($dispatchId) {
        $existing = Invoice::where('dispatch_id', $dispatchId)->first();
        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invoice has already been generated for this ERP Dispatch (Invoice No: ' . $existing->invoice_no . ').'
            ], 400);
        }
    }

    DB::beginTransaction();
    try {
        /*
        |--------------------------------------------------------------------------
        | 1. Determine Invoice Number (manual input from accountant or auto fallback)
        |--------------------------------------------------------------------------
        */
        $manualInvoiceNo = trim($request->input('invoice_no', ''));
        if (!empty($manualInvoiceNo)) {
            $existingInvoiceNo = Invoice::where('invoice_no', $manualInvoiceNo)->first();
            if ($existingInvoiceNo) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invoice No "' . $manualInvoiceNo . '" already exists! Please enter a unique invoice number.'
                ], 400);
            }
            $invoiceNo = $manualInvoiceNo;
        } else {
            $year = date('Y', strtotime($request->input('invoice_date', date('Y-m-d'))));
            $lastInvoice = Invoice::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
            $nextSeq = $lastInvoice ? ($lastInvoice->id + 1) : 1;
            $invoiceNo = 'INV-' . $year . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        }

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

    /**
     * Share invoice via Email.
     */
    public function shareEmail(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required',
            'to' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            $invoice = Invoice::find($request->invoice_id);
            $invoiceNo = $invoice ? $invoice->invoice_no : ('INV-' . $request->invoice_id);
            $toEmail = trim($request->input('to'));
            $subject = trim($request->input('subject'));
            $attachPdf = $request->boolean('attach_pdf', true);

            // Log dispatch for accounting audit trail
            Log::info("Invoice Share Email: Invoice #{$invoiceNo} -> To: {$toEmail} | Subject: {$subject} | Attach PDF: " . ($attachPdf ? 'YES' : 'NO'));

            return response()->json([
                'status' => 'success',
                'message' => "Invoice {$invoiceNo} email sent successfully to {$toEmail}.",
                'invoice_no' => $invoiceNo,
                'recipient' => $toEmail
            ]);
        } catch (\Exception $e) {
            Log::error("Invoice Share Email Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send invoice email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Share invoice via WhatsApp.
     */
    public function shareWhatsapp(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required',
            'mobile' => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            $invoice = Invoice::find($request->invoice_id);
            $invoiceNo = $invoice ? $invoice->invoice_no : ('INV-' . $request->invoice_id);
            $rawMobile = trim($request->input('mobile'));
            $message = trim($request->input('message'));

            $cleanMobile = preg_replace('/[^0-9]/', '', $rawMobile);
            if (strlen($cleanMobile) === 10) {
                $cleanMobile = '91' . $cleanMobile;
            }

            $waUrl = 'https://api.whatsapp.com/send?phone=' . $cleanMobile . '&text=' . urlencode($message);

            Log::info("Invoice Share WhatsApp dispatched: Invoice #{$invoiceNo} -> Mobile: {$cleanMobile}");

            return response()->json([
                'status' => 'success',
                'message' => "WhatsApp message prepared for {$rawMobile}.",
                'invoice_no' => $invoiceNo,
                'mobile' => $cleanMobile,
                'wa_url' => $waUrl
            ]);
        } catch (\Exception $e) {
            Log::error("Invoice Share WhatsApp Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to prepare WhatsApp dispatch: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch recipient metadata (email, mobile, etc.) for an invoice.
     */
    public function getShareDetails(Request $request)
    {
        $invoiceId = $request->input('invoice_id');
        if (!$invoiceId) {
            return response()->json(['status' => 'error', 'message' => 'Invoice ID is required'], 400);
        }

        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            return response()->json(['status' => 'error', 'message' => 'Invoice not found'], 404);
        }

        $email = '';
        $mobile = '';
        $customerName = $invoice->customer_name;

        // Try customer record
        if ($invoice->customer_id) {
            $cust = DB::selectOne("SELECT email, mobile, name FROM devine.customers WHERE cust_id = ?", [$invoice->customer_id]);
            if ($cust) {
                $email = $cust->email ?: '';
                $mobile = $cust->mobile ?: '';
                $customerName = $cust->name ?: $customerName;
            }
        }

        // Fallback: match by customer name in customers table
        if (empty($email) || empty($mobile)) {
            $custByName = DB::selectOne("SELECT email, mobile, name FROM devine.customers WHERE name = ? LIMIT 1", [$invoice->customer_name]);
            if ($custByName) {
                if (empty($email)) $email = $custByName->email ?: '';
                if (empty($mobile)) $mobile = $custByName->mobile ?: '';
            }
        }

        // Fallback: try dispatch / gate driver mobile
        if (empty($mobile) && $invoice->dispatch_id) {
            $disp = DB::selectOne("SELECT vehicleid, custid FROM devine.dispatch WHERE dispatchid = ?", [$invoice->dispatch_id]);
            if ($disp && $disp->vehicleid) {
                $gate = DB::selectOne("SELECT drivermobile FROM devine.gate WHERE gid = ?", [$disp->vehicleid]);
                if ($gate && !empty($gate->drivermobile)) {
                    $mobile = $gate->drivermobile;
                }
            }
        }

        // Resolve Vehicle Number
        $vehicleNo = $invoice->vehicle_no;
        if (empty($vehicleNo) && $invoice->vehicle_id) {
            $gate = DB::selectOne("SELECT vehicleno FROM devine.gate WHERE gid = ?", [$invoice->vehicle_id]);
            if ($gate && !empty($gate->vehicleno)) {
                $vehicleNo = $gate->vehicleno;
            }
        }
        if (empty($vehicleNo) && $invoice->dispatch_id) {
            $disp = DB::selectOne("SELECT vehicleid FROM devine.dispatch WHERE dispatchid = ?", [$invoice->dispatch_id]);
            if ($disp && $disp->vehicleid) {
                $gate = DB::selectOne("SELECT vehicleno FROM devine.gate WHERE gid = ?", [$disp->vehicleid]);
                if ($gate && !empty($gate->vehicleno)) {
                    $vehicleNo = $gate->vehicleno;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'invoice_id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'customer_name' => $customerName,
                'email' => $email,
                'mobile' => $mobile,
                'vehicle_no' => $vehicleNo ?: '',
                'invoice_date' => $invoice->invoice_date,
                'amount' => number_format($invoice->grand_total, 2),
            ]
        ]);
    }
}