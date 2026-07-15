<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TestingApi;
use Illuminate\Http\Request;

class TestApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil request dari Vue, beri nilai default jika kosong
        $entries = $request->entries ?? 10;
        $search = $request->search ?? '';

        $query = TestingApi::query();

        // Jika ada pencarian, filter berdasarkan nama atau code
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        }

        // Gunakan paginate() bukan all()
        $data = $query->paginate($entries);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * POST /api/test-api
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:testing,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive'
        ]);

        $data = TestingApi::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data saved successfully!',
            'data' => $data
        ], 201);
    }

    /**
     * GET /api/test-api/{id}
     */
    public function show(string $id)
    {
        $data = TestingApi::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * PUT /api/test-api/{id}
     */
    public function update(Request $request, string $id)
    {
        $data = TestingApi::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:testing,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive'
        ]);

        $data->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully!',
            'data' => $data
        ]);
    }

    /**
     * DELETE /api/test-api/{id}
     */
    public function destroy(string $id)
    {
        $data = TestingApi::findOrFail($id);

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data deleted successfully!'
        ]);
    }
}
