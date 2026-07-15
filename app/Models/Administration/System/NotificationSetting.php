<?php

namespace App\Models\Administration\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NotificationSetting extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'sys_notification_setting';

    protected $fillable = [
        'module',
        'event',
        'recipient_role',
        'type',
        'is_active',
        'delete_at'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('System Notification Setting');
    }
}
