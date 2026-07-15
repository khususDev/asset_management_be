<?php

namespace Tests\Unit\Services;

use App\Services\Approval\WorkflowApprovalService;
use PHPUnit\Framework\TestCase;

class WorkflowApprovalServiceTest extends TestCase
{
    public function test_it_sets_document_status_to_approved_when_no_next_approval_level_exists(): void
    {
        $service = new WorkflowApprovalService();

        $this->assertSame('APPROVED', $service->resolveDocumentStatus(false));
    }

    public function test_it_sets_document_status_to_partial_approved_when_next_level_exists(): void
    {
        $service = new WorkflowApprovalService();

        $this->assertSame('PARTIAL_APPROVED', $service->resolveDocumentStatus(true));
    }
}
