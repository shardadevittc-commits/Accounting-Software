<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_id',
        'ledger_id',
        'debit',
        'credit',
        'description',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    /**
     * Parent voucher.
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    /**
     * Ledger account.
     */
    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }
}
