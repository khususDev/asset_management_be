<?php

namespace App\Http\Controllers\Api\Administration\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Administration\Maintenance\MaintenanceType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaintenanceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;
        $search = $request->search ?? '';

        $query = MaintenanceType::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        }

        return response()->json(['success' => true, 'data' => $query->latest()->paginate($entries)]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:mst_maintenance_type,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $type = MaintenanceType::create($request->all());
        return response()->json(['success' => true, 'message' => 'Maintenance type created successfully!', 'data' => $type]);
    }

    public function show($id)
    {
        return response()->json(['success' => true, 'data' => MaintenanceType::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        $type = MaintenanceType::findOrFail($id);
        $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('mst_maintenance_type')->ignore($id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $type->update($request->all());
        return response()->json(['success' => true, 'message' => 'Maintenance type updated successfully!', 'data' => $type]);
    }

    public function destroy($id)
    {
        MaintenanceType::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Maintenance type deleted successfully!']);
    }
}
