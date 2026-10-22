<?php

namespace App\Services;

use App\Models\BerkasSidangNol;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SidangNolService
{
    /**
     * @param  array{search?: string|null, status?: int|null, per_page?: int}  $filters
     */
    public function datatable(User $user, array $filters): LengthAwarePaginator
    {
        $status = $filters['status'] ?? null;
        $search = $filters['search'] ?? null;
        $perPage = $filters['per_page'] ?? 10;

        return BerkasSidangNol::query()
            ->with('user')
            ->where(function ($query) use ($user) {
                if ($user->role_id === 1) {
                    $query->where('user_id', $user->id);

                    return;
                }

                $query->where('status', '>=', 1);
            })
            ->when($search, function ($query, string $search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('nama', 'like', "%{$search}%")
                            ->orWhere('nim', 'like', "%{$search}%")
                            ->orWhere('program_studi', 'like', "%{$search}%");
                    })
                        ->orWhere('nomor_surat', 'like', "%{$search}%");
                });
            })
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->orderByRaw('CASE WHEN status = 1 THEN 0 ELSE 1 END ASC')
            ->orderByDesc('tanggal_dikirim')
            ->paginate($perPage)
            ->withQueryString();
    }
}
