<?php

namespace App\Http\Resources;

use App\Models\Event;
use App\Services\LocationResolver;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Event */
class EventVisualResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title(),
            'description' => $this->description(),
            'type' => $this->type,
            'status' => $this->status,
            'starts_at' => $this->startsAt(),
            'ends_at' => $this->endsAt(),
            'location' => LocationResolver::resolve($this->latitude, $this->longitude, $this->venueName()),
            'images' => $this->imagePaths(),
            'attendee_count' => $this->whenCounted('attendees'),
        ];
    }
}
