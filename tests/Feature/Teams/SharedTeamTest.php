<?php

namespace Tests\Feature\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

class SharedTeamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }
    }

    private function register(string $email): User
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);
        $this->post('/logout');

        return User::where('email', $email)->sole();
    }

    public function test_first_user_creates_and_owns_the_shared_team(): void
    {
        $user = $this->register('first@example.com');

        $team = Team::shared();
        $this->assertNotNull($team);
        $this->assertFalse($team->personal_team);
        $this->assertTrue($user->ownsTeam($team));
        $this->assertSame($team->id, $user->current_team_id);
    }

    public function test_further_users_join_the_shared_team_as_editor(): void
    {
        $this->register('first@example.com');
        $user = $this->register('second@example.com');

        $team = Team::shared();
        $this->assertSame(1, Team::count());
        $this->assertNull($user->personalTeam());
        $this->assertTrue($user->belongsToTeam($team));
        $this->assertSame('editor', $user->teamRole($team)->key);
        $this->assertSame($team->id, $user->current_team_id);
    }

    public function test_owner_and_administrators_are_super_admins(): void
    {
        $owner = $this->register('owner@example.com');
        $admin = $this->register('admin@example.com');
        $editor = $this->register('editor@example.com');
        Team::shared()->users()->updateExistingPivot($admin->id, ['role' => 'admin']);

        $this->assertTrue($owner->isSuperAdmin());
        $this->assertTrue($admin->fresh()->isSuperAdmin());
        $this->assertFalse($editor->isSuperAdmin());
    }
}
