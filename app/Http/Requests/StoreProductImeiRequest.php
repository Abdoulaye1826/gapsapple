<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductImeiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'imeis' => ['required', 'array', 'min:1'],
            'imeis.*' => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'imeis.required' => 'Veuillez saisir au moins un IMEI.',
        ];
    }
}
