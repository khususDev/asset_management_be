<?php

namespace App\Models\Administration\Workflow;

use App\Models\Administration\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalSettingLevel extends Model
{
    use HasFactory;

    protected $table = 'wfl_approval_setting_levels';

    protected $fillable = [
        'wfl_approval_setting_id',
        'level',
        'user_id',
        'min_amount',
    ];

    public function header()
    {
        return $this->belongsTo(ApprovalSetting::class, 'wfl_approval_setting_id');
    }

    // Relasi ke User untuk mendapatkan nama orangnya
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
