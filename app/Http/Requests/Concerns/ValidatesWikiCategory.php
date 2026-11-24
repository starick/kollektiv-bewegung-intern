<?php

namespace App\Http\Requests\Concerns;

use App\Models\WikiCategory;
use Closure;
use Illuminate\Validation\Validator;

/**
 * Name and parent validation shared by creating and updating wiki categories.
 */
trait ValidatesWikiCategory
{
    /**
     * @return array<string, array<mixed>>
     */
    protected function categoryRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'string', 'exists:wiki_categories,id'],
        ];
    }

    /**
     * Rejects a name a sibling already uses, ignoring case.
     * Checked here because a unique index treats top-level categories (parent NULL) as distinct.
     */
    protected function uniqueAmongSiblings(?WikiCategory $ignore = null): Closure
    {
        return function (Validator $validator) use ($ignore) {
            if ($validator->errors()->hasAny(['name', 'parent_id'])) {
                return;
            }

            $exists = WikiCategory::query()
                ->when(
                    $this->filled('parent_id'),
                    fn ($query) => $query->where('parent_id', $this->input('parent_id')),
                    fn ($query) => $query->whereNull('parent_id'),
                )
                ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($this->input('name')))])
                ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
                ->exists();

            if ($exists) {
                $validator->errors()->add('name', __('A category with this name already exists here.'));
            }
        };
    }
}
