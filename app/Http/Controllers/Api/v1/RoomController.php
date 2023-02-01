<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Room\AvailableRoomRequest;
use App\Http\Resources\HotelRoomResource;
use App\Http\Services\BookingService;
use App\Http\Services\RoomService;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomController extends Controller
{
    public function getHotelAvailableRooms(AvailableRoomRequest $availableRoomRequest, int $hotelId): JsonResource
    {
        $roomService = RoomService::create();
        $roomService->setBuilder(Room::query());
        $roomService->setGuestCount($availableRoomRequest->adult_count + $availableRoomRequest->child_count);
        $roomService->setCheckIn($availableRoomRequest->check_in);
        $roomService->setCheckOut($availableRoomRequest->check_out);
        $roomService->setHotelId($hotelId);
        $rooms = $roomService->getAvailableRoomsBuilder()->get();

        $roomGroups = $rooms->groupBy('group_id');
        $rooms = $roomGroups->map(function ($groups) {
            $availableIds = $groups->pluck('id')->toArray();
            $room = $groups->first();
            $room['available_ids'] = $availableIds;

            return $room;
        });

        return HotelRoomResource::collection($rooms->values());
    }

    /**
     * @throws ModelNotFoundException
     */
    public function remove(Hotel $hotel, int $roomGroupId): JsonResponse
    {
        $bookingService = BookingService::create();
        $message = 'Номер успешно удален.';

        /** @var Room $roomToRemove */
        $roomsToRemove = Room::query()
            ->where('hotel_id', $hotel->getKey())
            ->where('group_id', $roomGroupId);

        if ($roomsToRemove->doesntExist()) {
            throw new ModelNotFoundException();
        }

        $roomsToRemove->each(function (Room $room) use (&$message, $bookingService) {
            if ($bookingService->hasActiveBookings($room)) {
                $message = 'Номер успешно удален. Номер имеет активные бронирования, которые должны быть выполнены.';
            }

            if ($room->bookings()->doesntExist()) {
                $room->forceDelete();
            } else {
                $room->delete();
            }
        });

        $hotel->updateStatusToUnderReview();

        return response()->json(['message' => $message]);
    }
}
