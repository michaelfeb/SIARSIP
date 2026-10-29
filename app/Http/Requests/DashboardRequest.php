<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'letter_type' => ['nullable', 'string', Rule::in(['all', 'sidang-nol', 'cuti-akademik'])],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ];
    }

    /** @return array{letter_type: string, month: int|null, year: int|null} */
    public function filters(): array
    {
        return [
            'letter_type' => $this->validated('letter_type', 'all'),
            'month' => $this->validated('month'),
            'year' => $this->validated('year'),
        ];
    }
}
