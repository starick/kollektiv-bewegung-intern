<?php

namespace Tests\Feature\Wiki;

use App\Models\WikiCategory;
use App\Models\WikiPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Protocols\CreatesTeamMembers;
use Tests\TestCase;

class WikiIndexTest extends TestCase
{
    use CreatesTeamMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The wiki pages may not be in the local Vite build yet; the tests only check the Inertia props
        $this->withoutVite();
    }

    public function test_tree_is_nested_and_sorted(): void
    {
        WikiCategory::factory()->create(['name' => 'beta']);
        $alpha = WikiCategory::factory()->create(['name' => 'Alpha']);
        WikiCategory::factory()->create(['name' => 'Kind', 'parent_id' => $alpha->id]);
        WikiPage::factory()->inCategory($alpha)->create(['title' => 'Zebra']);
        WikiPage::factory()->inCategory($alpha)->create(['title' => 'affe']);
        WikiPage::factory()->create(['title' => 'Links']);

        $this->actingAs($this->teamMember())
            ->get(route('wiki.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Wiki/Index')
                ->where('categories.0.name', 'Alpha')
                ->where('categories.1.name', 'beta')
                ->where('categories.0.children.0.name', 'Kind')
                ->where('categories.0.pages.0.title', 'affe')
                ->where('categories.0.pages.1.title', 'Zebra')
                ->where('rootPages.0.title', 'Links')
                ->has('rootPages', 1)
                ->where('results', null)
                ->where('can.create', true));
    }

    public function test_pinned_pages_are_listed_and_stay_in_the_tree(): void
    {
        WikiPage::factory()->pinned()->create(['title' => 'Links', 'slug' => 'links']);
        WikiPage::factory()->create(['title' => 'Anderes']);

        $this->actingAs($this->teamMember())
            ->get(route('wiki.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('pinned', 1)
                ->where('pinned.0.slug', 'links')
                ->has('rootPages', 2));
    }

    public function test_search_matches_title_and_text_and_includes_the_path(): void
    {
        $a = WikiCategory::factory()->create(['name' => 'A']);
        $b = WikiCategory::factory()->create(['name' => 'B', 'parent_id' => $a->id]);
        WikiPage::factory()->inCategory($b)->create(['title' => 'Kasse', 'text' => 'nichts']);
        WikiPage::factory()->create(['title' => 'Andere', 'text' => 'Die Kasse steht im Flur']);
        WikiPage::factory()->create(['title' => 'Unpassend', 'text' => 'nichts']);

        $this->actingAs($this->teamMember())
            ->get(route('wiki.index', ['search' => 'kasse']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('results', 2)
                ->where('results.0.title', 'Andere')
                ->where('results.0.path', null)
                ->where('results.1.title', 'Kasse')
                ->where('results.1.path', 'A › B')
                ->where('filters.search', 'kasse'));
    }

    public function test_search_escapes_like_wildcards(): void
    {
        WikiPage::factory()->create(['title' => 'Rabatt', 'text' => '10% weniger']);
        WikiPage::factory()->create(['title' => 'Normal', 'text' => 'voller Preis']);

        $this->actingAs($this->teamMember())
            ->get(route('wiki.index', ['search' => '%']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('results', 1)
                ->where('results.0.title', 'Rabatt'));
    }
}
