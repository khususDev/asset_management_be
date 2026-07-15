<?php

namespace App\Services\Approval;

class WorkflowApprovalService
{
    public function resolveDocumentStatus(bool $hasNextApproval): string
    {
        return $hasNextApproval ? 'PARTIAL_APPROVED' : 'APPROVED';
    }
}
