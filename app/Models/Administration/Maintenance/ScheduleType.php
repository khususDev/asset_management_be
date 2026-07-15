<?php

namespace App\Models\Administration\Maintenance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;


class ScheduleType extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;
    protected $table = 'mst_maintenance_schedule';
    protected $fillable = ['code', 'name', 'description', 'is_active'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('MASTER MAINTENANCE SCHEDULE TYPE');
    }
}
