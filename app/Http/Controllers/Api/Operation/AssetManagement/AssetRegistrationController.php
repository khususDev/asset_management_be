<?php

namespace App\Http\Controllers\Api\Operation\AssetManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Operation\AssetManagement\Asset;
use Illuminate\Support\Facades\DB;
use App\Models\Administration\Asset\Category;
use App\Models\Administration\Asset\Type;
use App\Models\Administration\Asset\Brand;
use App\Models\Administration\Asset\AssetModel;
use App\Models\Administration\Asset\Status;
use App\Models\Administration\Organization\Branch;
use App\Http\Resources\AssetManagement\AssetRegistrationResource;
use App\Services\AssetManagement\AssetCodeService;
use App\Models\Administration\Organization\Location;

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
    public function register(Request $request, $id)
    {
        $request->validate([
            'serial_number'     => 'nullable|string|max:255',

            'asset_category_id' => 'required|exists:mst_asset_categories,id',
            'asset_type_id'     => 'required|exists:mst_asset_types,id',

            'brand_id'          => 'nullable|exists:mst_asset_brands,id',
            'model_id'          => 'nullable|exists:mst_asset_models,id',

            'status_id'         => 'required|exists:mst_asset_statuses,id',

            'branch_id'         => 'required|exists:mst_branches,id',
            'location_id'       => 'nullable|exists:mst_locations,id',

            'warranty_start'    => 'nullable|date',
            'warranty_end'      => 'nullable|date',

            'useful_life'       => 'nullable|integer',

            'remarks'           => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {

            $asset = Asset::findOrFail($id);

            $assetCode = $this->assetCodeService->generateAssetCode();

            $asset->update([

                'asset_code'         => $assetCode,

                'serial_number'      => $request->serial_number,

                'asset_category_id'  => $request->asset_category_id,
                'asset_type_id'      => $request->asset_type_id,

                'brand_id'           => $request->brand_id,
                'model_id'           => $request->model_id,

                'status_id'          => $request->status_id,

                'branch_id'          => $request->branch_id,
                'location_id'        => $request->location_id,

                'warranty_start'     => $request->warranty_start,
                'warranty_end'       => $request->warranty_end,

                'useful_life'        => $request->useful_life,

                'remarks'            => $request->remarks,

                'registration_status' => 'REGISTERED',

                'qr_code'            => $assetCode,
                'barcode'            => $assetCode,
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
                'types'      => Type::orderBy('name')->get(),
                'brands'     => Brand::orderBy('name')->get(),
                'models'     => AssetModel::orderBy('name')->get(),
                'statuses'   => Status::orderBy('name')->get(),
                'branchs'    => Branch::orderBy('name')->get(),
                'locations'  => Location::orderBy('name')->get(),
                'asset_code' => $this->assetCodeService->generateAssetCode(),
            ]
        ]);
    }
}
