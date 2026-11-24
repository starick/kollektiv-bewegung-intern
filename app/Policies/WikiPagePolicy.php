<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WikiPage;

class WikiPagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->belongsToSharedTeam();
    }

    public function view(User $user, WikiPage $page): bool
    {
        return $user->belongsToSharedTeam();
    }

    public function create(User $user): bool
    {
        return $user->belongsToSharedTeam();
    }

    /**
     * Every team member may edit, so guides stay current without waiting for their author.
     */
    public function update(User $user, WikiPage $page): bool
    {
        return $user->belongsToSharedTeam();
    }

    public function delete(User $user, WikiPage $page): bool
    {
        if (! $user->belongsToSharedTeam()) {
            return false;
        }

        return $page->created_by === $user->id || $user->isSuperAdmin();
    }
}
