<?php

namespace App\Models\Administration\Workflow;

use App\Models\Administration\Organization\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ApprovalSetting extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'wfl_approval_settings';

    protected $fillable = [
        'module',
        'department_id',
        'is_active',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('Workflow Approval Setting');
    }

    // Relasi ke Detail (Levels)
    public function levels()
    {
        return $this->hasMany(ApprovalSettingLevel::class, 'wfl_approval_setting_id')->orderBy('level', 'asc');
    }

    public function department()
    {
        // Hubungkan ke Model Departemen Anda (sesuaikan 'Department::class' dengan nama class Model Departemen Anda yang sebenarnya)
        return $this->belongsTo(Department::class, 'department_id');
    }
}
