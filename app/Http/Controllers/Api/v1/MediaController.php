<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Media\UpdateMainMediaRequest;
use App\Models\Hotel;
use App\Models\Media;
use Illuminate\Http\JsonResponse;

class MediaController extends Controller
{
    /**
     * TODO split this method to 2 (for room and hotel)
     * add validation request to check if media is a part of the hotel or room
     * same us in UpdateMainMediaRequest
     */
    public function remove(Hotel $hotel, Media $media): JsonResponse
    {
        $model = $media->model_type::find($media->model_id);
        $model->deleteMedia($media->getKey());

        return response()->json([
            'message' => 'Вы успешно удалили картинку.',
        ]);
    }

    public function addMain(UpdateMainMediaRequest $request, Hotel $hotel, Media $media): JsonResponse
    {
        $hotel->getMedia('media', ['preview' => true])->map(function (Media $file) {
            $file->setCustomProperty('preview', false);
            $file->save();
        });

        $media->setCustomProperty('preview', true);
        $media->save();

        return response()->json([
            'message' => 'Вы успешно отметили главную фотографию.',
        ]);
    }

    public function removeMain(UpdateMainMediaRequest $request, Hotel $hotel, Media $media): JsonResponse
    {
        $media->setCustomProperty('preview', false);
        $media->save();

        return response()->json([
            'message' => 'Вы успешно убрали отметку главная фотография.',
        ]);
    }
}
