<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WikiPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'categoryId' => $this->category_id,
            'pinned' => $this->pinned,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            // Only id and name, other members' email addresses stay on the server
            'creator' => $this->whenLoaded('creator', fn () => $this->creator?->only('id', 'name')),
            'editor' => $this->whenLoaded('editor', fn () => $this->editor?->only('id', 'name')),
        ];
    }
}
