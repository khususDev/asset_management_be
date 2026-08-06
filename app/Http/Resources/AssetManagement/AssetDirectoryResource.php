<?php

namespace App\Http\Resources\AssetManagement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetDirectoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $activeAssignment = $this->currentAssignment;

        // Helper untuk memastikan variabel bernilai Object sebelum dipanggil relasingya
        $obj = fn($val) => is_object($val) ? $val : null;

        return [
            'id' => $this->id,

            'asset' => [
                'code' => $this->asset_code,
                'name' => $this->asset_name,
                'class' => $this->asset_class,
                'serial_number' => $this->serial_number,
                'qr_code' => $this->qr_code,
                'barcode' => $this->barcode,
            ],

            'category' => [
                'id' => $obj($this->category)?->id,
                'name' => $obj($this->category)?->name,
            ],

            'type' => [
                'id' => $obj($this->type)?->id,
                'name' => $obj($this->type)?->name,
            ],

            'brand' => [
                'id' => $obj($this->brand)?->id,
                'name' => $obj($this->brand)?->name,
            ],

            'model' => [
                'id' => $obj($this->model)?->id,
                'name' => $obj($this->model)?->name,
            ],

            'status' => [
                'id' => $obj($this->status)?->id,
                'name' => $obj($this->status)?->name,
                'color' => $obj($this->status)?->color,
            ],

            'vendor' => [
                'name' => $obj($this->vendor)?->name,
            ],

            'department' => [
                'name' => $obj($this->department)?->name,
            ],

            'branch' => [
                'code' => $obj($this->branch)?->code,
                'name' => $obj($this->branch)?->name,
            ],

            'location' => [
                'name' => $obj($this->location)?->name,
            ],

            'procurement' => [
                'purchase_date' => $this->purchase_date,
                'purchase_cost' => $this->purchase_cost,
            ],

            'procurement_document' => [
                'purchase_request' => $obj($this->goodsReceiptItem)?->goodsReceipt?->purchaseOrder?->purchaseRequest?->request_number,
                'purchase_order' => $obj($this->goodsReceiptItem)?->goodsReceipt?->purchaseOrder?->po_number,
                'goods_receipt' => $obj($this->goodsReceiptItem)?->goodsReceipt?->gr_number,
            ],

            'warranty' => [
                'start' => $this->warranty_start,
                'end' => $this->warranty_end,
            ],

            'registration_status' => $this->registration_status,

            'usage' => [
                'code' => $this->usage_status,
                'label' => match ($this->usage_status) {
                    'AVAILABLE' => 'Available',
                    'ASSIGNED' => 'Assigned',
                    'RESERVED' => 'Reserved',
                    'MAINTENANCE' => 'Maintenance',
                    'DISPOSED' => 'Disposed',
                    default => '-',
                },
                'color' => match ($this->usage_status) {
                    'AVAILABLE' => '#10B981',
                    'ASSIGNED' => '#3B82F6',
                    'RESERVED' => '#F59E0B',
                    'MAINTENANCE' => '#8B5CF6',
                    'DISPOSED' => '#6B7280',
                    default => '#6B7280',
                },
            ],

            'financial' => [
                'purchase_cost' => $this->purchase_cost,
                'salvage_value' => $this->salvage_value,
                'useful_life' => $this->useful_life,
            ],

            'assigned_to' => [
                'id' => $obj($activeAssignment)?->user?->id ?? null,
                'name' => $obj($activeAssignment)?->user?->name ?? null,
                'assigned_date' => $obj($activeAssignment)?->assigned_date ?? null,
            ],

            'remarks' => $this->remarks,
        ];
    }
}
