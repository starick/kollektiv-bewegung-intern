<?php

namespace Tests\Feature\Locale;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_default_to_german(): void
    {
        $user = User::factory()->create();

        $this->assertSame('de', $user->fresh()->locale);
    }

    public function test_guests_get_the_german_default_locale(): void
    {
        $this->get('/login')
            ->assertInertia(fn (Assert $page) => $page
                ->where('locale', 'de')
                ->where('translations.Log in', 'Einloggen'));
    }

    public function test_the_users_locale_is_applied_to_requests(): void
    {
        $this->actingAs(User::factory()->create(['locale' => 'en']));

        $this->get('/user/profile')
            ->assertInertia(fn (Assert $page) => $page
                ->where('locale', 'en')
                ->where('translations', []));
    }

    public function test_validation_messages_are_german_by_default(): void
    {
        $this->actingAs(User::factory()->create());

        $this->put('/user/locale', ['locale' => ''])
            ->assertSessionHasErrors(['locale' => 'Sprache muss ausgefüllt werden.']);
    }

    public function test_users_can_update_their_locale(): void
    {
        $this->actingAs($user = User::factory()->create());

        $this->put('/user/locale', ['locale' => 'en'])->assertRedirect();

        $this->assertSame('en', $user->fresh()->locale);
    }

    public function test_unsupported_locales_are_rejected(): void
    {
        $this->actingAs($user = User::factory()->create());

        $this->put('/user/locale', ['locale' => 'fr'])->assertSessionHasErrors('locale');

        $this->assertSame('de', $user->fresh()->locale);
    }

    public function test_guests_cannot_update_a_locale(): void
    {
        $this->put('/user/locale', ['locale' => 'en'])->assertRedirect('/login');
    }
}
