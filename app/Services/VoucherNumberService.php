<?php

namespace App\Services;

use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class VoucherNumberService
{
    /**
     * Voucher type prefixes.
     */
    protected static array $prefixes = [
        Voucher::TYPE_GENERAL => 'GV',
        Voucher::TYPE_CASH => 'CV',
        Voucher::TYPE_BANK => 'BV',
    ];

    /**
     * Get prefix for a voucher type.
     */
    public static function getPrefix(string $voucherType): string
    {
        return self::$prefixes[$voucherType] ?? 'VC';
    }

    /**
     * Peek next voucher number (non-locking preview for UI).
     */
    public function peekNextNumber(string $voucherType): string
    {
        $prefix = self::getPrefix($voucherType);
        
        $lastVoucher = Voucher::withTrashed()
            ->where('voucher_type', $voucherType)
            ->where('voucher_no', 'like', "{$prefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($lastVoucher) {
            $parts = explode('-', $lastVoucher->voucher_no);
            $lastNum = isset($parts[1]) ? (int)$parts[1] : 0;
            $nextSeq = $lastNum + 1;
        }

        return sprintf('%s-%06d', $prefix, $nextSeq);
    }

    /**
     * Atomically generate and reserve next sequential voucher number.
     * Must be called within DB::transaction.
     */
    public function generateNextNumber(string $voucherType): string
    {
        $prefix = self::getPrefix($voucherType);

        // Lock latest row with lockForUpdate to serialize concurrent requests
        $lastVoucher = Voucher::withTrashed()
            ->where('voucher_type', $voucherType)
            ->where('voucher_no', 'like', "{$prefix}-%")
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        $nextSeq = 1;
        if ($lastVoucher) {
            $parts = explode('-', $lastVoucher->voucher_no);
            $lastNum = isset($parts[1]) ? (int)$parts[1] : 0;
            $nextSeq = $lastNum + 1;
        }

        $candidate = sprintf('%s-%06d', $prefix, $nextSeq);

        // Double check existence in case of legacy or manual entries
        while (Voucher::withTrashed()->where('voucher_no', $candidate)->exists()) {
            $nextSeq++;
            $candidate = sprintf('%s-%06d', $prefix, $nextSeq);
        }

        return $candidate;
    }
}
