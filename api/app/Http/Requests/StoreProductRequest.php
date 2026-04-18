<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:255'],
            'price'         => ['required', 'numeric', 'min:0'],
            'description'   => ['nullable', 'string'],
            'category_uuid' => ['required', 'string', 'exists:categories,uuid'],
            'tag_uuids'     => ['nullable', 'array'],
            'tag_uuids.*'   => ['string', 'exists:tags,uuid'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Ensure price is cast to float for consistent validation
        if ($this->has('price')) {
            $this->merge(['price' => (float) $this->price]);
        }
    }
}
