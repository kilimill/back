<?php

namespace App\Http\Resources;

use App\Http\Services\MediaService;
use App\Models\Contact;
use App\Models\Hotel;
use App\Models\Lake;
use App\Models\Room;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelOwnerShowResource extends JsonResource
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
            'type_id' => $hotel->type_id,
            'name' => $hotel->name,
            'description' => $hotel->description,
            'country_id' => $hotel->country_id,
            'region_id' => $hotel->region_id,
            'city_id' => $hotel->city_id,
            'address' => $hotel->address,
            'coordinates' => $hotel->coordinates ? explode(',', $hotel->coordinates) : null,
//            'custom_lake' => $hotel->custom_lake,
            'detailed_route' => $hotel->detailed_route,
            'conditions' => $hotel->conditions,
            'season_id' => $hotel->season_id,
            'min_days' => $hotel->min_days,
            'check_in_hour' => $hotel->check_in_hour,
            'check_out_hour' => $hotel->check_out_hour,

            'media' => MediaResource::collection($hotel->getMedia('media')),

            'rooms' => $hotel->roomsGroup()->map(function (Room $room) {
                return [
                    'id' => $room->getKey(),
                    'group_id' => $room->group_id,
                    'name' => $room->name,
                    'description' => $room->description,
                    'guest_count' => $room->guest_count,
                    'meals_id' => $room->meals_id,
                    'price' => $room->price,
                    'price_weekend' => $room->price_weekend,
                    'quantity' => $room['quantity'],
                    'media' => MediaResource::collection($room->getMedia('media')),
                ];
            }),
            'tags' => $hotel->tags->pluck('id')->toArray(),
            'lakes' => $hotel->lakes->map(function (Lake $lake) {
                return [
                    'id' => $lake->getKey(),
                    'distance_shore' => $lake->pivot->distance_shore,
                ];
            }),
            'contacts' =>$hotel->contacts->map(function (Contact $contact) {
                return [
                    'id' => $contact->getKey(),
                    'type_id' => $contact->type_id,
                    'value' => $contact->value,
                ];
            }),
        ];
    }
}
