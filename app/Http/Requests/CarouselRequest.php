<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarouselRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        if ($this->isMethod('get')) {
            return [
                'search' => ['nullable', 'string', 'max:255'],
                'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
            ];
        }

        return [
            'nama' => ['required', 'string', 'max:255'],
            'tanggal_publish' => ['required', 'date'],
            'status' => ['required', 'boolean'],
            'gambar' => [
                Rule::requiredIf($this->isMethod('post')),
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:5120',
            ],
        ];
    }

    /** @return array{search: string|null, per_page: int} */
    public function filters(): array
    {
        $data = $this->validated();

        return [
            'search' => $data['search'] ?? null,
            'per_page' => $data['per_page'] ?? 10,
        ];
    }
}
