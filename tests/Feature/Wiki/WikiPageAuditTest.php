<?php

namespace Tests\Feature\Wiki;

use App\Models\WikiCategory;
use App\Models\WikiPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Protocols\CreatesTeamMembers;
use Tests\TestCase;

class WikiPageAuditTest extends TestCase
{
    use CreatesTeamMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The wiki pages may not be in the local Vite build yet; the tests only check the Inertia props
        $this->withoutVite();
    }

    private function update(WikiPage $page, array $data = []): void
    {
        $this->actingAs($this->teamMember())->put(route('wiki.update', $page), [
            'title' => $page->title,
            'category_id' => $page->category_id,
            'text' => $page->text,
            'pinned' => $page->pinned,
            ...$data,
        ])->assertSessionHasNoErrors();
    }

    public function test_creating_is_audited_with_the_user(): void
    {
        $user = $this->teamMember();

        $this->actingAs($user)->post(route('wiki.store'), ['title' => 'Links', 'text' => '- a']);

        $audit = WikiPage::sole()->audits()->sole();
        $this->assertSame('created', $audit->event);
        $this->assertSame($user->id, $audit->user_id);
    }

    public function test_text_change_is_recorded_as_diff(): void
    {
        $page = WikiPage::factory()->create(['text' => "eins\nzwei"]);
        $this->update($page, ['text' => "eins\ndrei"]);

        $this->actingAs($this->teamMember())
            ->get(route('wiki.show', $page))
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->where('audits.0.event', 'updated')
                ->where('audits.0.changes.0.attribute', 'text')
                ->where('audits.0.changes.0.type', 'diff'));
    }

    public function test_slug_and_editor_are_not_recorded(): void
    {
        $page = WikiPage::factory()->create();
        $this->update($page, ['text' => 'neu']);

        $audit = $page->audits()->where('event', 'updated')->sole();
        $this->assertArrayNotHasKey('edited_by', $audit->new_values);
        $this->assertArrayNotHasKey('slug', $audit->new_values);
    }

    public function test_moving_records_category_paths(): void
    {
        $a = WikiCategory::factory()->create(['name' => 'A']);
        $b = WikiCategory::factory()->create(['name' => 'B', 'parent_id' => $a->id]);
        $page = WikiPage::factory()->inCategory($a)->create();

        $this->update($page, ['category_id' => $b->id]);
        $this->update($page->fresh(), ['category_id' => null]);

        [$toB, $toTop] = $page->audits()->where('event', 'updated')->orderBy('id')->get();
        $this->assertSame('A', $toB->old_values['category']);
        $this->assertSame('A › B', $toB->new_values['category']);
        $this->assertArrayNotHasKey('category_id', $toB->new_values);
        $this->assertSame('A › B', $toTop->old_values['category']);
        $this->assertNull($toTop->new_values['category']);
    }

    public function test_creating_in_a_category_records_its_path(): void
    {
        $category = WikiCategory::factory()->create(['name' => 'Orga']);

        $this->actingAs($this->teamMember())->post(route('wiki.store'), ['title' => 'X', 'category_id' => $category->id, 'text' => '']);

        $this->assertSame('Orga', WikiPage::sole()->audits()->sole()->new_values['category']);
    }

    public function test_history_keeps_the_old_path_after_the_category_is_renamed_and_deleted(): void
    {
        $x = WikiCategory::factory()->create(['name' => 'X']);
        $page = WikiPage::factory()->inCategory($x)->create();

        $this->update($page, ['category_id' => null]);
        $x->update(['name' => 'Y']);
        $x->delete();

        $this->actingAs($this->teamMember())
            ->get(route('wiki.show', $page))
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->where('audits.0.changes.0.attribute', 'category')
                ->where('audits.0.changes.0.old', 'X')
                ->where('audits.0.changes.0.new', null));
    }
}
