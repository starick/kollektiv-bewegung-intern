<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesProtocolDetails;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProtocolRequest extends FormRequest
{
    use ValidatesProtocolDetails;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('protocol'));
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
            // Same size limit as the uploaded Markdown file (2 MB)
            'text' => ['required', 'string', 'max:2097152'],
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        return [$this->uniqueDateAndDescription($this->route('protocol'))];
    }
}
