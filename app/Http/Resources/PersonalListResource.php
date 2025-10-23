<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalListResource extends JsonResource
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
            'count_of_active_tasks' => $this->count_of_active_tasks,
            'color' => $this->color,

            // Отношения (только если они загружены)
            'owner' => new UserResource($this->whenLoaded('user')),

            // Timestamps
            'created_at' => $this->created_at?->timestamp,
            'updated_at' => $this->updated_at?->timestamp,
            'deleted_at' => $this->deleted_at?->timestamp,
        ];
    }
}
