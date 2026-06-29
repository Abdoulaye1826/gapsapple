<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'reference' => [
                'required', 'string', 'max:50',
                Rule::unique('products', 'reference')->ignore($product),
            ],
            'barcode' => [
                'nullable', 'string', 'max:50',
                Rule::unique('products', 'barcode')->ignore($product),
            ],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:100'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'supplier_sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_image' => ['boolean'],
            'is_active' => ['boolean'],
            'tracks_imei' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $tracksImei = $this->boolean('tracks_imei');
        $product = $this->route('product');

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'remove_image' => $this->boolean('remove_image'),
            'tracks_imei' => $tracksImei,
            // Le stock d'un produit suivi par IMEI n'est jamais saisi
            // manuellement : on conserve la valeur déjà synchronisée avec
            // les IMEI enregistrés plutôt que d'accepter une saisie libre.
            'stock_quantity' => $tracksImei ? $product?->stock_quantity ?? 0 : $this->input('stock_quantity'),
        ]);
    }
}
