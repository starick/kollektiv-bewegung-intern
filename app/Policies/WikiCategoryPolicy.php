<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WikiCategory;

/**
 * Every team member files pages, so every team member may change the tree.
 */
class WikiCategoryPolicy
{
    public function create(User $user): bool
    {
        return $user->belongsToSharedTeam();
    }

    public function update(User $user, WikiCategory $category): bool
    {
        return $user->belongsToSharedTeam();
    }

    /**
     * Whether the category is empty is checked separately, so the user gets a message instead of a 403.
     */
    public function delete(User $user, WikiCategory $category): bool
    {
        return $user->belongsToSharedTeam();
    }
}
