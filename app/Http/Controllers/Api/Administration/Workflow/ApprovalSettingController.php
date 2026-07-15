<?php

namespace App\Http\Controllers\Api\Administration\Workflow;

use App\Http\Controllers\Controller;
use App\Models\Administration\Workflow\ApprovalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalSettingController extends Controller
{
    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;
        $search = $request->search ?? '';

        // 1. Load relasi levels.user DAN relasi department yang baru dibuat
        $query = ApprovalSetting::with(['levels.user', 'department']);

        if ($search) {
            $query->where('module', 'like', "%{$search}%")
                // 2. Cari berdasarkan nama departemen lewat relasi tabel
                ->orWhereHas('department', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%"); // Asumsikan kolom nama departemen adalah 'name'
                });
        }

        return response()->json([
            'success' => true,
            // 3. Ubah orderBy dari 'department' ke 'department_id'
            'data' => $query->orderBy('module')->orderBy('department_id')->paginate($entries)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'module' => 'required|string|max:100',
            'department_id' => 'nullable|exists:mst_org_department,id',
            'is_active' => 'boolean',
            'levels' => 'required|array|min:1',
            'levels.*.user_id' => 'required|exists:users,id',
            'levels.*.min_amount' => 'numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $setting = ApprovalSetting::create([
                'module' => $request->module,
                'department_id' => $request->department_id,
                'is_active' => $request->is_active ?? true,
            ]);

            // Looping array levels dari frontend
            foreach ($request->levels as $index => $levelData) {
                $setting->levels()->create([
                    'level' => $index + 1, // Otomatis urut 1, 2, 3...
                    'user_id' => $levelData['user_id'],
                    'min_amount' => $levelData['min_amount'] ?? 0,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Aturan Approval berhasil ditambahkan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => ApprovalSetting::with('levels')->findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'module' => 'required|string|max:100',
            'department_id' => 'nullable|exists:mst_org_department,id',
            'is_active' => 'boolean',
            'levels' => 'required|array|min:1',
            'levels.*.user_id' => 'required|exists:users,id',
            'levels.*.min_amount' => 'numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $setting = ApprovalSetting::findOrFail($id);
            $setting->update([
                'module' => $request->module,
                'department_id' => $request->department_id,
                'is_active' => $request->is_active ?? true,
            ]);

            // Hapus detail lama, ganti dengan susunan baru
            $setting->levels()->delete();

            foreach ($request->levels as $index => $levelData) {
                $setting->levels()->create([
                    'level' => $index + 1,
                    'user_id' => $levelData['user_id'],
                    'min_amount' => $levelData['min_amount'] ?? 0,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Aturan Approval berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        ApprovalSetting::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Aturan Approval berhasil dihapus!']);
    }
}
