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
        $protocol = Protocol::factory()->create(['date' => '2026-11-02', 'updated_at' => '2026-01-01 10:00:00']);

        $this->actingAs($this->teamMember())
            ->post(route('protocols.share', $protocol))
            ->assertRedirect();

        $protocol->refresh();
        $this->assertMatchesRegularExpression('/^2026-11-02-[A-Za-z0-9]{32}$/', $protocol->share_token);
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

    public function test_link_keeps_working_when_the_date_changes(): void
    {
        $protocol = Protocol::factory()->create(['date' => '2026-11-02']);
        $protocol->share();
        $url = $protocol->shareUrl();

        $protocol->update(['date' => '2026-11-09']);

        $this->assertSame($url, $protocol->refresh()->shareUrl());
        $this->get($url)->assertOk();
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

    public function test_logged_in_team_members_get_a_link_to_the_internal_page(): void
    {
        $protocol = Protocol::factory()->create();
        $protocol->share();

        $this->actingAs($this->teamMember())
            ->get($protocol->shareUrl())
            ->assertInertia(fn (Assert $page) => $page->where('internalUrl', route('protocols.show', $protocol)));
    }

    public function test_guests_and_outsiders_get_no_link_to_the_internal_page(): void
    {
        $this->sharedTeam();
        $protocol = Protocol::factory()->create();
        $protocol->share();

        $this->get($protocol->shareUrl())
            ->assertInertia(fn (Assert $page) => $page->where('internalUrl', null));

        $this->actingAs(User::factory()->create())
            ->get($protocol->shareUrl())
            ->assertInertia(fn (Assert $page) => $page->where('internalUrl', null));
    }

    public function test_guests_get_a_login_link_that_leads_to_the_internal_page(): void
    {
        $protocol = Protocol::factory()->create();
        $protocol->share();
        $loginUrl = route('login', ['shared' => $protocol->share_token]);

        $this->get($protocol->shareUrl())
            ->assertInertia(fn (Assert $page) => $page->where('loginUrl', $loginUrl));

        $this->get($loginUrl)->assertOk()->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));

        $user = $this->teamMember();
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('protocols.show', $protocol));
    }

    public function test_logged_in_users_get_no_login_link(): void
    {
        $protocol = Protocol::factory()->create();
        $protocol->share();

        $this->actingAs($this->teamMember())
            ->get($protocol->shareUrl())
            ->assertInertia(fn (Assert $page) => $page->where('loginUrl', null));
    }

    public function test_login_with_an_unknown_token_continues_as_usual(): void
    {
        $this->get(route('login', ['shared' => 'does-not-exist']))->assertOk();

        $user = $this->teamMember();
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(config('fortify.home'));
    }

    public function test_unknown_or_unshared_tokens_are_not_found(): void
    {
        Protocol::factory()->create();

        $this->get(route('protocols.public', 'does-not-exist'))->assertNotFound();
    }
}
