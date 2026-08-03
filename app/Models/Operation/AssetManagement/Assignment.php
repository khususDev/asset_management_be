<?php

namespace App\Models\Operation\AssetManagement;

use App\Models\Administration\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $table = 'opt_assignments';

    protected $fillable = [
        'assignable_type',
        'assignable_id',
        'assigned_to',
        'assigned_date',
        'returned_date',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'returned_date' => 'date',
    ];

    /**
     * Relasi Polymorphic ke entitas induk (Asset / License / Consumable)
     */
    public function assignable()
    {
        return $this->morphTo();
    }

    /**
     * Relasi ke User yang menerima aset
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Relasi ke User pembuat record
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
