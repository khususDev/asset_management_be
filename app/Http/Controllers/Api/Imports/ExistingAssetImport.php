<?php

namespace App\Http\Controllers\Api\Imports;

use App\Models\Asset;
use App\Services\AssetCodeService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ExistingAssetImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $assetCodeService;

    public function __construct()
    {
        $this->assetCodeService = new AssetCodeService();
    }

    public function model(array $row)
    {
        $assignedTo = !empty($row['assigned_to_user_id']) ? $row['assigned_to_user_id'] : null;

        return new Asset([
            'asset_code' => $this->assetCodeService->generateAssetCode(),
            'asset_name' => $row['nama_aset'],
            'asset_class' => 'FIXED_ASSET',
            'asset_category_id' => $row['category_id'],
            'asset_type_id' => $row['type_id'],
            'brand_id' => $row['brand_id'] ?? null,
            'model_id' => $row['model_id'] ?? null,
            'serial_number' => $row['serial_number'] ?? null,
            'branch_id' => $row['branch_id'],
            'location_id' => $row['location_id'],
            'department_id' => $row['department_id'] ?? null,
            'assigned_to' => $assignedTo,
            'purchase_cost' => $row['harga_perolehan'] ?? 0,
            'purchase_date' => $row['tanggal_pembelian'] ?? null,
            'status_id' => $row['status_id'] ?? 1,
            'usage_status' => $assignedTo ? 'ASSIGNED' : 'AVAILABLE',
            'registration_status' => 'REGISTERED',
            'remarks' => $row['keterangan'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'nama_aset' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:asset_categories,id',
            'type_id' => 'required|integer|exists:asset_types,id',
            'branch_id' => 'required|integer|exists:branches,id',
            'location_id' => 'required|integer|exists:locations,id',
        ];
    }
}