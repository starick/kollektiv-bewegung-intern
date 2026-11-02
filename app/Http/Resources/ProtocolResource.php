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
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'uploader' => $this->whenLoaded('uploader', fn () => $this->uploader
                ? (new UserResource($this->uploader))->resolve()
                : null),
            'editor' => $this->whenLoaded('editor', fn () => $this->editor
                ? (new UserResource($this->editor))->resolve()
                : null),
        ];
    }
}
