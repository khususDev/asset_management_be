<?php

namespace App\Http\Controllers\Api\Operation\AssetOperation;

use App\Helpers\DocNumberHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequestRequest;
use App\Http\Requests\UpdatePurchaseRequestRequest;
use App\Models\Administration\Organization\Department;
use App\Models\Approvals\WorkflowApproval;
use App\Models\Operation\AssetOperation\PurchaseRequest;
use App\Models\Operation\AssetOperation\PurchaseRequestItem;
use App\Services\Operation\PurchaseRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Administration\User;

class PurchaseRequestController extends Controller
{
    public function __construct(private readonly PurchaseRequestService $purchaseRequestService)
    {
    }

    public function store(StorePurchaseRequestRequest $request)
    {

        $department = Department::find($request->department_id);

        $docNumber = DocNumberHelper::generate(
            'PR',
            $department->code
        );

        DB::beginTransaction();
        try {
            $purchaseRequest = PurchaseRequest::create([
                'request_number' => $docNumber,
                'user_id' => $request->user()->id,
                'department_id' => $request->department_id,
                'purpose' => $request->purpose,
                'approval_method' => $request->approval_method,
                'total_estimated_amount' => 0,
                'status' => 'PENDING'
            ]);

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $subTotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $subTotal;

                PurchaseRequestItem::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'item_description' => $item['item_description'],
                    'quantity' => $item['quantity'],
                    'uom_id' => $item['uom_id'] ?? null,
                    'unit_price' => $item['unit_price'],
                    'total_amount' => $subTotal,
                    'need_to_issue_po' => $item['need_to_issue_po'] ?? true,
                    'vendor_id' => $item['vendor_id'] ?? null,
                    'is_pkp' => $item['is_pkp'] ?? false,
                    'pic_contact' => $item['pic_contact'] ?? null,
                    'url' => $item['url'] ?? null,
                    'price_include_ppn' => $item['price_include_ppn'] ?? false,
                    'payment_term_id' => $item['payment_term_id'] ?? null,
                    'expected_arrival_date' => $item['expected_arrival_date'] ?? null,
                    'delivery_id' => $item['delivery_id'] ?? null,
                    'item_purpose' => $item['item_purpose'] ?? null,
                ]);
            }

            $purchaseRequest->update(['total_estimated_amount' => $this->purchaseRequestService->calculateTotalAmount($request->items)]);

            if ($purchaseRequest->approval_method === 'SYSTEM') {
                // 1. Ambil SATU konfigurasi yang aktif untuk modul tersebut
                $activeSetting = DB::table('wfl_approval_settings')
                    ->where('module', 'Purchase Request')
                    ->where('is_active', 1)
                    ->where('department_id', $request->department_id) // <--- TAMBAHKAN FILTER INI
                    ->first();

                if (!$activeSetting) {
                    throw new \Exception("Alur Approval untuk Departemen '{$request->department}' belum dikonfigurasi.");
                }

                // 2. Ambil semua level berdasarkan ID header yang didapat tadi
                $approvalSettings = DB::table('wfl_approval_setting_levels')
                    ->where('wfl_approval_setting_id', $activeSetting->id)
                    ->orderBy('level', 'asc')
                    ->get();

                // 3. Loop seperti biasa
                foreach ($approvalSettings as $setting) {
                    $userId = $setting->user_id;
                    $user = User::find($userId);

                    // ... (logic validasi role/user Anda tetap di sini)

                    WorkflowApproval::create([
                        'approvable_type' => get_class($purchaseRequest),
                        'approvable_id' => $purchaseRequest->id,
                        'level' => $setting->level,
                        'role_id' => $user->roles->first()?->id,
                        'user_id' => $userId,
                        'status' => 'PENDING',
                        'note' => null
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'data' => $purchaseRequest->load('items')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(), // Tambahkan ini
                'trace' => $e->getTraceAsString() // Opsional: untuk melihat letak baris error
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;
        $search = $request->search ?? '';

        $query = PurchaseRequest::with(['user', 'department']);

        if ($search) {
            $query->where('request_number', 'like', "%{$search}%")
                ->orWhere('department', 'like', "%{$search}%");
        }

        return response()->json(['success' => true, 'data' => $query->latest()->paginate($entries)]);
    }

    // ... fungsi store() tetap sama seperti sebelumnya ...

    // 1. AMBIL DATA DETAIL UNTUK VIEW / EDIT
    public function show($id)
    {
        try {

            $pr = PurchaseRequest::with([
                'user',
                'items.uom',
                'items.vendor',
                'items.paymentTerm',
                'items.deliveryBranch',
                'workflowApprovals.role',
                'workflowApprovals.approver'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $pr
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    // 2. FUNGSI UPDATE DATA (Hanya jika status PENDING)
    public function update(UpdatePurchaseRequestRequest $request, $id)
    {
        $pr = PurchaseRequest::find($id);

        if (!$pr) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        if ($pr->status !== 'PENDING') {
            return response()->json(['success' => false, 'message' => 'Dokumen sudah dikunci, tidak bisa diedit.'], 422);
        }

        DB::beginTransaction();
        try {
            // Update Header
            $pr->update([
                'department' => $request->department,
                'purpose' => $request->purpose,
            ]);

            // Hapus item lama, kita insert ulang yang baru (Metode paling aman untuk Header-Detail)
            PurchaseRequestItem::where('purchase_request_id', $pr->id)->delete();

            $totalAmount = 0;
            foreach ($request->items as $item) {
                $subTotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $subTotal;

                PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->id,
                    'item_description' => $item['item_description'],
                    'quantity' => $item['quantity'],
                    'uom_id' => $item['uom_id'] ?? null,
                    'unit_price' => $item['unit_price'],
                    'total_amount' => $subTotal,
                    'need_to_issue_po' => $item['need_to_issue_po'] ?? true,
                    'vendor_id' => $item['vendor_id'] ?? null,
                    'is_pkp' => $item['is_pkp'] ?? false,
                    'pic_contact' => $item['pic_contact'] ?? null,
                    'url' => $item['url'] ?? null,
                    'price_include_ppn' => $item['price_include_ppn'] ?? false,
                    'payment_term_id' => $item['payment_term_id'] ?? null,
                    'expected_arrival_date' => $item['expected_arrival_date'] ?? null,
                    'delivery_id' => $item['delivery_id'] ?? null,
                    'item_purpose' => $item['item_purpose'] ?? null,
                ]);
            }

            // Update Grand Total
            $pr->update(['total_estimated_amount' => $this->purchaseRequestService->calculateTotalAmount($request->items)]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false], 500);
        }
    }

    // 3. FUNGSI DELETE (Hanya jika status PENDING)
    public function destroy($id)
    {
        $pr = PurchaseRequest::find($id);

        if (!$pr) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        if ($pr->status !== 'PENDING') {
            return response()->json(['success' => false, 'message' => 'Tidak bisa menghapus dokumen yang sudah diproses.'], 422);
        }

        // Karena di migration kita pakai cascade, detail item akan otomatis ikut terhapus
        $pr->delete();

        return response()->json(['success' => true]);
    }

    public function manualApprove($id)
    {
        $pr = PurchaseRequest::findOrFail($id);

        if ($pr->approval_method !== 'MANUAL') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen bukan approval manual.'
            ], 422);
        }

        if ($pr->status !== 'PENDING') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen sudah diproses.'
            ], 422);
        }

        $pr->update([
            'status' => 'APPROVED',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Purchase Request berhasil diapprove.'
        ]);
    }

    public function markApproved($id)
    {
        $pr = PurchaseRequest::find($id);

        if (!$pr) {
            return response()->json([
                'success' => false,
                'message' => 'PR tidak ditemukan.'
            ], 404);
        }

        if ($pr->approval_method != 'MANUAL') {
            return response()->json([
                'success' => false,
                'message' => 'Bukan manual approval.'
            ], 400);
        }

        if ($pr->status != 'PENDING') {
            return response()->json([
                'success' => false,
                'message' => 'PR sudah diproses.'
            ], 400);
        }

        if ($pr->user_id != auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya creator yang boleh approve.'
            ], 403);
        }

        $pr->status = 'APPROVED';
        $pr->save();

        return response()->json([
            'success' => true,
            'message' => 'PR berhasil di approve.'
        ]);
    }

    // 4. MESIN CETAK PDF (Format Mengikuti Gambar Excel Anda)
    // FUNGSI CETAK PDF WORKFLOW DYNAMIC & WATERMARK ENTERPRISE
    public function printPdf($id)
    {
        // 1. Tarik data PR lengkap beserta susunan Workflow riil dari database Anda
        $pr = PurchaseRequest::with([
            'user',
            'items.uom',
            'items.vendor',
            'items.paymentTerm',
            'items.deliveryLocation',
            'workflowApprovals.role',     // Membawa nama jabatan (Manager, dsb)
            'workflowApprovals.approver' // Membawa nama asli atasan yang mengeklik
        ])->findOrFail($id);

        // 2. Logika Warna Watermark Dinamis Berbasis Status Dokumen
        $watermarkColor = 'rgba(108, 117, 125, 0.08)'; // Default Abu-abu untuk PENDING
        if ($pr->status === 'APPROVED') {
            $watermarkColor = 'rgba(40, 167, 69, 0.12)'; // Hijau Lembut
        } elseif ($pr->status === 'REJECTED') {
            $watermarkColor = 'rgba(220, 53, 69, 0.12)'; // Merah Lembut
        } elseif ($pr->status === 'PARTIAL_APPROVED') {
            $watermarkColor = 'rgba(0, 123, 255, 0.12)'; // Biru Lembut
        }

        // Ubah teks underscore status menjadi spasi bersih (misal: PARTIAL_APPROVED -> PARTIAL APPROVED)
        $watermarkText = str_replace('_', ' ', $pr->status);

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print PR - ' . $pr->request_number . '</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 11px; color: #333; margin: 20px; position: relative; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .bold { font-weight: bold; }
                
                /* 🔥 CSS WATERMARK BACKGROUND LAYER */
                .watermark {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%) rotate(-35deg);
                    font-size: 70px;
                    font-weight: 900;
                    color: ' . $watermarkColor . ';
                    z-index: -1000;
                    white-space: nowrap;
                    letter-spacing: 6px;
                    pointer-events: none;
                    text-transform: uppercase;
                }

                .title { font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; }
                
                .header-table, .main-table, .footer-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                .header-table td { border: 1px solid #000; padding: 6px; vertical-align: top; }
                
                .main-table th { background-color: #002060; color: #fff; border: 1px solid #000; padding: 6px; font-size: 11px; text-transform: uppercase; }
                .main-table td { border: 1px solid #000; padding: 5px; vertical-align: top; }
                
                .sub-info-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
                .sub-info-table td { border: none !important; padding: 2px 4px !important; font-size: 10px; }
                .bg-pink { background-color: #FFC0CB; }
                
                /* STYLE FOOTER DYNAMIC GRID */
                .footer-table th { background-color: #EAEAEA; border: 1px solid #000; padding: 5px; font-size: 10px; text-transform: uppercase; }
                .footer-table td { border: 1px solid #000; padding: 10px 5px; height: 70px; text-align: center; vertical-align: bottom; }
                
                .stamp-approved {
                    color: #28a745;
                    border: 2px solid #28a745;
                    display: inline-block;
                    padding: 3px 6px;
                    font-weight: bold;
                    font-size: 9px;
                    text-transform: uppercase;
                    transform: rotate(-4deg);
                    border-radius: 4px;
                    margin-bottom: 8px;
                    background-color: rgba(40, 167, 69, 0.03);
                    letter-spacing: 0.5px;
                }
                .stamp-rejected {
                    color: #dc3545;
                    border: 2px solid #dc3545;
                    display: inline-block;
                    padding: 3px 6px;
                    font-weight: bold;
                    font-size: 9px;
                    text-transform: uppercase;
                    transform: rotate(-4deg);
                    border-radius: 4px;
                    margin-bottom: 8px;
                    background-color: rgba(220, 53, 69, 0.03);
                    letter-spacing: 0.5px;
                }

                @media print {
                    @page { size: A4; margin: 1cm; }
                    body { margin: 0; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="watermark">' . $watermarkText . '</div>

            <div class="no-print" style="margin-bottom: 15px; background: #FFF3CD; padding: 10px; border: 1px solid #FFEBAA; border-radius: 4px;">
                <button onclick="window.print()" style="padding: 6px 12px; background: #002060; color: #fff; border: none; border-radius: 3px; cursor: pointer; font-weight: bold;">🖨️ Cetak Sekarang / Simpan PDF</button>
                <span style="margin-left: 10px; color: #666;">Gunakan layout <b>Portrait</b> pada pengaturan cetak browser.</span>
            </div>

            <div class="title">Purchase Request</div>

            <table class="header-table">
                <tr>
                    <td style="width: 15%;">Date</td>
                    <td style="width: 35%;" class="bold">' . $pr->created_at->format('d-M-y') . '</td>
                    <td style="width: 15%;">PR No.</td>
                    <td style="width: 35%;" class="bold">' . $pr->request_number . '</td>
                </tr>
                <tr>
                    <td>Prepared by</td>
                    <td class="bold">' . ($pr->user->name ?? '-') . '</td>
                    <td rowspan="2">Purpose</td>
                    <td rowspan="2" class="bold" style="white-space: pre-line;">' . $pr->purpose . '</td>
                </tr>
                <tr>
                    <td>Requested by<br><span style="font-size:8px;color:#777;">(name & signature)</span></td>
                    <td class="bold" style="vertical-align: bottom; text-align: center;">
                        <div style="font-size: 9px; color: #28a745; font-weight: bold; margin-bottom: 2px;">✓ SUBMITTED DIGITAL</div>
                        <div style="font-size: 11px;">' . ($pr->user->name ?? '-') . '</div>
                    </td>
                </tr>
            </table>

            <table class="main-table">
                <thead>
                    <tr>
                        <th style="width: 4%;">No.</th>
                        <th style="width: 56%;">Description</th>
                        <th style="width: 6%;">Qty</th>
                        <th style="width: 6%;">Unit</th>
                        <th style="width: 14%;">Unit Price (IDR)</th>
                        <th style="width: 14%;">Amount (IDR)</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($pr->items as $index => $item) {
            $html .= '
                    <tr>
                        <td class="text-center bold">' . ($index + 1) . '</td>
                        <td>
                            <div class="bold" style="font-size:11px; margin-bottom: 4px;">' . $item->item_description . '</div>
                            <table class="sub-info-table">
                                <tr>
                                    <td style="width: 30%; color: #555;">Need to issue PO</td>
                                    <td class="bold ' . ($item->need_to_issue_po ? '' : 'bg-pink') . '">' . ($item->need_to_issue_po ? 'YES' : 'NO') . '</td>
                                </tr>
                                <tr>
                                    <td style="color: #555;">Vendor Name</td>
                                    <td class="bold">' . ($item->vendor->name ?? '-') . '</td>
                                </tr>
                                <tr>
                                    <td style="color: #555;">Vendor PIC / Contact</td>
                                    <td>' . $item->pic_contact . '</td>
                                </tr>
                                <tr>
                                    <td style="color: #555;">URL</td>
                                    <td><a href="' . $item->url . '" target="_blank" style="color: blue; text-decoration: none;">' . $item->url . '</a></td>
                                </tr>
                                <tr>
                                    <td style="color: #555;">Price Include PPN</td>
                                    <td class="bold ' . ($item->price_include_ppn ? '' : 'bg-pink') . '">' . ($item->price_include_ppn ? 'YES' : 'NO') . '</td>
                                </tr>
                                <tr>
                                    <td style="color: #555;">TOP (Term of Payment)</td>
                                    <td>' . ($item->paymentTerm->name ?? '-') . '</td>
                                </tr>
                                <tr>
                                    <td style="color: #555;">Expected Arrival Date</td>
                                    <td class="bold">' . ($item->expected_arrival_date ? date('d M Y', strtotime($item->expected_arrival_date)) : '-') . '</td>
                                </tr>
                                <tr>
                                    <td style="color: #555;">Delivery Address</td>
                                    <td>' . ($item->deliveryLocation->name ?? '-') . '</td>
                                </tr>
                                <tr>
                                    <td style="color: #555;">Purpose per Item</td>
                                    <td style="color: #444; font-style: italic;">' . $item->item_purpose . '</td>
                                </tr>
                            </table>
                        </td>
                        <td class="text-center bold" style="vertical-align: middle;">' . $item->quantity . '</td>
                        <td class="text-center" style="vertical-align: middle;">' . ($item->uom->name ?? 'pcs') . '</td>
                        <td class="text-right" style="vertical-align: middle;">' . number_format($item->unit_price, 0, ',', '.') . '</td>
                        <td class="text-right bold" style="vertical-align: middle;">' . number_format($item->total_amount, 0, ',', '.') . '</td>
                    </tr>';
        }

        $html .= '
                    <tr style="background-color: #002060; color: #fff;">
                        <td colspan="5" class="text-right bold" style="padding: 8px; border-color: #000;">TOTAL (IDR)</td>
                        <td class="text-right bold" style="padding: 8px; border-color: #000; font-size: 12px;">' . number_format($pr->total_estimated_amount, 0, ',', '.') . '</td>
                    </tr>
                </tbody>
            </table>';

        $approvals = $pr->workflowApprovals;
        $totalCols = count($approvals);

        // Kalkulasi pembagi lebar kolom otomatis (Contoh: jika 3 level, maka masing-masing 33.3%)
        $colWidth = $totalCols > 0 ? (100 / $totalCols) : 100;

        if ($totalCols > 0) {
            $html .= '<table class="footer-table"><thead><tr>';

            // Loop 1: Membentuk Label Header Jabatan Dinamis
            foreach ($approvals as $wfl) {
                $html .= '<th style="width: ' . $colWidth . '%;">' . ($wfl->role->name ?? 'APPROVER') . '</th>';
            }

            $html .= '</tr></thead><tbody><tr>';

            // Loop 2: Membentuk Kotak Isi Stempel Tanda Tangan Dinamis
            foreach ($approvals as $wfl) {
                $html .= '<td>';

                if ($wfl->status === 'APPROVED') {
                    // Tampilkan stempel hijau jika sudah disetujui
                    $html .= '<div class="stamp-approved">APPROVED DIGITAL</div>';
                    $html .= '<div class="bold" style="font-size:11px;">' . ($wfl->approver->name ?? 'N/A') . '</div>';
                    $html .= '<div style="font-size: 8px; color: #666; margin-top: 2px;">Date: ' . date('d M Y H:i', strtotime($wfl->action_date)) . '</div>';
                } elseif ($wfl->status === 'REJECTED') {
                    // Tampilkan stempel merah jika ditolak
                    $html .= '<div class="stamp-rejected">REJECTED</div>';
                    $html .= '<div class="bold" style="font-size:11px;">' . ($wfl->approver->name ?? 'N/A') . '</div>';
                    $html .= '<div style="font-size: 8px; color: #666; margin-top: 2px;">Date: ' . date('d M Y H:i', strtotime($wfl->action_date)) . '</div>';
                } else {
                    // Kosongkan secara elegan jika masih antrean pending
                    $html .= '<div style="font-size: 9px; color: #bbb; font-style: italic; margin-bottom: 20px;">[ Waiting Approval ]</div>';
                    $html .= '<div style="height: 12px;"></div>'; // Penjaga tinggi agar kotak tetap simetris
                }

                $html .= '</td>';
            }

            $html .= '</tr></tbody></table>';
        }

        $html .= '</body></html>';

        return response($html);
    }

    public function getFormMasters()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'uoms' => \App\Models\Administration\Procurement\Uom::all(),
                    'vendors' => \App\Models\Administration\Procurement\Vendor::all(),
                    'payments' => \App\Models\Administration\Procurement\PaymentTerm::all(),
                    'branchs' => \App\Models\Administration\Organization\Branch::all(),
                    'departments' => \App\Models\Administration\Organization\Department::all(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil master data dropdown: ' . $e->getMessage()
            ], 500);
        }
    }


}
