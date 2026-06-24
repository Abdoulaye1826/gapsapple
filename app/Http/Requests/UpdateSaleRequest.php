<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'product_id' => ['required', 'array', 'min:1'],
            'product_id.*' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'array', 'min:1'],
            'quantity.*' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'array', 'min:1'],
            'unit_price.*' => ['required', 'numeric', 'min:0'],
            'sale_type' => ['required', 'in:vente,echange'],
            'exchange_product_id' => ['nullable', 'exists:products,id'],
            'exchange_quantity' => ['exclude_unless:sale_type,echange', 'required', 'integer', 'min:1'],
            'exchange_product_name' => ['exclude_unless:sale_type,echange', 'required_without:exchange_product_id', 'string'],
            'exchange_product_reference' => ['exclude_unless:sale_type,echange', 'nullable', 'string'],
            'exchange_product_brand' => ['exclude_unless:sale_type,echange', 'nullable', 'string', 'max:100'],
            'exchange_product_description' => ['exclude_unless:sale_type,echange', 'nullable', 'string'],
            'exchange_category_id' => ['exclude_unless:sale_type,echange', 'required_without:exchange_product_id', 'exists:categories,id'],
            'exchange_product_condition' => ['nullable', 'in:neuf,tres_bon_etat,bon_etat,defectueux'],
            'exchange_product_estimated_value' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,validated,cancelled'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sale_type' => $this->filled('sale_type') ? $this->input('sale_type') : 'vente',
            'discount_amount' => $this->filled('discount_amount') ? $this->input('discount_amount') : 0,
            'exchange_quantity' => $this->filled('exchange_quantity') ? $this->input('exchange_quantity') : 1,
        ]);
    }
}
