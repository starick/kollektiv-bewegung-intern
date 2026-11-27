<?php

namespace Tests\Feature\Wiki;

use App\Models\User;
use App\Models\WikiPage;
use App\Wiki\Changelog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Protocols\CreatesTeamMembers;
use Tests\TestCase;

class WikiChangelogTest extends TestCase
{
    use CreatesTeamMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The wiki pages may not be in the local Vite build yet; the tests only check the Inertia props
        $this->withoutVite();
    }

    public function test_team_members_see_the_changelog(): void
    {
        $this->actingAs($this->teamMember())
            ->get(route('wiki.changelog'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Wiki/Changelog')
                ->where('version', Changelog::fromRepository()->version())
                ->where('html', fn (string $html) => str_contains($html, '<h3>')
                    && ! str_contains($html, 'All notable changes')));
    }

    public function test_outsiders_cannot_see_the_changelog(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('wiki.changelog'))
            ->assertForbidden();
    }

    public function test_wiki_index_links_the_changelog_with_its_version(): void
    {
        $this->actingAs($this->teamMember())
            ->get(route('wiki.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('changelog.version', Changelog::fromRepository()->version()));
    }

    public function test_wiki_page_sidebar_links_the_changelog(): void
    {
        $page = WikiPage::factory()->create();

        $this->actingAs($this->teamMember())
            ->get(route('wiki.show', $page))
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->where('changelog.version', Changelog::fromRepository()->version()));
    }

    public function test_changelog_page_has_the_wiki_sidebar(): void
    {
        $page = WikiPage::factory()->create(['title' => 'Ablauf', 'pinned' => true]);

        $this->actingAs($this->teamMember())
            ->get(route('wiki.changelog'))
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->where('tree.rootPages.0.slug', $page->slug)
                ->where('pinned.0.slug', $page->slug)
                ->where('changelog.version', Changelog::fromRepository()->version()));
    }

    public function test_a_page_titled_changelog_gets_another_slug(): void
    {
        $page = WikiPage::factory()->create(['title' => 'Changelog']);

        $this->assertSame('changelog-2', $page->slug);
    }

    public function test_markdown_starts_at_the_newest_release(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'changelog');
        file_put_contents($path, "# Changelog\n\nAll notable changes...\n\n### [1.2.3](https://example.org) (2026-11-27)\n\n* fix\n\n## [1.2.0](https://example.org)\n");

        $changelog = new Changelog($path);

        $this->assertStringStartsWith('### [1.2.3]', $changelog->markdown());
        $this->assertSame('1.2.3', $changelog->version());
        unlink($path);
    }

    public function test_a_missing_changelog_has_no_version(): void
    {
        $changelog = new Changelog('/does/not/exist.md');

        $this->assertFalse($changelog->exists());
        $this->assertSame('', $changelog->markdown());
        $this->assertNull($changelog->version());
    }
}
