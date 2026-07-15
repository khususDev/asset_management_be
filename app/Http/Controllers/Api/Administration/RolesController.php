<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Administration\Role;

class RolesController extends Controller
{
    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;
        $search = $request->search ?? '';

        $query = Role::with('permissions');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $data = $query->paginate($entries);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array' // Array berisi nama-nama permission
        ]);

        // Buat Role Baru
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        // Jika ada permission yang dikirim dari Vue (dicentang), simpan ke role ini
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data saved successfully!',
            'data' => $role
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'nullable|array'
        ]);

        // Update nama role
        $role->update([
            'name' => $request->name
        ]);

        // Sinkronisasi permission (Spatie otomatis menghapus yang tidak dicentang dan menambah yang baru dicentang)
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully!',
            'data' => $role
        ]);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data deleted successfully!'
        ]);
    }
}
