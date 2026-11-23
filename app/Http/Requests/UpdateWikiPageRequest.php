<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesWikiPage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWikiPageRequest extends FormRequest
{
    use ValidatesWikiPage;

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('page'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->pageRules();
    }
}
