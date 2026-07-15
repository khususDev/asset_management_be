<?php

namespace Tests\Unit\Services;

use App\Models\Administration\User;
use App\Services\Authorization\RoleAccessService;
use App\Services\Operation\PurchaseRequestService;
use PHPUnit\Framework\TestCase;

class RoleAndPurchaseRequestServiceTest extends TestCase
{
    public function test_it_resolves_role_names_and_permissions_for_user(): void
    {
        $user = new User();
        $user->forceFill(['id' => 1, 'role_id' => 7]);
        $user->setRelation('role', (object) [
            'name' => 'Manager',
            'permissions' => collect([(object) ['name' => 'approve']]),
        ]);
        $user->setRelation('roles', collect([(object) ['id' => 8, 'name' => 'Finance']]));

        $service = new RoleAccessService();
        $result = $service->resolveForAuth($user);

        $this->assertSame([7, 8], $result['role_ids']);
        $this->assertSame(['Manager', 'Finance'], $result['role_names']);
        $this->assertSame(['approve'], $result['permission_names']);
    }

    public function test_it_calculates_total_amount_from_items(): void
    {
        $service = new PurchaseRequestService();

        $total = $service->calculateTotalAmount([
            ['quantity' => 2, 'unit_price' => 5000],
            ['quantity' => 3, 'unit_price' => 1000],
        ]);

        $this->assertSame(13000.0, $total);
    }
}
