<?php

namespace Tests\Feature\Protocols;

use App\Models\Protocol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProtocolCreateTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->withPersonalTeam()->create();
    }

    public function test_create_page_is_rendered(): void
    {
        $this->actingAs($this->user())
            ->get(route('protocols.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Protocols/Create'));
    }

    public function test_guests_cannot_store_protocols(): void
    {
        $this->post(route('protocols.store'))->assertRedirect(route('login'));
        $this->assertDatabaseCount('protocols', 0);
    }

    public function test_protocol_is_stored_from_markdown_file(): void
    {
        $user = $this->user();
        $file = UploadedFile::fake()->createWithContent('plenum.md', "# Plenum\n\n- Punkt 1");

        $response = $this->actingAs($user)->post(route('protocols.store'), [
            'date' => '2026-11-02',
            'description' => 'Plenum November',
            'file' => $file,
        ]);

        $protocol = Protocol::sole();
        $response->assertRedirect(route('protocols.show', $protocol));
        $this->assertSame('2026-11-02', $protocol->date->toDateString());
        $this->assertSame('Plenum November', $protocol->description);
        $this->assertSame("# Plenum\n\n- Punkt 1", $protocol->text);
        $this->assertSame($user->id, $protocol->uploaded_by);
        $this->assertNull($protocol->edited_by);
    }

    public function test_topics_are_split_trimmed_and_deduplicated(): void
    {
        $this->actingAs($this->user())->post(route('protocols.store'), [
            'date' => '2026-11-02',
            'topics' => ' Budget, Events,, Budget ,',
            'file' => UploadedFile::fake()->createWithContent('plenum.md', '# Plenum'),
        ])->assertSessionHasNoErrors();

        $this->assertSame(['Budget', 'Events'], Protocol::sole()->topics);
    }

    public function test_topics_are_optional(): void
    {
        $this->actingAs($this->user())->post(route('protocols.store'), [
            'date' => '2026-11-02',
            'file' => UploadedFile::fake()->createWithContent('plenum.md', '# Plenum'),
        ])->assertSessionHasNoErrors();

        $this->assertSame([], Protocol::sole()->topics);
    }

    public function test_description_is_optional(): void
    {
        $this->actingAs($this->user())->post(route('protocols.store'), [
            'date' => '2026-11-02',
            'file' => UploadedFile::fake()->createWithContent('plenum.md', '# Plenum'),
        ])->assertSessionHasNoErrors();

        $this->assertNull(Protocol::sole()->description);
    }

    public function test_byte_order_mark_is_stripped(): void
    {
        $this->actingAs($this->user())->post(route('protocols.store'), [
            'date' => '2026-11-02',
            'file' => UploadedFile::fake()->createWithContent('plenum.md', "\xEF\xBB\xBF# Plenum"),
        ]);

        $this->assertSame('# Plenum', Protocol::sole()->text);
    }

    public function test_date_and_file_are_required(): void
    {
        $this->actingAs($this->user())
            ->post(route('protocols.store'), [])
            ->assertSessionHasErrors(['date', 'file']);
    }

    public function test_date_and_description_must_be_unique_together(): void
    {
        Protocol::factory()->create(['date' => '2026-11-02', 'description' => 'Plenum']);

        $this->actingAs($this->user())->post(route('protocols.store'), [
            'date' => '2026-11-02',
            'description' => 'Plenum',
            'file' => UploadedFile::fake()->createWithContent('plenum.md', '# Plenum'),
        ])->assertSessionHasErrors(['date' => 'A protocol with this date and description already exists.']);

        $this->assertDatabaseCount('protocols', 1);
    }

    public function test_same_date_with_other_description_is_allowed(): void
    {
        Protocol::factory()->create(['date' => '2026-11-02', 'description' => 'Plenum']);

        $this->actingAs($this->user())->post(route('protocols.store'), [
            'date' => '2026-11-02',
            'description' => 'Vorstand',
            'file' => UploadedFile::fake()->createWithContent('vorstand.md', '# Vorstand'),
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('protocols', 2);
    }

    public function test_same_date_without_description_counts_as_duplicate(): void
    {
        Protocol::factory()->create(['date' => '2026-11-02', 'description' => null]);

        $this->actingAs($this->user())->post(route('protocols.store'), [
            'date' => '2026-11-02',
            'file' => UploadedFile::fake()->createWithContent('plenum.md', '# Plenum'),
        ])->assertSessionHasErrors('date');

        $this->assertDatabaseCount('protocols', 1);
    }

    public function test_non_markdown_files_are_rejected(): void
    {
        $this->actingAs($this->user())->post(route('protocols.store'), [
            'date' => '2026-11-02',
            'file' => UploadedFile::fake()->create('plenum.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('protocols', 0);
    }

    public function test_non_utf8_files_are_rejected(): void
    {
        $this->actingAs($this->user())->post(route('protocols.store'), [
            'date' => '2026-11-02',
            'file' => UploadedFile::fake()->createWithContent('plenum.md', "\xFF\xFE# Plenum"),
        ])->assertSessionHasErrors('file');
    }
}
