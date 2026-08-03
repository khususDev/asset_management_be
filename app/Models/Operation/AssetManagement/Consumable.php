<?php

namespace App\Models\Operation\AssetManagement;

use App\Models\Administration\Asset\Category;
use App\Models\Administration\Organization\Location;
use App\Models\Administration\Procurement\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consumable extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'opt_consumables';

    protected $fillable = [
        'goods_receipt_item_id',
        'purchase_order_item_id',
        'item_code',
        'item_name',
        'category_id',
        'vendor_id',
        'unit_of_measure',
        'total_quantity',
        'available_quantity',
        'min_stock_alert',
        'unit_price',
        'location_id',
        'registration_status',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'total_quantity' => 'integer',
        'available_quantity' => 'integer',
        'min_stock_alert' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
