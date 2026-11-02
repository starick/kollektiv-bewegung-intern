<?php

namespace App\Http\Requests;

use App\Models\Protocol;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

class StoreProtocolRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'description' => ['nullable', 'string', 'max:255'],
            'topics' => ['nullable', 'string', 'max:1000'],
            'file' => [
                'required',
                'file',
                'extensions:md,markdown',
                'max:2048',
                function (string $attribute, UploadedFile $file, Closure $fail) {
                    if (! mb_check_encoding($file->get(), 'UTF-8')) {
                        $fail('The file must be UTF-8 encoded text.');
                    }
                },
            ],
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
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
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('date', 'A protocol with this date and description already exists.');
                }
            },
        ];
    }

    /**
     * Topics from the comma separated input, trimmed and without empty entries or duplicates.
     *
     * @return array<int, string>
     */
    public function topics(): array
    {
        return collect(explode(',', (string) $this->validated('topics')))
            ->map(fn (string $topic) => trim($topic))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
