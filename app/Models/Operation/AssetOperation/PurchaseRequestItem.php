<?php

namespace App\Models\Operation\AssetOperation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseRequestItem extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'opt_purchase_request_item';

    protected $fillable = [
        'purchase_request_id',
        'item_description',
        'quantity',
        'uom_id',
        'unit_price',
        'total_amount',
        'need_to_issue_po',
        'vendor_id',
        'is_pkp',
        'pic_contact',
        'url',
        'price_include_ppn',
        'payment_term_id',
        'expected_arrival_date',
        'delivery_id',
        'item_purpose'
    ];

    // === RELASI MASTER DATA (WAJIB ADA & SINKRON DENGAN CONTROLLER) ===

    // Fungsi ini yang tadi dilaporkan hilang/error oleh Laravel:
    public function uom()
    {
        return $this->belongsTo(\App\Models\Administration\Procurement\Uom::class, 'uom_id');
    }

    public function vendor()
    {
        return $this->belongsTo(\App\Models\Administration\Procurement\Vendor::class, 'vendor_id');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(\App\Models\Administration\Procurement\PaymentTerm::class, 'payment_term_id');
    }

    public function deliveryBranch()
    {
        return $this->belongsTo(\App\Models\Administration\Organization\Branch::class, 'delivery_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('PURCHASE REQUEST ITEM');
    }
}
