<?php

namespace Tests\Feature\Protocols;

use App\Models\Team;
use App\Models\User;

trait CreatesTeamMembers
{
    protected function sharedTeam(): Team
    {
        return Team::shared() ?? Team::factory()->create(['personal_team' => false]);
    }

    protected function teamMember(string $role = 'editor'): User
    {
        $team = $this->sharedTeam();
        $user = User::factory()->create();
        $team->users()->attach($user, ['role' => $role]);
        $user->forceFill(['current_team_id' => $team->id])->save();

        return $user;
    }
}
