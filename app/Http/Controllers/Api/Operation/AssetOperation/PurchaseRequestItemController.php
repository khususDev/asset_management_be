<?php

namespace App\Models\Operation\AssetOperation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    use HasFactory;

    // Sesuaikan jika Anda memakai prefix tabel
    protected $table = 'opt_purchase_request_item';

    protected $fillable = [
        'purchase_request_id',
        'item_description',
        'quantity',
        'uom_id',
        'unit_price',
        'total_amount',
        'need_to_issue_po',
        'vendor_id',
        'is_pkp',
        'pic_contact',
        'url',
        'price_include_ppn',
        'payment_term_id',
        'expected_arrival_date',
        'delivery_id',
        'item_purpose'
    ];

    // === RELASI MASTER DATA (WAJIB AKTIF) ===

    public function vendor()
    {
        // PASTIKAN PATH INI SESUAI DENGAN FOLDER MODEL VENDOR ANDA
        return $this->belongsTo(\App\Models\Administration\Procurement\Vendor::class, 'vendor_id');
    }

    public function uom()
    {
        // PASTIKAN PATH INI SESUAI DENGAN FOLDER MODEL UOM ANDA
        return $this->belongsTo(\App\Models\Administration\Procurement\Uom::class, 'uom_id');
    }

    public function paymentTerm()
    {
        // PASTIKAN PATH INI SESUAI DENGAN FOLDER MODEL PAYMENT ANDA
        return $this->belongsTo(\App\Models\Administration\Procurement\PaymentTerm::class, 'payment_term_id');
    }

    public function deliveryBranch()
    {
        // PASTIKAN PATH INI SESUAI DENGAN FOLDER MODEL LOCATION ANDA
        return $this->belongsTo(\App\Models\Administration\Organization\Branch::class, 'delivery_id');
    }
}
