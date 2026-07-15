<?php

namespace App\Services\Operation;

use App\Helpers\DocNumberHelper;
use App\Models\Operation\AssetOperation\TransferRequest;

class TransferRequestService
{
    public function __construct(
        private readonly ?\Closure $persist = null,
        private readonly ?\Closure $documentNumberGenerator = null,
    ) {
    }

    public function createFromRequest($request): TransferRequest
    {
        $payload = [
            'request_number' => $this->generateDocumentNumber('TR'),
            'user_id' => $request->user()->id,
            'asset_name' => $request->asset_name,
            'origin_location' => $request->origin_location,
            'destination_location' => $request->destination_location,
            'reason' => $request->reason,
            'status' => 'PENDING',
        ];

        $persist = $this->persist ?? static fn(array $values): TransferRequest => TransferRequest::create($values);

        return $persist($payload);
    }

    private function generateDocumentNumber(string $prefix): string
    {
        $generator = $this->documentNumberGenerator ?? static fn(string $value): string => DocNumberHelper::generate($value);

        return $generator($prefix);
    }
}
