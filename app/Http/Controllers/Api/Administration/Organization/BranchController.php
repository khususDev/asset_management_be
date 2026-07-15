<?php

namespace App\Http\Controllers\Api\Administration\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Administration\Organization\Branch;
use App\Services\Administration\MasterDataService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(private readonly MasterDataService $masterDataService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;

        $data = $this->masterDataService->listItems(Branch::query(), ['name', 'code'], $entries);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBranchRequest $request)
    {
        $branch = $this->masterDataService->createModel(new Branch(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cabang berhasil ditambahkan!',
            'data' => $branch
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $branch = Branch::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $branch
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBranchRequest $request, string $id)
    {
        $branch = Branch::findOrFail($id);
        $branch = $this->masterDataService->updateModel($branch, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cabang berhasil diperbarui!',
            'data' => $branch
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cabang berhasil dihapus!'
        ]);
    }
}
