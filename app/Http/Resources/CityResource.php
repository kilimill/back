<?php

namespace App\Http\Resources;

use App\Models\City;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    private function getResource(): City
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        $city = $this->getResource();

        return [
            'id' => $city->getKey(),
            'name' => $city->name,
        ];
    }
}
