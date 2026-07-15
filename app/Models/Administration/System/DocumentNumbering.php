<?php

namespace App\Models\Administration\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Administration\Organization\Department;

class DocumentNumbering extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;
    protected $table = 'sys_document_numbering';

    protected $fillable = [
        'module',
        'department',
        'name',
        'format',
        'prefix',
        'digit_length',
        'current_sequence',
        'reset_type',
        'is_active',
        'deleted_at'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('System Document Numbering');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department', 'id');
    }
}
