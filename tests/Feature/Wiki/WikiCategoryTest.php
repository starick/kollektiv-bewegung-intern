<?php

namespace Tests\Feature\Wiki;

use App\Models\User;
use App\Models\WikiCategory;
use App\Models\WikiPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Protocols\CreatesTeamMembers;
use Tests\TestCase;

class WikiCategoryTest extends TestCase
{
    use CreatesTeamMembers;
    use RefreshDatabase;

    public function test_team_members_create_nested_categories(): void
    {
        $user = $this->teamMember();

        $this->actingAs($user)
            ->post(route('wiki-categories.store'), ['name' => 'Anleitungen', 'parent_id' => null])
            ->assertSessionHasNoErrors()
            ->assertRedirect();
        $parent = WikiCategory::sole();

        $this->actingAs($user)
            ->post(route('wiki-categories.store'), ['name' => 'Stundenplan', 'parent_id' => $parent->id])
            ->assertSessionHasNoErrors();

        $child = WikiCategory::where('name', 'Stundenplan')->sole();
        $this->assertSame($parent->id, $child->parent_id);
        $this->assertSame('Anleitungen › Stundenplan', $child->path());
    }

    public function test_sibling_names_must_be_unique_ignoring_case(): void
    {
        $user = $this->teamMember();
        WikiCategory::factory()->create(['name' => 'Finanzen']);
        $other = WikiCategory::factory()->create(['name' => 'Beschlüsse']);

        $this->actingAs($user)
            ->post(route('wiki-categories.store'), ['name' => 'finanzen', 'parent_id' => null])
            ->assertSessionHasErrors('name');

        $this->actingAs($user)
            ->post(route('wiki-categories.store'), ['name' => 'Finanzen', 'parent_id' => $other->id])
            ->assertSessionHasNoErrors();

        $this->assertSame(3, WikiCategory::count());
    }

    public function test_renaming_to_its_own_name_is_allowed(): void
    {
        $category = WikiCategory::factory()->create(['name' => 'Finanzen']);

        $this->actingAs($this->teamMember())
            ->put(route('wiki-categories.update', $category), ['name' => 'Finanzen', 'parent_id' => null])
            ->assertSessionHasNoErrors();
    }

    public function test_renaming_keeps_children_and_pages(): void
    {
        $category = WikiCategory::factory()->create(['name' => 'Alt']);
        $child = WikiCategory::factory()->create(['name' => 'Kind', 'parent_id' => $category->id]);
        $page = WikiPage::factory()->inCategory($category)->create();

        $this->actingAs($this->teamMember())
            ->put(route('wiki-categories.update', $category), ['name' => 'Neu', 'parent_id' => null])
            ->assertSessionHasNoErrors();

        $this->assertSame('Neu', $category->fresh()->name);
        $this->assertSame($category->id, $child->fresh()->parent_id);
        $this->assertSame($category->id, $page->fresh()->category_id);
        $this->assertSame('Neu › Kind', $child->fresh()->path());
    }

    public function test_moving_a_category_into_its_own_descendant_is_refused(): void
    {
        $user = $this->teamMember();
        $a = WikiCategory::factory()->create(['name' => 'A']);
        $b = WikiCategory::factory()->create(['name' => 'B', 'parent_id' => $a->id]);
        $c = WikiCategory::factory()->create(['name' => 'C', 'parent_id' => $b->id]);

        $this->actingAs($user)
            ->put(route('wiki-categories.update', $a), ['name' => 'A', 'parent_id' => $c->id])
            ->assertSessionHasErrors('parent_id');

        $this->actingAs($user)
            ->put(route('wiki-categories.update', $a), ['name' => 'A', 'parent_id' => $a->id])
            ->assertSessionHasErrors('parent_id');

        $this->assertNull($a->fresh()->parent_id);
    }

    public function test_moving_a_category_to_top_level(): void
    {
        $a = WikiCategory::factory()->create(['name' => 'A']);
        $b = WikiCategory::factory()->create(['name' => 'B', 'parent_id' => $a->id]);

        $this->actingAs($this->teamMember())
            ->put(route('wiki-categories.update', $b), ['name' => 'B', 'parent_id' => null])
            ->assertSessionHasNoErrors();

        $this->assertNull($b->fresh()->parent_id);
    }

    public function test_deleting_an_empty_category(): void
    {
        $category = WikiCategory::factory()->create();

        $this->actingAs($this->teamMember())
            ->delete(route('wiki-categories.destroy', $category))
            ->assertSessionHasNoErrors();

        $this->assertModelMissing($category);
    }

    public function test_deleting_a_category_with_pages_is_refused(): void
    {
        $category = WikiCategory::factory()->create();
        WikiPage::factory()->inCategory($category)->create();

        $this->actingAs($this->teamMember())
            ->delete(route('wiki-categories.destroy', $category))
            ->assertSessionHasErrors('category');

        $this->assertModelExists($category);
    }

    public function test_deleting_a_category_with_subcategories_is_refused(): void
    {
        $category = WikiCategory::factory()->create();
        WikiCategory::factory()->create(['parent_id' => $category->id]);

        $this->actingAs($this->teamMember())
            ->delete(route('wiki-categories.destroy', $category))
            ->assertSessionHasErrors('category');

        $this->assertModelExists($category);
    }

    public function test_users_outside_the_shared_team_are_forbidden(): void
    {
        $this->sharedTeam();
        $outsider = User::factory()->withPersonalTeam()->create();
        $category = WikiCategory::factory()->create();

        $this->actingAs($outsider)->post(route('wiki-categories.store'), ['name' => 'X', 'parent_id' => null])->assertForbidden();
        $this->actingAs($outsider)->put(route('wiki-categories.update', $category), ['name' => 'X', 'parent_id' => null])->assertForbidden();
        $this->actingAs($outsider)->delete(route('wiki-categories.destroy', $category))->assertForbidden();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->post(route('wiki-categories.store'), ['name' => 'X'])->assertRedirect(route('login'));
    }
}
