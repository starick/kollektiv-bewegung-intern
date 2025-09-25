<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeTableResource extends JsonResource
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
            'year' => $this->year,
            'week' => $this->week,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'courses' => CourseResource::collection($this->whenLoaded('courses')),
            'coursesCount' => $this->courses()->count(),
        ];
    }
}
