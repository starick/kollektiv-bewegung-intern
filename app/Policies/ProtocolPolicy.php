<?php

namespace App\Policies;

use App\Models\Protocol;
use App\Models\User;

class ProtocolPolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Protocol $protocol): bool
    {
        return $protocol->uploaded_by === $user->id || $user->isSuperAdmin();
    }
}
