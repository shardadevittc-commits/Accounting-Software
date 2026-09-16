<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Ledger extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'group_type',
        'category',
        'party_id',
        'party_type',
        'opening_balance',
        'opening_balance_type',
        'current_balance',
        'is_system',
        'status',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_system' => 'boolean',
    ];

    /**
     * Voucher entries for this ledger.
     */
    public function voucherEntries(): HasMany
    {
        return $this->hasMany(VoucherEntry::class, 'ledger_id');
    }

    /**
     * Scope for active ledgers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope by category.
     */
    public function scopeByCategory($query, $category)
    {
        if (is_array($category)) {
            return $query->whereIn('category', $category);
        }
        return $query->where('category', $category);
    }

    /**
     * Scope for bank accounts.
     */
    public function scopeBankAccounts($query)
    {
        return $query->where('category', 'bank')->where('status', 'active');
    }

    /**
     * Scope for cash accounts.
     */
    public function scopeCashAccounts($query)
    {
        return $query->where('category', 'cash')->where('status', 'active');
    }

    /**
     * Recalculate balance from all non-deleted vouchers entries.
     */
    public function recalculateBalance(): float
    {
        $totals = DB::table('voucher_entries')
            ->join('vouchers', 'voucher_entries.voucher_id', '=', 'vouchers.id')
            ->whereNull('vouchers.deleted_at')
            ->where('voucher_entries.ledger_id', $this->id)
            ->where('vouchers.status', 'posted')
            ->selectRaw('COALESCE(SUM(voucher_entries.debit), 0) as total_debit, COALESCE(SUM(voucher_entries.credit), 0) as total_credit')
            ->first();

        $totalDebit = (float)($totals->total_debit ?? 0);
        $totalCredit = (float)($totals->total_credit ?? 0);

        // Assets and Expenses normal balance is Debit (+Dr, -Cr)
        // Liabilities, Equity, Revenue normal balance is Credit (+Cr, -Dr)
        $isDebitNormal = in_array($this->group_type, ['assets', 'expenses']);
        
        $opening = (float)$this->opening_balance;
        if ($this->opening_balance_type === 'cr' && $isDebitNormal) {
            $opening = -$opening;
        } elseif ($this->opening_balance_type === 'dr' && !$isDebitNormal) {
            $opening = -$opening;
        }

        if ($isDebitNormal) {
            $balance = $opening + ($totalDebit - $totalCredit);
        } else {
            $balance = $opening + ($totalCredit - $totalDebit);
        }

        $this->current_balance = $balance;
        $this->save();

        return (float)$balance;
    }

    /**
     * Find or create a party ledger dynamically (e.g. for customer or supplier).
     */
    public static function firstOrCreatePartyLedger($partyId, $partyName, $partyType = 'customer'): self
    {
        $prefix = ($partyType === 'supplier') ? 'SUP-' : 'CUST-';
        $code = $prefix . str_pad($partyId, 4, '0', STR_PAD_LEFT);
        $groupType = ($partyType === 'supplier') ? 'liabilities' : 'assets';
        $normalBal = ($partyType === 'supplier') ? 'cr' : 'dr';

        $ledger = self::where('party_id', $partyId)
            ->where('party_type', $partyType)
            ->first();

        if (!$ledger) {
            $ledger = self::where('code', $code)->first();
        }

        if (!$ledger) {
            $ledger = self::create([
                'name' => $partyName ?: ($partyType === 'supplier' ? 'Supplier #' . $partyId : 'Customer #' . $partyId),
                'code' => $code,
                'group_type' => $groupType,
                'category' => $partyType,
                'party_id' => $partyId,
                'party_type' => $partyType,
                'opening_balance' => 0.00,
                'opening_balance_type' => $normalBal,
                'current_balance' => 0.00,
                'is_system' => false,
                'status' => 'active',
                'notes' => 'Auto-created party ledger',
            ]);
        } elseif ($partyName && $ledger->name !== $partyName) {
            $ledger->name = $partyName;
            $ledger->save();
        }

        return $ledger;
    }
}
