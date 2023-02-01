<?php

namespace App\Http\Requests\Api\v1;

use App\Http\Requests\Api\NolloApiRequest;

/**
 * @property-read int|null page
 * @property-read int|null per_page
 */
class PaginatedRequest extends NolloApiRequest
{
    public function rules(): array
    {
        return [
            'page' => ['filled', 'integer'],
            'per_page' => ['filled', 'integer'],
        ];
    }
}
