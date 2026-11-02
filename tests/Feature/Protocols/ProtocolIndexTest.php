<?php

namespace Tests\Feature\Protocols;

use App\Models\Protocol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProtocolIndexTest extends TestCase
{
    use CreatesTeamMembers;
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('protocols.index'))->assertRedirect(route('login'));
    }

    public function test_protocols_are_listed_newest_first(): void
    {
        $user = $this->teamMember();
        Protocol::factory()->create(['date' => '2026-01-10']);
        Protocol::factory()->create(['date' => '2026-03-05']);

        $this->actingAs($user)
            ->get(route('protocols.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Protocols/Index')
                ->has('protocols.data', 2)
                ->where('protocols.data.0.date', '2026-03-05')
                ->where('protocols.data.1.date', '2026-01-10')
            );
    }

    public function test_description_is_listed(): void
    {
        $user = $this->teamMember();
        Protocol::factory()->create(['description' => 'Plenum November']);

        $this->actingAs($user)
            ->get(route('protocols.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('protocols.data.0.description', 'Plenum November')
            );
    }

    public function test_topics_are_listed(): void
    {
        $user = $this->teamMember();
        Protocol::factory()->create(['topics' => ['Budget', 'Events']]);
        Protocol::factory()->create(['date' => '2000-01-01', 'topics' => null]);

        $this->actingAs($user)
            ->get(route('protocols.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('protocols.data.0.topics', ['Budget', 'Events'])
                ->where('protocols.data.1.topics', [])
            );
    }

    public function test_search_matches_description_topics_and_text(): void
    {
        $user = $this->teamMember();
        Protocol::factory()->create(['description' => 'Plenum November', 'topics' => [], 'text' => 'x']);
        Protocol::factory()->create(['description' => 'Vorstand', 'topics' => ['Budget'], 'text' => 'x']);
        Protocol::factory()->create(['description' => 'Vorstand', 'topics' => [], 'text' => 'Wir planen das Sommerfest']);
        Protocol::factory()->create(['description' => 'Unrelated', 'topics' => [], 'text' => 'nothing']);

        foreach (['plenum' => 'Plenum November', 'budget' => 'Vorstand', 'sommerfest' => 'Vorstand'] as $term => $description) {
            $this->actingAs($user)
                ->get(route('protocols.index', ['search' => $term]))
                ->assertInertia(fn (Assert $page) => $page
                    ->has('protocols.data', 1)
                    ->where('protocols.data.0.description', $description)
                    ->where('filters.search', $term)
                );
        }
    }

    public function test_topic_search_ignores_case(): void
    {
        $user = $this->teamMember();
        Protocol::factory()->create(['description' => 'Vorstand', 'topics' => ['Finanzen'], 'text' => 'x']);

        $this->actingAs($user)
            ->get(route('protocols.index', ['search' => 'finanzen']))
            ->assertInertia(fn (Assert $page) => $page->has('protocols.data', 1));
    }

    public function test_non_string_search_is_ignored(): void
    {
        $user = $this->teamMember();
        Protocol::factory()->count(2)->create();

        $this->actingAs($user)
            ->get('/protocols?search[]=x')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('protocols.data', 2)
                ->where('filters.search', '')
            );
    }

    public function test_no_users_are_sent(): void
    {
        $user = $this->teamMember();
        Protocol::factory()->edited()->create();

        $this->actingAs($user)
            ->get(route('protocols.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->missing('protocols.data.0.uploader')
                ->missing('protocols.data.0.editor')
            );
    }

    public function test_empty_search_lists_everything(): void
    {
        $user = $this->teamMember();
        Protocol::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('protocols.index', ['search' => '  ']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('protocols.data', 3)
                ->where('filters.search', '')
            );
    }

    public function test_list_is_paginated(): void
    {
        $user = $this->teamMember();
        Protocol::factory()->count(25)->create();

        $this->actingAs($user)
            ->get(route('protocols.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('protocols.data', 20)
                ->where('protocols.meta.total', 25)
                ->where('protocols.meta.current_page', 1)
                ->where('filters.perPage', 20)
            );

        $this->actingAs($user)
            ->get(route('protocols.index', ['page' => 2]))
            ->assertInertia(fn (Assert $page) => $page->has('protocols.data', 5));
    }

    public function test_page_size_can_be_chosen_from_allowed_values(): void
    {
        $user = $this->teamMember();
        Protocol::factory()->count(12)->create();

        $this->actingAs($user)
            ->get(route('protocols.index', ['per_page' => 10]))
            ->assertInertia(fn (Assert $page) => $page->has('protocols.data', 10));

        $this->actingAs($user)
            ->get(route('protocols.index', ['per_page' => 1000]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('protocols.data', 12)
                ->where('filters.perPage', 20)
            );
    }

    public function test_list_can_be_sorted_by_date_ascending(): void
    {
        $user = $this->teamMember();
        Protocol::factory()->create(['date' => '2026-03-05']);
        Protocol::factory()->create(['date' => '2026-01-10']);

        $this->actingAs($user)
            ->get(route('protocols.index', ['sort' => 'date', 'direction' => 'asc']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('protocols.data.0.date', '2026-01-10')
                ->where('filters.direction', 'asc')
            );
    }

    public function test_list_can_be_sorted_by_update_time(): void
    {
        $user = $this->teamMember();
        $older = Protocol::factory()->create(['date' => '2026-03-05', 'updated_at' => '2026-01-01 10:00:00']);
        $newer = Protocol::factory()->create(['date' => '2026-01-10', 'updated_at' => '2026-06-01 10:00:00']);

        $this->actingAs($user)
            ->get(route('protocols.index', ['sort' => 'updatedAt', 'direction' => 'desc']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('protocols.data.0.id', $newer->id)
                ->where('protocols.data.1.id', $older->id)
            );
    }

    public function test_search_is_kept_across_pages(): void
    {
        $user = $this->teamMember();
        Protocol::factory()->count(25)->create(['description' => 'Plenum']);
        Protocol::factory()->count(5)->create(['description' => 'Vorstand', 'topics' => [], 'text' => 'x']);

        $this->actingAs($user)
            ->get(route('protocols.index', ['search' => 'plenum']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('protocols.meta.total', 25)
                ->where('protocols.links.next', fn (string $url) => str_contains($url, 'search=plenum'))
            );
    }
}
