<?php

namespace App\Http\Resources;

use App\Models\Tag;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    private function getResource(): Tag
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        $tag = $this->getResource();

        return [
            'id' => $tag->getKey(),
            'name' => $tag->name,
            'icon' => $tag->icon,
        ];
    }
}
