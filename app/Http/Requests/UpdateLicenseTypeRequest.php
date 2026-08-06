<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLicenseTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $licenseTypeId = $this->route('lcs_type');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                // Ganti 'mst_license_types' dengan nama tabel license type Anda di database
                Rule::unique('mst_license_types', 'code')->ignore($licenseTypeId)
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}
