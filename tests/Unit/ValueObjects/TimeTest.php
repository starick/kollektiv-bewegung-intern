<?php

namespace Tests\Unit\ValueObjects;

use App\ValueObjects\Time;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TimeTest extends TestCase
{
    
    public function test_creates_valid_instance_from_hours_and_minutes(): void
    {
        $time = Time::parse('13:45');
        $this->assertSame(13, $time->hour);
        $this->assertSame(45, $time->minute);
        $this->assertSame('13:45', $time->toString());
    }
    
    public function test_accepts_time_with_seconds_and_ignores_them(): void
    {
        $time = Time::parse('09:15:59');
        $this->assertSame(9, $time->hour);
        $this->assertSame(15, $time->minute);
    }
    
    public function test_throws_for_invalid_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Time::parse('abc');
        $this->expectException(InvalidArgumentException::class);
        Time::parse('99');
        $this->expectException(InvalidArgumentException::class);
        Time::parse('20:A');
    }
    
    public function test_throws_for_hour_out_of_range(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $time = new Time(99, 0);
    }
    
    public function test_throws_for_minute_out_of_range(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $time = new Time(10, 80);
    }
    
    public function test_to_minutes_returns_total_minutes_since_midnight(): void
    {
        $time = Time::parse('02:30');
        $this->assertSame(150, $time->toMinutes());
    }

    public function from_minutes_builds_correct_time(): void
    {
        $time = Time::fromMinutes(150);
        $this->assertSame('02:30', $time->toString());
        $this->assertSame(2, $time->hour);
        $this->assertSame(30, $time->minute);
    }
    
    public function test_from_minutes_limits_to_23_59(): void
    {
        $time = Time::fromMinutes(20000);
        $this->assertSame('23:59', $time->toString());
    }
    
    public function test_from_minutes_treats_negative_as_absolute_value(): void
    {
        $time = Time::fromMinutes(-120);
        $this->assertSame('02:00', $time->toString());
    }
    
    public function test_json_serialization_returns_string(): void
    {
        $time = Time::parse('14:05');
        $this->assertSame('"14:05"', json_encode($time));
    }
}
