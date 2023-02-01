<?php

namespace App\Http\Requests\Api\v1\Media;

use App\Http\Requests\Api\NolloApiRequest;
use App\Models\Hotel;
use App\Models\Media;
use Illuminate\Validation\ValidationException;

/**
 * @property-read Hotel hotel
 * @property-read Media media
 */
class UpdateMainMediaRequest extends NolloApiRequest
{
    /**
     * @throws ValidationException
     */
    public function rulesPassed(): void
    {
        $image = $this->hotel->getMedia('media')->where('id', $this->media->getKey())->isNotEmpty();

        if (!$image) {
            throw ValidationException::withMessages([
                'media' => 'Картинка не найдена.',
            ]);
        }
    }
}
