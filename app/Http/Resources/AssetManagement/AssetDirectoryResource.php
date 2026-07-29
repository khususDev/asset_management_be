<?php

namespace App\Http\Resources\AssetManagement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetDirectoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
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
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],

            'type' => [
                'id' => $this->type?->id,
                'name' => $this->type?->name,
            ],

            'brand' => [
                'id' => $this->brand?->id,
                'name' => $this->brand?->name,
            ],

            'model' => [
                'id' => $this->model?->id,
                'name' => $this->model?->name,
            ],

            'status' => [
                'id' => $this->status?->id,
                'name' => $this->status?->name,
                'color' => $this->status?->color,
            ],

            'vendor' => [
                'name' => $this->vendor?->name,
            ],

            'department' => [
                'name' => $this->department?->name,
            ],

            'branch' => [
                'name' => $this->branch?->name,
            ],

            'location' => [
                'name' => $this->location?->name,
            ],

            'procurement' => [
                'purchase_date' => $this->purchase_date,
                'purchase_cost' => $this->purchase_cost,
            ],

            'procurement_document' => [
                'purchase_request' => $this->goodsReceiptItem?->goodsReceipt?->purchaseOrder?->purchaseRequest?->request_number,
                'purchase_order' => $this->goodsReceiptItem?->goodsReceipt?->purchaseOrder?->po_number,
                'goods_receipt' => $this->goodsReceiptItem?->goodsReceipt?->gr_number,
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
                'id' => null,
                'name' => null,
            ],
            'remarks' => $this->remarks,
        ];
    }
}
