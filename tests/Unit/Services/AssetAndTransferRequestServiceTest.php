<?php

namespace Tests\Unit\Services;

use App\Models\Operation\AssetOperation\AssetRequest;
use App\Models\Operation\AssetOperation\TransferRequest;
use App\Services\Operation\AssetRequestService;
use App\Services\Operation\TransferRequestService;
use PHPUnit\Framework\TestCase;

class AssetAndTransferRequestServiceTest extends TestCase
{
    public function test_asset_request_service_can_build_payload(): void
    {
        $service = new AssetRequestService(
            static fn(array $payload): AssetRequest => new AssetRequest($payload),
            static fn(string $prefix): string => 'AR-001'
        );
        $request = new class {
            public function user()
            {
                return new class {
                    public $id = 1;
                };
            }
            public $asset_name = 'Laptop';
            public $quantity = 2;
            public $reason = 'Need for team';
            public $needed_date = '2026-07-01';
        };

        $model = $service->createFromRequest($request);

        $this->assertSame('Laptop', $model->asset_name);
        $this->assertSame(2, $model->quantity);
    }

    public function test_transfer_request_service_can_build_payload(): void
    {
        $service = new TransferRequestService(
            static fn(array $payload): TransferRequest => new TransferRequest($payload),
            static fn(string $prefix): string => 'TR-001'
        );
        $request = new class {
            public function user()
            {
                return new class {
                    public $id = 1;
                };
            }
            public $asset_name = 'Monitor';
            public $origin_location = 'Warehouse A';
            public $destination_location = 'Warehouse B';
            public $reason = 'Relocation';
        };

        $model = $service->createFromRequest($request);

        $this->assertSame('Monitor', $model->asset_name);
        $this->assertSame('Warehouse A', $model->origin_location);
    }
}
