<?php

namespace Tests\Unit\Models;

use App\Models\Course;
use App\Models\TimeTable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_time_table_relation(): void
    {
        $relation = (new Course())->timeTable();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertSame(TimeTable::class, $relation->getRelated()::class);
        $this->assertSame('time_table_id', $relation->getForeignKeyName());
    }
}
