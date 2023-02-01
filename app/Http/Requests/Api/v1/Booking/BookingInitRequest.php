<?php

namespace App\Http\Requests\Api\v1\Booking;

use App\Http\Requests\Api\NolloApiRequest;
use App\Http\Services\BookingService;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * /**
 * @property-read Hotel hotel
 * @property-read string check_in
 * @property-read string check_out
 * @property-read int adult_count
 * @property-read int|null child_count
 * @property-read array rooms
 * @property-read int|null discount
 */
class BookingInitRequest extends NolloApiRequest
{
    public function rules(): array
    {
        return [
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in', 'after:today'],
            'adult_count' => ['required', 'integer', 'min:1', 'max:30'],
            'child_count' => ['filled', 'integer', 'min:0', 'max:30'],
            'rooms' => ['required', 'array'],
            'rooms.*' => ['required', 'integer', Rule::exists((new Room())->getTable(), (new Room())->getKeyName())],
            'discount' => ['filled', 'integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'check_in' => 'Дата заезда',
            'check_out' => 'Дата выезда',
            'adult_count' => 'Взрослые',
            'child_count' => 'Дети',
            'rooms' => 'Номера',
            'discount' => 'Скидка',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function rulesPassed(): void
    {
        $countNights = BookingService::create()->countNights($this->check_in, $this->check_out);
        if ($countNights < $this->hotel->min_days) {
            throw ValidationException::withMessages([
                'min_days' => 'Минимальный срок бронирования ' . $this->hotel->min_days . '.',
            ]);
        }
        $guestCount = Room::query()->find($this->rooms)->sum('guest_count');
        if ($guestCount < ($this->adult_count + $this->child_count ?? 0)) {
            throw ValidationException::withMessages([
                'adult_count' => 'Общая вместимость выбранных номеров ' . $guestCount . '.',
                'child_count' => 'Общая вместимость выбранных номеров ' . $guestCount . '.',
            ]);
        }
    }
}
