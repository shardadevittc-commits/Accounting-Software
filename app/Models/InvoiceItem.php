<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'disp_item_id',
        'slid',
        'product_name',
        'size_name',
        'grade_name',
        'pcs',
        'weight_tons',
        'rate',
        'amount',
    ];

    /**
     * Get the parent invoice.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
