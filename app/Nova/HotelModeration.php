<?php

namespace App\Nova;

use App\Models\Hotel as HotelModel;
use App\Nova\Actions\HotelStatusUpdateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;

class HotelModeration extends Hotel
{
    public static function label(): string
    {
        return 'Отели на модерации';
    }

    public static function singularLabel(): string
    {
        return 'Отель на модерации';
    }

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return $query->where('status_id', \App\Models\Hotel::STATUS_ID_UNDER_REVIEW);
    }

    public function actions(Request $request): array
    {
        return [
            (new HotelStatusUpdateAction(HotelModel::STATUS_ID_ACTIVE, 'Одобрить отель'))
                ->canRun(function () {
                    return true;
                }),
            (new HotelStatusUpdateAction(HotelModel::STATUS_ID_REJECTED, 'Отклонить отель'))
                ->canRun(function () {
                    return true;
                }),
        ];
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }
}
