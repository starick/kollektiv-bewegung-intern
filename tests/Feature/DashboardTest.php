<?php

namespace Tests\Feature;

use App\Models\Protocol;
use App\Models\TimeTable;
use App\Models\User;
use App\Models\WikiPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Protocols\CreatesTeamMembers;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use CreatesTeamMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_it_shows_the_timetable_of_the_latest_week(): void
    {
        // The timetable factory picks its author among existing users
        $member = $this->teamMember();
        TimeTable::factory()->create(['year' => 2026, 'week' => 47]);
        $newest = TimeTable::factory()->withDailyCourses()->create(['year' => 2026, 'week' => 48]);
        TimeTable::factory()->create(['year' => 2025, 'week' => 52]);

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Dashboard')
                ->where('timeTable.id', $newest->id)
                ->where('timeTable.week', 48)
                ->has('timeTable.courses', $newest->courses()->count())
                ->has('timeTable.displayConfig')
                ->has('timeTable.creator.name'));
    }

    public function test_it_shows_an_overview_of_the_latest_protocol_without_its_text(): void
    {
        $member = $this->teamMember();
        Protocol::factory()->create(['date' => '2026-11-02']);
        $newest = Protocol::factory()->create([
            'date' => '2026-11-23',
            'description' => 'Finanz-AK',
            'topics' => ['Budget', 'Raum'],
            'participants' => ['Anna', 'Ben', 'Cem'],
            'text' => 'Geheimer Inhalt',
        ]);

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->where('protocol.slug', $newest->slug)
                ->where('protocol.description', 'Finanz-AK')
                ->where('protocol.topics', ['Budget', 'Raum'])
                ->where('protocol.participantCount', 3)
                ->missing('protocol.text')
                ->missing('protocol.participants'));
    }

    public function test_wiki_changes_show_one_line_per_page_with_its_latest_change(): void
    {
        $anna = $this->teamMember();
        $ben = $this->teamMember();

        $this->actingAs($anna);
        $links = WikiPage::factory()->create(['title' => 'Linksammlung']);
        $other = WikiPage::factory()->create(['title' => 'Stunden loggen']);
        $links->update(['text' => 'eins']);
        $links->update(['text' => 'zwei']);

        $this->actingAs($ben);
        $other->update(['title' => 'Stunden eintragen']);

        $this->get(route('dashboard'))
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->has('wikiChanges', 2)
                ->where('wikiChanges.0.title', 'Stunden eintragen')
                ->where('wikiChanges.0.slug', $other->slug)
                ->where('wikiChanges.0.event', 'updated')
                ->where('wikiChanges.0.user', $ben->name)
                ->where('wikiChanges.1.title', 'Linksammlung')
                ->where('wikiChanges.1.user', $anna->name));
    }

    public function test_deleted_wiki_pages_keep_their_title_but_lose_the_link(): void
    {
        $this->actingAs($this->teamMember());
        $page = WikiPage::factory()->create(['title' => 'Alte Seite']);
        $page->delete();

        $this->get(route('dashboard'))
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->where('wikiChanges.0.title', 'Alte Seite')
                ->where('wikiChanges.0.event', 'deleted')
                ->where('wikiChanges.0.slug', null));
    }

    public function test_wiki_changes_are_limited_to_ten_pages(): void
    {
        $this->actingAs($this->teamMember());
        WikiPage::factory()->count(12)->create();

        $this->get(route('dashboard'))
            ->assertInertia(fn (Assert $inertia) => $inertia->has('wikiChanges', 10));
    }

    public function test_people_outside_the_team_only_see_the_timetable(): void
    {
        $outsider = User::factory()->create();
        TimeTable::factory()->create();
        Protocol::factory()->create();
        WikiPage::factory()->create();

        $this->actingAs($outsider)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->has('timeTable')
                ->where('protocol', null)
                ->where('wikiChanges', null));
    }

    public function test_empty_dashboard(): void
    {
        $this->actingAs($this->teamMember())
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->where('timeTable', null)
                ->where('protocol', null)
                ->where('wikiChanges', []));
    }
}
