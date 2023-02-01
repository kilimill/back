<?php

namespace App\Http\Resources\Booking;

use App\Http\Resources\MediaResource;
use App\Http\Services\MediaService;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    private function getResource(): Booking
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        $booking = $this->getResource();

        return [
            'id' => $booking->getKey(),
            'hotel_id' => $booking->hotel->getKey(),
            'user_name' => $booking->user->name,
            'status' => $booking->status_id,
            'hotel' => $booking->hotel->name,
            'guest_name' => $booking->guest_name,
            'phone' => $booking->phone,
            'email' => $booking->email,
            'comment' => $booking->comment,
            'adult_count' => $booking->adult_count,
            'child_count' => $booking->child_count,
            'check_in' => $booking->check_in->toDateString(),
            'check_out' => $booking->check_out->toDateString(),
            'check_in_hour' => $booking->hotel->check_in_hour,
            'check_out_hour' => $booking->hotel->check_out_hour,
            'count_nights' => $booking->count_nights,
            'discount' => $booking->discount,
            'total_price' => $booking->total_price,
            'media' => MediaResource::collection($booking->hotel->getMedia('media')),
            'extra' => $booking->hotel->extra(),

            'rooms' => $booking->hotel->roomsGroup($booking->rooms->pluck('id')->toArray())->map(function (Room $room) {
                return [
                    'name' => $room->name,
                    'group_id' => $room->group_id,
                    'guest_count' => $room->guest_count,
                    'meals_id' => $room->meals_id,
                    'price' => $room->price,
                    'quantity' => $room['quantity'],
                    'preview' => MediaService::create()->getPreview($room),
                ];
            }),
        ];
    }
}
