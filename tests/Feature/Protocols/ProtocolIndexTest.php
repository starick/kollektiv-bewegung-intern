<?php

namespace Tests\Feature\Protocols;

use App\Models\Protocol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProtocolIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('protocols.index'))->assertRedirect(route('login'));
    }

    public function test_protocols_are_listed_newest_first(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        Protocol::factory()->create(['date' => '2026-01-10']);
        Protocol::factory()->create(['date' => '2026-03-05']);

        $this->actingAs($user)
            ->get(route('protocols.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Protocols/Index')
                ->has('protocols.data', 2)
                ->where('protocols.data.0.date', '2026-03-05')
                ->where('protocols.data.1.date', '2026-01-10')
            );
    }

    public function test_description_is_listed(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        Protocol::factory()->create(['description' => 'Plenum November']);

        $this->actingAs($user)
            ->get(route('protocols.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('protocols.data.0.description', 'Plenum November')
            );
    }

    public function test_deleted_uploader_is_listed_as_null(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $protocol = Protocol::factory()->create();
        $protocol->uploader->delete();

        $this->actingAs($user)
            ->get(route('protocols.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('protocols.data.0.uploader', null)
            );
    }
}
