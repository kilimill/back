<?php

namespace App\Http\Requests\Api\v1\Room;

use App\Http\Requests\Api\NolloApiRequest;

/**
 * @property-read string|null check_in
 * @property-read string|null check_out
 * @property-read int|null adult_count
 * @property-read int|null child_count
 */

class AvailableRoomRequest extends NolloApiRequest
{
    public function rules(): array
    {
        return [
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in', 'after:today'],
            'adult_count' => ['required', 'numeric', 'min:1', 'max:30'],
            'child_count' => ['filled', 'numeric', 'min:0', 'max:30'],
        ];
    }

    public function attributes(): array
    {
        return [
            'check_in' => 'Дата заезда',
            'check_out' => 'Дата выезда',
            'adult_count' => 'Количество взрослых',
            'child_count' => 'Количество детей',
        ];
    }
}
