<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_GENERAL = 'general';
    const TYPE_CASH = 'cash';
    const TYPE_BANK = 'bank';

    const TYPES = [
        self::TYPE_GENERAL,
        self::TYPE_CASH,
        self::TYPE_BANK,
    ];

    protected $fillable = [
        'voucher_no',
        'voucher_type',
        'voucher_date',
        'party_id',
        'party_name',
        'invoice_id',
        'reference_no',
        'description',
        'narration',
        'transaction_mode',
        'payment_method',
        'instrument_no',
        'instrument_date',
        'bank_account_id',
        'status',
        'total_debit',
        'total_credit',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'voucher_date' => 'date:Y-m-d',
        'instrument_date' => 'date:Y-m-d',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    /**
     * Ledger entries for this voucher.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(VoucherEntry::class, 'voucher_id');
    }

    /**
     * Linked Sales invoice if applicable.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Linked Bank Ledger account if bank voucher.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'bank_account_id');
    }

    /**
     * User who created the voucher.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User who last updated the voucher.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if total debit equals total credit.
     */
    public function getIsBalancedAttribute(): bool
    {
        return abs((float)$this->total_debit - (float)$this->total_credit) < 0.01;
    }

    /**
     * Human-friendly voucher type title.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->voucher_type) {
            self::TYPE_GENERAL => 'General Voucher',
            self::TYPE_CASH => 'Cash Voucher',
            self::TYPE_BANK => 'Bank Voucher',
            default => ucfirst($this->voucher_type),
        };
    }

    /**
     * Badge class for voucher type.
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->voucher_type) {
            self::TYPE_GENERAL => 'badge-general',
            self::TYPE_CASH => 'badge-cash',
            self::TYPE_BANK => 'badge-bank',
            default => 'badge-secondary',
        };
    }

    /**
     * Scope by voucher type.
     */
    public function scopeByType($query, $type)
    {
        if (!empty($type) && in_array($type, self::TYPES)) {
            return $query->where('voucher_type', $type);
        }
        return $query;
    }

    /**
     * Scope date range.
     */
    public function scopeDateBetween($query, $from, $to)
    {
        if (!empty($from)) {
            $query->whereDate('voucher_date', '>=', $from);
        }
        if (!empty($to)) {
            $query->whereDate('voucher_date', '<=', $to);
        }
        return $query;
    }

    /**
     * Scope search keyword across voucher no, party name, reference, narration.
     */
    public function scopeSearch($query, $term)
    {
        if (!empty($term)) {
            $query->where(function ($q) use ($term) {
                $q->where('voucher_no', 'like', "%{$term}%")
                  ->orWhere('party_name', 'like', "%{$term}%")
                  ->orWhere('reference_no', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('narration', 'like', "%{$term}%");
            });
        }
        return $query;
    }
}
