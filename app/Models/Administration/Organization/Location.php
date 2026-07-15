<?php

namespace App\Models\Administration\Organization;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Location extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'mst_org_location';

    protected $fillable = [
        'branch_id', // Wajib dimasukkan ke fillable
        'code',
        'name',
        'description',
        'is_active',
    ];

    // Relasi ke Branch
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('MASTER LOCATION');
    }
}
