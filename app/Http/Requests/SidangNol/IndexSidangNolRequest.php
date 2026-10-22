<?php

namespace App\Http\Requests\SidangNol;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexSidangNolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'integer', Rule::in([0, 1, 2, 3])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array{search: string|null, status: int|null, per_page: int}
     */
    public function filters(): array
    {
        $search = trim((string) $this->validated('search', ''));

        return [
            'search' => $search === '' ? null : $search,
            'status' => $this->filled('status') ? (int) $this->validated('status') : null,
            'per_page' => (int) $this->validated('per_page', 10),
        ];
    }
}
