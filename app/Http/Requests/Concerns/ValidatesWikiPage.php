<?php

namespace App\Http\Requests\Concerns;

/**
 * Validation shared by creating and updating wiki pages.
 */
trait ValidatesWikiPage
{
    /**
     * @return array<string, array<mixed>>
     */
    protected function pageRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'string', 'exists:wiki_categories,id'],
            // Same size limit as protocols (2 MB), an empty text is fine
            'text' => ['present', 'nullable', 'string', 'max:2097152'],
            'pinned' => ['boolean'],
        ];
    }

    /**
     * The validated attributes ready to save.
     *
     * @return array<string, mixed>
     */
    public function pageAttributes(): array
    {
        return [
            'title' => $this->validated('title'),
            'category_id' => $this->validated('category_id'),
            // Empty input arrives as null, the column holds an empty string instead
            'text' => $this->validated('text') ?? '',
            'pinned' => $this->boolean('pinned'),
        ];
    }
}
