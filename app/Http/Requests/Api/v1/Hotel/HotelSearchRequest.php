<?php

namespace App\Http\Requests\Api\v1\Hotel;

use App\Http\Requests\Api\NolloApiRequest;
use App\Models\Room;
use Illuminate\Validation\Rule;

/**
 * @property-read string|null sort_field
 * @property-read string|null sort_direction
 * @property-read string location
 * @property-read int|null guest_count
 * @property-read string|null check_in
 * @property-read string|null check_out
 * @property-read int|null min_price
 * @property-read int|null max_price
 * @property-read int|null meals_id
 * @property-read array|null tags
 * @property-read int|null page
 * @property-read int|null per_page
 */
class HotelSearchRequest extends NolloApiRequest
{
    public function rules(): array
    {
        return [
            'sort_field' => ['filled', 'string'],
            'sort_direction' => ['filled', 'string', Rule::in(['asc', 'desc'])],
            'location' => ['required', 'string'],
            'city_id' => ['filled', 'integer', 'exists:cities,id'],
            'region_id' => ['filled', 'integer', 'exists:regions,id' ],
            'guest_count' => ['filled', 'integer'],
            'check_in' => ['filled', 'date', 'after_or_equal:today'],
            'check_out' => ['filled', 'date', 'after:check_in', 'after:today'],
            'min_price' => ['filled', 'numeric'],
            'max_price' => ['filled', 'numeric'],
            'meals_id' => ['filled', Rule::in(array_keys(Room::MEALS_IDS))],
            'tags' => ['filled', 'array'],
            'tags.*' => ['filled', 'integer'],
            'page' => ['filled', 'integer'],
            'per_page' => ['filled', 'integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'sort_field' => 'Поле сортировки',
            'sort_direction' => 'Направление сортировки',
            'location' => 'Местоположение',
            'guest_count' => 'Количество гостей',
            'check_in' => 'Дата заезда',
            'check_out' => 'Дата выезда',
            'min_price' => 'Минимальная стоимость',
            'max_price' => 'Максимальная стоимость',
            'meals_id' => 'Питание',
            'tags' => 'Теги',
            'page' => 'Страница',
        ];
    }
}
