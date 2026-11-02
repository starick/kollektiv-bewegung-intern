<?php

namespace App\ValueObjects;

use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Support\Collection;
use function PHPUnit\Framework\isString;

/**
 * A single row of an uploaded course sheet. Holds the rules for reading the values shared by all course imports.
 */
class CourseRow
{
    private const DATE_FORMAT = 'd.m.Y H:i';

    private const DEFAULT_LOCATIONS = ['', 'Bewegungsraum', 'Freiraum', 'Am Mittelhafen 42'];

    public function __construct(
        private readonly Collection $data,
        private readonly DateTimeZone $timeZone,
    ) {
    }

    public function title(): ?string
    {
        $title = $this->data->get('titel');

        if ($title === null || $title === '') {
            return null;
        }
        if (isString($title) ) {
            return (string)$title;
        }

        return null;
    }

    /**
     * Parses the start date of the course. Returns null if it is missing or not in the expected format.
     */
    public function startDate(): ?Carbon
    {
        $startDate = $this->data->get('start_datum');
        if (!is_string($startDate) || $startDate === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat(self::DATE_FORMAT, $startDate, $this->timeZone) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function startTime(): Time
    {
        return Time::parse($this->data->get('start_datum'));
    }

    public function endTime(): Time
    {
        return Time::parse($this->data->get('end_datum'));
    }

    /**
     * Returns who leads the course, falling back to the description if no one is given.
     */
    public function instructor(): ?string
    {
        $instructor = $this->data->get('wer');
        if ($instructor === null || $instructor === '') {
            $instructor = $this->data->get('beschreibung');
        }

        return $instructor === '' ? null : $instructor;
    }

    /**
     * Returns the location of the course, or null for the default rooms that don't need to be shown.
     */
    public function location(): ?string
    {
        $location = $this->data->get('wo');

        return in_array($location, self::DEFAULT_LOCATIONS) ? null : $location;
    }

    public function isCourse(): bool
    {
        return $this->title() !== null && $this->startDate() !== null;
    }
}
