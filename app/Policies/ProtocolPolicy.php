<?php

namespace App\Policies;

use App\Models\Protocol;
use App\Models\User;

class ProtocolPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->belongsToSharedTeam();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Protocol $protocol): bool
    {
        return $user->belongsToSharedTeam();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->belongsToSharedTeam();
    }

    /**
     * Determine whether the user can update the model.
     * Every team member may edit, so protocols can be corrected collaboratively.
     */
    public function update(User $user, Protocol $protocol): bool
    {
        return $user->belongsToSharedTeam();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Protocol $protocol): bool
    {
        if (! $user->belongsToSharedTeam()) {
            return false;
        }

        return $protocol->uploaded_by === $user->id || $user->isSuperAdmin();
    }
}
