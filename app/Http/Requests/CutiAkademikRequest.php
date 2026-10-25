<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class CutiAkademikRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()?->getName()) {
            'cuti-akademik.index' => $this->indexRules(),
            'cuti-akademik.store', 'cuti-akademik.update' => $this->draftRules(),
            'cuti-akademik.decide' => $this->decideRules(),
            'cuti-akademik.finalize' => $this->finalizeRules(),
            default => [],
        };
    }

    /** @return array{search: string|null, status: string|null, per_page: int} */
    public function filters(): array
    {
        $search = trim((string) $this->validated('search', ''));

        return [
            'search' => $search === '' ? null : $search,
            'status' => $this->validated('status'),
            'per_page' => (int) $this->validated('per_page', 10),
        ];
    }

    /** @return array{semester: string, tahun_akademik: string, nomor_hp: string, nominal_ukt: int|float|string, alasan_cuti: string, attachments: array<int, UploadedFile>} */
    public function payload(): array
    {
        $data = $this->validated();

        return [
            'semester' => $data['semester'],
            'tahun_akademik' => $data['tahun_akademik'],
            'nomor_hp' => $data['nomor_hp'],
            'nominal_ukt' => $data['nominal_ukt'],
            'alasan_cuti' => $data['alasan_cuti'],
            'attachments' => $this->file('attachments', []),
        ];
    }

    private function indexRules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', Rule::in(['DRAFT', 'IN_PROGRESS', 'REJECTED', 'ARCHIVED'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    private function draftRules(): array
    {
        return [
            'semester' => ['required', 'string', 'max:20'],
            'tahun_akademik' => ['required', 'string', 'max:20'],
            'nomor_hp' => ['required', 'string', 'max:30'],
            'nominal_ukt' => ['required', 'numeric', 'min:0'],
            'alasan_cuti' => ['required', 'string', 'max:2000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:100'],
        ];
    }

    private function decideRules(): array
    {
        return [
            'decision' => ['required', 'string', Rule::in(['FORWARD', 'REJECT'])],
            'note' => ['nullable', 'string', 'max:2000', 'required_if:decision,REJECT'],
        ];
    }

    private function finalizeRules(): array
    {
        return [
            'letter_number' => ['required', 'string', 'max:64', Rule::unique('letters', 'letter_number')],
            'signatory_id' => ['required', 'integer', Rule::exists(User::class, 'id')],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
