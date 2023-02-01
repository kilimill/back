<?php

namespace App\Http\Services;

use App\Models\Room;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RoomService
{
    use ServiceInstance;

    private Builder $builder;
    private ?int $hotelId = null;
    private ?int $guestCount;
    private ?string $checkIn;
    private ?string $checkOut;

    public function getAvailableRoomsBuilder(): Builder
    {
        $this->builder->when($this->hotelId, function (Builder $q) {
            $q->where('hotel_id', $this->hotelId);
        });

        $this->builder->where(function (Builder $q) {
            $q->whereHas('bookings', function (Builder $q) {
                $q->when(($this->checkIn && $this->checkOut), function (Builder $q) {
                    $q->whereNot(function (Builder $q) {
                        $q->where('check_in', '>=', $this->checkIn)
                            ->where('check_out', '<=', $this->checkOut);
                    })
                        ->whereNot(function (Builder $q) {
                            $q->where('check_in', '<=', $this->checkIn)
                                ->where('check_out', '>', $this->checkIn);
                        });
                });
            });
            $q->orWhereDoesntHave('bookings');
        });

        $this->builder->when($this->guestCount, function (Builder $q) {
            $q->where('guest_count', '>=', $this->guestCount);
        });

        return $this->builder;
    }

    public function setBuilder(Builder $builder): void
    {
        $this->builder = $builder;
    }

    public function setGuestCount(?int $guestCount): void
    {
        $this->guestCount = $guestCount;
    }

    public function setCheckIn(?string $checkIn): void
    {
        $this->checkIn = $checkIn;
    }

    public function setCheckOut(?string $checkOut): void
    {
        $this->checkOut = $checkOut;
    }

    public function setHotelId(int $hotelId): void
    {
        $this->hotelId = $hotelId;
    }

    public function getMeals(): Collection
    {
        return collect(Room::MEALS_IDS)->map(function (string $name, int $id) {
            return [
                'id' => $id,
                'name' => $name,
            ];
        })->values();
    }
}
