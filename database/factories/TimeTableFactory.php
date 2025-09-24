<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\TimeTable;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeTable>
 */
class TimeTableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = Carbon::now()->year;
        $week = Carbon::now()->weekOfYear;

        return [
            'year' => Carbon::now()->year,
            'week' => Carbon::now()->weekOfYear,
            'created_by' => $this->faker->randomElement(User::all())->id,
        ];
    }

    public function withDailyCourses(int $min = 1, int $max = 5): static
    {
        return $this->afterCreating(function (TimeTable $timeTable) use ($min, $max) {

            $start = Carbon::now()->setISODate($timeTable->year, $timeTable->week)->setTime(3, 0);
            $end = $start->copy()->addDays(6);

            $period = CarbonPeriod::create($start, $end);


            $date = $start->copy();

            while ($date <= $end) {

                $count = $this->faker->numberBetween($min, $max);

                if ($count <= 0) {
                    continue;
                }

                $randomTime = $date->copy()
                    ->setHour($this->faker->numberBetween(8, 18))
                    ->setMinute($this->faker->randomElement([0, 15, 30, 45]));


                Course::factory()->count($count)->create([
                    'time_table_id' => $timeTable->id,
                    'start_time' => $randomTime,
                    'end_time' => $randomTime->copy()->addMinutes($this->faker->randomElement([60, 90, 120])),
                ]);

                $date->addDay();
            }
        });
    }
}
