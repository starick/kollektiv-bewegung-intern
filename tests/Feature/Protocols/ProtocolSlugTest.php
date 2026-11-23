<?php

namespace Tests\Feature\Protocols;

use App\Models\Protocol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProtocolSlugTest extends TestCase
{
    use CreatesTeamMembers;
    use RefreshDatabase;

    public function test_slug_is_built_from_date_and_description(): void
    {
        $protocol = Protocol::factory()->create(['date' => '2026-11-02', 'description' => 'Plenum Übergabe & Kasse']);

        $this->assertSame('2026-11-02-plenum-uebergabe-kasse', $protocol->slug);
        $this->assertSame(url('/protocols/2026-11-02-plenum-uebergabe-kasse'), route('protocols.show', $protocol));
    }

    public function test_slug_is_only_the_date_without_description(): void
    {
        $protocol = Protocol::factory()->create(['date' => '2026-11-02', 'description' => null]);

        $this->assertSame('2026-11-02', $protocol->slug);
    }

    public function test_long_descriptions_are_cut_at_a_word_boundary(): void
    {
        $protocol = Protocol::factory()->create([
            'date' => '2026-11-02',
            'description' => 'Außerordentliche Mitgliederversammlung zur Wahl des neuen Vorstands und der Kassenprüfung',
        ]);

        // The 40 characters end inside "zur", so the slug stops after "mitgliederversammlung" (39 characters)
        $this->assertSame('2026-11-02-ausserordentliche-mitgliederversammlung', $protocol->slug);
    }

    public function test_words_up_to_the_limit_are_kept(): void
    {
        $protocol = Protocol::factory()->create(['date' => '2026-11-02', 'description' => 'Plenum zur Wahl des neuen Vorstands und Kasse']);

        // "plenum-zur-wahl-des-neuen-vorstands-und" has 39 characters, adding "-kasse" would exceed 40
        $this->assertSame('2026-11-02-plenum-zur-wahl-des-neuen-vorstands-und', $protocol->slug);
    }

    public function test_a_single_overlong_word_is_cut_hard(): void
    {
        $protocol = Protocol::factory()->create(['date' => '2026-11-02', 'description' => str_repeat('a', Protocol::SLUG_DESCRIPTION_LENGTH + 10)]);

        $this->assertSame('2026-11-02-'.str_repeat('a', Protocol::SLUG_DESCRIPTION_LENGTH), $protocol->slug);
    }

    public function test_descriptions_with_the_same_slug_get_a_counter(): void
    {
        Protocol::factory()->create(['date' => '2026-11-02', 'description' => 'Plenum']);
        $second = Protocol::factory()->create(['date' => '2026-11-02', 'description' => 'Plenum!']);
        $third = Protocol::factory()->create(['date' => '2026-11-02', 'description' => 'plenum']);

        $this->assertSame('2026-11-02-plenum-2', $second->slug);
        $this->assertSame('2026-11-02-plenum-3', $third->slug);
    }

    public function test_rewording_the_description_keeps_the_slug(): void
    {
        $protocol = Protocol::factory()->create(['date' => '2026-11-02', 'description' => 'Plenum']);

        $protocol->update(['description' => 'Plenum Dezember', 'text' => 'Neu']);

        $this->assertSame('2026-11-02-plenum', $protocol->slug);
    }

    public function test_changing_the_date_changes_the_slug(): void
    {
        $protocol = Protocol::factory()->create(['date' => '2026-11-02', 'description' => 'Plenum']);

        $protocol->update(['date' => '2026-11-09', 'description' => 'Vorstand']);

        $this->assertSame('2026-11-09-vorstand', $protocol->slug);
    }

    public function test_adding_or_removing_the_description_changes_the_slug(): void
    {
        $protocol = Protocol::factory()->create(['date' => '2026-11-02', 'description' => null]);

        $protocol->update(['description' => 'Plenum']);
        $this->assertSame('2026-11-02-plenum', $protocol->slug);

        $protocol->update(['description' => null]);
        $this->assertSame('2026-11-02', $protocol->slug);
    }

    public function test_protocol_page_is_reached_by_slug_and_not_by_id(): void
    {
        $user = $this->teamMember();
        $protocol = Protocol::factory()->create(['date' => '2026-11-02', 'description' => 'Plenum']);

        $this->actingAs($user)->get('/protocols/2026-11-02-plenum')->assertOk();
        $this->actingAs($user)->get("/protocols/{$protocol->id}")->assertNotFound();
    }

    public function test_updating_redirects_to_the_new_slug(): void
    {
        $user = $this->teamMember();
        $protocol = Protocol::factory()->create(['date' => '2026-11-02', 'description' => null, 'uploaded_by' => $user->id]);

        $this->actingAs($user)
            ->put(route('protocols.update', $protocol), [
                'date' => '2026-11-02',
                'description' => 'Vorstand',
                'topics' => '',
                'participants' => '',
                'text' => 'x',
            ])
            ->assertRedirect('/protocols/2026-11-02-vorstand');
    }
}
