<?php

namespace App\Services\AssetManagement;

use App\Models\Operation\AssetManagement\Asset;

class AssetCodeService
{
    public function generateAssetCode()
    {

        $last = Asset::latest()->first();

        $next = $last ? $last->id + 1 : 1;

        return 'AST-'.date('Ym').'-'.str_pad($next,6,'0',STR_PAD_LEFT);

    }

}