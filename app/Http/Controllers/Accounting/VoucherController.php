<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVoucherRequest;
use App\Http\Requests\UpdateVoucherRequest;
use App\Models\Invoice;
use App\Models\Ledger;
use App\Models\Voucher;
use App\Services\LedgerService;
use App\Services\VoucherNumberService;
use App\Services\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    protected VoucherService $voucherService;
    protected VoucherNumberService $numberService;
    protected LedgerService $ledgerService;

    public function __construct(
        VoucherService $voucherService,
        VoucherNumberService $numberService,
        LedgerService $ledgerService
    ) {
        $this->voucherService = $voucherService;
        $this->numberService = $numberService;
        $this->ledgerService = $ledgerService;
    }

    /**
     * Check user permission with fallback for administrators.
     */
    protected function checkPermission(string $permission): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return;
        }

        if (method_exists($user, 'hasPermission') && $user->hasPermission($permission)) {
            return;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole(['admin', 'administrator', 'super-admin', 'accountant'])) {
            return;
        }

        abort(403, "Unauthorized: Missing '{$permission}' permission.");
    }

    /**
     * Display the Accounting Vouchers Dashboard.
     */
    public function index()
    {
        $this->checkPermission('voucher.view');

        $activeTab = request('tab', 'all');


        // Fetch pre-grouped ledgers for fast UI drawer population
        $bankAccounts = Ledger::bankAccounts()->get(['id', 'name', 'code', 'current_balance']);
        if ($bankAccounts->isEmpty()) {
            $defaultBank = Ledger::firstOrCreate(
                ['code' => 'BANK-01'],
                [
                    'name' => 'Primary Bank Account',
                    'category' => 'bank',
                    'group_type' => 'assets',
                    'status' => 'active',
                    'is_system' => true,
                    'opening_balance' => 0,
                    'current_balance' => 0,
                ]
            );
            $bankAccounts = collect([$defaultBank]);
        }

        $cashAccounts = Ledger::cashAccounts()->get(['id', 'name', 'code', 'current_balance']);
        if ($cashAccounts->isEmpty()) {
            $defaultCash = Ledger::firstOrCreate(
                ['code' => 'CASH-01'],
                [
                    'name' => 'Cash in Hand',
                    'category' => 'cash',
                    'group_type' => 'assets',
                    'status' => 'active',
                    'is_system' => true,
                    'opening_balance' => 0,
                    'current_balance' => 0,
                ]
            );
            $cashAccounts = collect([$defaultCash]);
        }

        $taxAccounts = Ledger::byCategory('tax')->active()->get(['id', 'name', 'code']);
        // Fetch only parties whose invoices have been generated in invoices table
        $invoicedCustomers = Invoice::select('customer_id', 'customer_name', 'customer_gst')
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->distinct()
            ->orderBy('customer_name', 'asc')
            ->get();

        $allParties = collect();
        foreach ($invoicedCustomers as $inv) {
            $existingLedger = Ledger::where(function ($q) use ($inv) {
                if ($inv->customer_id) {
                    $q->where('party_id', $inv->customer_id);
                }
                $q->orWhere('name', $inv->customer_name);
            })->where('category', 'customer')->first();

            $allParties->push((object)[
                'id' => $existingLedger ? $existingLedger->id : $inv->customer_id,
                'party_id' => $inv->customer_id,
                'name' => $inv->customer_name,
                'gst' => $inv->customer_gst,
                'category' => 'customer',
            ]);
        }
        $allParties = $allParties->unique('name')->values();
        $otherLedgers = Ledger::whereNotIn('category', ['customer', 'supplier', 'cash', 'bank'])
            ->active()
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'code', 'category', 'group_type']);
        $allLedgers = Ledger::active()->orderBy('name', 'asc')->get(['id', 'name', 'code', 'category', 'group_type', 'party_id']);

        $discountAccount = Ledger::firstOrCreate(
            ['code' => 'DISC-01'],
            [
                'name' => 'Discount / Rebate A/c',
                'category' => 'expense',
                'group_type' => 'expenses',
                'status' => 'active',
                'is_system' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]
        );

        // Summary metrics for KPI row
        $kpi = [
            'total_count' => Voucher::count(),
            'total_debit' => (float)Voucher::where('status', 'posted')->sum('total_debit'),
            'general_count' => Voucher::where('voucher_type', Voucher::TYPE_GENERAL)->count(),
            'cash_count' => Voucher::where('voucher_type', Voucher::TYPE_CASH)->count(),
            'bank_count' => Voucher::where('voucher_type', Voucher::TYPE_BANK)->count(),
        ];

        return view('accounting.vouchers.index', compact(
            'activeTab',
            'bankAccounts',
            'cashAccounts',
            'taxAccounts',
            'discountAccount',
            'allParties',
            'otherLedgers',
            'allLedgers',
            'kpi'
        ));
    }

    /**
     * Fetch paginated and filtered voucher list via AJAX.
     */
    public function data(Request $request): JsonResponse
    {
        $this->checkPermission('voucher.view');

        $query = Voucher::with(['entries.ledger', 'creator'])
            ->latest('voucher_date')
            ->latest('id');

        // Tab filter
        if ($request->filled('type') && $request->type !== 'all') {
            $query->byType($request->type);
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('voucher_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('voucher_date', '<=', $request->date_to);
        }

        // Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Party filter
        if ($request->filled('party_id')) {
            $query->where('party_id', $request->party_id);
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Calculate filtered summary stats
        $statsQuery = clone $query;
        $totalDebit = (float)$statsQuery->sum('total_debit');
        $totalCount = $statsQuery->count();

        $perPage = (int)$request->input('per_page', 25);
        $vouchers = $query->paginate($perPage);

        // Format items for fast front-end rendering
        $vouchers->getCollection()->transform(function ($v, $index) use ($vouchers) {
            $sNo = ($vouchers->currentPage() - 1) * $vouchers->perPage() + $index + 1;

            // Determine party / main counter account name
            $counterAccount = $v->party_name;
            if (!$counterAccount && $v->entries->isNotEmpty()) {
                // Find primary debit or credit entry
                $firstOpposite = $v->entries->firstWhere('ledger.category', '!=', 'bank');
                $counterAccount = $firstOpposite?->ledger?->name ?? $v->entries->first()->ledger?->name;
            }

            // Determine DR / CR badge for party / non-cash side
            $drCr = null;
            $partyEntry = null;
            if ($v->party_id) {
                $partyEntry = $v->entries->firstWhere('ledger_id', $v->party_id);
            }
            if (!$partyEntry && $v->entries->isNotEmpty()) {
                $partyEntry = $v->entries->first(function ($e) {
                    $cat = $e->ledger?->category;
                    return !in_array($cat, ['cash', 'bank']);
                }) ?? $v->entries->first();
            }
            if ($partyEntry) {
                if ((float)$partyEntry->debit > 0) {
                    $drCr = 'Debit';
                } elseif ((float)$partyEntry->credit > 0) {
                    $drCr = 'Credit';
                }
            }
            if (!$drCr) {
                $drCr = ((float)$v->total_debit > 0) ? 'Debit' : 'Credit';
            }

            $amount = (float)($v->total_debit > 0 ? $v->total_debit : $v->total_credit);

            return [
                'id' => $v->id,
                's_no' => $sNo,
                'voucher_no' => $v->voucher_no,
                'voucher_type' => $v->voucher_type,
                'type_label' => $v->type_label,
                'type_badge_class' => $v->type_badge_class,
                'voucher_date' => $v->voucher_date ? $v->voucher_date->format('d M Y') : '-',
                'voucher_date_raw' => $v->voucher_date ? $v->voucher_date->format('Y-m-d') : '',
                'party_name' => $counterAccount ?: 'General Accounting',
                'party_id' => $v->party_id,
                'reference_no' => $v->reference_no ?: '-',
                'description' => $v->description ?: ($v->narration ?: '-'),
                'narration' => $v->narration ?: '',
                'amount' => $amount,
                'dr_cr' => $drCr,
                'total_debit' => (float)$v->total_debit,
                'total_credit' => (float)$v->total_credit,
                'status' => $v->status,
                'is_balanced' => $v->is_balanced,
                'created_by_name' => $v->creator?->name ?? 'System',
                'created_at' => $v->created_at ? $v->created_at->format('d M Y, h:i A') : '-',
                'updated_at' => $v->updated_at ? $v->updated_at->format('d M Y, h:i A') : '-',
                'entries_count' => $v->entries->count(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $vouchers,
            'summary' => [
                'total_records' => $totalCount,
                'total_debit' => $totalDebit,
            ],
        ]);
    }

    /**
     * Show single voucher details for View Drawer / Modal.
     */
    public function show($id): JsonResponse
    {
        $this->checkPermission('voucher.view');

        $voucher = Voucher::with(['entries.ledger', 'invoice', 'bankAccount', 'creator', 'updater'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $voucher->id,
                'voucher_no' => $voucher->voucher_no,
                'voucher_type' => $voucher->voucher_type,
                'type_label' => $voucher->type_label,
                'type_badge_class' => $voucher->type_badge_class,
                'voucher_date' => $voucher->voucher_date ? $voucher->voucher_date->format('d M Y') : '-',
                'party_id' => $voucher->party_id,
                'party_name' => $voucher->party_name ?: '-',
                'reference_no' => $voucher->reference_no ?: '-',
                'description' => $voucher->description ?: '-',
                'narration' => $voucher->narration ?: '-',
                'transaction_mode' => $voucher->transaction_mode,
                'payment_method' => $voucher->payment_method,
                'instrument_no' => $voucher->instrument_no,
                'instrument_date' => $voucher->instrument_date ? $voucher->instrument_date->format('d M Y') : null,
                'bank_account_name' => $voucher->bankAccount?->name,
                'invoice_no' => $voucher->invoice?->invoice_no,
                'status' => $voucher->status,
                'total_debit' => (float)$voucher->total_debit,
                'total_credit' => (float)$voucher->total_credit,
                'is_balanced' => $voucher->is_balanced,
                'created_by' => $voucher->creator?->name ?? 'System',
                'created_at' => $voucher->created_at ? $voucher->created_at->format('d M Y, h:i A') : '-',
                'updated_by' => $voucher->updater?->name ?? 'None',
                'updated_at' => $voucher->updated_at ? $voucher->updated_at->format('d M Y, h:i A') : '-',
                'entries' => $voucher->entries->map(function ($e) {
                    return [
                        'id' => $e->id,
                        'ledger_id' => $e->ledger_id,
                        'ledger_name' => $e->ledger?->name ?? 'Unknown Ledger',
                        'ledger_code' => $e->ledger?->code ?? '',
                        'ledger_category' => $e->ledger?->category ?? '',
                        'debit' => (float)$e->debit,
                        'credit' => (float)$e->credit,
                        'description' => $e->description ?: '-',
                    ];
                }),
            ]
        ]);
    }

    /**
     * Store a new voucher and its ledger entries atomically.
     */
    public function store(StoreVoucherRequest $request): JsonResponse
    {
        $this->checkPermission('voucher.create');

        try {
            $data = $request->validated();
            $entries = $request->input('entries', []);

            // Auto-resolve party ledger if needed
            if (!empty($data['party_id']) && !empty($data['party_name'])) {
                $type = $request->input('party_type', ($request->input('transaction_mode') === 'payment' ? 'supplier' : 'customer'));
                $partyLedger = $this->ledgerService->getOrCreatePartyLedger($data['party_id'], $data['party_name'], $type);
                foreach ($entries as &$entry) {
                    if (isset($entry['ledger_id']) && ($entry['ledger_id'] == $data['party_id'] || !\App\Models\Ledger::where('id', $entry['ledger_id'])->exists())) {
                        $entry['ledger_id'] = $partyLedger->id;
                    }
                }
                unset($entry);
                $data['party_id'] = $partyLedger->id;
            }

            // Auto-resolve invoice_id if not supplied but reference_no matches an invoice
            if (empty($data['invoice_id']) && !empty($data['reference_no'])) {
                $linkedInv = Invoice::where('invoice_no', trim($data['reference_no']))->first();
                if ($linkedInv) {
                    $data['invoice_id'] = $linkedInv->id;
                }
            }

            $voucher = $this->voucherService->createVoucher($data, $entries, Auth::id());

            return response()->json([
                'status' => 'success',
                'message' => "Voucher {$voucher->voucher_no} created and posted successfully.",
                'voucher_no' => $voucher->voucher_no,
                'voucher_id' => $voucher->id,
                'data' => $voucher,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Voucher Store Error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save voucher: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return voucher data pre-formatted for editing in drawer.
     */
    public function edit($id): JsonResponse
    {
        $this->checkPermission('voucher.edit');

        $voucher = Voucher::with(['entries.ledger', 'invoice', 'bankAccount'])->findOrFail($id);

        $salesData = null;
        $purchaseData = null;
        $cashData = null;
        $bankData = null;
        $generalData = null;

        if ($voucher->voucher_type === Voucher::TYPE_CASH) {
            $oppositeEntry = $voucher->entries->firstWhere('ledger.category', '!=', 'cash');
            $cashData = [
                'direction' => $voucher->transaction_mode ?: 'receipt',
                'amount' => (float)$voucher->total_debit,
                'counter_ledger_id' => $oppositeEntry?->ledger_id,
            ];
        } elseif ($voucher->voucher_type === Voucher::TYPE_BANK) {
            $oppositeEntry = $voucher->entries->firstWhere('ledger.category', '!=', 'bank');
            $bankData = [
                'direction' => $voucher->transaction_mode ?: 'receipt',
                'amount' => (float)$voucher->total_debit,
                'counter_ledger_id' => $oppositeEntry?->ledger_id,
            ];
        } elseif ($voucher->voucher_type === Voucher::TYPE_GENERAL) {
            $partyEntry = $voucher->entries->firstWhere('ledger.code', '!=', 'DISC-01');
            $discEntry = $voucher->entries->firstWhere('ledger.code', 'DISC-01');
            $generalData = [
                'direction' => 'payment',
                'amount' => (float)$voucher->total_debit,
                'discount' => (float)($discEntry ? ($discEntry->credit > 0 ? $discEntry->credit : $discEntry->debit) : $voucher->total_debit),
                'counter_ledger_id' => $partyEntry?->ledger_id ?: $voucher->party_id,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $voucher->id,
                'voucher_no' => $voucher->voucher_no,
                'voucher_type' => $voucher->voucher_type,
                'voucher_date' => $voucher->voucher_date ? $voucher->voucher_date->format('Y-m-d') : date('Y-m-d'),
                'party_id' => $voucher->party_id,
                'party_name' => $voucher->party_name,
                'invoice_id' => $voucher->invoice_id,
                'reference_no' => $voucher->reference_no,
                'description' => $voucher->description,
                'narration' => $voucher->narration,
                'transaction_mode' => $voucher->transaction_mode,
                'payment_method' => $voucher->payment_method,
                'instrument_no' => $voucher->instrument_no,
                'instrument_date' => $voucher->instrument_date ? $voucher->instrument_date->format('Y-m-d') : '',
                'bank_account_id' => $voucher->bank_account_id,
                'status' => $voucher->status,
                'total_debit' => (float)$voucher->total_debit,
                'total_credit' => (float)$voucher->total_credit,
                'cash_data' => $cashData,
                'bank_data' => $bankData,
                'general_data' => $generalData,
                'entries' => $voucher->entries->map(function ($e) {
                    return [
                        'ledger_id' => $e->ledger_id,
                        'ledger_name' => $e->ledger?->name ?? '',
                        'debit' => (float)$e->debit,
                        'credit' => (float)$e->credit,
                        'description' => $e->description ?? '',
                    ];
                }),
            ]
        ]);
    }

    /**
     * Update an existing voucher atomically with ledger reversal.
     */
    public function update(UpdateVoucherRequest $request, $id): JsonResponse
    {
        $this->checkPermission('voucher.edit');

        $voucher = Voucher::with('entries')->findOrFail($id);

        try {
            $data = $request->validated();
            $entries = $request->input('entries', []);

            $updated = $this->voucherService->updateVoucher($voucher, $data, $entries, Auth::id());

            return response()->json([
                'status' => 'success',
                'message' => "Voucher {$updated->voucher_no} updated successfully with ledger adjustments.",
                'data' => $updated,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Voucher Update Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update voucher: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a voucher atomically with ledger balance reversal.
     */
    public function destroy($id): JsonResponse
    {
        $this->checkPermission('voucher.delete');

        $voucher = Voucher::with('entries')->findOrFail($id);

        try {
            $voucherNo = $voucher->voucher_no;
            $this->voucherService->deleteVoucher($voucher);

            return response()->json([
                'status' => 'success',
                'message' => "Voucher {$voucherNo} deleted and its ledger impact reversed successfully.",
            ]);
        } catch (\Exception $e) {
            Log::error('Voucher Delete Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete voucher: ' . $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Render printable voucher format.
     */
    public function print($id)
    {
        $this->checkPermission('voucher.print');

        $voucher = Voucher::with(['entries.ledger', 'invoice', 'bankAccount', 'creator'])->findOrFail($id);

        return view('accounting.vouchers.print', compact('voucher'));
    }

    /**
     * Return the next prospective voucher number for the given type.
     */
    public function getNextVoucherNumber(Request $request): JsonResponse
    {
        $type = $request->input('type', Voucher::TYPE_GENERAL);
        if (!in_array($type, Voucher::TYPES)) {
            $type = Voucher::TYPE_GENERAL;
        }

        $nextNumber = $this->numberService->peekNextNumber($type);

        return response()->json([
            'status' => 'success',
            'voucher_type' => $type,
            'next_voucher_no' => $nextNumber,
        ]);
    }


    /**
     * Return list of parties (customers & suppliers) for search dropdowns.
     */
    public function getParties(Request $request): JsonResponse
    {
        $this->checkPermission('voucher.view');

        $type = $request->input('type'); // 'customer', 'supplier', or null for all
        $search = $request->input('search');

        try {
            $query = DB::table('devine.customers')
                ->where(function ($q) {
                    $q->where('status', 1)->orWhereNull('status');
                });

            if ($type === 'customer') {
                $query->where(function ($q) {
                    $q->where('typ', 'like', '%B%')->orWhereNull('typ');
                });
            } elseif ($type === 'supplier') {
                $query->where('typ', 'like', '%S%');
            }

            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            $parties = $query->limit(60)->get(['cust_id as id', 'name', 'gst', 'city', 'typ']);

            return response()->json([
                'status' => 'success',
                'data' => $parties,
            ]);
        } catch (\Exception $e) {
            // Fallback to ledgers table
            $query = Ledger::whereIn('category', ['customer', 'supplier'])->active();
            if ($type) {
                $query->where('category', $type);
            }
            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }
            $ledgers = $query->limit(60)->get(['id', 'name', 'code', 'category']);

            return response()->json([
                'status' => 'success',
                'data' => $ledgers,
            ]);
        }
    }

    /**
     * Resolve latest pending bill or invoice number for a given party.
     */
    public function getPartyBill(Request $request): JsonResponse
    {
        $this->checkPermission('voucher.view');

        $partyId = $request->input('party_id');
        $ledgerId = $request->input('ledger_id');
        $partyName = $request->input('party_name');

        // If party_id not passed but ledger_id passed, find ledger
        if (!$partyId && $ledgerId) {
            $ledger = Ledger::find($ledgerId);
            $partyId = $ledger?->party_id;
            if (!$partyName) {
                $partyName = $ledger?->name;
            }
        }

        if (!$partyId && !$partyName) {
            return response()->json([
                'status' => 'success',
                'bill_no' => null,
                'bills' => [],
            ]);
        }

        $bills = [];

        // 1. Check Invoices table with chronological FIFO & Advance adjustment
        try {
            $invoicesQuery = Invoice::orderBy('invoice_date', 'asc')->orderBy('id', 'asc');
            if ($partyId) {
                $invoicesQuery->where('customer_id', $partyId);
            } elseif ($partyName) {
                $invoicesQuery->where('customer_name', 'like', "%{$partyName}%");
            }
            $allInvoices = $invoicesQuery->get();

            // Calculate standalone advance pool (Receipt vouchers not linked to any invoice)
            $advanceQuery = Voucher::where('status', 'posted')
                ->whereIn('voucher_type', [Voucher::TYPE_CASH, Voucher::TYPE_BANK, Voucher::TYPE_GENERAL])
                ->where(function ($q) {
                    $q->where('transaction_mode', 'receipt')
                      ->orWhere('total_credit', '>', 0);
                })
                ->where(function ($q) use ($partyId, $partyName) {
                    if ($partyId) {
                        $q->where('party_id', $partyId);
                    } elseif ($partyName) {
                        $q->where('party_name', 'like', "%{$partyName}%");
                    }
                });
            $standaloneVouchers = $advanceQuery->get();

            $allInvoiceNos = $allInvoices->pluck('invoice_no')->map(fn($v) => strtoupper(trim($v)))->all();
            $allInvoiceIds = $allInvoices->pluck('id')->all();

            $advancePool = 0.0;
            foreach ($standaloneVouchers as $sv) {
                $isLinked = (!empty($sv->invoice_id) && in_array($sv->invoice_id, $allInvoiceIds)) ||
                            (!empty($sv->reference_no) && in_array(strtoupper(trim($sv->reference_no)), $allInvoiceNos));
                if (!$isLinked) {
                    $amt = (float)($sv->total_debit > 0 ? $sv->total_debit : $sv->total_credit);
                    $advancePool = round($advancePool + $amt, 2);
                }
            }
            $totalInitialAdvance = $advancePool;

            foreach ($allInvoices as $inv) {
                // Check payment vouchers (Cash/Bank/General) linked to this invoice
                $linkedVouchers = Voucher::where('status', 'posted')
                    ->where(function ($q) use ($inv) {
                        $q->where('invoice_id', $inv->id)
                          ->orWhere('reference_no', $inv->invoice_no);
                    })->get();

                $paymentVouchers = $linkedVouchers->filter(function ($lv) {
                    return in_array($lv->voucher_type, [Voucher::TYPE_CASH, Voucher::TYPE_BANK, Voucher::TYPE_GENERAL]);
                });

                $totalRecv = 0.0;
                $hasPaymentReceipts = $paymentVouchers->isNotEmpty();

                if ($hasPaymentReceipts) {
                    $totalRecv = (float)$paymentVouchers->sum(function ($lv) {
                        return (float)($lv->total_debit > 0 ? $lv->total_debit : $lv->total_credit);
                    });
                }

                $invTotal = (float)$inv->grand_total;
                $pendingBeforeAdvance = max(0.0, round($invTotal - $totalRecv, 2));

                $advanceAdjusted = 0.0;
                if ($pendingBeforeAdvance > 0 && $advancePool > 0) {
                    $advanceAdjusted = min($advancePool, $pendingBeforeAdvance);
                    $advancePool = round($advancePool - $advanceAdjusted, 2);
                }

                $pendingAmount = max(0.0, round($pendingBeforeAdvance - $advanceAdjusted, 2));
                $isSettled = ($pendingAmount <= 0.01 && $invTotal > 0 && ($hasPaymentReceipts || $advanceAdjusted > 0));

                $bills[] = [
                    'bill_no' => $inv->invoice_no,
                    'invoice_id' => $inv->id,
                    'source' => 'invoice',
                    'source_label' => 'Sales Invoice',
                    'date' => $inv->invoice_date,
                    'amount' => $invTotal,
                    'paid_amount' => $totalRecv,
                    'advance_adjusted' => $advanceAdjusted,
                    'pending_amount' => $pendingAmount,
                    'is_settled' => $isSettled,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Invoice lookup warning in getPartyBill: ' . $e->getMessage());
        }

        $partyType = 'customer';
        if ($partyId) {
            $custRow = DB::table('devine.customers')->where('cust_id', $partyId)->first();
            if ($custRow && !empty($custRow->typ)) {
                $partyType = (str_contains($custRow->typ, 'S') && !str_contains($custRow->typ, 'B')) ? 'supplier' : 'customer';
            }
        } elseif ($ledgerId) {
            $ledger = Ledger::find($ledgerId);
            if ($ledger && $ledger->category === 'supplier') {
                $partyType = 'supplier';
            }
        }

        // 2. If Supplier, check Purchase Orders (devine.purchaseorder)
        if ($partyType === 'supplier') {
            try {
                $poQuery = DB::table('devine.purchaseorder')->orderBy('poid', 'desc');
                if ($partyId) {
                    $poQuery->where('cid', $partyId);
                }
                $pos = $poQuery->take(10)->get();
                foreach ($pos as $po) {
                    $bills[] = [
                        'bill_no' => 'PO-' . $po->poid,
                        'source' => 'purchase_order',
                        'source_label' => 'Purchase Order',
                        'date' => $po->createdon ? date('Y-m-d', strtotime($po->createdon)) : null,
                        'amount' => (float)($po->bRate ?? 0),
                        'is_settled' => false,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('PO lookup warning in getPartyBill: ' . $e->getMessage());
            }
        }

        // 3. If Customer, check Sale Orders (devine.saleorder) only if no invoices found
        if ($partyType === 'customer' && empty($bills)) {
            try {
                $soQuery = DB::table('devine.saleorder')->whereNotNull('cust_po')->where('cust_po', '!=', '')->orderBy('slid', 'desc');
                if ($partyId) {
                    $soQuery->where('cid', $partyId);
                }
                $sos = $soQuery->take(10)->get();
                foreach ($sos as $so) {
                    $bills[] = [
                        'bill_no' => $so->cust_po,
                        'source' => 'sale_order',
                        'source_label' => 'Customer Order / PO',
                        'date' => $so->createdon ? date('Y-m-d', strtotime($so->createdon)) : null,
                        'amount' => (float)($so->bprice ?? 0),
                        'is_settled' => false,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('SO lookup warning in getPartyBill: ' . $e->getMessage());
            }
        }

        // 4. Check past Vouchers reference_no only if no prior bills found
        if (empty($bills)) {
            try {
                $vQuery = Voucher::whereNotNull('reference_no')->where('reference_no', '!=', '')->latest('id');
                if ($partyId) {
                    $vQuery->where('party_id', $partyId);
                } elseif ($partyName) {
                    $vQuery->where('party_name', 'like', "%{$partyName}%");
                }
                $pastVouchers = $vQuery->take(5)->get();
                foreach ($pastVouchers as $pv) {
                    $bills[] = [
                        'bill_no' => $pv->reference_no,
                        'source' => 'voucher',
                        'source_label' => 'Past Voucher Ref',
                        'date' => $pv->voucher_date ? $pv->voucher_date->format('Y-m-d') : null,
                        'amount' => (float)$pv->total_debit,
                        'is_settled' => false,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('Voucher lookup warning in getPartyBill: ' . $e->getMessage());
            }
        }

        // Pick first UNSETTLED bill if exists (oldest unpaid invoice)
        $pendingBill = null;
        foreach ($bills as $b) {
            if (empty($b['is_settled'])) {
                $pendingBill = $b;
                break;
            }
        }

        $allSettled = !empty($bills) && $pendingBill === null;

        return response()->json([
            'status' => 'success',
            'bill_no' => $pendingBill['bill_no'] ?? null,
            'invoice_id' => $pendingBill['invoice_id'] ?? null,
            'source' => $pendingBill['source'] ?? null,
            'source_label' => $pendingBill['source_label'] ?? null,
            'amount' => $pendingBill['amount'] ?? 0,
            'paid_amount' => $pendingBill['paid_amount'] ?? 0,
            'advance_adjusted' => $pendingBill['advance_adjusted'] ?? 0,
            'pending_amount' => $pendingBill['pending_amount'] ?? 0,
            'available_advance' => $advancePool ?? 0,
            'initial_advance' => $totalInitialAdvance ?? 0,
            'all_settled' => $allSettled,
            'is_advance' => $allSettled || empty($bills),
            'bills' => $bills,
        ]);
    }

    /**
     * Get chart of accounts / ledgers for search dropdowns.
     */
    public function getLedgers(Request $request): JsonResponse
    {
        $this->checkPermission('voucher.view');

        $category = $request->input('category');
        $groupType = $request->input('group_type');
        $search = $request->input('search');

        $query = Ledger::active()->orderBy('name', 'asc');

        if ($category) {
            $categories = explode(',', $category);
            $query->whereIn('category', $categories);
        }

        if ($groupType) {
            $query->where('group_type', $groupType);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $ledgers = $query->get(['id', 'name', 'code', 'category', 'group_type', 'current_balance']);

        return response()->json([
            'status' => 'success',
            'data' => $ledgers,
        ]);
    }
}
