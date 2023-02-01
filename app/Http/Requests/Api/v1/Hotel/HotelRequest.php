<?php

namespace App\Http\Requests\Api\v1\Hotel;

use App\Http\Requests\Api\NolloApiRequest;

/**
 * @property-read string location
 * @property-read int|null page
 * @property-read int|null per_page
 */
class HotelRequest extends NolloApiRequest
{
    public function rules(): array
    {
        return [
            'location' => ['required', 'string'],
            'page' => ['filled', 'integer'],
            'per_page' => ['filled', 'integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'location' => 'Местоположение',
            'page' => 'Страница',
        ];
    }
}
