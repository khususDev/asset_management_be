<?php

namespace App\Http\Controllers\Api\Operation\AssetOperation;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetRequestRequest;
use App\Models\Operation\AssetOperation\AssetRequest;
use App\Services\Operation\AssetRequestService;
use Illuminate\Http\Request;

class AssetRequestController extends Controller
{
    public function __construct(private readonly AssetRequestService $assetRequestService)
    {
    }

    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;
        $search = $request->search ?? '';

        $query = AssetRequest::with('user');

        if ($search) {
            $query->where('request_number', 'like', "%{$search}%")
                ->orWhere('asset_name', 'like', "%{$search}%");
        }

        return response()->json(['success' => true, 'data' => $query->latest()->paginate($entries)]);
    }

    public function store(StoreAssetRequestRequest $request)
    {
        $assetRequest = $this->assetRequestService->createFromRequest($request);

        return response()->json(['success' => true, 'data' => $assetRequest]);
    }
}
