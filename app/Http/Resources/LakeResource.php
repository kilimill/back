<?php

namespace App\Http\Resources;

use App\Models\Lake;
use Illuminate\Http\Resources\Json\JsonResource;

class LakeResource extends JsonResource
{
    private function getResource(): Lake
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        $lake = $this->getResource();

        return [
            'id' => $lake->getKey(),
            'name' => $lake->name,
        ];
    }
}
