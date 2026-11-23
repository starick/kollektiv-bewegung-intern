<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * What a shared protocol shows to people outside the team.
 *
 * No id, because the ULID contains the creation time, and no uploader, editor or timestamps.
 */
class PublicProtocolResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->date->toDateString(),
            'description' => $this->description,
            'topics' => $this->topics ?? [],
            'participants' => $this->participants ?? [],
        ];
    }
}
