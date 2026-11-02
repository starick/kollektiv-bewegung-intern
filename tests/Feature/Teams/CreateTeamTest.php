<?php

namespace Tests\Feature\Teams;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_teams_cannot_be_created(): void
    {
        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        $this->post('/teams', [
            'name' => 'Test Team',
        ])->assertForbidden();

        $this->assertCount(1, $user->fresh()->ownedTeams);
    }
}
