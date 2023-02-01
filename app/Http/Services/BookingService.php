<?php

namespace App\Http\Services;

use App\Http\Requests\Api\v1\Booking\BookingInitRequest;
use App\Http\Requests\Api\v1\Booking\BookingProcessRequest;
use App\Http\Requests\Api\v1\PaginatedRequest;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BookingService
{
    use ServiceInstance;
    use ServicePaginate;

    public function createInit(BookingInitRequest $bookingInitRequest): Booking
    {
        $booking = new Booking();
        $booking->status_id = Booking::STATUS_ID_PREPARE;
        $booking->hotel_id = $bookingInitRequest->hotel->getKey();

        if ($user = auth_user_or_null()) {
            $booking->user_id = $user->getKey();
            $booking->guest_name = $user->name;
            $booking->phone = $user->phone;
            $booking->email = $user->email;
        }

        $booking->adult_count = $bookingInitRequest->adult_count;
        $booking->child_count = $bookingInitRequest->child_count ?? 0;
        $booking->check_in = $bookingInitRequest->check_in;
        $booking->check_out = $bookingInitRequest->check_out;
        $booking->discount = $bookingInitRequest->discount ?? 0;

        $countNights = $this->countNights($bookingInitRequest->check_in, $bookingInitRequest->check_out);
        $booking->count_nights = $countNights;

        $rooms = $bookingInitRequest->hotel->rooms->find($bookingInitRequest->rooms);
        $booking->total_price = $this->totalPrice($rooms, $countNights, $booking->discount);

        $booking->save();
        $booking->rooms()->saveMany($rooms);

        return $booking;
    }

    public function process(BookingProcessRequest $bookingProcessRequest): Booking
    {
        $booking = $bookingProcessRequest->booking;

        $user = auth_user_or_fail();
        if (!$bookingProcessRequest->is_another_guest) {
            if (!$user->name || !$user->email) {
                if (!$user->name) {
                    $user->name = $bookingProcessRequest->guest_name;
                }
                if (!$user->email) {
                    $user->email = $bookingProcessRequest->email;
                }
                $user->save();
            }
        }

        $booking->status_id = Booking::STATUS_ID_PROCESS;
        $booking->user_id = $user->getKey();
        $booking->guest_name = $bookingProcessRequest->guest_name;
        $booking->phone = $bookingProcessRequest->phone;
        $booking->email = $bookingProcessRequest->email;
        $booking->comment = $bookingProcessRequest->comment ?? null;
        $booking->save();

        return $booking;
    }

    public function countNights(string $checkIn, string $checkOut): int
    {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        return $checkOutDate->diffInDays($checkInDate);
    }

    private function totalPrice(Collection $rooms, int $countNights, int $discount): int
    {
        $price = 0;
        $rooms->each(function (Room $room) use (&$price) {
            $price = $price + $room->price;
        });
        $sum = $price * $countNights;

        return $sum - ($sum * ($discount / 100));
    }

    public function hasActiveBookings(Room $room): bool
    {
        return $room->bookings()->where(function (Builder $q) {
            $q->where(function (Builder $q) {
                $q->where('check_in', '<=', now())
                    ->where('check_out', '>=', now());
            })
                ->orWhere(function (Builder $q) {
                    $q->where('check_in', '>=', now());
                });
        })->exists();
    }

    public function getBookings(PaginatedRequest $paginatedRequest): Collection
    {
        $this->setPagination($paginatedRequest->page, $paginatedRequest->per_page);

        return auth_user_or_fail()
            ->bookings()
            ->with(['hotel.rooms', 'rooms', 'user'])
            ->offset(($this->page - 1) * $this->perPage)
            ->limit($this->perPage + 1)
            ->get();
    }

    public function getOwnerBookings(PaginatedRequest $paginatedRequest): Collection
    {
        $this->setPagination($paginatedRequest->page, $paginatedRequest->per_page);

        $hotels = auth_user_or_fail()
            ->hotels()
            ->with(['bookings.user','bookings.hotel','bookings.rooms'])
            ->get();

        $bookings = $hotels->map(function ($hotel){
            return $hotel->bookings;
        });

        if (!$bookings->first()){
            return collect();
        }
        return $bookings
            ->first()
            ->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage + 1);
    }
}
