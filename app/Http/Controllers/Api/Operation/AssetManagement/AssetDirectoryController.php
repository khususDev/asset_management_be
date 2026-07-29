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
        $query = Asset::with([
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
            ->where('asset_class', 'FIXED_ASSET');

        $assets = $query
            ->latest()
            ->paginate(10);

        $statistics = [
            'total' => Asset::where('asset_class', 'FIXED_ASSET')->count(),
            'available' => Asset::where('asset_class', 'FIXED_ASSET')->where('usage_status', 'AVAILABLE')->count(),
            'assigned' => Asset::where('asset_class', 'FIXED_ASSET')->where('usage_status', 'ASSIGNED')->count(),
            'maintenance' => Asset::where('asset_class', 'FIXED_ASSET')->where('usage_status', 'MAINTENANCE')->count(),
            'disposed' => Asset::where('asset_class', 'FIXED_ASSET')->where('usage_status', 'DISPOSED')->count(),
        ];

        // Ekstrak data resource beserta paginasinya
        $resource = AssetDirectoryResource::collection($assets)->response()->getData(true);

        return response()->json([
            'success' => true,
            'statistics' => $statistics,
            'data' => $resource['data'],
            // Tambahkan data paginasi ini agar terbaca oleh Vue
            'links' => $resource['meta']['links'] ?? [],
            'from' => $resource['meta']['from'] ?? 0,
            'to' => $resource['meta']['to'] ?? 0,
            'total' => $resource['meta']['total'] ?? 0,
        ]);
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
            'goodsReceiptItem.goodsReceipt.purchaseOrder.purchaseRequest',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new AssetDirectoryResource($asset),
        ]);
    }
}
