<?php

namespace Tests\Feature\Protocols;

use App\Models\Protocol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProtocolShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $protocol = Protocol::factory()->create();

        $this->get(route('protocols.show', $protocol))->assertRedirect(route('login'));
    }

    public function test_protocol_is_shown_with_rendered_markdown(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $protocol = Protocol::factory()->create([
            'date' => '2026-11-02',
            'description' => 'Plenum',
            'text' => "## Agenda\n\n- **Budget**",
        ]);

        $this->actingAs($user)
            ->get(route('protocols.show', $protocol))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Protocols/Show')
                ->where('protocol.date', '2026-11-02')
                ->where('protocol.description', 'Plenum')
                ->where('protocol.uploader.id', $protocol->uploaded_by)
                ->where('html', fn (string $html) => str_contains($html, '<h2>Agenda</h2>')
                    && str_contains($html, '<strong>Budget</strong>'))
            );
    }

    public function test_raw_html_and_unsafe_links_are_neutralised(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $protocol = Protocol::factory()->create([
            'text' => "<script>alert(1)</script>\n\n[click](javascript:alert(1))",
        ]);

        $this->actingAs($user)
            ->get(route('protocols.show', $protocol))
            ->assertInertia(fn (Assert $page) => $page
                ->where('html', fn (string $html) => ! str_contains($html, '<script>')
                    && ! str_contains($html, 'javascript:'))
            );
    }

    public function test_missing_protocol_returns_404(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($user)->get(route('protocols.show', 999))->assertNotFound();
    }
}
