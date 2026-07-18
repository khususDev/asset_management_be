<?php

namespace App\Models\Operation\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceiptItem extends Model
{
    use SoftDeletes;

    protected $table = 'opt_goods_receipt_item';

    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'item_description',
        'ordered_qty',
        'received_before_qty',
        'receive_qty',
        'accepted_qty',
        'rejected_qty',
        'return_qty',
        'replacement_qty',
        'remarks',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(
            GoodsReceipt::class
        );
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(
            PurchaseOrderItem::class
        );
    }

    public function getOutstandingQtyAttribute()
    {
        $accepted =
            GoodsReceiptItem::where(
                'purchase_order_item_id',
                $this->purchase_order_item_id
            )
            ->sum('accepted_qty');

        return
            $this->purchaseOrderItem->quantity -
            $accepted;
    }
}
