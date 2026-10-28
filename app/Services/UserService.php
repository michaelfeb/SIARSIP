<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Return a filtered, paginated list of users for the selected audience.
     *
     * @param  array{audience: 'pegawai'|'mahasiswa', search: string|null, per_page: int}  $filters
     */
    public function datatable(array $filters): LengthAwarePaginator
    {
        return User::query()
            ->with('role')
            ->when(
                $filters['audience'] === 'mahasiswa',
                fn (Builder $query) => $query->where('role_id', 1),
                fn (Builder $query) => $query->where('role_id', '!=', 1),
            )
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nama', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate($filters['per_page'])
            ->withQueryString();
    }

    /**
     * Create a user account.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        return User::query()->create($this->prepareUserData($data));
    }

    /**
     * Update an existing user account.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        $user->update($this->prepareUserData($data));

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function resetPassword(User $user): void
    {
        $user->update(['password' => Hash::make('Password1234')]);
    }

    /** @param array<string, mixed> $data */
    private function prepareUserData(array $data): array
    {
        $data['program_studi'] = (int) $data['role_id'] === 1
            ? $data['program_studi']
            : null;

        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
