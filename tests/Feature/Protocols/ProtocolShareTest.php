<?php

namespace Tests\Feature\Protocols;

use App\Models\Protocol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProtocolShareTest extends TestCase
{
    use CreatesTeamMembers;
    use RefreshDatabase;

    public function test_team_member_can_share_a_protocol(): void
    {
        $protocol = Protocol::factory()->create(['updated_at' => '2026-01-01 10:00:00']);

        $this->actingAs($this->teamMember())
            ->post(route('protocols.share', $protocol))
            ->assertRedirect();

        $protocol->refresh();
        $this->assertSame(40, strlen($protocol->share_token));
        // Sharing is not an edit
        $this->assertSame('2026-01-01 10:00:00', $protocol->updated_at->toDateTimeString());
        $this->assertSame(1, $protocol->audits()->count());
    }

    public function test_sharing_again_replaces_the_link(): void
    {
        $protocol = Protocol::factory()->create();
        $protocol->share();
        $oldUrl = $protocol->shareUrl();

        $this->actingAs($this->teamMember())->post(route('protocols.share', $protocol));

        $this->get($oldUrl)->assertNotFound();
        $this->get($protocol->refresh()->shareUrl())->assertOk();
    }

    public function test_team_member_can_stop_sharing(): void
    {
        $protocol = Protocol::factory()->create();
        $protocol->share();
        $url = $protocol->shareUrl();

        $this->actingAs($this->teamMember())
            ->delete(route('protocols.unshare', $protocol))
            ->assertRedirect();

        $this->assertNull($protocol->refresh()->share_token);
        $this->get($url)->assertNotFound();
    }

    public function test_users_outside_the_team_cannot_share(): void
    {
        $this->sharedTeam();
        $protocol = Protocol::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('protocols.share', $protocol))
            ->assertForbidden();

        $this->assertNull($protocol->refresh()->share_token);
    }

    public function test_guests_cannot_share(): void
    {
        $protocol = Protocol::factory()->create();

        $this->post(route('protocols.share', $protocol))->assertRedirect(route('login'));
        $this->assertNull($protocol->refresh()->share_token);
    }

    public function test_show_page_contains_the_share_url(): void
    {
        $protocol = Protocol::factory()->create();
        $user = $this->teamMember();

        $this->actingAs($user)
            ->get(route('protocols.show', $protocol))
            ->assertInertia(fn (Assert $page) => $page->where('shareUrl', null));

        $protocol->share();

        $this->actingAs($user)
            ->get(route('protocols.show', $protocol))
            ->assertInertia(fn (Assert $page) => $page->where('shareUrl', route('protocols.public', $protocol->share_token)));
    }

    public function test_guest_can_read_a_shared_protocol_without_internal_details(): void
    {
        $protocol = Protocol::factory()->edited()->create([
            'date' => '2026-11-02',
            'description' => 'Plenum',
            'topics' => ['Kasse'],
            'participants' => ['Anna'],
            'text' => '## Agenda',
        ]);
        $protocol->share();

        $this->get($protocol->shareUrl())
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Protocols/Public')
                ->where('protocol', [
                    'date' => '2026-11-02',
                    'description' => 'Plenum',
                    'topics' => ['Kasse'],
                    'participants' => ['Anna'],
                ])
                ->where('html', fn (string $html) => str_contains($html, '<h2>Agenda</h2>'))
                ->missing('audits')
                ->missing('shareUrl')
            );
    }

    public function test_unknown_or_unshared_tokens_are_not_found(): void
    {
        Protocol::factory()->create();

        $this->get(route('protocols.public', 'does-not-exist'))->assertNotFound();
    }
}
