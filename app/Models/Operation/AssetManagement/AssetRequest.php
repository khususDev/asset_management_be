<?php

namespace App\Models\Operation\AssetManagement;

use App\Models\Administration\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AssetRequest extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'opt_asset_request';
    protected $fillable = ['request_number', 'user_id', 'asset_name', 'quantity', 'reason', 'needed_date', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('ASSET REQUEST');
    }
}
