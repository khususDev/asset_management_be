<?php

namespace App\Http\Controllers\Api\Operation\AssetManagement;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetManagement\AssetDirectoryResource;
use App\Models\Operation\AssetManagement\Asset;
use Illuminate\Http\Request;

class AssetDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $assets = Asset::with([
            'category',
            'type',
            'brand',
            'model',
            'status',
            'vendor',
            'department',
            'branch',
            'location',
        ])
            ->where('registration_status', 'REGISTERED')
            ->whereIn('asset_class', ['FIXED_ASSET', 'CONSUMABLE', 'LICENSE'])
            ->latest()
            ->paginate(10);

        return AssetDirectoryResource::collection($assets);
    }

    public function show($id)
    {
        $asset = Asset::with([
            'category',
            'type',
            'brand',
            'model',
            'status',
            'vendor',
            'department',
            'branch',
            'location',
            'goodsReceiptItem.goodsReceipt.purchaseOrder',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new AssetDirectoryResource($asset)
        ]);
    }
}
