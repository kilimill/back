<?php

namespace App\Http\Resources;

use App\Models\Country;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    private function getResource(): Country
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        $country = $this->getResource();

        return [
            'id' => $country->getKey(),
            'name' => $country->name,
        ];
    }
}
