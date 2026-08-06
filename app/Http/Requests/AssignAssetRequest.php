<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AssignAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'assigned_type'    => 'required|in:user,department,location',
            'assigned_to_id'   => 'required|integer',
            'assigned_date'    => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'condition'        => 'required|in:GOOD,FAIR,NEEDS_REPAIR',
            'notes'            => 'nullable|string|max:500',
        ];
    }
}
