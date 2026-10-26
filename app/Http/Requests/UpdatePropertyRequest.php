<?php

namespace App\Http\Requests;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
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
        $routeProperty = $this->route('property') ?? $this->route('id');
        $id = $routeProperty instanceof Property
            ? $routeProperty->getKey()
            : $routeProperty;

        return [
            'agent_id' => ['sometimes', 'required', 'exists:agents,id'],
            'property_code' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('properties', 'property_code')->ignore($id),
            ],
            'property_type' => ['sometimes', 'required', 'string', 'max:255'],
            'listing_price' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }
}
