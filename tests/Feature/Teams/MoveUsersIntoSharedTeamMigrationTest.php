<?php

namespace Tests\Feature\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoveUsersIntoSharedTeamMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function runMigration(): void
    {
        $migration = require database_path('migrations/2026_11_02_160000_move_users_into_shared_team.php');
        $migration->up();
    }

    public function test_oldest_team_becomes_shared_team_with_all_users(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $other = User::factory()->withPersonalTeam()->create();
        $teamless = User::factory()->create();
        $sharedTeam = $owner->ownedTeams()->sole();

        $this->runMigration();

        $this->assertSame($sharedTeam->id, Team::shared()->id);
        $this->assertTrue($owner->fresh()->ownsTeam($sharedTeam));
        foreach ([$other, $teamless] as $user) {
            $user = $user->fresh();
            $this->assertTrue($user->belongsToTeam($sharedTeam));
            $this->assertSame('editor', $user->teamRole($sharedTeam)->key);
            $this->assertSame($sharedTeam->id, $user->current_team_id);
        }
    }

    public function test_existing_member_roles_are_kept(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $admin = User::factory()->create();
        $sharedTeam = $owner->ownedTeams()->sole();
        $sharedTeam->users()->attach($admin, ['role' => 'admin']);

        $this->runMigration();

        $this->assertSame('admin', $admin->fresh()->teamRole($sharedTeam)->key);
        $this->assertSame(1, $sharedTeam->users()->where('user_id', $admin->id)->count());
    }

    public function test_does_nothing_without_teams(): void
    {
        User::factory()->create();

        $this->runMigration();

        $this->assertNull(Team::shared());
    }
}
