<?php

namespace App\Http\Services;

use App\Models\Hotel;
use App\Models\Media;
use App\Models\Room;

class MediaService
{
    use ServiceInstance;

    public function getPreview(Hotel|Room $model): ?string
    {
        $media = $model->getMedia('media');

        $preview = $media->filter(function (Media $image) {
            return $image->getCustomProperty('preview');
        });

        if ($preview->isNotEmpty()) {
            return $preview->pluck('original_url')->first();
        }

        return $media->pluck('original_url')->first();
    }
}
