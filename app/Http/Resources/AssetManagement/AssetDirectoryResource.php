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

            'warranty' => [
                'start' => $this->warranty_start,
                'end' => $this->warranty_end,
            ],

            'registration_status' => $this->registration_status,
        ];
    }
}
