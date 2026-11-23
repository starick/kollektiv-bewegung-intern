<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WikiCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WikiPage>
 */
class WikiPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'category_id' => null,
            'text' => "# {$title}\n\n{$this->faker->paragraphs(2, true)}",
            'pinned' => false,
            'created_by' => User::factory(),
            'edited_by' => null,
        ];
    }

    public function inCategory(WikiCategory $category): static
    {
        return $this->state(fn () => ['category_id' => $category->id]);
    }

    public function pinned(): static
    {
        return $this->state(fn () => ['pinned' => true]);
    }
}
