<?php

namespace App\Models\Administration\Asset;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Type extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'mst_asset_type';

    protected $fillable = ['asset_category_id', 'code', 'name', 'description', 'is_active'];

    // Relasi ke Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'asset_category_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('MASTER ASSET TYPE');
    }
}
