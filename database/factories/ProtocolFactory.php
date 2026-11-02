<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Protocol>
 */
class ProtocolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Unique dates keep generated protocols clear of the date + description unique key
            'date' => now()->subDays($this->faker->unique()->numberBetween(0, 3650))->toDateString(),
            'description' => $this->faker->sentence(),
            'topics' => $this->faker->words($this->faker->numberBetween(0, 3)),
            'text' => "# {$this->faker->sentence(3)}\n\n{$this->faker->paragraphs(3, true)}",
            'uploaded_by' => User::factory(),
            'edited_by' => null,
        ];
    }

    public function edited(): static
    {
        return $this->state(fn () => [
            'edited_by' => User::factory(),
        ]);
    }
}
