<?php

namespace App\Http\Controllers\Api\Operation\AssetOperation;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransferRequestRequest;
use App\Models\Operation\AssetOperation\TransferRequest;
use App\Services\Operation\TransferRequestService;
use Illuminate\Http\Request;

class TransferRequestController extends Controller
{
    public function __construct(private readonly TransferRequestService $transferRequestService)
    {
    }

    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;
        $search = $request->search ?? '';

        $query = TransferRequest::with('user');

        if ($search) {
            $query->where('request_number', 'like', "%{$search}%")
                ->orWhere('asset_name', 'like', "%{$search}%");
        }

        return response()->json(['success' => true, 'data' => $query->latest()->paginate($entries)]);
    }

    public function store(StoreTransferRequestRequest $request)
    {
        $transferRequest = $this->transferRequestService->createFromRequest($request);

        return response()->json(['success' => true, 'data' => $transferRequest]);
    }
}
