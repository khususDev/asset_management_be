<?php

namespace App\Models\Operation\Procurement;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderLog extends Model
{
    protected $table = 'opt_purchase_order_log';

    protected $fillable = [
        'purchase_order_id',
        'status',
        'remarks',
        'created_by',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(
            PurchaseOrder::class,
            'purchase_order_id'
        );
    }
}
