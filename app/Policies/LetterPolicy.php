<?php

namespace App\Policies;

use App\Models\Letter;
use App\Models\User;

class LetterPolicy
{
    public function view(User $user, Letter $letter): bool
    {
        if ($this->roleName($user) === 'MAHASISWA') {
            return $letter->applicant_id === $user->id;
        }

        if ($this->roleName($user) === 'SUPER ADMIN') {
            return true;
        }

        $role = $this->roleName($user);

        if ($letter->status === 'ARCHIVED' && $role === 'RESEPSIONIS') {
            return true;
        }

        if ($letter->status !== 'IN_PROGRESS' || $letter->current_stage !== $role) {
            return false;
        }

        return $role !== 'PRODI'
            || (string) $user->program_studi === (string) ($letter->meta['program_studi_id'] ?? '');
    }

    public function update(User $user, Letter $letter): bool
    {
        return $this->roleName($user) === 'MAHASISWA'
            && $letter->applicant_id === $user->id
            && $letter->status === 'DRAFT';
    }

    public function delete(User $user, Letter $letter): bool
    {
        return $this->update($user, $letter);
    }

    private function roleName(User $user): ?string
    {
        return $user->relationLoaded('role') ? $user->role?->nama : $user->role()->value('nama');
    }
}
