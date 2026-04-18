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
        $uuid = $this->route('product');

        return [
            'name'          => ['sometimes', 'string', 'max:255'],
            'slug'          => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                // Unique ignoring: the current product (by uuid) and soft-deleted records
                Rule::unique('products', 'slug')
                    ->where(fn($q) => $q->whereNull('deleted_at'))
                    ->whereNot('uuid', $uuid),
            ],
            'price'         => ['sometimes', 'numeric', 'min:0'],
            'description'   => ['sometimes', 'nullable', 'string'],
            'category_uuid' => ['sometimes', 'string', 'exists:categories,uuid'],
            'tag_uuids'     => ['sometimes', 'nullable', 'array'],
            'tag_uuids.*'   => ['string', 'exists:tags,uuid'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('price')) {
            $this->merge(['price' => (float) $this->price]);
        }
    }
}
