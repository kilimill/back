<?php

namespace App\Http\Resources\Booking;

use App\Http\Services\MediaService;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingCardResource extends JsonResource
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
            'guest_count' => $booking->adult_count + $booking->child_count,
            'check_in' => $booking->check_in->toDateString(),
            'check_out' => $booking->check_out->toDateString(),
            'count_nights' => $booking->count_nights,
            'total_price' => $booking->total_price,
            'preview' => MediaService::create()->getPreview($booking->hotel),

            'rooms' => $booking->hotel->roomsGroup($booking->rooms->pluck('id')->toArray())->map(function (Room $room) {
                return [
                    'name' => $room->name,
                ];
            }),
        ];
    }
}
