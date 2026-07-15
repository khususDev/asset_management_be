<?php

namespace App\Models\Administration\Asset;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AssetModel extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'mst_asset_model';

    protected $fillable = ['brand_id', 'code', 'name', 'description', 'is_active'];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('MASTER ASSET MODEL');
    }
}
