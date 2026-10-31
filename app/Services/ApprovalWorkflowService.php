<?php

namespace App\Services;

use App\Models\Letter;
use App\Models\LetterType;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApprovalWorkflowService
{
    /** @return array{letterTypes: array<int, array<string, mixed>>, roles: array<int, string>} */
    public function settings(): array
    {
        return [
            'letterTypes' => LetterType::query()
                ->withCount(['letters as active_letters_count' => fn($query) => $query->where('status', 'IN_PROGRESS')])
                ->orderBy('name')
                ->get()
                ->map(fn(LetterType $letterType) => $this->displayData($letterType))
                ->values()
                ->all(),
            'roles' => Role::query()->orderBy('nama')->pluck('nama')->values()->all(),
        ];
    }

    /** @param array{approval_chain: array<int, string>, notes_markdown: string|null} $data */
    public function update(LetterType $letterType, array $data): LetterType
    {
        return DB::transaction(function () use ($letterType, $data): LetterType {
            $lockedType = LetterType::query()->lockForUpdate()->findOrFail($letterType->id);
            $activeLetters = Letter::query()
                ->where('type_id', $lockedType->id)
                ->where('status', 'IN_PROGRESS')
                ->lockForUpdate()
                ->count();

            if ($activeLetters > 0) {
                throw ValidationException::withMessages([
                    'approval_chain' => sprintf(
                        'Ada %d surat sedang dalam proses persetujuan. Perubahan belum bisa dilakukan saat ada proses berlangsung.',
                        $activeLetters,
                    ),
                ]);
            }

            $this->ensureValidApprovalChain($lockedType, $data['approval_chain']);

            $lockedType->update([
                'approval_chain' => $data['approval_chain'],
                'notes_markdown' => $data['notes_markdown'],
            ]);

            return $lockedType->refresh();
        });
    }

    /** @return array<string, mixed> */
    public function displayData(LetterType $letterType): array
    {
        $notesMarkdown = $letterType->notes_markdown;

        return [
            'id' => $letterType->id,
            'code' => $letterType->code,
            'name' => $letterType->name,
            'approvalChain' => $letterType->approval_chain ?? [],
            'notesMarkdown' => $notesMarkdown,
            'notesHtml' => $notesMarkdown === null
                ? ''
                : Str::markdown($notesMarkdown, ['html_input' => 'strip', 'allow_unsafe_links' => false]),
            'activeLettersCount' => (int) ($letterType->active_letters_count ?? 0),
        ];
    }

    /** @param array<int, string> $chain */
    private function ensureValidApprovalChain(LetterType $letterType, array $chain): void
    {
        $existingChain = $letterType->approval_chain ?? [];
        $existingTerminalStage = $existingChain[array_key_last($existingChain)] ?? null;
        $proposedTerminalStage = $chain[array_key_last($chain)] ?? null;

        if ($existingTerminalStage === 'RESEPSIONIS' && $proposedTerminalStage !== 'RESEPSIONIS') {
            throw ValidationException::withMessages([
                'approval_chain' => 'RESEPSIONIS yang berada pada tahap akhir tidak dapat dipindahkan atau dihapus.',
            ]);
        }

        if ($letterType->code !== 'CUTI_AKADEMIK') {
            return;
        }

        if ($proposedTerminalStage !== 'RESEPSIONIS' || count($chain) < 2) {
            throw ValidationException::withMessages([
                'approval_chain' => 'Alur Cuti Akademik harus berakhir di RESEPSIONIS dan memiliki satu tahap penandatangan sebelumnya.',
            ]);
        }
    }
}
