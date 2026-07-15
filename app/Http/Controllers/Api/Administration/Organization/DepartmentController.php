<?php

namespace App\Http\Controllers\Api\Administration\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Administration\Organization\Department;
use App\Services\Administration\MasterDataService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
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

        $data = $this->masterDataService->listItems(Department::query(), ['name', 'code'], $entries);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(StoreDepartmentRequest $request)
    {
        $department = $this->masterDataService->createModel(new Department(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Departemen berhasil ditambahkan!',
            'data' => $department
        ]);
    }

    public function show($id)
    {
        $department = Department::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $department
        ]);
    }

    public function update(UpdateDepartmentRequest $request, $id)
    {
        $department = Department::findOrFail($id);
        $department = $this->masterDataService->updateModel($department, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Departemen berhasil diperbarui!',
            'data' => $department
        ]);
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Departemen berhasil dihapus!'
        ]);
    }
}
