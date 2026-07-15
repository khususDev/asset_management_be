<?php

namespace App\Http\Controllers\Api\Administration\Asset;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStatusRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Administration\Asset\Status;
use App\Services\Administration\MasterDataService;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function __construct(private readonly MasterDataService $masterDataService)
    {
    }

    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;

        return response()->json([
            'success' => true,
            'data' => $this->masterDataService->listItems(Status::query(), ['name', 'code'], $entries),
        ]);
    }

    public function store(StoreStatusRequest $request)
    {
        $status = $this->masterDataService->createModel(new Status(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    public function show(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => Status::findOrFail($id),
        ]);
    }

    public function update(UpdateStatusRequest $request, string $id)
    {
        $status = Status::findOrFail($id);
        $status = $this->masterDataService->updateModel($status, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    public function destroy(string $id)
    {
        Status::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
