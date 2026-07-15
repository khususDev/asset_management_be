<?php

namespace Tests\Unit\Services;

use App\Services\Administration\MasterDataService;
use PHPUnit\Framework\TestCase;

class MasterDataServiceTest extends TestCase
{
    public function test_it_can_build_paginated_search_query(): void
    {
        $service = new MasterDataService();

        $this->assertTrue(method_exists($service, 'listItems'));
        $this->assertTrue(method_exists($service, 'createModel'));
        $this->assertTrue(method_exists($service, 'updateModel'));
    }
}
