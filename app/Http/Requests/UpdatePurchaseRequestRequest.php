<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'department_id' => 'required',

            'purpose' => 'required',

            'items' => 'required|array|min:1',

            'items.*.item_description' => 'required',

            'items.*.quantity' => 'required|numeric|min:1',

            'items.*.unit_price' => 'required|numeric|min:0',

            'items.*.asset_class' =>
            'required|in:FIXED_ASSET,CONSUMABLE,LICENSE,SERVICE',

            'items.*.vendor_name' =>
            'nullable|string|max:255',

            'items.*.vendor_id' => [
                'nullable',
                'required_if:items.*.need_to_issue_po,1'
            ],

            'items.*.payment_term_id' => [
                'nullable',
                'required_if:items.*.need_to_issue_po,1'
            ],

            'items.*.expected_arrival_date' => [
                'nullable',
                'required_if:items.*.need_to_issue_po,1'
            ],

            'items.*.delivery_branch_id' => [
                'nullable',
                'required_if:items.*.need_to_issue_po,1'
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            foreach ($this->items ?? [] as $index => $item) {

                if (!empty($item['need_to_issue_po'])) {

                    if (empty($item['vendor_id'])) {

                        $validator->errors()->add(
                            "items.$index.vendor_id",
                            "Vendor wajib diisi."
                        );
                    }

                    if (empty($item['payment_term_id'])) {

                        $validator->errors()->add(
                            "items.$index.payment_term_id",
                            "Payment Term wajib diisi."
                        );
                    }

                    if (empty($item['expected_arrival_date'])) {

                        $validator->errors()->add(
                            "items.$index.expected_arrival_date",
                            "Expected Arrival wajib diisi."
                        );
                    }

                    if (empty($item['delivery_branch_id'])) {

                        $validator->errors()->add(
                            "items.$index.delivery_branch_id",
                            "Delivery Branch wajib diisi."
                        );
                    }
                }
            }
        });
    }
}
