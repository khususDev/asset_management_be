<?php

namespace App\Http\Controllers\Api\Administration\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVendorTypeRequest;
use App\Http\Requests\UpdateVendorTypeRequest;
use App\Models\Administration\Procurement\VendorType;
use App\Services\Administration\MasterDataService;
use Illuminate\Http\Request;

class VendorTypeController extends Controller
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

        return response()->json(['success' => true, 'data' => $this->masterDataService->listItems(VendorType::query(), ['name', 'code'], $entries)]);
    }

    public function store(StoreVendorTypeRequest $request)
    {
        $type = $this->masterDataService->createModel(new VendorType(), $request->validated());
        return response()->json(['success' => true, 'message' => 'Tipe Vendor berhasil ditambahkan!', 'data' => $type]);
    }

    public function show($id)
    {
        return response()->json(['success' => true, 'data' => VendorType::findOrFail($id)]);
    }

    public function update(UpdateVendorTypeRequest $request, $id)
    {
        $type = VendorType::findOrFail($id);
        $type = $this->masterDataService->updateModel($type, $request->validated());
        return response()->json(['success' => true, 'message' => 'Tipe Vendor berhasil diperbarui!', 'data' => $type]);
    }

    public function destroy($id)
    {
        VendorType::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Tipe Vendor berhasil dihapus!']);
    }
}
