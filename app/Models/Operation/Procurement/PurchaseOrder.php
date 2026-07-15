<?php

namespace App\Models\Operation\Procurement;

use App\Models\Administration\Procurement\Vendor;
use App\Models\Operation\AssetOperation\PurchaseRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $table = 'opt_purchase_order';

    protected $fillable = [
        'po_number',
        'purchase_request_id',
        'vendor_id',
        'department_id',
        'branch_id',
        'payment_term_id',
        'order_date',
        'expected_delivery_date',
        'subtotal',
        'ppn_amount',
        'grand_total',
        'remarks',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    public function items()
    {
        return $this->hasMany(
            PurchaseOrderItem::class,
            'purchase_order_id'
        );
    }

    public function logs()
    {
        return $this->hasMany(
            PurchaseOrderLog::class,
            'purchase_order_id'
        );
    }

    public function vendor()
    {
        return $this->belongsTo(
            Vendor::class,
            'vendor_id'
        );
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(
            PurchaseRequest::class,
            'purchase_request_id'
        );
    }
}
