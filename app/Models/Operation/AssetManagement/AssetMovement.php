<?php

namespace App\Models\Operation\AssetManagement;

use App\Models\Administration\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMovement extends Model
{
    use HasFactory;

    protected $table = 'opt_asset_movements';

    protected $fillable = [
        'asset_id',
        'action',
        'assigned_type',
        'assigned_to_id',
        'action_date',
        'reference_number',
        'condition',
        'notes',
        'user_id',
    ];

    /**
     * Relasi balik ke Asset
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Relasi ke Admin/User yang memproses transaksi
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
