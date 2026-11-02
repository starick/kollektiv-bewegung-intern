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
            'date' => $this->faker->dateTimeBetween('-1 year')->format('Y-m-d'),
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
