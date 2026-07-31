<?php

namespace App\Http\Controllers\Api\Operation\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\Administration\Organization\Department;
use App\Models\Administration\Procurement\Vendor;
use App\Models\Administration\User;
use Illuminate\Http\Request;
use App\Models\Operation\AssetManagement\Asset;
use Illuminate\Support\Facades\DB;
use App\Models\Administration\Asset\Category;
use App\Models\Administration\Asset\Type;
use App\Models\Administration\Asset\Brand;
use App\Models\Administration\Asset\Status;
use App\Models\Administration\Organization\Branch;
use App\Http\Resources\AssetManagement\AssetRegistrationResource;
use App\Services\AssetManagement\AssetCodeService;
use App\Models\Administration\Organization\Location;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class AssetRegistrationController extends Controller
{
    protected $assetCodeService;

    public function __construct(AssetCodeService $assetCodeService)
    {
        $this->assetCodeService = $assetCodeService;
    }

    public function index(Request $request)
    {
        $assets = Asset::with([
            'goodsReceiptItem.goodsReceipt.purchaseOrder.vendor',
            'goodsReceiptItem.goodsReceipt.purchaseOrder.department',
            'goodsReceiptItem.purchaseOrderItem.purchaseRequestItem.purchaseRequest',
            'goodsReceiptItem.purchaseOrderItem.uom',
        ])
            ->where('registration_status', 'WAITING_REGISTRATION')
            ->latest()
            ->paginate(10);
        return AssetRegistrationResource::collection($assets);
    }

    /**
     * Detail Asset
     */
    public function show($id)
    {
        $asset = Asset::with([
            'goodsReceiptItem.goodsReceipt.purchaseOrder.vendor',
            'goodsReceiptItem.goodsReceipt.purchaseOrder.department',
            'goodsReceiptItem.purchaseOrderItem.purchaseRequestItem.purchaseRequest',
            'goodsReceiptItem.purchaseOrderItem.uom',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new AssetRegistrationResource($asset),
        ]);
    }

    /**
     * Registrasi Asset
     */

    public function storeExisting(Request $request)
    {
        $validated = $request->validate([
            // Informasi Dasar
            'asset_name' => 'required|string|max:255',
            'asset_class' => 'required|in:FIXED_ASSET,CONSUMABLE,LICENSE',
            'asset_category_id' => 'required|exists:mst_asset_category,id',
            'asset_type_id' => 'nullable|exists:mst_asset_type,id',
            'brand_id' => 'nullable|exists:mst_asset_brand,id',
            'model_id' => 'nullable|exists:mst_asset_model,id',
            'serial_number' => 'nullable|string|max:100',

            // Penempatan
            'branch_id' => 'required|exists:mst_org_branch,id',
            'location_id' => 'nullable|exists:mst_org_location,id',
            'department_id' => 'nullable|exists:mst_org_department,id',

            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'status_id' => 'required|exists:mst_asset_status,id',
            'remarks' => 'nullable|string',

            'assigned_to' => 'nullable|exists:users,id',
            'assigned_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            // Auto-generate Asset Code (misal: AST-202607-000001)
            $assetCode = 'AST-' . date('Ym') . '-' . Str::padLeft(Asset::count() + 1, 6, '0');

            // Jika assigned_to diisi, otomatis usage_status = ASSIGNED. Jika kosong = AVAILABLE
            $usageStatus = !empty($validated['assigned_to']) ? 'ASSIGNED' : 'AVAILABLE';

            $asset = Asset::create([
                'asset_code' => $assetCode,
                'asset_name' => $validated['asset_name'],
                'asset_class' => $validated['asset_class'],
                'asset_category_id' => $validated['asset_category_id'],
                'asset_type_id' => $validated['asset_type_id'] ?? null,
                'brand_id' => $validated['brand_id'] ?? null,
                'model_id' => $validated['model_id'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,

                'branch_id' => $validated['branch_id'],
                'location_id' => $validated['location_id'] ?? null,
                'department_id' => $validated['department_id'] ?? null,

                'purchase_date' => $validated['purchase_date'] ?? null,
                'purchase_cost' => $validated['purchase_cost'] ?? 0,
                'status_id' => $validated['status_id'],
                'usage_status' => $usageStatus,
                'assigned_to' => $validated['assigned_to'] ?? null, // Simpan ID Pemakai
                'remarks' => $validated['remarks'] ?? 'Aset Eksisting (Migrasi)',

                'registration_status' => 'REGISTERED',
                'goods_receipt_item_id' => null,
            ]);

            if (!empty($validated['assigned_to'])) {

            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aset eksisting berhasil didaftarkan!',
                'data' => $asset,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan aset: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function register(Request $request, $id)
    {
        $request->validate([
            'serial_number' => 'nullable|string|max:255',
            'asset_class' => 'required',
            'asset_category_id' => 'required|exists:mst_asset_category,id',
            'asset_type_id' => 'required|exists:mst_asset_type,id',

            'brand_id' => 'nullable|exists:mst_asset_brand,id',
            'model_id' => 'nullable|exists:mst_asset_model,id',

            'status_id' => 'required|exists:mst_asset_status,id',

            'branch_id' => 'required|exists:mst_org_branch,id',
            'location_id' => 'nullable|exists:mst_org_location,id',

            'warranty_start' => 'nullable|date',
            'warranty_end' => 'nullable|date',

            'useful_life' => 'nullable|integer',

            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {

            $asset = Asset::findOrFail($id);

            $assetCode = $this->assetCodeService->generateAssetCode();

            $asset->update([

                'asset_code' => $assetCode,
                'asset_class' => $request->asset_class,
                'serial_number' => $request->serial_number,

                'asset_category_id' => $request->asset_category_id,
                'asset_type_id' => $request->asset_type_id,

                'brand_id' => $request->brand_id,
                'model_id' => $request->model_id,

                'status_id' => $request->status_id,

                'branch_id' => $request->branch_id,
                'location_id' => $request->location_id,

                'warranty_start' => $request->warranty_start,
                'warranty_end' => $request->warranty_end,

                'useful_life' => $request->useful_life,

                'remarks' => $request->remarks,

                'registration_status' => 'REGISTERED',

                'qr_code' => $assetCode,
                'barcode' => $assetCode,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asset berhasil diregistrasi.'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function masters()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => Category::orderBy('name')->get(),
                'types' => Type::orderBy('name')->get(),
                'brands' => Brand::orderBy('name')->get(),
                'locations' => Location::orderBy('name')->get(),
                'statuses' => Status::orderBy('name')->get(),
                'branches' => Branch::orderBy('name')->get(),
                'vendors' => Vendor::orderBy('name')->get(),

                // --- TAMBAHKAN DUA BARIS INI ---
                'departments' => Department::orderBy('name')->get(['id', 'name']),
                'employees' => User::orderBy('name')->get(['id', 'name', 'email']),
                // -------------------------------

                'usage_statuses' => [
                    ['id' => 'AVAILABLE', 'name' => 'Available'],
                    ['id' => 'ASSIGNED', 'name' => 'Assigned'],
                    ['id' => 'MAINTENANCE', 'name' => 'Maintenance'],
                ],
            ]
        ]);
    }

    public function downloadTemplate()
    {
        // Path ke file template yang disimpan di folder storage/app/templates/
        $filePath = storage_path('app/templates/template_import_aset_eksisting.xlsx');

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'File template tidak ditemukan.'], 444);
        }

        return response()->download($filePath, 'Template_Import_Aset_Eksisting.xlsx');
    }

    public function importExisting(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120', // Maksimal 5MB
        ]);

        try {
            Excel::import(new \App\Http\Controllers\Api\Imports\ExistingAssetImport(), $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Data aset berhasil di-import!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor data: ' . $e->getMessage(),
            ], 422);
        }
    }
}
