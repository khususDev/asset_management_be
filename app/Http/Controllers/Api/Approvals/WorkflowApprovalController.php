<?php

namespace App\Http\Controllers\Api\Approvals;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveWorkflowRequest;
use App\Http\Requests\RejectWorkflowRequest;
use App\Models\Approvals\WorkflowApproval;
use App\Services\Approval\WorkflowApprovalService;
use App\Models\Operation\Procurement\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkflowApprovalController extends Controller
{
    public function __construct(private readonly WorkflowApprovalService $workflowApprovalService) {}

    public function index(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json(['success' => false, 'message' => 'User tidak terautentikasi.'], 401);
    }

    $entries = $request->entries ?? 10;
    $search = $request->search ?? '';

    // Ambil nama tabel secara dinamis dari model WorkflowApproval
    $tableName = (new WorkflowApproval)->getTable(); 

    $query = WorkflowApproval::with([
            'approvable.user',
            'approvable.items.uom',
            'approvable.workflowApprovals.role',
            'approvable.workflowApprovals.approver',
            'approvable.department'
    ])
    ->where('status', 'PENDING')
    ->where('user_id', $user->id)
    ->whereNotExists(function ($subQuery) use ($tableName) {
        // Menggunakan nama tabel dinamis
        $subQuery->select(DB::raw(1))
            ->from($tableName, 'wfl_sub')
            ->whereColumn('wfl_sub.approvable_type', "{$tableName}.approvable_type")
            ->whereColumn('wfl_sub.approvable_id', "{$tableName}.approvable_id")
            ->whereColumn('wfl_sub.level', '<', "{$tableName}.level")
            ->where('wfl_sub.status', 'PENDING');
    });

    // Fitur Pencarian
    if ($search) {
        $query->whereHasMorph('approvable', [PurchaseRequest::class], function ($q) use ($search) {
            $q->where('request_number', 'like', "%{$search}%");
        });
    }

    $approvals = $query->latest()->paginate($entries);

    return response()->json([
        'success' => true,
        'data' => $approvals
    ]);
}

    public function approve(ApproveWorkflowRequest $request, $id)
    {

        $approval = WorkflowApproval::find($id);
        if (!$approval) {
            return response()->json(['success' => false, 'message' => 'Data antrean approval tidak ditemukan.'], 404);
        }

        if ($approval->status !== 'PENDING') {
            return response()->json(['success' => false, 'message' => 'Antrean ini sudah diproses sebelumnya.'], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Update baris antrean approval saat ini
            $approval->update([
                'user_id' => $request->user()->id,
                'status' => 'APPROVED',
                'note' => $request->note,
                'action_date' => now()
            ]);

            // 2. Cek apakah ada level di atasnya lagi untuk dokumen yang sama
            $nextApproval = WorkflowApproval::where('approvable_type', $approval->approvable_type)
                ->where('approvable_id', $approval->approvable_id)
                ->where('level', '>', $approval->level)
                ->orderBy('level', 'asc')
                ->first();

            // Ambil dokumen induknya secara dinamis (bisa model PurchaseRequest / PurchaseOrder)
            $document = $approval->approvable;

            $document->update(['status' => $this->workflowApprovalService->resolveDocumentStatus((bool) $nextApproval)]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false], 500);
        }
    }

    // FUNGSI UNTUK PROSES REJECT / PENOLAKAN
    public function reject(RejectWorkflowRequest $request, $id)
    {

        $approval = WorkflowApproval::find($id);
        if (!$approval) {
            return response()->json(['success' => false, 'message' => 'Data antrean tidak ditemukan.'], 404);
        }

        DB::beginTransaction();
        try {
            // 1. Update status antrean saat ini menjadi REJECTED
            $approval->update([
                'user_id' => $request->user()->id,
                'status' => 'REJECTED',
                'note' => $request->note,
                'action_date' => now()
            ]);

            // 2. Langsung kunci dokumen induk menjadi REJECTED (Gugur, tidak lanjut ke level atasnya)
            $document = $approval->approvable;
            $document->update(['status' => 'REJECTED']);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false], 500);
        }
    }
}