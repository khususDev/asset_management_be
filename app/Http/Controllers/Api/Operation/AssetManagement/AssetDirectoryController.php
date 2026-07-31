<?php

namespace App\Http\Controllers\Api\Operation\AssetManagement;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetManagement\AssetDirectoryResource;
use App\Models\Operation\AssetManagement\Asset;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Http\Request;
use App\Models\Administration\Asset\Category;
use App\Models\Administration\Asset\Type;
use App\Models\Administration\Asset\Brand;
use App\Models\Administration\Asset\Status;
use App\Models\Administration\Organization\Branch;
use App\Models\Administration\Organization\Location;
use App\Models\Administration\Procurement\Vendor;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AssetDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with([
            'category',
            'type',
            'brand',
            'model',
            'status',
            'vendor',
            'department',
            'branch',
            'location',
        ])
            ->where('registration_status', 'REGISTERED')
            ->where('asset_class', 'FIXED_ASSET');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asset_code', 'ILIKE', "%{$search}%")
                    ->orWhere('asset_name', 'ILIKE', "%{$search}%")
                    ->orWhere('serial_number', 'ILIKE', "%{$search}%");
            });
        }
        if ($request->filled('category')) {
            $query->where('asset_category_id', $request->category);
        }
        if ($request->filled('type')) {
            $query->where('asset_type_id', $request->type);
        }
        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }
        if ($request->filled('status')) {
            $query->where('status_id', $request->status);
        }
        if ($request->filled('usage')) {
            $query->where('usage_status', $request->usage);
        }
        if ($request->filled('branch')) {
            $query->where('branch_id', $request->branch);
        }
        if ($request->filled('location')) {
            $query->where('location_id', $request->location);
        }
        if ($request->filled('vendor')) {
            $query->where('vendor_id', $request->vendor);
        }

        $assets = $query
            ->latest()
            ->paginate(
                $request->entries ?? 10
            )
            ->withQueryString();

        $statistics = [
            'total' => Asset::where('asset_class', 'FIXED_ASSET')->count(),
            'available' => Asset::where('asset_class', 'FIXED_ASSET')->where('usage_status', 'AVAILABLE')->count(),
            'assigned' => Asset::where('asset_class', 'FIXED_ASSET')->where('usage_status', 'ASSIGNED')->count(),
            'maintenance' => Asset::where('asset_class', 'FIXED_ASSET')->where('usage_status', 'MAINTENANCE')->count(),
            'disposed' => Asset::where('asset_class', 'FIXED_ASSET')->where('usage_status', 'DISPOSED')->count(),
        ];

        // Ekstrak data resource beserta paginasinya
        $resource = AssetDirectoryResource::collection($assets)->response()->getData(true);

        return response()->json([
            'success' => true,
            'statistics' => $statistics,
            'data' => $resource['data'],
            // Tambahkan data paginasi ini agar terbaca oleh Vue
            'links' => $resource['meta']['links'] ?? [],
            'from' => $resource['meta']['from'] ?? 0,
            'to' => $resource['meta']['to'] ?? 0,
            'total' => $resource['meta']['total'] ?? 0,
        ]);
    }

    public function printLabel(Request $request)
    {
        // 1. Base query disamakan persis dengan fungsi index()
        $query = Asset::with(['category', 'type', 'brand', 'model', 'status', 'vendor', 'department', 'branch', 'location'])
            ->where('registration_status', 'REGISTERED')
            ->where('asset_class', 'FIXED_ASSET');

        // 2. Jika pengguna memilih ID secara spesifik lewat checkbox
        if ($request->filled('selected_ids') && is_array($request->selected_ids) && count(array_filter($request->selected_ids)) > 0) {
            $query->whereIn('id', $request->selected_ids);
        }
        // 3. Jika cetak massal, terapkan seluruh filter yang ada di halaman index
        else {
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('asset_code', 'ILIKE', "%{$search}%")
                        ->orWhere('asset_name', 'ILIKE', "%{$search}%")
                        ->orWhere('serial_number', 'ILIKE', "%{$search}%");
                });
            }

            if ($request->filled('category')) {
                $query->where('asset_category_id', $request->category);
            }

            if ($request->filled('type')) {
                $query->where('asset_type_id', $request->type);
            }

            if ($request->filled('brand')) {
                $query->where('brand_id', $request->brand);
            }

            if ($request->filled('status')) {
                $query->where('status_id', $request->status);
            }

            if ($request->filled('usage')) {
                $query->where('usage_status', $request->usage);
            }

            if ($request->filled('branch')) {
                $query->where('branch_id', $request->branch);
            }

            if ($request->filled('location')) {
                $query->where('location_id', $request->location);
            }

            if ($request->filled('vendor')) {
                $query->where('vendor_id', $request->vendor);
            }
        }

        $assets = $query->latest()->get();

        if ($assets->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data asset untuk dicetak.'], 404);
        }

        // Process Barcode & QR Code
        $barcodeGenerator = new BarcodeGeneratorPNG();

        foreach ($assets as $item) {
            $code = $item->asset_code ?? $item->barcode ?? $item->qr_code ?? 'NO-CODE';

            $item->barcode_base64 = base64_encode(
                $barcodeGenerator->getBarcode($code, $barcodeGenerator::TYPE_CODE_128, 1, 28)
            );

            $qrSvg = QrCode::format('svg')->size(60)->margin(0)->generate($code);
            $item->qrcode_base64 = base64_encode($qrSvg);
        }

        $paperSize = $request->input('paper_size', 'A4');

        if ($paperSize === 'THERMAL') {
            $pdf = Pdf::loadView('pdf.asset-label-thermal', compact('assets'))
                ->setPaper([0, 0, 141.73, 70.86], 'portrait');
        } else {
            $pdf = Pdf::loadView('pdf.asset-label-a4', compact('assets'))
                ->setPaper('a4', 'portrait');
        }

        return $pdf->stream('asset-labels.pdf');
    }

    // public function printLabel(Request $request)
    // {
    //     $query = Asset::query();

    //     if ($request->category) {
    //         $query->where('category_id', $request->category);
    //     }

    //     if ($request->usage) {
    //         $query->where('usage_status', $request->usage);
    //     }

    //     if ($request->search) {
    //         $query->whereHas('asset', function ($q) use ($request) {
    //             $q->where('code', 'ilike', "%{$request->search}%")
    //                 ->orWhere('name', 'ilike', "%{$request->search}%");
    //         });
    //     }

    //     $assets = $query
    //         ->with('asset')
    //         ->orderBy('id')
    //         ->get();

    //     $pdf = Pdf::loadView(
    //         'pdf.asset-label',
    //         compact('assets')
    //     )->setPaper('a4');

    //     return $pdf->stream('asset-label.pdf');
    // }

    public function masters()
    {
        return response()->json([

            'success' => true,

            'data' => [

                'categories' => Category::orderBy('name')->get([
                    'id',
                    'name',
                ]),

                'types' => Type::orderBy('name')->get([
                    'id',
                    'name',
                ]),

                'brands' => Brand::orderBy('name')->get([
                    'id',
                    'name',
                ]),

                'statuses' => Status::orderBy('name')->get([
                    'id',
                    'name',
                    'color',
                ]),

                'branches' => Branch::orderBy('name')->get([
                    'id',
                    'name',
                ]),

                'locations' => Location::orderBy('name')->get([
                    'id',
                    'name',
                ]),

                'vendors' => Vendor::orderBy('name')->get([
                    'id',
                    'name',
                ]),

                'usage_statuses' => [

                    [
                        'id' => 'AVAILABLE',
                        'name' => 'Available',
                    ],

                    [
                        'id' => 'ASSIGNED',
                        'name' => 'Assigned',
                    ],

                    [
                        'id' => 'MAINTENANCE',
                        'name' => 'Maintenance',
                    ],

                    [
                        'id' => 'DISPOSED',
                        'name' => 'Disposed',
                    ],

                ],

            ],

        ]);
    }

    public function show($id)
    {
        $asset = Asset::with([
            'category',
            'type',
            'brand',
            'model',
            'status',
            'vendor',
            'department',
            'branch',
            'location',
            'goodsReceiptItem.goodsReceipt.purchaseOrder.purchaseRequest',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new AssetDirectoryResource($asset),
        ]);
    }
}
