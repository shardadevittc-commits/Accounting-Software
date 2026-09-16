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
        'delivery_type',
        'taxable_amount',
        'discount_percent',
        'discount_amount',
        'cgst_rate',
        'cgst_amount',
        'sgst_rate',
        'sgst_amount',
        'igst_rate',
        'igst_amount',
        'freight_charges',
        'freight_amount',
        'insurance_amount',
        'labour_charges_per_ton',
        'labour_total_amount',
        'other_charges',
        'tcs_amount',
        'tds_amount',
        'grand_total',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date:Y-m-d',
        'taxable_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'freight_charges' => 'decimal:2',
        'freight_amount' => 'decimal:2',
        'insurance_amount' => 'decimal:2',
        'labour_charges_per_ton' => 'decimal:2',
        'labour_total_amount' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'tcs_amount' => 'decimal:2',
        'tds_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
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
