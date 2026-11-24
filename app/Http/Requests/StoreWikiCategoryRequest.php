<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesWikiCategory;
use App\Models\WikiCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWikiCategoryRequest extends FormRequest
{
    use ValidatesWikiCategory;

    public function authorize(): bool
    {
        return $this->user()->can('create', WikiCategory::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->categoryRules();
    }

    public function after(): array
    {
        return [$this->uniqueAmongSiblings()];
    }
}
