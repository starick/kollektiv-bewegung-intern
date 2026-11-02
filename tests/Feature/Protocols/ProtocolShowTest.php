<?php

namespace Tests\Feature\Protocols;

use App\Models\Protocol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProtocolShowTest extends TestCase
{
    use CreatesTeamMembers;
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $protocol = Protocol::factory()->create();

        $this->get(route('protocols.show', $protocol))->assertRedirect(route('login'));
    }

    public function test_protocol_is_shown_with_rendered_markdown(): void
    {
        $user = $this->teamMember();
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
        $user = $this->teamMember();
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

    public function test_only_names_of_users_are_sent(): void
    {
        $user = $this->teamMember();
        $protocol = Protocol::factory()->edited()->create();

        $this->actingAs($user)
            ->get(route('protocols.show', $protocol))
            ->assertInertia(fn (Assert $page) => $page
                ->has('protocol.uploader', fn (Assert $uploader) => $uploader->hasAll(['id', 'name'])->missing('email'))
                ->has('protocol.editor', fn (Assert $editor) => $editor->hasAll(['id', 'name'])->missing('email'))
            );
    }

    public function test_images_are_not_rendered(): void
    {
        $user = $this->teamMember();
        $protocol = Protocol::factory()->create([
            'text' => "Before ![logo](https://example.com/logo.png) after\n\n![][image1]\n\n[image1]: <data:image/png;base64,iVBORw0KGgo=>",
        ]);

        $this->actingAs($user)
            ->get(route('protocols.show', $protocol))
            ->assertInertia(fn (Assert $page) => $page
                ->where('html', fn (string $html) => ! str_contains($html, '<img')
                    && ! str_contains($html, 'logo')
                    && ! str_contains($html, 'base64')
                    && str_contains($html, 'Before')
                    && str_contains($html, 'after'))
            );
    }

    public function test_topics_are_shown(): void
    {
        $user = $this->teamMember();
        $protocol = Protocol::factory()->create(['topics' => ['Budget', 'Events']]);

        $this->actingAs($user)
            ->get(route('protocols.show', $protocol))
            ->assertInertia(fn (Assert $page) => $page->where('protocol.topics', ['Budget', 'Events']));
    }

    public function test_protocol_can_be_downloaded_as_markdown(): void
    {
        $user = $this->teamMember();
        $protocol = Protocol::factory()->create([
            'date' => '2026-11-02',
            'description' => 'Plenum November',
            'text' => "# Plenum\n\n- Punkt 1",
        ]);

        $response = $this->actingAs($user)->get(route('protocols.download', $protocol));

        $response->assertOk()
            ->assertDownload('2026-11-02-plenum-november.md')
            ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
        $this->assertSame("# Plenum\n\n- Punkt 1", $response->streamedContent());
    }

    public function test_download_filename_without_description_is_the_date(): void
    {
        $user = $this->teamMember();
        $protocol = Protocol::factory()->create(['date' => '2026-11-02', 'description' => null]);

        $this->actingAs($user)
            ->get(route('protocols.download', $protocol))
            ->assertDownload('2026-11-02.md');
    }

    public function test_guests_cannot_download(): void
    {
        $protocol = Protocol::factory()->create();

        $this->get(route('protocols.download', $protocol))->assertRedirect(route('login'));
    }

    public function test_missing_protocol_returns_404(): void
    {
        $user = $this->teamMember();

        $this->actingAs($user)->get(route('protocols.show', 999))->assertNotFound();
    }
}
