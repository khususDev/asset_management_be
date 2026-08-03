<?php

namespace App\Models\Operation\AssetManagement;

use App\Models\Administration\Asset\AssetModel;
use App\Models\Administration\Asset\Brand;
use App\Models\Administration\Asset\Category;
use App\Models\Administration\Asset\Status;
use App\Models\Administration\Asset\Type;
use App\Models\Administration\Organization\Branch;
use App\Models\Administration\Organization\Department;
use App\Models\Administration\Organization\Location;
use App\Models\Administration\Procurement\Vendor;
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
        'asset_class',
        'asset_code',
        'asset_name',
        'serial_number',
        'qr_code',
        'barcode',
        'branch_id',
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
        'usage_status',

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

    public function category()
    {
        return $this->belongsTo(Category::class, 'asset_category_id');
    }

    public function type()
    {
        return $this->belongsTo(Type::class, 'asset_type_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function model()
    {
        return $this->belongsTo(AssetModel::class, 'model_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    // Relasi ke Model Assignment
    public function assignments()
    {
        return $this->morphMany(Assignment::class, 'assignable');
    }

    // Relasi untuk mengambil Assignment yang SEDANG AKTIF (belum dikembalikan)
    public function currentAssignment()
    {
        return $this->morphOne(Assignment::class, 'assignable')
            ->whereNull('returned_date')
            ->where('status', 'ACTIVE')
            ->latestOfMany();
    }
}
