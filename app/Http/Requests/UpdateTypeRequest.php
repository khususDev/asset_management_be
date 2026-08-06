<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $typeId = $this->route('asm_type');

        return [
            'asset_category_id' => 'required|exists:mst_asset_category,id',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('mst_asset_type', 'code')->ignore($typeId)
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}
