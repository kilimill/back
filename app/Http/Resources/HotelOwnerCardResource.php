<?php

namespace App\Http\Resources;

use App\Http\Services\HotelService;
use App\Http\Services\MediaService;
use App\Models\Hotel;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelOwnerCardResource extends JsonResource
{
    private function getResource(): Hotel
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        $hotel = $this->getResource();

        return [
            'id' => $hotel->getKey(),
            'status_id' => $hotel->status_id,
            'name' => $hotel->name,
            'city' => $hotel->city?->name,
            'preview' => MediaService::create()->getPreview($hotel),
            'min_price' => $hotel->getMinPriceRoom(),
            'is_favorite' => HotelService::create()->isFavoriteForAuthUser($hotel),
        ];
    }
}
