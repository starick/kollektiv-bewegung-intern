<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesWikiCategory;
use App\Models\WikiCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateWikiCategoryRequest extends FormRequest
{
    use ValidatesWikiCategory;

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('wiki_category'));
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
        $category = $this->route('wiki_category');

        return [
            $this->uniqueAmongSiblings($category),
            // A category can't move into itself or below one of its own subcategories
            function (Validator $validator) use ($category) {
                if ($validator->errors()->has('parent_id') || ! $this->filled('parent_id')) {
                    return;
                }

                $parent = WikiCategory::find($this->input('parent_id'));

                if ($parent && ($parent->is($category) || $parent->isDescendantOf($category))) {
                    $validator->errors()->add('parent_id', __('A category can\'t be moved into itself or one of its subcategories.'));
                }
            },
        ];
    }
}
