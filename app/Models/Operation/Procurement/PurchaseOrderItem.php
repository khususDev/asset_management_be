<?php

namespace App\Models\Operation\Procurement;

use App\Models\Administration\Organization\Branch;
use App\Models\Administration\Procurement\Uom;
use App\Models\Operation\AssetOperation\PurchaseRequestItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends Model
{
    use SoftDeletes;

    protected $table = 'opt_purchase_order_item';

    protected $fillable = [
        'purchase_order_id',
        'purchase_request_item_id',
        'item_description',
        'quantity',
        'uom_id',
        'unit_price',
        'total_amount',
        'is_pkp',
        'price_include_ppn',
        'item_purpose',
        'expected_arrival_date',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function purchaseRequestItem()
    {
        return $this->belongsTo(PurchaseRequestItem::class);
    }

    public function uom()
    {
        return $this->belongsTo(Uom::class);
    }

    public function deliveryBranch()
    {
        return $this->belongsTo(
            Branch::class,
            'delivery_id'
        );
    }
}
