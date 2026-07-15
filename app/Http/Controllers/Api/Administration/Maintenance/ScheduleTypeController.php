<?php

namespace App\Http\Controllers\Api\Administration\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Administration\Maintenance\ScheduleType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScheduleTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;
        $search = $request->search ?? '';

        $query = ScheduleType::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        }

        return response()->json(['success' => true, 'data' => $query->latest()->paginate($entries)]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:mst_maintenance_schedule,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $type = ScheduleType::create($request->all());
        return response()->json(['success' => true, 'message' => 'Maintenance Schedule Type created successfully!', 'data' => $type]);
    }

    public function show($id)
    {
        return response()->json(['success' => true, 'data' => ScheduleType::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        $type = ScheduleType::findOrFail($id);
        $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('mst_maintenance_schedule')->ignore($id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $type->update($request->all());
        return response()->json(['success' => true, 'message' => 'Maintenance schedule type updated successfully!', 'data' => $type]);
    }

    public function destroy($id)
    {
        ScheduleType::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Maintenance schedule type deleted successfully!']);
    }
}
