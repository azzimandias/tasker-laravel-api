<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'is_done' => $this->is_done,
            'is_flagged' => $this->is_flagged,
            'priority' => $this->priority,
            'deadline' => $this->deadline?->timestamp,

            // Отношения
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'possibleTags' => [],
            'list' => new PersonalListResource($this->whenLoaded('personalList')),
            'user' => new UserResource($this->whenLoaded('assignedTo')),

            // Timestamps
            'created_at' => $this->created_at?->timestamp,
            'updated_at' => $this->updated_at?->timestamp,
            'deleted_at' => $this->deleted_at?->timestamp,
        ];
    }
}
