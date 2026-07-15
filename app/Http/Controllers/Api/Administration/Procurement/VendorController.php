<?php

namespace App\Http\Controllers\Api\Administration\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Models\Administration\Procurement\Vendor;
use App\Services\Administration\MasterDataService;
use Illuminate\Http\Request;

class VendorController extends Controller
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

        $query = Vendor::with('vendorType');
        $data = $this->masterDataService->listItems($query, ['name', 'code'], $entries);

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function store(StoreVendorRequest $request)
    {
        $vendor = $this->masterDataService->createModel(new Vendor(), $request->validated());
        return response()->json(['success' => true, 'message' => 'Vendor berhasil ditambahkan!', 'data' => $vendor]);
    }

    public function show($id)
    {
        return response()->json(['success' => true, 'data' => Vendor::with('vendorType')->findOrFail($id)]);
    }

    public function update(UpdateVendorRequest $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor = $this->masterDataService->updateModel($vendor, $request->validated());
        return response()->json(['success' => true, 'message' => 'Vendor berhasil diperbarui!', 'data' => $vendor]);
    }

    public function destroy($id)
    {
        Vendor::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Vendor berhasil dihapus!']);
    }
}
