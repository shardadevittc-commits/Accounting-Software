<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'invoice_date',
        'vehicle_id',
        'dispatch_id',
        'customer_id',
        'customer_name',
        'customer_gst',
        'customer_address',
        'vehicle_no',
        'transport_name',
        'taxable_amount',
        'cgst_rate',
        'cgst_amount',
        'sgst_rate',
        'sgst_amount',
        'igst_rate',
        'igst_amount',
        'freight_charges',
        'other_charges',
        'tcs_amount',
        'grand_total',
        'remarks',
        'created_by',
    ];

    /**
     * Get the line items for the invoice.
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * User who generated this invoice.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
