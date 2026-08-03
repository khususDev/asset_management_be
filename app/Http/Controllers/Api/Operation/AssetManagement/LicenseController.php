<?php

namespace App\Http\Controllers\Api\Operation\AssetManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LicenseController extends Controller
{
    public function index(Request $request)
    {
        try {
            $licenses = DB::table('opt_licenses as l')
                ->leftJoin('mst_asset_category as cat', 'l.category_id', '=', 'cat.id')
                ->leftJoin('mst_procurement_vendor as v', 'l.vendor_id', '=', 'v.id')
                ->select(
                    'l.*', // Mengambil seluruh kolom asli opt_licenses secara aman
                    'cat.name as category_name',
                    'v.name as vendor_name'
                )
                ->orderBy('l.created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    // Mapping Fallback Fleksibel agar Frontend Vue tidak error
                    $item->total_seats = $item->total_seats ?? $item->seats ?? $item->capacity ?? $item->qty ?? 0;
                    $item->used_seats = $item->used_seats ?? $item->seats_used ?? $item->assigned_seats ?? 0;
                    $item->expiration_date = $item->expiration_date ?? $item->expiry_date ?? $item->expire_date ?? $item->valid_until ?? null;
                    return $item;
                });

            return response()->json([
                'success' => true,
                'data' => $licenses
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data license: ' . $e->getMessage()
            ], 500);
        }
    }
}
