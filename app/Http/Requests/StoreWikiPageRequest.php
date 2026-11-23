<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesWikiPage;
use App\Models\WikiPage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWikiPageRequest extends FormRequest
{
    use ValidatesWikiPage;

    public function authorize(): bool
    {
        return $this->user()->can('create', WikiPage::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->pageRules();
    }
}
