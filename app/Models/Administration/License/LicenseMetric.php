<?php

namespace App\Models\Administration\License;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LicenseMetric extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    protected $table = 'mst_license_metric';

    protected $fillable = ['code', 'name', 'description', 'is_active'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('MASTER LICENSE METRIC');
    }
}
