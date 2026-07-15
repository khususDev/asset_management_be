<?php

namespace App\Http\Controllers\Api\Administration\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUomRequest;
use App\Http\Requests\UpdateUomRequest;
use App\Models\Administration\Procurement\Uom;
use App\Services\Administration\MasterDataService;
use Illuminate\Http\Request;

class UomController extends Controller
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

        return response()->json(['success' => true, 'data' => $this->masterDataService->listItems(Uom::query(), ['name', 'code'], $entries)]);
    }

    public function store(StoreUomRequest $request)
    {
        $uom = $this->masterDataService->createModel(new Uom(), $request->validated());
        return response()->json(['success' => true, 'message' => 'Satuan (UoM) berhasil ditambahkan!', 'data' => $uom]);
    }

    public function show($id)
    {
        return response()->json(['success' => true, 'data' => Uom::findOrFail($id)]);
    }

    public function update(UpdateUomRequest $request, $id)
    {
        $uom = Uom::findOrFail($id);
        $uom = $this->masterDataService->updateModel($uom, $request->validated());
        return response()->json(['success' => true, 'message' => 'Satuan (UoM) berhasil diperbarui!', 'data' => $uom]);
    }

    public function destroy($id)
    {
        Uom::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Satuan (UoM) berhasil dihapus!']);
    }
}
