<?php

namespace App\Http\Resources\v2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
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
            'starts_at' => $this->starts_at->toIso8601String(),
            'status' => $this->status,
            'patient' => [
                'id' => $this->patient->id,
                'name' => $this->patient->name,
            ],
            'clinic' => [
                'id' => $this->clinic->id,
                'name' => $this->clinic->name,
            ],
        ];
    }
}
