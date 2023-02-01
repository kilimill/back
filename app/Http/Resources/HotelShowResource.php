<?php

namespace App\Http\Resources;

use App\Http\Services\HotelService;
use App\Models\Hotel;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelShowResource extends JsonResource
{
    private function getResource(): Hotel
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        $hotel = $this->getResource();
        $hotelService = HotelService::create();

        return [
            'name' => $hotel->name,
            'media' => MediaResource::collection($hotel->getMedia('media')),
            'description' => $hotel->description,
            'address' => $hotel->address,
            'detailed_route' => $hotel->detailed_route,
            'coordinates' => $hotel->coordinates ? explode(',', $hotel->coordinates) : null,
            'contacts' => $hotelService->getContacts($hotel),
            'tags' => TagResource::collection($hotel->tags),
            'extra' => $hotel->extra(),
            'is_favorite' => $hotelService->isFavoriteForAuthUser($hotel),
            'is_new' => $hotel->isNew(),
        ];
    }
}
