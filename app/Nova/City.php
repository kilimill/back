<?php

namespace App\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class City extends Resource
{
    public static string $model = \App\Models\City::class;

    public static $title = 'name';

    public static $search = [
        'id', 'name',
    ];

    public static function label(): string
    {
        return 'Города';
    }

    public static function singularLabel(): string
    {
        return 'Город';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->onlyOnDetail(),

            Text::make('Наименование', 'name')
                ->rules('required', 'max:255')
                ->sortable()
                ->onlyOnIndex(),

            Text::make('Наименование', 'name')
                ->rules('required', 'max:255')
                ->hideFromIndex(),

            BelongsTo::make('Страна', 'country', Country::class)
                ->rules('required', 'max:255')
                ->hideFromIndex(),

            BelongsTo::make('Регион', 'region', Region::class)
                ->viewable(false)
                ->rules('required', 'max:255')
                ->sortable(),

            HasMany::make('Отели', 'hotels', Hotel::class),
        ];
    }
}
