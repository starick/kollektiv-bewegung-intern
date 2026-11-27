<?php

namespace App\Auditing;

use Illuminate\Database\Eloquent\Collection;
use OwenIt\Auditing\Models\Audit;

/**
 * Merges updates the same user saved in quick succession into one trail entry,
 * so fixing a typo right after saving does not add a second entry.
 *
 * Only the presentation changes: the audits in the database stay untouched.
 */
class AuditSquasher
{
    /**
     * @param  Collection<int, Audit>  $audits  Newest first
     * @param  int  $minutes  Maximum gap between two saves of the same run
     * @return Collection<int, Audit> Newest first
     */
    public static function squash(Collection $audits, int $minutes): Collection
    {
        /** @var array<int, Audit> $squashed */
        $squashed = [];

        // Walk oldest to newest, so each audit is compared with the entry right before it
        foreach ($audits->reverse() as $audit) {
            $previous = end($squashed) ?: null;

            if ($previous && self::continues($previous, $audit, $minutes)) {
                array_pop($squashed);
                $audit = self::merge($previous, $audit);

                if ($audit === null) {
                    continue;
                }
            }

            $squashed[] = $audit;
        }

        return new Collection(array_reverse($squashed));
    }

    private static function continues(Audit $previous, Audit $audit, int $minutes): bool
    {
        return $previous->event === 'updated'
            && $audit->event === 'updated'
            && $previous->user_id !== null
            && $previous->user_id === $audit->user_id
            && $previous->user_type === $audit->user_type
            && $previous->created_at->diffInMinutes($audit->created_at, true) < $minutes;
    }

    /**
     * Combines two consecutive updates: the old values of the first with the new values of the last.
     * Returns null when the run ended where it started.
     */
    private static function merge(Audit $earlier, Audit $later): ?Audit
    {
        $old = $earlier->old_values ?? [];
        $new = $earlier->new_values ?? [];

        foreach ($later->new_values ?? [] as $attribute => $value) {
            if (! array_key_exists($attribute, $old)) {
                $old[$attribute] = $later->old_values[$attribute] ?? null;
            }
            $new[$attribute] = $value;
        }

        // Attributes that were changed and changed back are no change at all
        foreach (array_keys($new) as $attribute) {
            if (self::same($old[$attribute] ?? null, $new[$attribute])) {
                unset($old[$attribute], $new[$attribute]);
            }
        }

        if ($new === []) {
            return null;
        }

        // A copy of the latest audit keeps its id, time and user; it is never saved
        $merged = clone $later;
        $merged->old_values = $old;
        $merged->new_values = $new;

        return $merged;
    }

    /**
     * Audits store values in their database form: an old boolean is 0/1, a new one true/false.
     */
    private static function same(mixed $a, mixed $b): bool
    {
        if (is_bool($a) || is_bool($b)) {
            return (bool) $a === (bool) $b;
        }

        return $a === $b;
    }
}
