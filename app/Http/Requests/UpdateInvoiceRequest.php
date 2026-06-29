<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_id' => ['required', 'exists:sales,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'issued_at' => ['required', 'date'],
            'subtotal_ht' => ['required', 'numeric', 'min:0'],
            'total_ttc' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:issued,partial,paid,cancelled'],
            'pdf_path' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'subtotal_ht' => $this->filled('subtotal_ht') ? $this->input('subtotal_ht') : 0,
            'total_ttc' => $this->filled('total_ttc') ? $this->input('total_ttc') : 0,
        ]);
    }
}
