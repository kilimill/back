<?php

namespace App\Http\Resources;

use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    private function getResource(): Media
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        $media = $this->getResource();

        return [
            'id' => $media->getKey(),
            'url' => $media->getFullUrl(),
            'is_preview' => $media->getCustomProperty('preview') ?? false,
        ];
    }
}
