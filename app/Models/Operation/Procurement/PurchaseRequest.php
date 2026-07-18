<?php

namespace App\Models\Operation\Procurement;

use App\Models\Administration\User;
use App\Models\Operation\Procurement\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseRequest extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'opt_purchase_request';

    protected $fillable = [
        'request_number',
        'user_id',
        'department_id',
        'purpose',
        'total_estimated_amount',
        'status',
        'approval_method',
        'approved_at',
        'approved_by',
    ];

    // === RELASI KE USER (PREPARED BY) ===
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(
            \App\Models\Administration\Organization\Department::class,
            'department_id'
        );
    }

    // === RELASI KE DETAIL ITEMS (WAJIB ADA & NAMANYA HARUS 'items') ===
    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class, 'purchase_request_id');
    }

    public function workflowApprovals()
    {
        // Mengikat ke tabel workflow_approval secara polimorfik
        return $this->morphMany(\App\Models\Approvals\WorkflowApproval::class, 'approvable')->orderBy('level', 'asc');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('PURCHASE REQUEST');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(
            PurchaseOrder::class,
            'purchase_request_id'
        );
    }
}
