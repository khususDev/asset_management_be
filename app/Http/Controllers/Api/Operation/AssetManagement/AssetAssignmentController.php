<?php

namespace App\Http\Controllers\Api\Operation\AssetManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignAssetRequest;
use App\Models\Operation\AssetManagement\Asset;
use App\Models\Operation\AssetManagement\AssetMovement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetAssignmentController extends Controller
{
    public function assign(AssignAssetRequest $request, $id)
    {
        $asset = Asset::findOrFail($id);

        // 🛠️ PERBAIKAN 1: Cek kolom usage_status (bukan status)
        if ($asset->usage_status !== 'AVAILABLE') {
            return response()->json([
                'success' => false,
                'message' => 'Aset tidak dalam status AVAILABLE dan tidak dapat diserahkan.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 🛠️ PERBAIKAN 2: Update usage_status menjadi ASSIGNED
            $asset->update([
                'usage_status'    => 'ASSIGNED',
                'assigned_type'   => $request->assigned_type,
                'assigned_to_id'  => $request->assigned_to_id,
                'assigned_date'   => $request->assigned_date,
                'condition'       => $request->condition,
            ]);

            // Catat transaksi ke Audit Log / Asset Movements
            AssetMovement::create([
                'asset_id'         => $asset->id,
                'action'           => 'ASSIGNMENT',
                'assigned_type'    => $request->assigned_type,
                'assigned_to_id'   => $request->assigned_to_id,
                'action_date'      => $request->assigned_date,
                'reference_number' => $request->reference_number,
                'condition'        => $request->condition,
                'notes'            => $request->notes,
                'user_id'          => auth()->id() ?? null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aset berhasil di-assign!',
                'data'    => $asset
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses assignment: ' . $e->getMessage()
            ], 500);
        }
    }
}
