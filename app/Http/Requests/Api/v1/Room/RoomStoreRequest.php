<?php

namespace App\Http\Requests\Api\v1\Room;

use App\Http\Requests\Api\NolloApiRequest;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Validation\Rule;

/**
 * @property-read Hotel $hotel
 */
class RoomStoreRequest extends NolloApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'description' => ['filled', 'string', 'min:5', 'max:2000'],
            'guest_count' => ['required', 'numeric', 'min:1', 'max:100'],
            'meals_id' => ['required', Rule::in(array_keys(Room::MEALS_IDS))],
            'price' => ['required', 'numeric', 'min:10', 'max:1000000'],
            'price_weekend' => ['required', 'numeric', 'min:10', 'max:1000000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Название',
            'description' => 'Описание',
            'guest_count' => 'Количество гостей',
            'meals_id' => 'Питание',
            'price' => 'Стоимость',
            'price_weekend' => 'Стоимость на выходные',
        ];
    }
}
