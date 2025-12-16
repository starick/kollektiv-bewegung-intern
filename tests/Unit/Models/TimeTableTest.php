<?php

namespace Tests\Unit\Models;

use App\Models\Course;
use App\Models\TimeTable;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeTableTest extends TestCase
{
    use RefreshDatabase;
public function test_year_is_required_on_create(): void
{
    $this->expectException(QueryException::class);

    TimeTable::create([
        'week' => 5,
        'created_by' => null,
        'display_config' => [],
    ]);
}

public function test_week_is_required_on_create(): void
{
    $this->expectException(QueryException::class);

    TimeTable::create([
        'year' => 2025,
        'created_by' => null,
        'display_config' => [],
    ]);
}

    public function test_start_and_end_time_accessors_match_iso_week_bounds(): void
    {
        $tt = TimeTable::create([
            'year' => 2025,
            'week' => 1,
            'created_by' => null,
            'display_config' => ['view' => 'week'],
        ]);

        $expectedStart = Carbon::now()->setISODate(2025, 1, 1)->startOfDay();
        $expectedEnd   = Carbon::now()->setISODate(2025, 1, 7)->endOfDay();

        $this->assertSame($expectedStart->format('Y-m-d H:i:s'), $tt->start_time);
        $this->assertSame($expectedEnd->format('Y-m-d H:i:s'), $tt->end_time);
    }

    public function test_is_within_time_range_is_inclusive_on_valid_rows(): void
    {
        $tt = TimeTable::create([
            'year' => 2025,
            'week' => 1,
            'display_config' => [],
        ]);

        $start = Carbon::parse($tt->start_time);
        $end   = Carbon::parse($tt->end_time);

        $this->assertTrue($tt->isWithinTimeRange($start));
        $this->assertTrue($tt->isWithinTimeRange($start->copy()->addDays(3)));
        $this->assertTrue($tt->isWithinTimeRange($end));

        $this->assertFalse($tt->isWithinTimeRange($start->copy()->subSecond()));
        $this->assertFalse($tt->isWithinTimeRange($end->copy()->addSecond()));
    }

    public function test_display_config_is_json_casted_to_array(): void
    {
        $config = ['view' => 'agenda', 'collapse' => true];

        $tt = TimeTable::create([
            'year' => 2024,
            'week' => 10,
            'display_config' => $config,
        ]);

        $this->assertIsArray($tt->fresh()->display_config);
        $this->assertSame($config, $tt->fresh()->display_config);
    }

    public function test_courses_relation_is_has_many(): void
    {
        $relation = (new TimeTable())->courses();
        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertSame(Course::class, $relation->getRelated()::class);
    }

    public function test_creator_relation_is_belongs_to_user(): void
    {
        $relation = (new TimeTable())->creator();
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertSame(User::class, $relation->getRelated()::class);
        $this->assertSame('created_by', $relation->getForeignKeyName());
    }
}
