<?php

namespace App\Http\Resources\Availability;

use App\Http\Resources\Property\PropertyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailabilityResource extends JsonResource
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
            'property_id' => $this->property_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'property' => new PropertyResource($this->whenLoaded('property')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
