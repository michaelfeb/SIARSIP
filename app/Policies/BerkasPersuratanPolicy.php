<?php

namespace App\Policies;

use App\Models\BerkasPersuratan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BerkasPersuratanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BerkasPersuratan $berkasPersuratan): bool
    {
        return $user->role_id !== 1 || $user->id === $berkasPersuratan->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BerkasPersuratan $berkasPersuratan): bool
    {
        return $user->role_id !== 1 || $user->id === $berkasPersuratan->user_id || $berkasPersuratan == null;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BerkasPersuratan $berkasPersuratan): bool
    {
        return $user->role_id !== 1 || $user->id === $berkasPersuratan->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BerkasPersuratan $berkasPersuratan): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BerkasPersuratan $berkasPersuratan): bool
    {
        return false;
    }
}
