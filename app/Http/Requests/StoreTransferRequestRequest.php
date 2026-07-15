<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_name' => 'required|string|max:255',
            'origin_location' => 'required|string|max:255',
            'destination_location' => 'required|string|max:255',
            'reason' => 'required|string',
        ];
    }
}
