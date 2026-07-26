<?php

namespace App\Models\Operation\AssetManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Operation\Procurement\GoodsReceiptItem;
use App\Models\Operation\Procurement\PurchaseOrderItem;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'opt_assets';

    protected $fillable = [

        'goods_receipt_item_id',
        'purchase_order_item_id',

        'asset_code',
        'asset_name',
        'serial_number',
        'qr_code',
        'barcode',

        'asset_category_id',
        'asset_type_id',
        'brand_id',
        'model_id',
        'status_id',

        'vendor_id',
        'department_id',
        'location_id',

        'purchase_date',
        'purchase_cost',

        'warranty_start',
        'warranty_end',

        'useful_life',
        'salvage_value',

        'registration_status',

        'remarks',

        'created_by',
    ];

    protected $casts = [

        'purchase_date' => 'date',

        'warranty_start' => 'date',

        'warranty_end' => 'date',
    ];

    public function goodsReceiptItem()
    {
        return $this->belongsTo(
            GoodsReceiptItem::class,
            'goods_receipt_item_id'
        );
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(
            PurchaseOrderItem::class,
            'purchase_order_item_id'
        );
    }
}
