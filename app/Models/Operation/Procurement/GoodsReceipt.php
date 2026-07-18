<?php

namespace App\Models\Operation\Procurement;

use App\Models\Administration\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceipt extends Model
{
    use SoftDeletes;

    protected $table = 'opt_goods_receipt';

    protected $fillable = [
        'gr_number',
        'purchase_order_id',
        'received_date',
        'remarks',
        'status',
        'created_by',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(
            PurchaseOrder::class
        );
    }

    public function items()
    {
        return $this->hasMany(
            GoodsReceiptItem::class
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}
