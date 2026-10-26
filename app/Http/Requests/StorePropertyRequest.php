<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('property_code'))) {
            $this->merge([
                'property_code' => strtoupper(trim($this->input('property_code'))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'agent_id' => ['required', 'exists:agents,id'],
            'property_code' => [
                'required',
                'string',
                'max:255',
                'unique:properties,property_code',
            ],
            'property_type' => ['required', 'string', 'max:255'],
            'listing_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
