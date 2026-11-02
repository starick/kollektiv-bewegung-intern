<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProtocolResource extends JsonResource
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
            'date' => $this->date->toDateString(),
            'description' => $this->description,
            'topics' => $this->topics ?? [],
            'participants' => $this->participants ?? [],
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            // Only id and name, other members' email addresses stay on the server
            'uploader' => $this->whenLoaded('uploader', fn () => $this->uploader?->only('id', 'name')),
            'editor' => $this->whenLoaded('editor', fn () => $this->editor?->only('id', 'name')),
        ];
    }
}
