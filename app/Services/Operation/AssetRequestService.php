<?php

namespace App\Services\Operation;

use App\Helpers\DocNumberHelper;
use App\Models\Operation\AssetOperation\AssetRequest;

class AssetRequestService
{
    public function __construct(
        private readonly ?\Closure $persist = null,
        private readonly ?\Closure $documentNumberGenerator = null,
    ) {
    }

    public function createFromRequest($request): AssetRequest
    {
        $payload = [
            'request_number' => $this->generateDocumentNumber('AR'),
            'user_id' => $request->user()->id,
            'asset_name' => $request->asset_name,
            'quantity' => $request->quantity,
            'reason' => $request->reason,
            'needed_date' => $request->needed_date,
            'status' => 'PENDING',
        ];

        $persist = $this->persist ?? static fn(array $values): AssetRequest => AssetRequest::create($values);

        return $persist($payload);
    }

    private function generateDocumentNumber(string $prefix): string
    {
        $generator = $this->documentNumberGenerator ?? static fn(string $value): string => DocNumberHelper::generate($value);

        return $generator($prefix);
    }
}
