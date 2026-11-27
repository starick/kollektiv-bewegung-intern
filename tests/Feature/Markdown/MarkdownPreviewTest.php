<?php

namespace Tests\Feature\Markdown;

use App\Models\Protocol;
use App\Models\User;
use Tests\Feature\Protocols\CreatesTeamMembers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkdownPreviewTest extends TestCase
{
    use CreatesTeamMembers;
    use RefreshDatabase;

    public function test_markdown_is_rendered_without_saving(): void
    {
        $protocol = Protocol::factory()->create(['text' => '# Original']);

        $this->actingAs($this->teamMember())
            ->postJson(route('markdown.preview'), ['text' => "## Agenda\n\n- **Budget**"])
            ->assertOk()
            ->assertJsonPath('html', "<h2>Agenda</h2>\n<ul>\n<li><strong>Budget</strong></li>\n</ul>\n");

        $this->assertSame('# Original', $protocol->fresh()->text);
    }

    public function test_preview_is_sanitised_like_the_protocol_page(): void
    {
        $html = $this->actingAs($this->teamMember())
            ->postJson(route('markdown.preview'), [
                'text' => "<script>alert(1)</script>\n\n[x](javascript:alert(1)) ![img](data:image/png;base64,iVBORw0KGgo=)",
            ])
            ->assertOk()
            ->json('html');

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('<img', $html);
    }

    public function test_empty_text_renders_nothing(): void
    {
        $this->actingAs($this->teamMember())
            ->postJson(route('markdown.preview'), ['text' => ''])
            ->assertOk()
            ->assertJsonPath('html', '');
    }

    public function test_text_is_required(): void
    {
        $this->actingAs($this->teamMember())
            ->postJson(route('markdown.preview'), [])
            ->assertJsonValidationErrors('text');
    }

    public function test_guests_cannot_preview(): void
    {
        $this->postJson(route('markdown.preview'), ['text' => '# x'])->assertUnauthorized();
    }

    public function test_users_outside_the_shared_team_cannot_preview(): void
    {
        $this->sharedTeam();

        $this->actingAs(User::factory()->withPersonalTeam()->create())
            ->postJson(route('markdown.preview'), ['text' => '# x'])
            ->assertForbidden();
    }
}
