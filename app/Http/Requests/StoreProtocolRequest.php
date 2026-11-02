<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesProtocolDetails;
use App\Models\Protocol;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StoreProtocolRequest extends FormRequest
{
    use ValidatesProtocolDetails;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Protocol::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->detailRules(),
            'file' => [
                'bail',
                'required',
                'file',
                'extensions:md,markdown',
                'max:2048',
                function (string $attribute, UploadedFile $file, Closure $fail) {
                    if (! mb_check_encoding($file->get(), 'UTF-8')) {
                        $fail(__('The file must be UTF-8 encoded text.'));
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
        return [$this->uniqueDateAndDescription()];
    }
}
