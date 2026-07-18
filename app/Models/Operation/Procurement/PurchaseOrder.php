<?php

namespace App\Models\Operation\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Administration\User;
use App\Models\Administration\Organization\Branch;
use App\Models\Administration\Organization\Department;
use App\Models\Administration\Procurement\Vendor;
use App\Models\Administration\Procurement\PaymentTerm;
use App\Models\Operation\Procurement\PurchaseRequest;

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

    /*
    |--------------------------------------------------------------------------
    | HEADER RELATION
    |--------------------------------------------------------------------------
    */

    public function purchaseRequest()
    {
        return $this->belongsTo(
            PurchaseRequest::class,
            'purchase_request_id'
        );
    }

    public function vendor()
    {
        return $this->belongsTo(
            Vendor::class,
            'vendor_id'
        );
    }

    public function department()
    {
        return $this->belongsTo(
            Department::class,
            'department_id'
        );
    }

    public function branch()
    {
        return $this->belongsTo(
            Branch::class,
            'branch_id'
        );
    }

    public function paymentTerm()
    {
        return $this->belongsTo(
            PaymentTerm::class,
            'payment_term_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | USER RELATION
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function approvedBy()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL RELATION
    |--------------------------------------------------------------------------
    */

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

    public function goodsReceipts()
    {
        return $this->hasMany(
            GoodsReceipt::class
        );
    }
}
