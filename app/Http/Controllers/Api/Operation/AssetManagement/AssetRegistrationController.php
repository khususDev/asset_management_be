<?php

namespace App\Http\Controllers\Api\Operation\AssetManagement;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetManagement\AssetRegistrationResource;
use App\Models\Administration\Asset\Brand;
use App\Models\Administration\Asset\Category;
use App\Models\Administration\Asset\Status;
use App\Models\Administration\Asset\Type;
use App\Models\Administration\Organization\Branch;
use App\Models\Administration\Organization\Department;
use App\Models\Administration\Organization\Location;
use App\Models\Administration\Procurement\Vendor;
use App\Models\Administration\User;
use App\Models\Operation\AssetManagement\Asset;
use App\Models\Operation\AssetManagement\Consumable;
use App\Models\Operation\AssetManagement\License;
use App\Services\AssetManagement\AssetCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
        try {
            // 1. Aset dari Procurement (GR) yang MASIH PENDING (Belum selesai di-register)
            $pendingGrItems = DB::table('opt_assets as a')
                ->leftJoin('opt_goods_receipt_item as gri', 'a.goods_receipt_item_id', '=', 'gri.id')
                ->leftJoin('opt_goods_receipt as gr', 'gri.goods_receipt_id', '=', 'gr.id')
                ->where(function ($q) {
                    $q->where('a.registration_status', 'PENDING')
                        ->orWhere('a.registration_status', 'WAITING_REGISTRATION')
                        ->orWhereNull('a.registration_status');
                })
                ->whereNotNull('a.goods_receipt_item_id') // Hanya yang berasal dari GR/Procurement
                ->select(
                    'a.id as record_id',
                    DB::raw("COALESCE(gr.gr_number, '-') as reference_code"),
                    'a.asset_name as item_name',
                    DB::raw("NULL as category_name"),
                    DB::raw("NULL as department_name"),
                    DB::raw("NULL as vendor_name"),
                    DB::raw("'PROCUREMENT' as source"),
                    DB::raw("'FIXED_ASSET' as asset_class"),
                    DB::raw("'WAITING_REGISTRATION' as registration_status"),
                    'a.created_at'
                )
                ->get();

            // 2. Fixed Assets yang SUDAH REGISTERED
            $registeredAssets = DB::table('opt_assets as a')
                ->leftJoin('mst_asset_category as cat', 'a.asset_category_id', '=', 'cat.id')
                ->leftJoin('mst_org_department as dept', 'a.department_id', '=', 'dept.id')
                ->leftJoin('mst_procurement_vendor as v', 'a.vendor_id', '=', 'v.id')
                ->where(function ($q) {
                    $q->where('a.registration_status', 'REGISTERED')
                        ->orWhereNull('a.goods_receipt_item_id'); // Aset manual/migrasi
                })
                ->select(
                    'a.id as record_id',
                    'a.asset_code as reference_code',
                    'a.asset_name as item_name',
                    'cat.name as category_name',
                    'dept.name as department_name',
                    'v.name as vendor_name',
                    DB::raw("CASE WHEN a.goods_receipt_item_id IS NOT NULL THEN 'PROCUREMENT' ELSE 'EXISTING_MANUAL' END as source"),
                    DB::raw("'FIXED_ASSET' as asset_class"),
                    DB::raw("'REGISTERED' as registration_status"),
                    'a.created_at'
                )->get();

            // 3. Registered Consumables
            $registeredConsumables = DB::table('opt_consumables as c')
                ->leftJoin('mst_asset_category as cat', 'c.category_id', '=', 'cat.id')
                ->leftJoin('mst_procurement_vendor as v', 'c.vendor_id', '=', 'v.id')
                ->select(
                    'c.id as record_id',
                    'c.item_code as reference_code',
                    'c.item_name',
                    'cat.name as category_name',
                    DB::raw("NULL as department_name"),
                    'v.name as vendor_name',
                    DB::raw("CASE WHEN c.goods_receipt_item_id IS NOT NULL THEN 'PROCUREMENT' ELSE 'EXISTING_MANUAL' END as source"),
                    DB::raw("'CONSUMABLE' as asset_class"),
                    DB::raw("'REGISTERED' as registration_status"),
                    'c.created_at'
                )->get();

            // 4. Registered Licenses
            $registeredLicenses = DB::table('opt_licenses as l')
                ->leftJoin('mst_asset_category as cat', 'l.category_id', '=', 'cat.id')
                ->leftJoin('mst_procurement_vendor as v', 'l.vendor_id', '=', 'v.id')
                ->select(
                    'l.id as record_id',
                    'l.license_code as reference_code',
                    'l.software_name as item_name',
                    'cat.name as category_name',
                    DB::raw("NULL as department_name"),
                    'v.name as vendor_name',
                    DB::raw("CASE WHEN l.goods_receipt_item_id IS NOT NULL THEN 'PROCUREMENT' ELSE 'EXISTING_MANUAL' END as source"),
                    DB::raw("'LICENSE' as asset_class"),
                    DB::raw("'REGISTERED' as registration_status"),
                    'l.created_at'
                )->get();

            // Gabungkan dan urutkan (WAITING_REGISTRATION selalu di atas)
            $combined = $pendingGrItems
                ->concat($registeredAssets)
                ->concat($registeredConsumables)
                ->concat($registeredLicenses)
                ->sort(function ($a, $b) {
                    if ($a->registration_status === 'WAITING_REGISTRATION' && $b->registration_status !== 'WAITING_REGISTRATION') return -1;
                    if ($a->registration_status !== 'WAITING_REGISTRATION' && $b->registration_status === 'WAITING_REGISTRATION') return 1;
                    return strtotime($b->created_at) - strtotime($a->created_at);
                })
                ->values();

            return response()->json(['success' => true, 'data' => $combined]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Mengambil detail Draft Asset berdasarkan ID opt_assets
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

    // Proses Finalisasi Registrasi (Mengubah status dari PENDING ke REGISTERED)
    public function register(Request $request, $id)
    {
        $request->validate([
            'asset_class' => 'required|in:FIXED_ASSET,CONSUMABLE,LICENSE',
            'asset_category_id' => 'required|exists:mst_asset_category,id',
        ]);

        DB::beginTransaction();

        try {
            $draftAsset = Asset::findOrFail($id);

            if ($request->asset_class === 'FIXED_ASSET') {
                $assetCode = $this->assetCodeService->generateAssetCode();

                $draftAsset->update([
                    'asset_code' => $assetCode,
                    'asset_class' => 'FIXED_ASSET',
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
                    'usage_status' => 'AVAILABLE',
                    'qr_code' => $assetCode,
                    'barcode' => $assetCode,
                ]);
            } elseif ($request->asset_class === 'LICENSE') {
                $licenseCode = 'LIC-' . date('Ym') . '-' . Str::padLeft(License::count() + 1, 6, '0');

                License::create([
                    'goods_receipt_item_id' => $draftAsset->goods_receipt_item_id,
                    'license_code' => $licenseCode,
                    'software_name' => $draftAsset->asset_name,
                    'category_id' => $request->asset_category_id,
                    'vendor_id' => $draftAsset->vendor_id,
                    'license_key' => $request->license_key ?? $request->serial_number,
                    'license_type' => $request->license_type ?? 'SUBSCRIPTION',
                    'total_seats' => $request->total_seats ?? 1,
                    'purchase_cost' => $draftAsset->purchase_cost ?? 0,
                    'expiration_date' => $request->expiration_date ?? $request->warranty_end,
                    'registration_status' => 'REGISTERED',
                    'status' => 'ACTIVE',
                    'remarks' => $request->remarks,
                    'created_by' => auth()->id(),
                ]);

                $draftAsset->forceDelete(); // Hapus draft di opt_assets karena dipindah ke tabel opt_licenses
            } elseif ($request->asset_class === 'CONSUMABLE') {
                $itemCode = 'CNS-' . date('Ym') . '-' . Str::padLeft(Consumable::count() + 1, 6, '0');
                $qty = $request->total_quantity ?? 1;

                Consumable::create([
                    'goods_receipt_item_id' => $draftAsset->goods_receipt_item_id,
                    'item_code' => $itemCode,
                    'item_name' => $draftAsset->asset_name,
                    'category_id' => $request->asset_category_id,
                    'vendor_id' => $draftAsset->vendor_id,
                    'unit_of_measure' => $request->unit_of_measure ?? 'Pcs',
                    'total_quantity' => $qty,
                    'available_quantity' => $qty,
                    'min_stock_alert' => $request->min_stock_alert ?? 5,
                    'unit_price' => $draftAsset->purchase_cost ?? 0,
                    'location_id' => $request->location_id,
                    'registration_status' => 'REGISTERED',
                    'status' => 'IN_STOCK',
                    'remarks' => $request->remarks,
                    'created_by' => auth()->id(),
                ]);

                $draftAsset->forceDelete(); // Hapus draft di opt_assets karena dipindah ke tabel opt_consumables
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil diregistrasikan ke modul ' . strtolower($request->asset_class) . '.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan item: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function storeExisting(Request $request)
    {
        $validated = $request->validate([
            'asset_name' => 'required|string|max:255',
            'asset_class' => 'required|in:FIXED_ASSET,CONSUMABLE,LICENSE',
            'asset_category_id' => ['required', Rule::exists(Category::class, 'id')],
            'asset_type_id' => ['nullable', Rule::exists(Type::class, 'id')],
            'brand_id' => ['nullable', Rule::exists(Brand::class, 'id')],
            'model_id' => 'nullable|integer',
            'serial_number' => 'nullable|string|max:100',
            'vendor_id' => ['nullable', Rule::exists(Vendor::class, 'id')],
            'branch_id' => ['nullable', Rule::exists(Branch::class, 'id')],
            'location_id' => ['nullable', Rule::exists(Location::class, 'id')],
            'department_id' => ['nullable', Rule::exists(Department::class, 'id')],
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'status_id' => ['nullable', Rule::exists(Status::class, 'id')],
            'remarks' => 'nullable|string',
            'assigned_to' => ['nullable', Rule::exists(User::class, 'id')],
            'assigned_date' => 'nullable|date',
            'license_key' => 'nullable|string',
            'license_type' => 'nullable|string',
            'total_seats' => 'nullable|integer|min:1',
            'expiration_date' => 'nullable|date',
            'unit_of_measure' => 'nullable|string',
            'total_quantity' => 'nullable|integer|min:1',
            'min_stock_alert' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $createdData = null;

            if ($validated['asset_class'] === 'FIXED_ASSET') {
                $assetCode = 'AST-' . date('Ym') . '-' . Str::padLeft(Asset::count() + 1, 6, '0');
                $usageStatus = !empty($validated['assigned_to']) ? 'ASSIGNED' : 'AVAILABLE';

                $createdData = Asset::create([
                    'asset_code' => $assetCode,
                    'asset_name' => $validated['asset_name'],
                    'asset_class' => 'FIXED_ASSET',
                    'asset_category_id' => $validated['asset_category_id'],
                    'asset_type_id' => $validated['asset_type_id'] ?? null,
                    'brand_id' => $validated['brand_id'] ?? null,
                    'model_id' => $validated['model_id'] ?? null,
                    'serial_number' => $validated['serial_number'] ?? null,
                    'vendor_id' => $validated['vendor_id'] ?? null,

                    'branch_id' => $validated['branch_id'] ?? null,
                    'location_id' => $validated['location_id'] ?? null,
                    'department_id' => $validated['department_id'] ?? null,

                    'purchase_date' => $validated['purchase_date'] ?? null,
                    'purchase_cost' => $validated['purchase_cost'] ?? 0,
                    'status_id' => $validated['status_id'] ?? null,
                    'usage_status' => $usageStatus,
                    'assigned_to' => $validated['assigned_to'] ?? null,
                    'remarks' => $validated['remarks'] ?? 'Aset Eksisting (Migrasi)',

                    'registration_status' => 'REGISTERED',
                    'goods_receipt_item_id' => null,
                ]);

                if (!empty($validated['assigned_to'])) {
                    $createdData->assignments()->create([
                        'assigned_to' => $validated['assigned_to'],
                        'assigned_date' => $validated['assigned_date'] ?? now(),
                        'status' => 'ACTIVE',
                        'remarks' => 'Initial assignment from manual registration',
                        'created_by' => auth()->id(),
                    ]);
                }
            } elseif ($validated['asset_class'] === 'LICENSE') {
                $licenseCode = 'LIC-' . date('Ym') . '-' . Str::padLeft(License::count() + 1, 6, '0');

                $createdData = License::create([
                    'license_code' => $licenseCode,
                    'software_name' => $validated['asset_name'],
                    'category_id' => $validated['asset_category_id'],
                    'vendor_id' => $validated['vendor_id'] ?? null,
                    'license_key' => $request->license_key ?? $validated['serial_number'] ?? null,
                    'license_type' => $request->license_type ?? 'SUBSCRIPTION',
                    'total_seats' => $request->total_seats ?? 1,
                    'purchase_date' => $validated['purchase_date'] ?? null,
                    'purchase_cost' => $validated['purchase_cost'] ?? 0,
                    'expiration_date' => $request->expiration_date ?? null,
                    'registration_status' => 'REGISTERED',
                    'status' => 'ACTIVE',
                    'remarks' => $validated['remarks'] ?? 'Lisensi Eksisting (Migrasi)',
                    'created_by' => auth()->id(),
                ]);

                if (!empty($validated['assigned_to'])) {
                    $createdData->assignments()->create([
                        'assigned_to' => $validated['assigned_to'],
                        'assigned_date' => $validated['assigned_date'] ?? now(),
                        'status' => 'ACTIVE',
                        'remarks' => 'Initial seat assignment from manual registration',
                        'created_by' => auth()->id(),
                    ]);
                }
            } elseif ($validated['asset_class'] === 'CONSUMABLE') {
                $itemCode = 'CNS-' . date('Ym') . '-' . Str::padLeft(Consumable::count() + 1, 6, '0');
                $qty = $request->total_quantity ?? 1;

                $createdData = Consumable::create([
                    'item_code' => $itemCode,
                    'item_name' => $validated['asset_name'],
                    'category_id' => $validated['asset_category_id'],
                    'vendor_id' => $validated['vendor_id'] ?? null,
                    'unit_of_measure' => $request->unit_of_measure ?? 'Pcs',
                    'total_quantity' => $qty,
                    'available_quantity' => $qty,
                    'min_stock_alert' => $request->min_stock_alert ?? 5,
                    'unit_price' => $validated['purchase_cost'] ?? 0,
                    'location_id' => $validated['location_id'] ?? null,
                    'registration_status' => 'REGISTERED',
                    'status' => 'IN_STOCK',
                    'remarks' => $validated['remarks'] ?? 'Consumable Eksisting (Migrasi)',
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data ' . strtolower($validated['asset_class']) . ' berhasil didaftarkan!',
                'data' => $createdData,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan aset: ' . $e->getMessage(),
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
                'branchs' => Branch::orderBy('name')->get(),
                'vendors' => Vendor::orderBy('name')->get(),
                'departments' => Department::orderBy('name')->get(['id', 'name']),
                'employees' => User::orderBy('name')->get(['id', 'name', 'email']),
                'usage_statuses' => [
                    ['id' => 'AVAILABLE', 'name' => 'Available'],
                    ['id' => 'ASSIGNED', 'name' => 'Assigned'],
                    ['id' => 'MAINTENANCE', 'name' => 'Maintenance'],
                ],
            ],
        ]);
    }
}
