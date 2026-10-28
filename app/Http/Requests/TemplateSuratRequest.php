<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TemplateSuratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        if ($this->isMethod('get')) {
            return ['search' => ['nullable', 'string', 'max:255'], 'per_page' => ['nullable', 'integer', 'min:5', 'max:100']];
        }

        return [
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],
            'tanggal_publish' => ['required', 'date'],
            'dokumen_path' => [Rule::requiredIf($this->isMethod('post')), 'nullable', 'file', 'mimes:doc,docx', 'max:2048'],
        ];
    }

    public function filters(): array
    {
        $data = $this->validated();

        return ['search' => $data['search'] ?? null, 'per_page' => $data['per_page'] ?? 10];
    }
}
