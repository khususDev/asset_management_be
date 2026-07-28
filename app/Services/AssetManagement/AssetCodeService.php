<?php

namespace App\Services\AssetManagement;

use App\Models\Operation\AssetManagement\Asset;

class AssetCodeService
{
    public function generateAssetCode()
    {
        $prefix = 'AST-' . date('Ym') . '-';

        $lastAsset = Asset::whereNotNull('asset_code')
            ->where('asset_code', 'like', $prefix . '%')
            ->orderByDesc('asset_code')
            ->first();

        if ($lastAsset) {

            $lastNumber = (int) substr($lastAsset->asset_code, -6);

            $nextNumber = $lastNumber + 1;

        } else {

            $nextNumber = 1;

        }

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}