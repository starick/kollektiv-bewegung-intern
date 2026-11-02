<?php

namespace App\Http\Requests\Concerns;

use App\Models\Protocol;
use Closure;
use Illuminate\Validation\Validator;

/**
 * Date, description and topics validation shared by creating and updating protocols.
 */
trait ValidatesProtocolDetails
{
    /**
     * @return array<string, array<mixed>>
     */
    protected function detailRules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'description' => ['nullable', 'string', 'max:255'],
            'topics' => ['nullable', 'string', 'max:1000'],
            'participants' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Rejects a date and description combination another protocol already uses.
     */
    protected function uniqueDateAndDescription(?Protocol $ignore = null): Closure
    {
        return function (Validator $validator) use ($ignore) {
            if ($validator->errors()->has('date') || $validator->errors()->has('description')) {
                return;
            }

            // whereDate keeps the check independent of how the driver stores the date column.
            // Empty descriptions count as equal here, which the unique index (NULLs are distinct) can't enforce.
            $exists = Protocol::whereDate('date', $this->input('date'))
                ->when(
                    $this->filled('description'),
                    fn ($query) => $query->where('description', $this->input('description')),
                    fn ($query) => $query->whereNull('description'),
                )
                ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
                ->exists();

            if ($exists) {
                $validator->errors()->add('date', __('A protocol with this date and description already exists.'));
            }
        };
    }

    /**
     * Topics from the comma separated input.
     *
     * @return array<int, string>
     */
    public function topics(): array
    {
        return $this->commaSeparated('topics');
    }

    /**
     * Participants from the comma separated input.
     *
     * @return array<int, string>
     */
    public function participants(): array
    {
        return $this->commaSeparated('participants');
    }

    /**
     * Entries of a comma separated input, trimmed and without empty entries or duplicates.
     *
     * @return array<int, string>
     */
    private function commaSeparated(string $key): array
    {
        return collect(explode(',', (string) $this->validated($key)))
            ->map(fn (string $topic) => trim($topic))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
