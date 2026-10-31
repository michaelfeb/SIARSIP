<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApprovalWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return match ($this->route()?->getName()) {
            'approval-workflows.update' => [
                'approval_chain' => ['required', 'array', 'min:1', 'max:30'],
                'approval_chain.*' => ['required', 'string', 'max:100', 'distinct:strict', Rule::exists('roles', 'nama')],
                'notes_markdown' => ['nullable', 'string', 'max:30000'],
            ],
            default => [],
        };
    }

    /** @return array{approval_chain: array<int, string>, notes_markdown: string|null} */
    public function payload(): array
    {
        $data = $this->validated();

        return [
            'approval_chain' => array_values($data['approval_chain']),
            'notes_markdown' => isset($data['notes_markdown']) && trim($data['notes_markdown']) !== ''
                ? $data['notes_markdown']
                : null,
        ];
    }
}
