<?php

namespace App\Models\Approvals;

use App\Models\Administration\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class WorkflowApproval extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'workflow_approval';

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'level',
        'role_id',
        'user_id',
        'status',
        'note',
        'action_date'
    ];

    public function approvable()
    {
        return $this->morphTo();
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('APPROVAL WORKFLOW');
    }
}
