<?php

namespace App\Http\Controllers\Api\Operation\AssetManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsumableController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Gunakan c.* agar PostgreSQL TIDAK AKAN melempar error "Undefined Column"
            $consumables = DB::table('opt_consumables as c')
                ->leftJoin('mst_asset_category as cat', 'c.category_id', '=', 'cat.id')
                ->leftJoin('mst_procurement_vendor as v', 'c.vendor_id', '=', 'v.id')
                ->select(
                    'c.*', // Ambil semua kolom yang ada di tabel opt_consumables secara aman
                    'cat.name as category_name',
                    'v.name as vendor_name'
                )
                ->orderBy('c.created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    // Fallback Mapping Otomatis di level PHP untuk kebebasan nama kolom
                    $item->item_code = $item->item_code ?? $item->code ?? $item->sku ?? '-';
                    $item->item_name = $item->item_name ?? $item->name ?? $item->title ?? '-';

                    // Deteksi kuantitas stok dari berbagai variasi nama kolom yang mungkin
                    $item->quantity = $item->quantity ?? $item->qty ?? $item->stock ?? $item->stock_qty ?? $item->total_qty ?? 0;

                    // Satuan & Batas Minimum Stok
                    $item->unit = $item->unit ?? $item->uom ?? $item->unit_type ?? 'Pcs';
                    $item->min_stock = $item->min_stock ?? $item->minimum_stock ?? $item->min_qty ?? 5;

                    return $item;
                });

            return response()->json([
                'success' => true,
                'data' => $consumables
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data consumable: ' . $e->getMessage()
            ], 500);
        }
    }
}
