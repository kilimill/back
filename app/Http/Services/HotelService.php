<?php

namespace App\Http\Services;

use App\Http\Requests\Api\v1\Hotel\HotelRequest;
use App\Http\Requests\Api\v1\Hotel\HotelSearchRequest;
use App\Http\Requests\Api\v1\PaginatedRequest;
use App\Models\Contact;
use App\Models\Hotel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HotelService
{
    use ServiceInstance;
    use ServicePaginate;

    public function getHotelsBuilder(HotelRequest $hotelRequest): Builder
    {
        $this->setPagination($hotelRequest->page, $hotelRequest->per_page);

        return Hotel::query()->with(['city', 'rooms'])
            ->whereHas('city', function (Builder $query) use ($hotelRequest) {
                $query->where('name', $hotelRequest->location);
            })
            ->where('status_id', Hotel::STATUS_ID_ACTIVE)
            ->offset(($this->page - 1) * $this->perPage)
            ->limit($this->perPage + 1);
    }

    public function searchHotels(HotelSearchRequest $hotelSearchRequest): Collection
    {
        $this->setPagination($hotelSearchRequest->page, $hotelSearchRequest->per_page);

        $roomService = RoomService::create();

        $hotels = Hotel::query()->with(['city', 'rooms', 'tags'])
            ->where(function (Builder $query) use ($hotelSearchRequest) {
                $query->where('name', 'LIKE', '%' . $hotelSearchRequest->location . '%')
                    ->orWhereHas('city', function (Builder $query2) use ($hotelSearchRequest) {
                        $query2->where('name', 'LIKE', $hotelSearchRequest->location . '%');
                    });
            })
            ->when(($hotelSearchRequest->check_in && $hotelSearchRequest->check_out), function (Builder $query) use ($hotelSearchRequest) {
                $bookingService = BookingService::create();
                $countNights = $bookingService->countNights($hotelSearchRequest->check_in, $hotelSearchRequest->check_out);
                $query->where('min_days', '<=', $countNights);
            })
            ->whereHas('rooms', function (Builder $query) use ($roomService, $hotelSearchRequest) {
                $roomService->setBuilder($query);
                $roomService->setGuestCount($hotelSearchRequest->guest_count);
                $roomService->setCheckIn($hotelSearchRequest->check_in);
                $roomService->setCheckOut($hotelSearchRequest->check_out);

                $roomsBuilder = $roomService->getAvailableRoomsBuilder();

                if ($hotelSearchRequest->min_price && $hotelSearchRequest->max_price) {
                    $roomsBuilder->whereBetween('price', [$hotelSearchRequest->min_price, $hotelSearchRequest->max_price]);
                }

                if ($hotelSearchRequest->min_price && !$hotelSearchRequest->max_price) {
                    $roomsBuilder->where('price', '>=', $hotelSearchRequest->min_price);
                }

                if (!$hotelSearchRequest->min_price && $hotelSearchRequest->max_price) {
                    $roomsBuilder->where('price', '<=', $hotelSearchRequest->max_price);
                }

                $roomsBuilder->when($hotelSearchRequest->meals_id, function (Builder $query, int $mealsId) {
                    $query->where('meals_id', $mealsId);
                });
            })
            ->when($hotelSearchRequest->tags, function (Builder $query, array $tags) {
                $query->whereHas('tags', function (Builder $query2) use ($tags) {
                    $query2->whereIn('id', $tags);
                });
            });

        return $hotels->where('status_id', Hotel::STATUS_ID_ACTIVE)
            ->orderBy($hotelSearchRequest->sort_field ?? 'name', $hotelSearchRequest->sort_direction ?? 'asc')
            ->offset(($this->page - 1) * $this->perPage)
            ->limit($this->perPage + 1)
            ->get();
    }

    public function getFavoriteHotels(PaginatedRequest $paginatedRequest): Collection
    {
        $this->setPagination($paginatedRequest->page, $paginatedRequest->per_page);

        return auth_user_or_fail()
            ->favoriteHotels()
            ->with('city', 'rooms')
            ->offset(($this->page - 1) * $this->perPage)
            ->limit($this->perPage + 1)
            ->get();
    }

    public function getOwnerHotels(PaginatedRequest $paginatedRequest): Collection
    {
        $this->setPagination($paginatedRequest->page, $paginatedRequest->per_page);

        return auth_user_or_fail()
            ->hotels()
            ->with('city', 'rooms')
            ->offset(($this->page - 1) * $this->perPage)
            ->limit($this->perPage + 1)
            ->get();
    }

    public function getContacts(Hotel $hotel): Collection
    {
        return $hotel->contacts->map(function (Contact $contact) {
            return [
                'value' => $contact->value,
                'type' => $contact->type_id,
            ];
        });
    }

    public function isFavoriteForAuthUser(Hotel $hotel): bool
    {
        $isFavorite = false;
        if ($user = auth_user_or_null()) {
            $isFavorite = $user->favoriteHotels->contains(function (Hotel $favoriteHotel) use ($hotel) {
                return $favoriteHotel->getKey() === $hotel->getKey();
            });
        }

        return $isFavorite;
    }

    public function getAllHotels(): Collection
    {
        return Hotel::query()->with(['city', 'rooms'])
            ->where('status_id', Hotel::STATUS_ID_ACTIVE)
            ->get();
    }
}
