<?php

namespace Tests\Feature\Wiki;

use App\Models\User;
use App\Models\WikiCategory;
use App\Models\WikiPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Protocols\CreatesTeamMembers;
use Tests\TestCase;

class WikiPageTest extends TestCase
{
    use CreatesTeamMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The wiki pages may not be in the local Vite build yet; the tests only check the Inertia props
        $this->withoutVite();
    }

    private function createPage(User $user, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($user)->post(route('wiki.store'), [
            'title' => 'Stundenplan anlegen',
            'category_id' => null,
            'text' => '# Hallo',
            'pinned' => false,
            ...$data,
        ]);
    }

    public function test_team_members_create_a_page(): void
    {
        $user = $this->teamMember();
        $category = WikiCategory::factory()->create();

        $this->createPage($user, ['category_id' => $category->id])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/wiki/stundenplan-anlegen');

        $page = WikiPage::sole();
        $this->assertSame('Stundenplan anlegen', $page->title);
        $this->assertSame($category->id, $page->category_id);
        $this->assertSame('# Hallo', $page->text);
        $this->assertSame($user->id, $page->created_by);
        $this->assertFalse($page->pinned);
    }

    public function test_slug_gets_a_counter_on_collision(): void
    {
        $user = $this->teamMember();

        $this->createPage($user);
        $this->createPage($user)->assertRedirect('/wiki/stundenplan-anlegen-2');
    }

    public function test_title_create_does_not_take_the_create_slug(): void
    {
        $this->createPage($this->teamMember(), ['title' => 'Create'])->assertRedirect('/wiki/create-2');
    }

    public function test_title_without_sluggable_characters_falls_back(): void
    {
        $user = $this->teamMember();

        $this->createPage($user, ['title' => '???'])->assertRedirect('/wiki/seite');
        $this->createPage($user, ['title' => '🙂'])->assertRedirect('/wiki/seite-2');
    }

    public function test_german_umlauts_are_transliterated(): void
    {
        $this->createPage($this->teamMember(), ['title' => 'Übersicht Größe'])->assertRedirect('/wiki/uebersicht-groesse');
    }

    public function test_renaming_and_moving_keep_the_slug(): void
    {
        $page = WikiPage::factory()->create(['title' => 'Alt', 'slug' => 'alt']);
        $category = WikiCategory::factory()->create();

        $this->actingAs($this->teamMember())
            ->put(route('wiki.update', $page), [
                'title' => 'Ganz neu',
                'category_id' => $category->id,
                'text' => 'x',
                'pinned' => true,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/wiki/alt');

        $page->refresh();
        $this->assertSame('alt', $page->slug);
        $this->assertSame('Ganz neu', $page->title);
        $this->assertTrue($page->pinned);
    }

    public function test_update_records_the_editor(): void
    {
        $page = WikiPage::factory()->create();
        $editor = $this->teamMember();

        $this->actingAs($editor)->put(route('wiki.update', $page), [
            'title' => $page->title,
            'category_id' => null,
            'text' => 'neu',
            'pinned' => false,
        ]);

        $this->assertSame($editor->id, $page->fresh()->edited_by);
    }

    public function test_show_renders_markdown_and_breadcrumbs(): void
    {
        $a = WikiCategory::factory()->create(['name' => 'A']);
        $b = WikiCategory::factory()->create(['name' => 'B', 'parent_id' => $a->id]);
        $page = WikiPage::factory()->inCategory($b)->create(['text' => '# Titel']);

        $this->actingAs($this->teamMember())
            ->get(route('wiki.show', $page))
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Wiki/Show')
                ->where('page.slug', $page->slug)
                ->where('breadcrumbs.0.name', 'A')
                ->where('breadcrumbs.1.name', 'B')
                ->has('breadcrumbs', 2)
                ->where('html', fn (string $html) => str_contains($html, '<h1>Titel</h1>'))
                ->where('page.creator.id', $page->created_by)
                ->missing('page.creator.email')
                ->has('audits'));
    }

    public function test_raw_html_is_escaped(): void
    {
        $page = WikiPage::factory()->create(['text' => '<script>alert(1)</script>']);

        $this->actingAs($this->teamMember())
            ->get(route('wiki.show', $page))
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->where('html', fn (string $html) => ! str_contains($html, '<script>')));
    }

    public function test_edit_page_receives_text_and_category_options(): void
    {
        $a = WikiCategory::factory()->create(['name' => 'b-Kategorie']);
        WikiCategory::factory()->create(['name' => 'Kind', 'parent_id' => $a->id]);
        WikiCategory::factory()->create(['name' => 'A-Kategorie']);
        $page = WikiPage::factory()->create(['text' => '# Text']);

        $this->actingAs($this->teamMember())
            ->get(route('wiki.edit', $page))
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Wiki/Edit')
                ->where('text', '# Text')
                ->where('categoryOptions.0.path', 'A-Kategorie')
                ->where('categoryOptions.1.path', 'b-Kategorie')
                ->where('categoryOptions.2.path', 'b-Kategorie › Kind'));
    }

    public function test_create_page_preselects_a_known_category(): void
    {
        $category = WikiCategory::factory()->create();
        $user = $this->teamMember();

        $this->actingAs($user)
            ->get(route('wiki.create', ['category' => $category->id]))
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Wiki/Create')
                ->where('categoryId', $category->id));

        $this->actingAs($user)
            ->get(route('wiki.create', ['category' => 'unknown']))
            ->assertInertia(fn (Assert $inertia) => $inertia->where('categoryId', null));
    }

    public function test_any_member_can_edit(): void
    {
        $page = WikiPage::factory()->create();

        $this->actingAs($this->teamMember())->get(route('wiki.edit', $page))->assertOk();
    }

    public function test_only_creator_or_super_admin_can_delete(): void
    {
        $creator = $this->teamMember();
        $page = WikiPage::factory()->create(['created_by' => $creator->id]);
        $other = WikiPage::factory()->create();

        $this->actingAs($this->teamMember())->delete(route('wiki.destroy', $page))->assertForbidden();
        $this->assertModelExists($page);

        $this->actingAs($creator)->delete(route('wiki.destroy', $page))->assertRedirect(route('wiki.index'));
        $this->assertModelMissing($page);

        $this->actingAs($this->teamMember('admin'))->delete(route('wiki.destroy', $other))->assertRedirect(route('wiki.index'));
        $this->assertModelMissing($other);
    }

    public function test_outsiders_are_forbidden(): void
    {
        $this->sharedTeam();
        $outsider = User::factory()->withPersonalTeam()->create();
        $page = WikiPage::factory()->create();

        $this->actingAs($outsider)->get(route('wiki.index'))->assertForbidden();
        $this->actingAs($outsider)->get(route('wiki.show', $page))->assertForbidden();
        $this->createPage($outsider)->assertForbidden();
        $this->actingAs($outsider)->put(route('wiki.update', $page), ['title' => 'x', 'text' => ''])->assertForbidden();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $page = WikiPage::factory()->create();

        $this->get(route('wiki.index'))->assertRedirect(route('login'));
        $this->get(route('wiki.show', $page))->assertRedirect(route('login'));
    }

    public function test_unknown_category_is_rejected(): void
    {
        $this->createPage($this->teamMember(), ['category_id' => 'nope'])->assertSessionHasErrors('category_id');
    }

    public function test_title_is_required(): void
    {
        $this->createPage($this->teamMember(), ['title' => ''])->assertSessionHasErrors('title');
    }

    public function test_text_may_be_empty(): void
    {
        $this->createPage($this->teamMember(), ['text' => ''])->assertSessionHasNoErrors();

        $this->assertSame('', WikiPage::sole()->text);
    }
}
