<?php

namespace App\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Audit extends Resource
{
    public static string $model = \App\Models\Audit::class;

    public static $title = 'event';

    public static $search = [
        'id', 'name',
    ];

    public static function label(): string
    {
        return 'Журнал изменений';
    }

    public static function singularLabel(): string
    {
        return 'Журнал изменений';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Кто изменил', 'user', User::class)
                ->viewable(false)
                ->sortable()
                ->onlyOnIndex(),

            Text::make('Событие', 'event')
                ->sortable()
                ->onlyOnIndex(),

            Text::make('Тип', 'auditable_type')
                ->sortable()
                ->onlyOnIndex(),

            Text::make('Старое значение', 'old_values')
                ->sortable()
                ->onlyOnIndex(),

            Text::make('Новое значение', 'new_values')
                ->sortable()
                ->onlyOnIndex(),

            Text::make('ip address', 'ip_address')
                ->sortable()
                ->onlyOnIndex(),

            DateTime::make('Дата создания', 'created_at')
                ->onlyOnDetail(),

            DateTime::make('Дата изменения', 'updated_at')
                ->onlyOnDetail()
                ->showOnIndex()
                ->sortable(),
        ];
    }
}
