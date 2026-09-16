<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Ledger;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VoucherService
{
    protected VoucherNumberService $numberService;
    protected LedgerService $ledgerService;

    public function __construct(VoucherNumberService $numberService, LedgerService $ledgerService)
    {
        $this->numberService = $numberService;
        $this->ledgerService = $ledgerService;
    }

    /**
     * Validate double-entry accounting integrity rules.
     *
     * @throws ValidationException
     */
    public function validateAccountingRules(array $entries): array
    {
        if (empty($entries) || count($entries) < 2) {
            throw ValidationException::withMessages([
                'entries' => 'An accounting voucher must contain at least 2 entries (at least one debit and one credit).'
            ]);
        }

        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $cleanEntries = [];

        foreach ($entries as $index => $row) {
            $ledgerId = (int)($row['ledger_id'] ?? 0);
            if (!$ledgerId || !Ledger::where('id', $ledgerId)->exists()) {
                throw ValidationException::withMessages([
                    "entries.{$index}.ledger_id" => "Row " . ($index + 1) . ": Invalid or missing ledger account."
                ]);
            }

            $debit = round((float)($row['debit'] ?? 0), 2);
            $credit = round((float)($row['credit'] ?? 0), 2);

            if ($debit < 0 || $credit < 0) {
                throw ValidationException::withMessages([
                    "entries.{$index}" => "Row " . ($index + 1) . ": Debit and Credit amounts cannot be negative."
                ]);
            }

            if ($debit > 0 && $credit > 0) {
                throw ValidationException::withMessages([
                    "entries.{$index}" => "Row " . ($index + 1) . ": Debit and Credit cannot both be greater than zero on the same line."
                ]);
            }

            if ($debit <= 0 && $credit <= 0) {
                throw ValidationException::withMessages([
                    "entries.{$index}" => "Row " . ($index + 1) . ": Amount must be greater than zero (enter either Debit or Credit)."
                ]);
            }

            $totalDebit += $debit;
            $totalCredit += $credit;

            $cleanEntries[] = [
                'ledger_id' => $ledgerId,
                'debit' => $debit,
                'credit' => $credit,
                'description' => $row['description'] ?? null,
            ];
        }

        $totalDebit = round($totalDebit, 2);
        $totalCredit = round($totalCredit, 2);

        if (abs($totalDebit - $totalCredit) >= 0.01) {
            $diff = abs($totalDebit - $totalCredit);
            throw ValidationException::withMessages([
                'balance' => sprintf(
                    'Voucher is unbalanced! Total Debit (₹%s) must equal Total Credit (₹%s). Difference: ₹%s',
                    number_format($totalDebit, 2),
                    number_format($totalCredit, 2),
                    number_format($diff, 2)
                )
            ]);
        }

        return [
            'entries' => $cleanEntries,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
        ];
    }

    /**
     * Create a new voucher inside a database transaction.
     */
    public function createVoucher(array $data, array $rawEntries, ?int $userId = null): Voucher
    {
        return DB::transaction(function () use ($data, $rawEntries, $userId) {
            $voucherType = $data['voucher_type'];

            // Auto-resolve invoice_id from reference_no if not explicitly set
            if (empty($data['invoice_id']) && !empty($data['reference_no'])) {
                $linkedInv = Invoice::where('invoice_no', trim($data['reference_no']))->first();
                if ($linkedInv) {
                    $data['invoice_id'] = $linkedInv->id;
                }
            }


            // Validate double entry rules
            $validated = $this->validateAccountingRules($rawEntries);
            $cleanEntries = $validated['entries'];
            $totalDebit = $validated['total_debit'];
            $totalCredit = $validated['total_credit'];

            // Atomically allocate next unique voucher number
            $voucherNo = $this->numberService->generateNextNumber($voucherType);

            // Create Voucher Header
            $voucher = Voucher::create([
                'voucher_no' => $voucherNo,
                'voucher_type' => $voucherType,
                'voucher_date' => $data['voucher_date'],
                'party_id' => $data['party_id'] ?? null,
                'party_name' => $data['party_name'] ?? null,
                'invoice_id' => $data['invoice_id'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'description' => $data['description'] ?? null,
                'narration' => $data['narration'] ?? null,
                'transaction_mode' => $data['transaction_mode'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'instrument_no' => $data['instrument_no'] ?? null,
                'instrument_date' => $data['instrument_date'] ?? null,
                'bank_account_id' => $data['bank_account_id'] ?? null,
                'status' => $data['status'] ?? 'posted',
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // Create Voucher Entries
            $createdEntries = [];
            $touchedLedgerIds = [];
            foreach ($cleanEntries as $entry) {
                $createdEntries[] = $voucher->entries()->create($entry);
                $touchedLedgerIds[$entry['ledger_id']] = $entry['ledger_id'];
            }

            // Update Ledger Balances if posted
            if ($voucher->status === 'posted') {
                foreach ($touchedLedgerIds as $ledgerId) {
                    $ledger = Ledger::find($ledgerId);
                    if ($ledger) {
                        $ledger->recalculateBalance();
                    }
                }
            }

            return $voucher->fresh(['entries.ledger', 'invoice', 'bankAccount', 'creator']);
        });
    }

    /**
     * Update an existing voucher atomically with ledger reversal.
     */
    public function updateVoucher(Voucher $voucher, array $data, array $rawEntries, ?int $userId = null): Voucher
    {
        return DB::transaction(function () use ($voucher, $data, $rawEntries, $userId) {
            // Auto-resolve invoice_id from reference_no if not explicitly set
            if (empty($data['invoice_id']) && !empty($data['reference_no'])) {
                $linkedInv = Invoice::where('invoice_no', trim($data['reference_no']))->first();
                if ($linkedInv) {
                    $data['invoice_id'] = $linkedInv->id;
                }
            }


            // Validate double entry rules
            $validated = $this->validateAccountingRules($rawEntries);
            $cleanEntries = $validated['entries'];
            $totalDebit = $validated['total_debit'];
            $totalCredit = $validated['total_credit'];

            // Gather all ledger IDs before deleting old entries
            $touchedLedgerIds = $voucher->entries->pluck('ledger_id')->unique()->toArray();

            // 1. Delete old entries
            $voucher->entries()->delete();

            // 2. Update Voucher Header
            $voucher->update([
                'voucher_date' => $data['voucher_date'],
                'party_id' => $data['party_id'] ?? $voucher->party_id,
                'party_name' => $data['party_name'] ?? $voucher->party_name,
                'invoice_id' => $data['invoice_id'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'description' => $data['description'] ?? null,
                'narration' => $data['narration'] ?? null,
                'transaction_mode' => $data['transaction_mode'] ?? $voucher->transaction_mode,
                'payment_method' => $data['payment_method'] ?? $voucher->payment_method,
                'instrument_no' => $data['instrument_no'] ?? null,
                'instrument_date' => $data['instrument_date'] ?? null,
                'bank_account_id' => $data['bank_account_id'] ?? null,
                'status' => $data['status'] ?? 'posted',
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'updated_by' => $userId,
            ]);

            // 3. Create new entries
            foreach ($cleanEntries as $entry) {
                $voucher->entries()->create($entry);
                $touchedLedgerIds[] = $entry['ledger_id'];
            }

            // 4. Recalculate all affected ledgers (both old and new)
            $uniqueIds = array_unique($touchedLedgerIds);
            foreach ($uniqueIds as $ledgerId) {
                $ledger = Ledger::find($ledgerId);
                if ($ledger) {
                    $ledger->recalculateBalance();
                }
            }

            return $voucher->fresh(['entries.ledger', 'invoice', 'bankAccount', 'creator', 'updater']);
        });
    }

    /**
     * Delete a voucher atomically with ledger reversal.
     */
    public function deleteVoucher(Voucher $voucher): void
    {
        DB::transaction(function () use ($voucher) {
            $ledgerIds = $voucher->entries->pluck('ledger_id')->unique()->toArray();

            // Soft-delete the voucher
            $voucher->delete();

            // Recalculate balances for all affected ledgers excluding deleted voucher
            foreach ($ledgerIds as $id) {
                $ledger = Ledger::find($id);
                if ($ledger) {
                    $ledger->recalculateBalance();
                }
            }
        });
    }


    /**
     * Helper to build Cash Voucher entries.
     * Receipt: Cash Dr, Party/Income Cr
     * Payment: Party/Expense Dr, Cash Cr
     */
    public function buildCashVoucherEntries(
        int $cashLedgerId,
        int $counterLedgerId,
        float $amount,
        string $direction = 'receipt',
        ?string $description = null
    ): array {
        $amount = round($amount, 2);
        $entries = [];

        if ($direction === 'receipt') {
            // Cash Dr
            $entries[] = [
                'ledger_id' => $cashLedgerId,
                'debit' => $amount,
                'credit' => 0.00,
                'description' => $description ?: 'Cash Received',
            ];
            // Counterparty Cr
            $entries[] = [
                'ledger_id' => $counterLedgerId,
                'debit' => 0.00,
                'credit' => $amount,
                'description' => $description ?: 'Cash Receipt credit',
            ];
        } else {
            // Counterparty Dr
            $entries[] = [
                'ledger_id' => $counterLedgerId,
                'debit' => $amount,
                'credit' => 0.00,
                'description' => $description ?: 'Cash Payment debit',
            ];
            // Cash Cr
            $entries[] = [
                'ledger_id' => $cashLedgerId,
                'debit' => 0.00,
                'credit' => $amount,
                'description' => $description ?: 'Cash Paid out',
            ];
        }

        return $entries;
    }

    /**
     * Helper to build Bank Voucher entries.
     * Receipt: Bank Dr, Party/Income Cr
     * Payment: Party/Expense Dr, Bank Cr
     */
    public function buildBankVoucherEntries(
        int $bankLedgerId,
        int $counterLedgerId,
        float $amount,
        string $direction = 'receipt',
        ?string $description = null
    ): array {
        $amount = round($amount, 2);
        $entries = [];

        if ($direction === 'receipt') {
            // Bank Dr
            $entries[] = [
                'ledger_id' => $bankLedgerId,
                'debit' => $amount,
                'credit' => 0.00,
                'description' => $description ?: 'Bank Receipt deposit',
            ];
            // Counterparty Cr
            $entries[] = [
                'ledger_id' => $counterLedgerId,
                'debit' => 0.00,
                'credit' => $amount,
                'description' => $description ?: 'Bank Receipt credit',
            ];
        } else {
            // Counterparty Dr
            $entries[] = [
                'ledger_id' => $counterLedgerId,
                'debit' => $amount,
                'credit' => 0.00,
                'description' => $description ?: 'Bank Payment debit',
            ];
            // Bank Cr
            $entries[] = [
                'ledger_id' => $bankLedgerId,
                'debit' => 0.00,
                'credit' => $amount,
                'description' => $description ?: 'Bank Payment credit',
            ];
        }

        return $entries;
    }
}
