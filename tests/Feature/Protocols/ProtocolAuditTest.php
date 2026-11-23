<?php

namespace Tests\Feature\Protocols;

use App\Models\Protocol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use OwenIt\Auditing\Models\Audit;
use Tests\TestCase;

class ProtocolAuditTest extends TestCase
{
    use CreatesTeamMembers;
    use RefreshDatabase;

    public function test_creating_a_protocol_is_audited_with_the_user(): void
    {
        $user = $this->teamMember();

        $this->actingAs($user)->post(route('protocols.store'), [
            'date' => '2026-11-02',
            'file' => UploadedFile::fake()->createWithContent('plenum.md', '# Plenum'),
        ]);

        $audit = Protocol::sole()->audits()->sole();
        $this->assertSame('created', $audit->event);
        $this->assertSame($user->id, $audit->user_id);
        $this->assertNull($audit->ip_address);
        $this->assertNull($audit->user_agent);
    }

    public function test_updating_a_protocol_records_old_and_new_values(): void
    {
        $protocol = Protocol::factory()->create(['date' => '2026-11-02', 'description' => 'Plenum', 'text' => '# Alt']);
        $editor = $this->teamMember();

        $this->actingAs($editor)->put(route('protocols.update', $protocol), [
            'date' => '2026-11-02',
            'description' => 'Vorstand',
            'text' => '# Neu',
        ])->assertSessionHasNoErrors();

        $audit = $protocol->audits()->where('event', 'updated')->sole();
        $this->assertSame($editor->id, $audit->user_id);
        $this->assertSame('Plenum', $audit->old_values['description']);
        $this->assertSame('Vorstand', $audit->new_values['description']);
        $this->assertSame('# Neu', $audit->new_values['text']);
        $this->assertArrayNotHasKey('date', $audit->new_values);
        $this->assertArrayNotHasKey('edited_by', $audit->new_values);
    }

    public function test_saving_without_changes_is_not_audited(): void
    {
        $protocol = Protocol::factory()->create(['date' => '2026-11-02', 'description' => null, 'topics' => [], 'participants' => [], 'text' => '# Gleich']);

        $this->actingAs($this->teamMember())->put(route('protocols.update', $protocol), [
            'date' => '2026-11-02',
            'text' => '# Gleich',
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, $protocol->audits()->where('event', 'updated')->count());
    }

    public function test_deleting_a_protocol_keeps_its_audit_trail(): void
    {
        $uploader = $this->teamMember();
        $protocol = Protocol::factory()->create(['uploaded_by' => $uploader->id]);

        $this->actingAs($uploader)->delete(route('protocols.destroy', $protocol));

        $audits = Audit::where('auditable_type', Protocol::class)->where('auditable_id', $protocol->id)->pluck('event');
        $this->assertSame(['created', 'deleted'], $audits->all());
    }

    public function test_show_page_lists_the_trail_newest_first_with_decoded_values(): void
    {
        $user = $this->teamMember();
        $protocol = Protocol::factory()->create(['date' => '2026-11-02', 'topics' => ['Budget'], 'participants' => [], 'text' => '# Alt']);

        $this->actingAs($user)->put(route('protocols.update', $protocol), [
            'date' => '2026-11-03',
            'description' => $protocol->description,
            'topics' => 'Budget, Events',
            'text' => '# Neu',
        ])->assertSessionHasNoErrors();

        // The date is part of the slug, so the URL changed
        $this->actingAs($user)
            ->get(route('protocols.show', $protocol->refresh()))
            ->assertInertia(fn (Assert $page) => $page
                ->has('audits', 2)
                ->where('audits.0.event', 'updated')
                ->where('audits.0.user', ['id' => $user->id, 'name' => $user->name])
                ->where('audits.0.changes', fn ($changes) => collect($changes)->keyBy('attribute')->all() == [
                    'date' => ['attribute' => 'date', 'type' => 'value', 'old' => '2026-11-02', 'new' => '2026-11-03'],
                    'topics' => ['attribute' => 'topics', 'type' => 'value', 'old' => ['Budget'], 'new' => ['Budget', 'Events']],
                    'text' => ['attribute' => 'text', 'type' => 'diff', 'added' => 1, 'removed' => 1, 'hunks' => [[
                        ['type' => 'removed', 'text' => '# Alt'],
                        ['type' => 'added', 'text' => '# Neu'],
                    ]]],
                ])
                ->where('audits.1.event', 'created')
            );
    }

    public function test_trail_of_a_deleted_user_shows_no_user(): void
    {
        $user = $this->teamMember();
        $protocol = Protocol::factory()->create();
        $this->actingAs($user)->put(route('protocols.update', $protocol), [
            'date' => '2026-01-01',
            'text' => '# Neu',
        ]);
        $user->delete();

        $this->actingAs($this->teamMember())
            ->get(route('protocols.show', $protocol->refresh()))
            ->assertInertia(fn (Assert $page) => $page->where('audits.0.user', null));
    }
}
