<?php

use App\Http\Controllers\Api\Administration\AppSettingController;
use App\Http\Controllers\Api\Administration\BackupController;
use App\Http\Controllers\Api\Operation\Procurement\PurchaseOrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Administration\UserController;
use App\Http\Controllers\Api\Administration\RolesController;
use App\Http\Controllers\Api\Administration\PermissionController;
use App\Http\Controllers\Api\Administration\SystemLogController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Administration\MediaController;
use App\Http\Controllers\Api\Administration\Organization\BranchController;
use App\Http\Controllers\Api\Administration\Organization\DepartmentController;
use App\Http\Controllers\Api\Administration\Organization\LocationController;
use App\Http\Controllers\Api\Administration\Organization\CostCenterController;
use App\Http\Controllers\Api\Administration\Asset\CategoryController;
use App\Http\Controllers\Api\Administration\Asset\TypeController;
use App\Http\Controllers\Api\Administration\Asset\BrandController;
use App\Http\Controllers\Api\Administration\Asset\AssetModelController;
use App\Http\Controllers\Api\Administration\Asset\StatusController;
use App\Http\Controllers\Api\Administration\License\LicenseMetricController;
use App\Http\Controllers\Api\Administration\License\LicenseTypeController;
use App\Http\Controllers\Api\Administration\Maintenance\MaintenanceTypeController;
use App\Http\Controllers\Api\Administration\Maintenance\ScheduleTypeController;
use App\Http\Controllers\Api\Administration\Procurement\PaymentTermController;
use App\Http\Controllers\Api\Administration\Procurement\TaxController;
use App\Http\Controllers\Api\Administration\Procurement\UomController;
use App\Http\Controllers\Api\Administration\Procurement\VendorController;
use App\Http\Controllers\Api\Administration\Procurement\VendorTypeController;
use App\Http\Controllers\Api\Administration\System\DocumentNumberingController;
use App\Http\Controllers\Api\Administration\System\NotificationSettingController;
use App\Http\Controllers\Api\Administration\Workflow\ApprovalSettingController;
use App\Http\Controllers\Api\Approvals\WorkflowApprovalController;
use App\Http\Controllers\Api\Operation\AssetOperation\AssetRequestController;
use App\Http\Controllers\Api\Operation\AssetOperation\PurchaseRequestController;
use App\Http\Controllers\Api\Operation\AssetOperation\TransferRequestController;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(
    function () {
        Route::post('logout', [AuthController::class, 'logout']);

        Route::apiResource('adm_users', UserController::class);
        Route::apiResource('adm_roles', RolesController::class);
        Route::apiResource('adm_permissions', PermissionController::class);
        Route::apiResource('sys_logs', SystemLogController::class);
        Route::put('adm_profile/update', [ProfileController::class, 'update']);

        Route::post('media/upload', [MediaController::class, 'upload']);

        Route::get('database/backups', [BackupController::class, 'index']);
        Route::post('database/backup', [BackupController::class, 'store']);
        Route::get('database/backup/download/{filename}', [BackupController::class, 'download']);
        Route::delete('database/backup/{filename}', [BackupController::class, 'destroy']);

        Route::get('app_settings', [AppSettingController::class, 'index']);
        Route::put('app_settings', [AppSettingController::class, 'update']);

        ## Organization
        Route::apiResource('org_branch', BranchController::class);
        Route::apiResource('org_department', DepartmentController::class);
        Route::apiResource('org_location', LocationController::class);
        Route::apiResource('org_costcenter', CostCenterController::class);
        ## Asset Master
        Route::apiResource('asm_category', CategoryController::class);
        Route::apiResource('asm_type', TypeController::class);
        Route::apiResource('asm_brand', BrandController::class);
        Route::apiResource('asm_model', AssetModelController::class);
        Route::apiResource('asm_status', StatusController::class);
        ## License Master
        Route::apiResource('lcs_type', LicenseTypeController::class);
        Route::apiResource('lcs_metric', LicenseMetricController::class);
        ## Procurement Master
        Route::apiResource('prc_vendor', VendorController::class);
        Route::apiResource('prc_vendor_type', VendorTypeController::class);
        Route::apiResource('prc_uom', UomController::class);
        Route::apiResource('prc_payment', PaymentTermController::class);
        Route::apiResource('prc_tax', TaxController::class);
        ## Maintenance Master
        Route::apiResource('mtn_schedule', ScheduleTypeController::class);
        Route::apiResource('mtn_type', MaintenanceTypeController::class);
        ## System Document Numbering
        Route::apiResource('sys_docnum', DocumentNumberingController::class);
        ## Workflow Approval Settings
        Route::apiResource('wfl_approval_setting', ApprovalSettingController::class);
        ## Notification Settings
        Route::apiResource('sys_notif_setting', NotificationSettingController::class);

        ## Asset Operation
        Route::apiResource('opt_asset_request', AssetRequestController::class);
        // -- Purchase Request
        Route::post('opt_purchase_request', [PurchaseRequestController::class, 'store']);
        Route::get('opt_purchase_request', [PurchaseRequestController::class, 'index']);
        Route::get('opt_purchase_request_masters', [PurchaseRequestController::class, 'getFormMasters']);
        Route::get('opt_purchase_request/{id}', [PurchaseRequestController::class, 'show']); // <-- INI YANG MEMBACA EDIT/SHOW
        Route::put('opt_purchase_request/{id}', [PurchaseRequestController::class, 'update']); // <-- INI YANG MENYIMPAN EDIT
        Route::delete('opt_purchase_request/{id}', [PurchaseRequestController::class, 'destroy']); // <-- INI UNTUK HAPUS
        Route::get('opt_purchase_request/{id}/print', [PurchaseRequestController::class, 'printPdf']); // <-- INI UNTUK CETAK PDF
        Route::post('opt_purchase_request/{id}/mark-approved', [PurchaseRequestController::class, 'markApproved']);
        Route::post('purchase-request/{id}/manual-approve', [PurchaseRequestController::class, 'manualApprove']);
        // -- Transfer Request
        Route::post('opt_transfer_request', [TransferRequestController::class, 'store']);
        Route::get('opt_transfer_request', [TransferRequestController::class, 'index']);

        ## Approvals
        Route::get('workflow_approval', [WorkflowApprovalController::class, 'index']);
        Route::post('workflow_approval/{id}/approve', [WorkflowApprovalController::class, 'approve']);
        Route::post('workflow_approval/{id}/reject', [WorkflowApprovalController::class, 'reject']);
        ### Procurement PO
        Route::apiResource('opt_purchase_order', PurchaseOrderController::class);
        Route::get('opt_purchase_order/{id}/edit', [PurchaseOrderController::class, 'edit']);
        Route::get('opt_purchase_order/create', [PurchaseOrderController::class, 'create']);
        Route::get('opt_purchase_order/{id}/print', [PurchaseOrderController::class, 'printPdf']);
        Route::post('opt_purchase_order/{id}/send', [PurchaseOrderController::class, 'send']);
    }
);
