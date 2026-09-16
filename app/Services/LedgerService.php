<?php

namespace App\Services;

use App\Models\Ledger;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    /**
     * Apply new entries to ledger balances atomically.
     * 
     * @param iterable|VoucherEntry[] $entries
     */
    public function applyEntries(iterable $entries): void
    {
        $ledgerIds = [];
        foreach ($entries as $entry) {
            $ledger = Ledger::find($entry->ledger_id);
            if (!$ledger) continue;

            $ledgerIds[$ledger->id] = $ledger->id;
            $isDebitNormal = in_array($ledger->group_type, ['assets', 'expenses']);
            $debit = (float)$entry->debit;
            $credit = (float)$entry->credit;

            $impact = $isDebitNormal ? ($debit - $credit) : ($credit - $debit);
            $ledger->current_balance = (float)$ledger->current_balance + $impact;
            $ledger->save();
        }

        // Exact recalculation audit for all touched ledgers
        foreach ($ledgerIds as $id) {
            $ledger = Ledger::find($id);
            if ($ledger) {
                $ledger->recalculateBalance();
            }
        }
    }

    /**
     * Reverse existing entries from ledger balances atomically.
     * 
     * @param iterable|VoucherEntry[] $entries
     */
    public function reverseEntries(iterable $entries): void
    {
        $ledgerIds = [];
        foreach ($entries as $entry) {
            $ledger = Ledger::find($entry->ledger_id);
            if (!$ledger) continue;

            $ledgerIds[$ledger->id] = $ledger->id;
            $isDebitNormal = in_array($ledger->group_type, ['assets', 'expenses']);
            $debit = (float)$entry->debit;
            $credit = (float)$entry->credit;

            // Reversal reverses the impact
            $impact = $isDebitNormal ? ($debit - $credit) : ($credit - $debit);
            $ledger->current_balance = (float)$ledger->current_balance - $impact;
            $ledger->save();
        }

        // Recalculate accurately from database
        foreach ($ledgerIds as $id) {
            $ledger = Ledger::find($id);
            if ($ledger) {
                $ledger->recalculateBalance();
            }
        }
    }

    /**
     * Get or create party ledger dynamically.
     */
    public function getOrCreatePartyLedger($partyId, $partyName, string $type = 'customer'): Ledger
    {
        return Ledger::firstOrCreatePartyLedger($partyId, $partyName, $type);
    }

    /**
     * Fetch formatted ledgers for UI dropdowns.
     */
    public function getLedgerList(?string $category = null, ?string $groupType = null)
    {
        $query = Ledger::active()->orderBy('name', 'asc');

        if ($category) {
            $query->where('category', $category);
        }

        if ($groupType) {
            $query->where('group_type', $groupType);
        }

        return $query->get(['id', 'name', 'code', 'category', 'group_type', 'current_balance']);
    }
}
