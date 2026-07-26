<?php

namespace App\Http\Resources\AssetManagement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetRegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $grItem = $this->goodsReceiptItem;
        $poItem = $grItem?->purchaseOrderItem;
        $prItem = $poItem?->purchaseRequestItem;

        $gr = $grItem?->goodsReceipt;
        $po = $gr?->purchaseOrder;

        return [

            'id' => $this->id,

            'asset' => [
                'id' => $this->id,
                'code' => $this->asset_code,
                'name' => $this->asset_name ?: $prItem?->item_description,
                'serial_number' => $this->serial_number,
                'class' => $prItem?->asset_class,
                'status' => $this->registration_status,
            ],

            'document' => [
                'pr_number' => $poItem?->purchaseRequestItem?->purchaseRequest?->request_number,
                'po_number' => $po?->po_number,
                'gr_number' => $gr?->gr_number,
            ],

            'procurement' => [
                'vendor_name' => $prItem?->vendor_name,
                'purchase_cost' => $this->purchase_cost,
                'purchase_date' => $this->purchase_date,
                'qty' => $poItem?->quantity,
                'uom' => $poItem?->uom?->name,
                'purpose' => $prItem?->item_purpose,
                'expected_arrival' => $prItem?->expected_arrival_date,
            ],

            'department' => [
                'id' => $po?->department?->id,
                'name' => $po?->department?->name,
            ],

            'vendor' => [
                'id' => $po?->vendor?->id,
                'name' => $po?->vendor?->name,
            ],

            'registration' => [
                'category_id' => $this->asset_category_id,
                'type_id' => $this->asset_type_id,
                'brand_id' => $this->brand_id,
                'model_id' => $this->model_id,
                'status_id' => $this->status_id,
                'location_id' => $this->location_id,
                'warranty_start' => $this->warranty_start,
                'warranty_end' => $this->warranty_end,
                'useful_life' => $this->useful_life,
                'remarks' => $this->remarks,
            ],
        ];
    }
}
