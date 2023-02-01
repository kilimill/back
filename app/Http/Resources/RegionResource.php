<?php

namespace App\Http\Resources;

use App\Models\Region;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionResource extends JsonResource
{
    private function getResource(): Region
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        $region = $this->getResource();

        return [
            'id' => $region->getKey(),
            'name' => $region->name,
        ];
    }
}
