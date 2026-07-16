<?php

namespace App\Models\Administration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;
    protected $table = 'app_settings';
    protected $fillable = ['key', 'value'];

    protected $guarded = [];

    public $timestamps = false;

    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        return $setting?->value ?? $default;
    }
}
