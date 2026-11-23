<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WikiCategory>
 */
class WikiCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Unique names keep generated siblings clear of the name check
            'name' => ucfirst($this->faker->unique()->words(2, true)),
            'parent_id' => null,
        ];
    }
}
