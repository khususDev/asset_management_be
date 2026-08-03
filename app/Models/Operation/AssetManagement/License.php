<?php

namespace App\Models\Operation\AssetManagement;

use App\Models\Administration\Asset\Category;
use App\Models\Administration\Procurement\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class License extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'opt_licenses';

    protected $fillable = [
        'goods_receipt_item_id',
        'purchase_order_item_id',
        'license_code',
        'software_name',
        'category_id',
        'vendor_id',
        'license_key',
        'license_type',
        'total_seats',
        'purchase_date',
        'purchase_cost',
        'expiration_date',
        'registration_status',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expiration_date' => 'date',
        'total_seats' => 'integer',
        'purchase_cost' => 'decimal:2',
    ];

    // Relasi Polymorphic ke Assignment (Seat allocation per user/device)
    public function assignments()
    {
        return $this->morphMany(Assignment::class, 'assignable');
    }

    public function activeAssignments()
    {
        return $this->morphMany(Assignment::class, 'assignable')
            ->whereNull('returned_date')
            ->where('status', 'ACTIVE');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
