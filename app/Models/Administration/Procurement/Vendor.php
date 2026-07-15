<?php

namespace App\Models\Administration\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;
    protected $table = 'mst_procurement_vendor';
    protected $fillable = ['code', 'name', 'vendor_type_id', 'contact_person', 'phone', 'email', 'address', 'is_active'];

    public function vendorType()
    {
        return $this->belongsTo(VendorType::class, 'vendor_type_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('Master Procurement Vendor');
    }
}
