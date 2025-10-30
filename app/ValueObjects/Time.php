<?php

namespace App\ValueObjects;

use \Faker\Generator;
use InvalidArgumentException;
use JsonSerializable;
use Stringable;

/**
 * Time value object representing hours and minutes. Used as an equivalent for the SQL TIME type, but  ditching seconds.
 */
class Time implements Stringable, JsonSerializable
{
    public readonly int $hour;
    public readonly int $minute;

    /**
     * Create a new Time instance.
     * @param int $hour only accepts values between 0 and 23
     * @param int $minute only accepts values between 0 and 59
     * @throws \InvalidArgumentException
     */
    public function __construct(int $hour, int $minute)
    {
        if ($hour < 0 || $hour > 23) {
            throw new InvalidArgumentException('Hour must be between 0 and 23');
        }
        if ($minute < 0 || $minute > 59) {
            throw new InvalidArgumentException('Minute must be between 0 and 59');
        }
        $this->hour = $hour;
        $this->minute = $minute;
    }

    /**
     * Parse a string as a new Time instance. Expected input format is either HH:MM or HH:MM:SS.
     * @param string $time
     * @throws \InvalidArgumentException
     * @return Time
     */
    public static function parse(string $time): self
    {
    $time = trim($time);

    if (preg_match('/^(?<h>\d{1,2}):(?<m>\d{2})(?::\d{2})?$/', $time, $match) === 1) {
        return new self((int)$match['h'], (int)$match['m']);
    }

    if (preg_match('/\b(?<h>\d{1,2}):(?<m>\d{2})(?::\d{2})?\b/', $time, $match) === 1) {
        return new self((int)$match['h'], (int)$match['m']);
    }

    throw new InvalidArgumentException(
        'Expected time format HH:MM, HH:MM:SS, or a string containing a time like "31.12.2024 10:00".'
    );
    }

    public function toMinutes(): int
    {
        return $this->hour * 60 + $this->minute;
    }

    public static function fromMinutes(int $minutes): self
    {
        $minutes = abs($minutes);
        $minutes = min (23*60 + 59, $minutes);

        return new self(intdiv($minutes, 60), $minutes % 60);
    }

    public function __toString(): string
    {
        return sprintf('%02d:%02d', $this->hour, $this->minute);
    }

    public function toString(): string
    {
        return (string)$this;
    }

    public function jsonSerialize(): string
    {
        return (string)$this;
    }

    public static function random(Generator $faker) : self {
        $hour = $faker->numberBetween(0, 23);
        $minute = $faker->numberBetween(0, 59);

        return new self($hour, $minute);
    }
}
