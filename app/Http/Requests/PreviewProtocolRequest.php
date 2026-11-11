<?php

namespace App\Http\Requests;

use App\Models\Protocol;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PreviewProtocolRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Protocol::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Same limit as saving, an empty text simply previews as nothing
            'text' => ['present', 'nullable', 'string', 'max:2097152'],
        ];
    }
}
