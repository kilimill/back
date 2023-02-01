<?php

namespace App\Nova;

use App\Models\ConfirmationCode as ConfirmationCodeModel;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class ConfirmationCode extends Resource
{
    public static string $model = ConfirmationCodeModel::class;

    public static $search = [
         'code',
    ];

    public static function label(): string
    {
        return 'Коды подтверждения';
    }

    public static function singularLabel(): string
    {
        return 'Код подтверждения';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make(),
            BelongsTo::make('Пользователь', 'user', User::class),
            Select::make('Статус', 'status_id')->options(ConfirmationCodeModel::STATUSES)->displayUsingLabels(),
            Select::make('Тип', 'type_id')->options(ConfirmationCodeModel::TYPES)->displayUsingLabels(),
            Text::make('Телефон', 'phone'),
            Number::make('Код', 'code'),
            Text::make('Сообщение', 'message'),
            Text::make('Ошибка', 'error'),
            DateTime::make('Дата', 'created_at'),
        ];
    }
}
