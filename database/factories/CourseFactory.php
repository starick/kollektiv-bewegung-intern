<?php

namespace Database\Factories;

use App\ValueObjects\Time;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'instructor' => $this->faker->name() . ' (' . $this->faker->phoneNumber() . ')',
            'location' => $this->faker->optional(0.1)->word(),
            'start_time' => Time::random($this->faker),
            'end_time' => Time::random($this->faker),
            'date' =>  Carbon::instance($this->faker->dateTimeBetween('-1 week', '+1 week')),
        ];
    }
}
