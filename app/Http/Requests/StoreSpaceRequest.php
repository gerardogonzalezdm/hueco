<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['nullable', 'required_if:fixed_duration,1', 'integer', 'min:5', 'max:1440'],
            'fixed_duration' => ['boolean'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'show_price' => ['boolean'],
            'show_duration' => ['boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'fixed_duration' => $this->boolean('fixed_duration', true),
            'show_price' => $this->boolean('show_price', true),
            'show_duration' => $this->boolean('show_duration', true),
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (is_array($data) && ! isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(4));
        }

        return $data;
    }
}
